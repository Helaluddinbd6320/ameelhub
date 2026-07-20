<?php

namespace App\Filament\Admin\Resources\Workers\Tables;

use App\Models\Worker;
use App\Services\CvApprovalService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker as FormDatePicker;
use Filament\Forms\Components\Textarea as FormTextarea;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WorkerTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('workerUser'))
            ->recordUrl(null)      // 👈 রো ক্লিক করলে আর কোনো পেজে navigate করবে না
            ->recordAction(null)   // 👈 রো/কলামে ক্লিক করলে ডিফল্ট অ্যাকশন (EditAction) আর ট্রিগার হবে না
            ->columns([

                ImageColumn::make('photo')
                    ->label('ছবি')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(fn () => asset('images/default-avatar.png'))
                    ->size(48),

                TextColumn::make('wallet_balance')
                    ->label('ব্যালেন্স')
                    ->state(function (Worker $record) {
                        if (! $record->workerUser) {
                            return null;
                        }
                        return $record->workerUser->totalBalance();
                    })
                    ->formatStateUsing(fn ($state) => $state === null
                        ? '—'
                        : number_format($state, 2) . ' SAR')
                    ->color(fn ($state) => $state === null
                        ? 'gray'
                        : ($state > 0 ? 'success' : 'gray'))
                    ->weight('semibold')
                    ->tooltip(function (Worker $record) {
                        if (! $record->workerUser) {
                            return 'ওয়ালেট নেই — অ্যাকাউন্ট claim করেনি';
                        }
                        return sprintf(
                            'উত্তোলনযোগ্য: %s SAR · হোল্ড: %s SAR',
                            number_format($record->workerUser->available_balance, 2),
                            number_format($record->workerUser->held_balance, 2)
                        );
                    }),

                TextColumn::make('full_name_bn')
                    ->label('নাম')
                    ->searchable(['full_name_bn', 'full_name_en'])
                    ->sortable()
                    ->description(fn (Worker $r) => $r->full_name_en),

                TextColumn::make('skillCategory.name_bn')
                    ->label('পেশা')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('স্ট্যাটাস')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'pending'  => 'warning',
                        'active'   => 'success',
                        'inactive' => 'gray',
                        'hired'    => 'info',
                        'featured' => 'primary',
                        'rejected' => 'danger',
                        default    => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('iqama_expiry')
                    ->label('ইকামা মেয়াদ')
                    ->date('d M Y')
                    ->color(function (Worker $record): string {
                        if (! $record->iqama_expiry) return 'gray';
                        if ($record->iqama_expiry->isPast()) return 'danger';
                        if ($record->iqama_expiry->diffInDays(now()) <= 30) return 'warning';
                        return 'success';
                    })
                    ->sortable(),

                IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray'),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueColor('warning')
                    ->falseColor('gray'),

                TextColumn::make('featured_until')
                    ->label('Featured মেয়াদ')
                    ->date('d M Y')
                    ->placeholder('—')
                    ->color(fn (Worker $r) => $r->featured_until && $r->featured_until->isPast() ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('view_count')
                    ->label('ভিউ')
                    ->numeric()
                    ->sortable()
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
                        'inactive' => 'Inactive',
                        'hired'    => 'Hired',
                        'featured' => 'Featured',
                        'rejected' => 'Rejected',
                    ]),

                SelectFilter::make('skill_category_id')
                    ->label('পেশা')
                    ->relationship('skillCategory', 'name_bn'),

                SelectFilter::make('is_verified')
                    ->label('Verification')
                    ->options([
                        '1' => 'Verified ✓',
                        '0' => 'Not Verified',
                    ]),

                SelectFilter::make('is_in_saudi')
                    ->label('অবস্থান')
                    ->options([
                        '1' => 'সৌদিতে আছে',
                        '0' => 'বাংলাদেশে আছে',
                    ]),

            ])
            ->recordActions([   // ✅ v5 official: recordActions (not actions)

                EditAction::make()
                    ->label('এডিট'),

                // ── APPROVE ──
                Action::make('approve')
                    ->label('অনুমোদন')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Worker $r) => $r->status === 'pending')
                    ->requiresConfirmation()
                    ->modalHeading('CV অনুমোদন করুন')
                    ->modalDescription('এই Worker এর CV অনুমোদন করবেন?')
                    ->modalSubmitActionLabel('হ্যাঁ, অনুমোদন করুন')
                    ->action(fn (Worker $r) => app(CvApprovalService::class)
                        ->approve($r, auth()->user())),

                // ── REJECT ──
                Action::make('reject')
                    ->label('বাতিল')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Worker $r) => $r->status === 'pending')
                    ->form([
                        FormTextarea::make('rejection_reason')
                            ->label('বাতিলের কারণ (Worker দেখতে পাবে)')
                            ->required()
                            ->maxLength(1000)
                            ->rows(4),
                    ])
                    ->action(function (Worker $r, array $data) {
                        app(CvApprovalService::class)
                            ->reject($r, $data['rejection_reason'], auth()->user());
                    }),

                // ── FEATURE ──
                Action::make('feature')
                    ->label('Feature করুন')
                    ->icon('heroicon-o-star')
                    ->color('warning')
                    ->visible(fn (Worker $r) => $r->status === 'active' && ! $r->is_featured)
                    ->form([
                        FormDatePicker::make('featured_until')
                            ->label('কতদিন Featured থাকবে?')
                            ->required()
                            ->native(false)
                            ->minDate(now()->addDay()),
                    ])
                    ->action(function (Worker $r, array $data) {
                        $r->forceFill([
                            'is_featured'    => true,
                            'status'         => 'featured',
                            'featured_until' => $data['featured_until'],
                        ])->save();
                    }),

                // ── UNFEATURE ──
                Action::make('unfeature')
                    ->label('Unfeature করুন')
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (Worker $r) => $r->is_featured)
                    ->requiresConfirmation()
                    ->modalHeading('Featured বাতিল করুন')
                    ->modalDescription('এই CV আর Featured থাকবে না, Status Active এ ফিরে যাবে।')
                    ->modalSubmitActionLabel('হ্যাঁ, Unfeature করুন')
                    ->action(function (Worker $r) {
                        $r->forceFill([
                            'is_featured'    => false,
                            'status'         => 'active',
                            'featured_until' => null,
                        ])->save();
                    }),

                // ── DEACTIVATE ──
                Action::make('deactivate')
                    ->label('Deactivate')
                    ->icon('heroicon-o-pause-circle')
                    ->color('gray')
                    ->visible(fn (Worker $r) => in_array($r->status, ['active', 'featured']))
                    ->requiresConfirmation()
                    ->modalHeading('Deactivate করুন')
                    ->modalDescription('এই Worker এর CV inactive করবেন?')
                    ->action(fn (Worker $r) => $r->forceFill(['status' => 'inactive'])->save()),

            ])
            ->defaultSort('created_at', 'desc')
            ->poll('30s');
    }
}