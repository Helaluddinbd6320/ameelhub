<?php

namespace App\Filament\Admin\Resources\PaymentAccounts\Tables;

use App\Models\PaymentAccount;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentAccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_method')
                    ->label('মাধ্যম')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => PaymentAccount::methodLabels()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'bank'   => 'gray',
                        'bkash'  => 'success',
                        'nagad'  => 'danger',
                        'stcpay' => 'info',
                        default  => 'gray',
                    }),

                TextColumn::make('account_label')
                    ->label('লেবেল')
                    ->searchable(),

                TextColumn::make('account_holder_name')
                    ->label('Holder Name')
                    ->searchable(),

                TextColumn::make('account_number')
                    ->label('Account Number')
                    ->copyable()
                    ->searchable(),

                IconColumn::make('is_active')
                    ->label('সক্রিয়')
                    ->boolean(),

                TextColumn::make('sort_order')
                    ->label('ক্রম')
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                SelectFilter::make('payment_method')
                    ->label('মাধ্যম')
                    ->options(PaymentAccount::methodLabels()),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}