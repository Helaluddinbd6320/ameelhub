<?php

namespace App\Filament\Admin\Resources\PaymentAccounts;

use App\Filament\Admin\Resources\PaymentAccounts\Pages\CreatePaymentAccount;
use App\Filament\Admin\Resources\PaymentAccounts\Pages\EditPaymentAccount;
use App\Filament\Admin\Resources\PaymentAccounts\Pages\ListPaymentAccounts;
use App\Filament\Admin\Resources\PaymentAccounts\Schemas\PaymentAccountForm;
use App\Filament\Admin\Resources\PaymentAccounts\Tables\PaymentAccountsTable;
use App\Models\PaymentAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class PaymentAccountResource extends Resource
{
    protected static ?string $model = PaymentAccount::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    // NOTE: তোমাদের অন্যান্য Admin Resource-এ যদি translation helper (যেমন
    // __('messages.navigation.groups.settings')) ব্যবহার হয়ে থাকে, তাহলে
    // নিচের লিটারেল স্ট্রিং সেটা দিয়ে replace করে দিয়ো — আমার কাছে অন্য কোনো
    // Admin Resource ফাইলের নমুনা না থাকায় ব্লুপ্রিন্টের কনভেনশন অনুযায়ী লিখেছি।
    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 90;

    public static function form(Schema $schema): Schema
    {
        return PaymentAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentAccountsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListPaymentAccounts::route('/'),
            'create' => CreatePaymentAccount::route('/create'),
            'edit'   => EditPaymentAccount::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'পেমেন্ট অ্যাকাউন্ট';
    }

    public static function getModelLabel(): string
    {
        return 'পেমেন্ট অ্যাকাউন্ট';
    }

    public static function getPluralModelLabel(): string
    {
        return 'পেমেন্ট অ্যাকাউন্টসমূহ';
    }
}