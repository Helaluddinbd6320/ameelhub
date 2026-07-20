<?php

namespace App\Filament\Admin\Resources\AgentVerifications\Pages;

use App\Filament\Admin\Resources\AgentVerifications\AgentVerificationResource;
use Filament\Resources\Pages\EditRecord;

class EditAgentVerification extends EditRecord
{
    protected static string $resource = AgentVerificationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return null; // এই পেজে সরাসরি সেভ নেই, সব কাজ Table Actions থেকে হয়
    }

    // ফর্মটি শুধু তথ্য দেখানোর জন্য — save disable রাখা হলো
    protected function handleRecordUpdate($record, array $data): \Illuminate\Database\Eloquent\Model
    {
        return $record;
    }
}
