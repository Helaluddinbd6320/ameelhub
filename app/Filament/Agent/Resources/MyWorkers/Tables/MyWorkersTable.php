<?php

namespace App\Filament\Agent\Resources\MyWorkers\Tables;

use App\Models\Worker;
use App\Services\WalletService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyWorkersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            // PERFORMANCE FIX (Step 10.8): eager-load skillCategory to avoid
            // N+1 queries — skillCategory.name_en is rendered per-row below.
            ->modifyQueryUsing(fn ($query) => $query->with('skillCategory'))
            ->columns([
                ImageColumn::make('photo')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name_en ?? 'Worker')),

                TextColumn::make('full_name_en')
                    ->label('নাম')
                    ->description(fn (Worker $record) => $record->full_name_bn ?? '')
                    ->searchable(['full_name_en', 'full_name_bn'])
                    ->url(fn (Worker $record) => route('workers.show', $record->uuid))
                    ->color('primary')
                    ->openUrlInNewTab(),

                TextColumn::make('skillCategory.name_bn')
                    ->label('পেশা')
                    ->placeholder('—'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'draft'    => 'gray',
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'featured' => 'success',
                        'rejected' => 'danger',
                        default    => 'secondary',
                    }),

                TextColumn::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable()
                    ->wrap(),

                TextColumn::make('created_at')
                    ->label('তৈরির তারিখ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft'    => 'Draft',
                        'pending'  => 'Pending',
                        'active'   => 'Active',
                        'featured' => 'Featured',
                        'inactive' => 'Inactive',
                        'hired'    => 'Hired',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (Worker $record) => in_array($record->status, ['draft', 'rejected'], true)),

                // Resubmit — only for rejected CVs
                Action::make('resubmit')
                    ->label('আবার আবেদন করুন')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('CV পুনরায় জমা দিন')
                    ->modalDescription('Resubmit করলে আপনার Wallet থেকে ১০ SAR কাটা যাবে এবং CV Admin এর কাছে পাঠানো হবে।')
                    ->modalSubmitActionLabel('হ্যাঁ, জমা দিন')
                    ->visible(fn (Worker $record) => $record->status === 'rejected')
                    ->action(function (Worker $record) {
                        $agent   = auth()->user();
                        $cvFee   = (float) (\App\Models\Setting::where('key', 'cv_approval_fee')->value('value') ?? 10);

                        if ($agent->available_balance < $cvFee) {
                            Notification::make()
                                ->title('Wallet এ পর্যাপ্ত ব্যালেন্স নেই')
                                ->body("Wallet এ {$cvFee} SAR নেই। রিচার্জ করুন।")
                                ->danger()
                                ->send();

                            return;
                        }

                        DB::transaction(function () use ($record, $agent, $cvFee) {
                            // WalletService::deduct() handles lockForUpdate internally
                            app(WalletService::class)->deduct(
                                $agent,
                                $cvFee,
                                'cv_approval_fee',
                                Worker::class,
                                $record->id
                            );

                            // forceFill() required — status & approval_fee_charged are guarded
                            $record->forceFill([
                                'status'               => 'pending',
                                'rejection_reason'     => null,
                                'approval_fee_charged' => true,
                            ])->save();
                        });

                        Log::info('Agent resubmitted worker CV', [
                            'worker_id'       => $record->id,
                            'submitted_by_id' => $agent->id,
                        ]);

                        Notification::make()
                            ->title('CV পুনরায় জমা দেওয়া হয়েছে')
                            ->body('Admin শীঘ্রই রিভিউ করবে।')
                            ->success()
                            ->send();
                    }),

                DeleteAction::make()
                    ->visible(fn (Worker $record) => in_array($record->status, ['draft', 'rejected'], true)),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('কোনো Worker CV নেই')
            ->emptyStateDescription('নতুন Worker CV যোগ করতে উপরের বোতামে ক্লিক করুন।')
            ->emptyStateIcon('heroicon-o-identification');
    }
}