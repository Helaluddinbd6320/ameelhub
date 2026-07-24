<?php

namespace App\Filament\Admin\Resources\WithdrawalRequests\Tables;

use App\Services\WithdrawalService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class WithdrawalRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // PERFORMANCE FIX: eager-load user.worker, user.agentProfile, user.roles এবং processedBy
            ->modifyQueryUsing(fn ($query) => $query->with([
                'user.worker',
                'user.agentProfile',
                'user.roles',
                'processedBy',
            ]))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                // ১. প্রোফাইল ছবি (Worker হলে Worker photo, অন্যথায় User avatar)
                ImageColumn::make('user_photo')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->state(fn ($record) => $record->user?->worker?->photo ?? $record->user?->avatar)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name ?? 'User')),

                // ২. ইউজারের নাম ও ইমেইল (পাবলিক প্রোফাইল লিংকের জন্য সঠিক AgentProfile / Worker UUID)
                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn ($record) => $record->user->email ?? null)
                    ->searchable()
                    ->color('primary')
                    ->weight('bold')
                    ->url(function ($record) {
                        $user = $record->user;

                        if (! $user) {
                            return null;
                        }

                        // ইউজার Worker হলে Worker Public Profile (Worker-এর UUID দিয়ে)
                        if ($user->worker && !empty($user->worker->uuid)) {
                            return route('workers.show', ['worker' => $user->worker->uuid]);
                        }

                        // ইউজার Agent/Agency হলে AgentProfile-এর UUID দিয়ে
                        if ($user->agentProfile && !empty($user->agentProfile->uuid)) {
                            return route('agents.show', ['agentProfile' => $user->agentProfile->uuid]);
                        }

                        // Fallback: যদি agentProfile না থাকে কিন্তু সরাসরি User-এ UUID থাকে
                        if (!empty($user->uuid)) {
                            return route('agents.show', ['agentProfile' => $user->uuid]);
                        }

                        return null;
                    })
                    ->openUrlInNewTab(),

                // ৩. কন্ডিশনাল ইউজার টাইপ ব্যাজ (Worker নাকি Agent)
                TextColumn::make('user_type')
                    ->label('টাইপ')
                    ->badge()
                    ->state(function ($record) {
                        if ($record->user?->worker || $record->user?->hasRole('worker')) {
                            return 'Worker';
                        }
                        if ($record->user?->hasRole('agent') || $record->user?->hasRole('agency')) {
                            return 'Agent';
                        }
                        return 'User';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Worker' => 'info',
                        'Agent'  => 'warning',
                        default  => 'gray',
                    }),

                TextColumn::make('amount')
                    ->label('পরিমাণ (SAR)')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('মাধ্যম')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank'    => 'ব্যাংক',
                        'bkash'   => 'বিকাশ',
                        'nagad'   => 'নগদ',
                        'stcpay'  => 'STC Pay',
                        'cash'    => 'ক্যাশ',
                        default   => $state,
                    }),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'   => 'warning',
                        'completed' => 'success',
                        'rejected'  => 'danger',
                        'approved'  => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'   => 'অপেক্ষমান',
                        'approved'  => 'অনুমোদিত',
                        'completed' => 'সম্পন্ন',
                        'rejected'  => 'প্রত্যাখ্যাত',
                        default     => $state,
                    }),

                TextColumn::make('processedBy.name')
                    ->label('প্রসেসকারী')
                    ->placeholder('—'),

                TextColumn::make('created_at')
                    ->label('তারিখ')
                    ->dateTime('d M Y, h:i A')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('স্ট্যাটাস')
                    ->options([
                        'pending'   => 'অপেক্ষমান',
                        'completed' => 'সম্পন্ন',
                        'rejected'  => 'প্রত্যাখ্যাত',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('মাধ্যম')
                    ->options([
                        'bank'   => 'ব্যাংক',
                        'bkash'  => 'বিকাশ',
                        'nagad'  => 'নগদ',
                        'stcpay' => 'STC Pay',
                        'cash'   => 'ক্যাশ',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('অনুমোদন করুন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isPending()
                        && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    ->requiresConfirmation()
                    ->modalHeading('Withdrawal অনুমোদন করবেন?')
                    ->modalDescription('এই request অনুমোদন করলে ইউজারকে টাকা পাঠাতে হবে (manual transfer) এবং স্ট্যাটাস সম্পন্ন হিসেবে চিহ্নিত হবে।')
                    ->action(function ($record) {
                        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);

                        try {
                            app(WithdrawalService::class)->approve($record, Auth::user());

                            Notification::make()
                                ->title('Withdrawal request সম্পন্ন হয়েছে')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('ব্যর্থ হয়েছে')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('reject')
                    ->label('প্রত্যাখ্যান করুন')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->isPending()
                        && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    ->schema([
                        Textarea::make('reason')
                            ->label('প্রত্যাখ্যানের কারণ')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function ($record, array $data) {
                        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);

                        try {
                            app(WithdrawalService::class)->reject(
                                $record,
                                $data['reason'],
                                Auth::user()
                            );

                            Notification::make()
                                ->title('Withdrawal request প্রত্যাখ্যাত হয়েছে, টাকা ফেরত দেওয়া হয়েছে')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('ব্যর্থ হয়েছে')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }
}