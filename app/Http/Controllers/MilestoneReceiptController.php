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

        $isWorker = $deal->worker && $deal->worker->worker_user_id === $user->id;
        $isAgent  = $deal->agent_id === $user->id;
        $isAdmin  = in_array($user->role, ['super_admin', 'admin', 'staff'], true);

        abort_unless($isWorker || $isAgent || $isAdmin, 403, 'আপনি এই রশিদ দেখার অনুমতিপ্রাপ্ত নন।');

        abort_unless($milestone->receipt_path && Storage::disk('private_docs')->exists($milestone->receipt_path), 404, 'রশিদ পাওয়া যায়নি।');

        return Storage::disk('private_docs')->download(
            $milestone->receipt_path,
            "milestone-{$milestone->milestone_number}-receipt-{$deal->uuid}.pdf"
        );
    }
}