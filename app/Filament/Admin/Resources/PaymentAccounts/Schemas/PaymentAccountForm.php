<?php

namespace App\Filament\Admin\Resources\PaymentAccounts\Schemas;

use App\Models\PaymentAccount;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account তথ্য')
                ->columns(2)
                ->schema([
                    Select::make('payment_method')
                        ->label('মাধ্যম')
                        ->options(PaymentAccount::methodLabels())
                        ->required()
                        ->native(false),

                    TextInput::make('account_label')
                        ->label('Account লেবেল (Admin-এর জন্য)')
                        ->helperText('যেমন: "Chapai Main bKash" — শুধু Admin দেখবে')
                        ->required()
                        ->stripTags()
                        ->maxLength(100),

                    TextInput::make('account_holder_name')
                        ->label('Account Holder Name')
                        ->required()
                        ->stripTags()
                        ->maxLength(150),

                    TextInput::make('account_number')
                        ->label('Account Number')
                        ->required()
                        ->stripTags()
                        ->maxLength(100),

                    TextInput::make('bank_name')
                        ->label('ব্যাংকের নাম')
                        ->stripTags()
                        ->maxLength(100)
                        ->visible(fn ($get) => $get('payment_method') === 'bank'),

                    TextInput::make('branch_name')
                        ->label('শাখার নাম')
                        ->stripTags()
                        ->maxLength(100)
                        ->visible(fn ($get) => $get('payment_method') === 'bank'),

                    TextInput::make('routing_or_iban')
                        ->label('Routing / IBAN')
                        ->stripTags()
                        ->maxLength(100)
                        ->visible(fn ($get) => $get('payment_method') === 'bank'),
                ]),

            Section::make('অতিরিক্ত নির্দেশনা')
                ->schema([
                    Textarea::make('instructions_bn')
                        ->label('বিশেষ নির্দেশনা (Worker/Agent দেখবে)')
                        ->helperText('যেমন: "Send Money অপশনে পাঠান, Cash In/Payment না"')
                        ->stripTags()
                        ->rows(3)
                        ->maxLength(500),
                ]),

            Grid::make(2)->schema([
                Toggle::make('is_active')
                    ->label('সক্রিয়')
                    ->default(true),

                TextInput::make('sort_order')
                    ->label('ক্রম')
                    ->numeric()
                    ->default(0),
            ]),
        ]);
    }
}