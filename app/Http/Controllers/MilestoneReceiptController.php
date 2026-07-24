<?php

namespace App\Http\Controllers;

use App\Models\JobDealMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MilestoneReceiptController extends Controller
{
    public function download(Request $request, JobDealMilestone $milestone): StreamedResponse
    {
        $milestone->loadMissing('deal.worker', 'deal');
        $deal = $milestone->deal;
        $user = $request->user();

        // BUG FIX (Helal-reported, Step 10.9 audit): strict `===` is a PDO
        // string/int type-mismatch gotcha — MySQL values can come back
        // through PDO as strings while $user->id is an int. Same bug class
        // already fixed in JobInterests.php / JobSelectionService.php /
        // JobDetail.php etc. Casting both sides to int makes it type-safe.
        $isWorker = $deal->worker && (int) $deal->worker->worker_user_id === (int) $user->id;
        $isAgent  = (int) $deal->agent_id === (int) $user->id;
        $isAdmin  = in_array($user->role, ['super_admin', 'admin', 'staff'], true);

        abort_unless($isWorker || $isAgent || $isAdmin, 403, 'আপনি এই রশিদ দেখার অনুমতিপ্রাপ্ত নন।');

        abort_unless($milestone->receipt_path && Storage::disk('private_docs')->exists($milestone->receipt_path), 404, 'রশিদ পাওয়া যায়নি।');

        return Storage::disk('private_docs')->download(
            $milestone->receipt_path,
            "milestone-{$milestone->milestone_number}-receipt-{$deal->uuid}.pdf"
        );
    }
}