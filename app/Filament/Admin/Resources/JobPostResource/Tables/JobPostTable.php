<?php

namespace App\Filament\Admin\Resources\JobPostResource\Tables;

use App\Models\JobPost;
use App\Services\NotificationService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class JobPostTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('job_title')
                    ->label('জব টাইটেল')
                    ->searchable()
                    ->sortable()
                    ->description(fn(JobPost $r) => $r->employer_name),

                TextColumn::make('employer_city')
                    ->label('শহর')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('skillCategory.name_bn')
                    ->label('পেশা')
                    ->badge()
                    ->color('info'),

                TextColumn::make('vacancies')
                    ->label('ভ্যাকেন্সি')
                    ->formatStateUsing(fn(JobPost $r) => "{$r->filled_count} / {$r->vacancies}")
                    ->sortable(),

                TextColumn::make('salary_sar')
                    ->label('বেতন')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'paused'   => 'info',
                        'filled'   => 'primary',
                        'closed'   => 'gray',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('মেয়াদ শেষ')
                    ->date('d M Y')
                    ->color(fn(JobPost $r) => $r->expires_at && $r->expires_at->isPast() ? 'danger' : 'gray')
                    ->sortable(),

                TextColumn::make('view_count')
                    ->label('ভিউ')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('postedBy.name')
                    ->label('পোস্টকারী')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

            ])
            ->filters([

                SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'draft'    => 'Draft',
                        'pending'  => 'Pending',
                        'active'   => 'Active',
                        'paused'   => 'Paused',
                        'closed'   => 'Closed',
                        'filled'   => 'Filled',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('employer_type')
                    ->label('নিয়োগকর্তার ধরন')
                    ->options([
                        'restaurant' => 'Restaurant',
                        'hotel'      => 'Hotel',
                        'factory'    => 'Factory',
                        'house'      => 'House',
                        'company'    => 'Company',
                        'other'      => 'Other',
                    ]),

                SelectFilter::make('skill_category_id')
                    ->label('পেশা')
                    ->relationship('skillCategory', 'name_bn'),

            ])
            ->recordActions([

                ViewAction::make(),
                EditAction::make(),

                // ── APPROVE ──
                Action::make('approve')
                    ->label('অনুমোদন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(JobPost $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('জব পোস্ট অনুমোদন করুন')
                    ->modalDescription('এই জব পোস্ট Public এ Active দেখানো হবে?')
                    ->modalSubmitActionLabel('হ্যাঁ, অনুমোদন করুন')
                    ->action(function (JobPost $r) {
                        $r->forceFill([
                            'status'         => 'active',
                            'approved_by_id' => auth()->id(),
                            'approved_at'    => now(),
                        ])->save();

                        app(NotificationService::class)->jobApproved($r->fresh());
                    }),

                // ── REJECT ──
                Action::make('reject')
                    ->label('বাতিল')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(JobPost $r) => $r->status === 'pending')
                    ->form([
                        FormTextarea::make('close_reason')
                            ->label('বাতিলের কারণ (Agent দেখতে পাবে)')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->action(function (JobPost $r, array $data) {
                        $r->forceFill([
                            'status'       => 'rejected',
                            'close_reason' => $data['close_reason'],
                        ])->save();

                        app(NotificationService::class)->jobRejected($r->fresh(), $data['close_reason']);
                    }),

                // ── CLOSE (Admin override) ──
                // NOTE: Section 16-তে এই ম্যানুয়াল Admin "Close" এর জন্য আলাদা কোনো
                // notification event নেই (শুধু job_auto_closed আছে, যেটা Scheduler
                // কর্তৃক expiry-তে auto-close হলে fire হয় — JobLifecycleService দেখুন)।
                // তাই ইচ্ছাকৃতভাবে এখানে কোনো NotificationService কল যোগ করা হয়নি।
                // চাইলে একটা আলাদা job_closed_by_admin ইভেন্ট বানিয়ে দিতে পারি।
                Action::make('close')
                    ->label('বন্ধ করুন')
                    ->icon('heroicon-o-lock-closed')
                    ->color('gray')
                    ->visible(fn(JobPost $r) => in_array($r->status, ['active', 'paused'], true))
                    ->requiresConfirmation()
                    ->form([
                        FormTextarea::make('close_reason')
                            ->label('বন্ধের কারণ')
                            ->maxLength(500)
                            ->rows(3),
                    ])
                    ->action(function (JobPost $r, array $data) {
                        $r->forceFill([
                            'status'       => 'closed',
                            'closed_by_id' => auth()->id(),
                            'closed_at'    => now(),
                            'close_reason' => $data['close_reason'] ?? null,
                        ])->save();
                    }),

            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}