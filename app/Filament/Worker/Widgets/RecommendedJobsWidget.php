<?php

namespace App\Filament\Worker\Widgets;

use App\Models\Worker;
use App\Services\JobMatchService;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

/**
 * Step 11.3 — AI Job Match (Worker Panel side).
 *
 * "আপনার জন্য সেরা Job" — Dashboard widget, top 5 active/visible jobs
 * scored against this worker's CV via JobMatchService. Auto-discovered
 * by WorkerPanelProvider's existing ->discoverWidgets() call, so it
 * appears on the default Dashboard page without any Provider changes.
 */
class RecommendedJobsWidget extends Widget
{
    protected string $view = 'filament.worker.widgets.recommended-jobs-widget';

    protected int | string | array $columnSpan = 'full';

    protected static ?int $sort = 3;

    /**
     * @return array<int, array{job: \App\Models\JobPost, score: array}>
     */
    public function getRecommendedJobs(): array
    {
        $worker = Worker::where('worker_user_id', Auth::id())->first();

        // No CV yet, or CV has no skill set — scoring wouldn't be meaningful.
        if (! $worker || ! $worker->skill_category_id) {
            return [];
        }

        return app(JobMatchService::class)->recommendedJobsForWorker($worker, limit: 5);
    }
}