<?php

namespace App\Filament\Admin\Resources\RechargeRequests\Tables;

use App\Filament\Admin\Resources\AgentResource; // 👈 আপনার Agent Resource class
use App\Filament\Admin\Resources\WorkerResource; // 👈 আপনার Worker Resource class
use App\Services\RechargeService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RechargeRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['user.worker', 'user.roles', 'processedBy']))
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                ImageColumn::make('user_photo')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->state(fn ($record) => $record->user?->worker?->photo ?? $record->user?->avatar)
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->user->name ?? 'User')),

                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn ($record) => $record->user->email ?? null)
                    ->searchable()
                    ->color('primary')
                    ->weight('bold')
                    ->url(function ($record) {
                        $user = $record->user;
                        if (! $user) return null;

                        try {
                            // ১. ইউজার Worker হলে Worker Resource-এর Edit/View পেজে নিয়ে যাবে
                            if ($user->worker) {
                                return WorkerResource::getUrl('edit', ['record' => $user->worker->id]);
                            }

                            // ২. ইউজার Agent/Agency হলে Agent Resource-এ নিয়ে যাবে
                            if (class_exists(AgentResource::class)) {
                                return AgentResource::getUrl('edit', ['record' => $user->id]);
                            }

                            // ৩. Fallback: যদি ফ্রন্টএন্ড ওয়েবসাইটের পাবলিক প্রোফাইল লিংকে পাঠাতে চান
                            $identifier = $user->uuid ?? $user->id;
                            return route('agents.show', $identifier);

                        } catch (\Throwable $e) {
                            return null; // কোনো কারণে রাউট না মিললে পেজ ক্র্যাশ করবে না
                        }
                    })
                    ->openUrlInNewTab(),

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

                TextColumn::make('reference_number')
                    ->label('রেফারেন্স নম্বর')
                    ->placeholder('—')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending'  => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending'  => 'অপেক্ষমান',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'প্রত্যাখ্যাত',
                        default    => $state,
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
                        'pending'  => 'অপেক্ষমান',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'প্রত্যাখ্যাত',
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
                Action::make('view_proof')
                    ->label('প্রুফ দেখুন')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record->proof_file)
                        && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    ->action(function ($record) {
                        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
                        abort_unless(
                            Storage::disk('private_docs')->exists($record->proof_file),
                            404
                        );

                        return response()->streamDownload(
                            function () use ($record) {
                                echo Storage::disk('private_docs')->get($record->proof_file);
                            },
                            basename($record->proof_file)
                        );
                    }),

                Action::make('approve')
                    ->label('অনুমোদন করুন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->isPending()
                        && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    ->requiresConfirmation()
                    ->modalHeading('Recharge অনুমোদন করবেন?')
                    ->modalDescription('ব্যাংক/বিকাশ স্টেটমেন্টে টাকা পাওয়া গেছে নিশ্চিত হওয়ার পরই অনুমোদন করুন। এটি সাথে সাথে ইউজারের wallet এ যোগ হবে।')
                    ->action(function ($record) {
                        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);

                        try {
                            app(RechargeService::class)->approve($record, Auth::user());

                            Notification::make()
                                ->title('Recharge অনুমোদিত ও wallet এ যোগ হয়েছে')
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
                            app(RechargeService::class)->reject(
                                $record,
                                $data['reason'],
                                Auth::user()
                            );

                            Notification::make()
                                ->title('Recharge request প্রত্যাখ্যাত হয়েছে')
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