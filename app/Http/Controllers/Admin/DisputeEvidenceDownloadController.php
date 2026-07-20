<?php

namespace App\Http\Controllers\Admin;

use App\Models\DisputeEvidence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DisputeEvidenceDownloadController
{
    /**
     * Dispute Evidence ফাইল download — শুধুমাত্র signed URL + super_admin/admin অ্যাক্সেস।
     * private_docs disk থেকে সরাসরি stream করা হয় (publicly symlinked নয়)।
     */
    public function __invoke(Request $request, DisputeEvidence $evidence): StreamedResponse
    {
        abort_unless($request->hasValidSignature(), 403, 'লিংকের মেয়াদ শেষ হয়ে গেছে, পেজ রিফ্রেশ করে আবার চেষ্টা করুন।');

        $user = $request->user();

        abort_unless(
            $user && $user->hasAnyRole(['super_admin', 'admin']),
            403,
            'এই ফাইল দেখার অনুমতি আপনার নেই।'
        );

        abort_unless(
            Storage::disk('private_docs')->exists($evidence->file_path),
            404,
            'ফাইলটি খুঁজে পাওয়া যায়নি।'
        );

        $extension = pathinfo($evidence->file_path, PATHINFO_EXTENSION) ?: 'bin';

        return Storage::disk('private_docs')->download(
            $evidence->file_path,
            "evidence-{$evidence->id}.{$extension}"
        );
    }
}