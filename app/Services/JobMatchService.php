<?php

namespace App\Services;

use App\Models\JobPost;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Step 11.3 — AI Job Match (rule-based, no external API/cost).
 *
 * Score = skill(40%) + location(20%) + salary(20%) + visa(20%), each
 * sub-score already expressed in its final weighted points (so they sum
 * to 100 directly — no extra multiplication needed by callers).
 *
 * Two usage patterns:
 *  - score()                 → precise PHP calculation for a single
 *                              Worker/JobPost pair (used for the visible
 *                              percentage + tooltip breakdown).
 *  - orderByMatchScore()     → an equivalent approximation expressed as
 *                              a SQL CASE expression, so a paginated
 *                              Filament table query can be ORDER BY'd at
 *                              the database level without loading every
 *                              row into PHP first.
 *
 * Both must be kept in sync if the weighting ever changes.
 */
class JobMatchService
{
    /** Visa statuses that mean the worker is immediately available for a fresh job. */
    private const VISA_READY = ['free_exit', 'final_exit', 'new_visa', 'not_in_saudi'];

    /**
     * @return array{skill:int, location:int, salary:int, visa:int, total:int}
     */
    public function score(Worker $worker, JobPost $job): array
    {
        $skill    = $this->skillPoints($worker, $job);
        $location = $this->locationPoints($worker, $job);
        $salary   = $this->salaryPoints($worker, $job);
        $visa     = $this->visaPoints($worker);

        return [
            'skill'    => $skill,
            'location' => $location,
            'salary'   => $salary,
            'visa'     => $visa,
            'total'    => $skill + $location + $salary + $visa,
        ];
    }

    /** @return string one of: success, warning, gray */
    public function color(int $total): string
    {
        return match (true) {
            $total >= 75 => 'success',
            $total >= 50 => 'warning',
            default      => 'gray',
        };
    }

    public function label(int $total): string
    {
        return match (true) {
            $total >= 75 => 'চমৎকার মিল',
            $total >= 50 => 'ভালো মিল',
            $total >= 25 => 'সাধারণ মিল',
            default      => 'কম মিল',
        };
    }

    // ─── Worker → Jobs (Worker Panel dashboard widget) ──────────────

    /**
     * Top-N active, visible jobs for this worker, scored and sorted best-first.
     *
     * @return array<int, array{job: JobPost, score: array}>
     */
    public function recommendedJobsForWorker(Worker $worker, int $limit = 5): array
    {
        $jobs = JobPost::query()
            ->where('status', 'active')
            ->whereColumn('filled_count', '<', 'vacancies')
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhereDate('expires_at', '>=', now()->toDateString());
            })
            ->when($worker->skill_category_id, fn (Builder $q) => $q->orderByRaw(
                'skill_category_id = ? DESC',
                [$worker->skill_category_id]
            ))
            ->latest('id')
            ->limit(300) // safety cap so we never load the whole table into PHP
            ->get();

        return $jobs
            ->map(fn (JobPost $job) => ['job' => $job, 'score' => $this->score($worker, $job)])
            ->sortByDesc(fn (array $row) => $row['score']['total'])
            ->take($limit)
            ->values()
            ->all();
    }

    // ─── Job → Workers (Agent's BrowseWorkers page) ─────────────────

    /**
     * Applies an approximate match-score ORDER BY directly in SQL so the
     * Filament table's native pagination still works correctly at the
     * database level (no need to fetch every worker into PHP first).
     */
    public function orderByMatchScore(Builder $query, JobPost $job): Builder
    {
        $skillId      = (int) $job->skill_category_id;
        $employerCity = mb_strtolower(trim((string) $job->employer_city));
        $salary       = (float) $job->salary_sar;

        return $query->orderByRaw(
            '(
                CASE WHEN skill_category_id = ? THEN 40 ELSE 0 END
                +
                CASE
                    WHEN LOWER(TRIM(present_location_city)) = ? THEN 20
                    WHEN is_in_saudi = 1 THEN 12
                    ELSE 6
                END
                +
                CASE
                    WHEN expected_salary_sar IS NULL THEN 14
                    WHEN expected_salary_sar <= 0 THEN 14
                    WHEN expected_salary_sar <= ? THEN 20
                    ELSE GREATEST(0, LEAST(20, ROUND(20 * ? / expected_salary_sar)))
                END
                +
                CASE
                    WHEN visa_status IN (\'free_exit\',\'final_exit\',\'new_visa\',\'not_in_saudi\') THEN 18
                    WHEN visa_status = \'iqama\' AND transfer_possible = 1 THEN 20
                    WHEN visa_status = \'iqama\' THEN 4
                    WHEN visa_status = \'visit\' THEN 10
                    ELSE 10
                END
            ) DESC',
            [$skillId, $employerCity, $salary, $salary]
        );
    }

    // ─── Sub-score calculators (PHP — precise, used for display) ────

    private function skillPoints(Worker $worker, JobPost $job): int
    {
        if (! $worker->skill_category_id || ! $job->skill_category_id) {
            return 0;
        }

        return $worker->skill_category_id === $job->skill_category_id ? 40 : 0;
    }

    private function locationPoints(Worker $worker, JobPost $job): int
    {
        $workerCity = mb_strtolower(trim((string) $worker->present_location_city));
        $jobCity    = mb_strtolower(trim((string) $job->employer_city));

        if ($workerCity !== '' && $jobCity !== '' && $workerCity === $jobCity) {
            return 20;
        }

        return $worker->is_in_saudi ? 12 : 6;
    }

    private function salaryPoints(Worker $worker, JobPost $job): int
    {
        $expected = $worker->expected_salary_sar !== null ? (float) $worker->expected_salary_sar : null;
        $offered  = (float) $job->salary_sar;

        if ($expected === null || $expected <= 0) {
            return 14; // no stated expectation — neutral-ish default
        }

        if ($offered >= $expected) {
            return 20;
        }

        $ratio = $offered / $expected;

        return (int) max(0, min(20, round(20 * $ratio)));
    }

    private function visaPoints(Worker $worker): int
    {
        $status = $worker->visa_status;

        if (in_array($status, self::VISA_READY, true)) {
            return 18;
        }

        if ($status === 'iqama') {
            return $worker->transfer_possible ? 20 : 4;
        }

        if ($status === 'visit') {
            return 10;
        }

        return 10; // unknown/null visa_status
    }
}