<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Pages;

use App\Filament\Agent\Resources\MyJobPosts\MyJobPostsResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMyJobPosts extends CreateRecord
{
    protected static string $resource = MyJobPostsResource::class;

    // NOTE (Step 10.9 audit): email-verification gate deliberately NOT
    // placed here — this page only creates a 'draft' (agent can still
    // fill in a job post before verifying, same as Worker CV drafts).
    // The actual gate lives on the "submit" (অনুমোদনের জন্য পাঠান) action
    // in MyJobPostsTable.php, since that's the real "submit for review"
    // moment — consistent with how CvApprovalService::deductFee() gates
    // the CV flow at actual submission, not at initial draft creation.
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uuid']         = (string) str()->uuid();
        $data['posted_by_id'] = auth()->id();
        $data['status']       = 'draft';

        return $data;
    }
}