<?php

namespace App\Filament\Admin\Resources\RechargeRequests\Tables;

use App\Services\RechargeService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
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
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->description(fn ($record) => $record->user->email ?? null)
                    ->searchable(),

                TextColumn::make('amount')
                    ->label('পরিমাণ (SAR)')
                    ->money('SAR')
                    ->sortable(),

                TextColumn::make('payment_method')
                    ->label('মাধ্যম')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'bank' => 'ব্যাংক',
                        'bkash' => 'বিকাশ',
                        'nagad' => 'নগদ',
                        'stcpay' => 'STC Pay',
                        'cash' => 'ক্যাশ',
                        default => $state,
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
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'অপেক্ষমান',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'প্রত্যাখ্যাত',
                        default => $state,
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
                        'pending' => 'অপেক্ষমান',
                        'approved' => 'অনুমোদিত',
                        'rejected' => 'প্রত্যাখ্যাত',
                    ]),
                SelectFilter::make('payment_method')
                    ->label('মাধ্যম')
                    ->options([
                        'bank' => 'ব্যাংক',
                        'bkash' => 'বিকাশ',
                        'nagad' => 'নগদ',
                        'stcpay' => 'STC Pay',
                        'cash' => 'ক্যাশ',
                    ]),
            ])
            ->recordActions([
                // SECURITY (consistent with 10.7d AgentVerificationTable fix):
                // Proof file is on the private_docs disk, not publicly
                // symlinked. Only super_admin/admin may view/download it —
                // ->visible() (UI) + abort_unless() (defense-in-depth).
                Action::make('view_proof')
                    ->label('প্রুফ দেখুন')
                    ->icon('heroicon-o-photo')
                    ->color('gray')
                    ->visible(fn ($record) => filled($record->proof_file)
                        && auth()->user()->hasAnyRole(['super_admin', 'admin']))
                    ->url(fn ($record) => filled($record->proof_file)
                        ? Storage::disk('private_docs')->temporaryUrl(
                            $record->proof_file,
                            now()->addMinutes(5)
                        )
                        : null)
                    ->openUrlInNewTab()
                    ->action(function ($record) {
                        abort_unless(auth()->user()->hasAnyRole(['super_admin', 'admin']), 403);
                    }),

                Action::make('approve')
                    ->label('অনুমোদন করুন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // Same Option B gating as WithdrawalRequestsTable::approve —
                    // super_admin/admin only, staff excluded (this moves real
                    // money INTO the wallet based on a manually-verified proof).
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