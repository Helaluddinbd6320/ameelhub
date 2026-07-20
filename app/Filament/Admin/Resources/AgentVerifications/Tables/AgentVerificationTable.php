<?php

namespace App\Filament\Admin\Resources\AgentVerifications\Tables;

use App\Models\JobDeal;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class AgentVerificationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query
                ->whereNotNull('passport_copy')
                ->whereNotNull('nid_copy')
                ->withCount(['dealsAsAgent as successful_deals_count' => function ($q) {
                    $q->where('status', 'completed');
                }])
                ->addSelect([
                    'workers_placed_count' => JobDeal::selectRaw('COUNT(DISTINCT worker_id)')
                        ->whereColumn('agent_id', 'agent_profiles.user_id')
                        ->where('status', 'completed'),
                ])
            )
            ->columns([
                TextColumn::make('agent_name_bn')
                    ->label('নাম')
                    ->description(fn ($record) => $record->agent_name_en)
                    ->searchable(['agent_name_bn', 'agent_name_en'])
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label('কোম্পানি')
                    ->searchable()
                    ->toggleable()
                    ->placeholder('—'),

                TextColumn::make('city')
                    ->label('শহর')
                    ->toggleable(),

                TextColumn::make('user.email')
                    ->label('ইমেইল')
                    ->searchable()
                    ->toggleable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('successful_deals_count')
                    ->label('সফল ডিল')
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('workers_placed_count')
                    ->label('কর্মী প্লেসড')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('verified_at')
                    ->label('ভেরিফাই তারিখ')
                    ->dateTime('d M Y')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('আবেদনের তারিখ')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_verified')
                    ->label('স্ট্যাটাস')
                    ->placeholder('সব')
                    ->trueLabel('Verified')
                    ->falseLabel('Pending'),
            ])
            ->recordActions([
                Action::make('download_passport')
                    ->label('পাসপোর্ট')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    // SECURITY FIX (Step 10.7d audit):
                    // 1) Disk name corrected: 'private' → 'private_docs' (the
                    //    actual private disk used everywhere else in this
                    //    project for sensitive documents — storage/app/private/docs).
                    //    'private' either doesn't exist in config/filesystems.php
                    //    (would throw) or could be misconfigured to a
                    //    publicly-readable disk, which would leak passport scans.
                    // 2) Role gate added: Blueprint Section 4 states "Only
                    //    super_admin/admin can download documents" — staff
                    //    must NOT see this action, even though staff can
                    //    access the Admin panel itself (canAccessPanel()).
                    ->visible(fn ($record) => auth()->user()->hasAnyRole(['super_admin', 'admin'])
                        && filled($record->passport_copy)
                        && Storage::disk('private_docs')->exists($record->passport_copy))
                    ->action(fn ($record) => Storage::disk('private_docs')->download(
                        $record->passport_copy,
                        'passport-' . $record->id . '.' . pathinfo($record->passport_copy, PATHINFO_EXTENSION)
                    )),

                Action::make('download_nid')
                    ->label('NID')
                    ->icon('heroicon-o-identification')
                    ->color('gray')
                    // SECURITY FIX (Step 10.7d audit): same disk-name + role-gate fix as download_passport above.
                    ->visible(fn ($record) => auth()->user()->hasAnyRole(['super_admin', 'admin'])
                        && filled($record->nid_copy)
                        && Storage::disk('private_docs')->exists($record->nid_copy))
                    ->action(fn ($record) => Storage::disk('private_docs')->download(
                        $record->nid_copy,
                        'nid-' . $record->id . '.' . pathinfo($record->nid_copy, PATHINFO_EXTENSION)
                    )),

                Action::make('verify')
                    ->label('Verify করুন')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('এই এজেন্টকে Verify করবেন?')
                    ->modalDescription('Verify হলে এজেন্ট Job Post করতে পারবেন।')
                    ->visible(fn ($record) => ! $record->is_verified)
                    ->action(function ($record) {
                        $record->forceFill([
                            'is_verified' => true,
                            'verified_by_id' => auth()->id(),
                            'verified_at' => now(),
                            'verification_notes' => null,
                        ])->save();

                        // AgentProfile-এর সাথে সম্পর্কিত User (agent_profiles.user_id → users.id)
                        $user = $record->user;
                        if ($user) {
                            app(NotificationService::class)->agentVerified($user);
                        }
                    })
                    ->successNotificationTitle('এজেন্ট ভেরিফাই হয়েছে'),

                Action::make('reject')
                    ->label('Reject করুন')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('reason')
                            ->label('Reject করার কারণ')
                            ->required()
                            ->rows(3),
                    ])
                    ->visible(fn ($record) => $record->passport_copy || $record->nid_copy)
                    ->action(function ($record, array $data) {
                        $record->forceFill([
                            'is_verified' => false,
                            'verified_by_id' => auth()->id(),
                            'verified_at' => now(),
                            'verification_notes' => $data['reason'],
                        ])->save();

                        $user = $record->user;
                        if ($user) {
                            app(NotificationService::class)->agentRejected($user, $data['reason']);
                        }
                    })
                    ->successNotificationTitle('এজেন্টকে Reject করা হয়েছে'),

                // NOTE: "Un-verify" এর জন্য Section 16-তে আলাদা কোনো notification
                // event নেই (agent_verified/agent_rejected শুধু প্রাথমিক verification
                // decision-এর জন্য) — তাই এখানে ইচ্ছাকৃতভাবে কোনো notify যোগ করা হয়নি।
                Action::make('unverify')
                    ->label('Un-verify')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->is_verified)
                    ->action(function ($record) {
                        $record->forceFill([
                            'is_verified' => false,
                            'verified_by_id' => auth()->id(),
                            'verified_at' => now(),
                        ])->save();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}