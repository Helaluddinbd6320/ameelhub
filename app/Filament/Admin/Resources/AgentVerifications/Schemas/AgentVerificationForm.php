<?php

namespace App\Filament\Admin\Resources\AgentVerifications\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Storage;

class AgentVerificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Agent Verification Tabs')
                ->tabs([

                    Tab::make('প্রোফাইল তথ্য')
                        ->schema([
                            Grid::make(2)->schema([
                                Placeholder::make('agent_name_bn')
                                    ->label('নাম (বাংলা)')
                                    ->content(fn ($record) => $record?->agent_name_bn ?? '—'),

                                Placeholder::make('agent_name_en')
                                    ->label('Name (English)')
                                    ->content(fn ($record) => $record?->agent_name_en ?? '—'),

                                Placeholder::make('company_name')
                                    ->label('কোম্পানির নাম')
                                    ->content(fn ($record) => $record?->company_name ?? '—'),

                                Placeholder::make('company_type')
                                    ->label('কোম্পানির ধরন')
                                    ->content(fn ($record) => $record?->company_type?->value ?? $record?->company_type ?? '—'),

                                Placeholder::make('office_address')
                                    ->label('অফিসের ঠিকানা')
                                    ->content(fn ($record) => $record?->office_address ?? '—')
                                    ->columnSpan(2),

                                Placeholder::make('city')
                                    ->label('শহর')
                                    ->content(fn ($record) => $record?->city ?? '—'),

                                Placeholder::make('country')
                                    ->label('দেশ')
                                    ->content(fn ($record) => $record?->country ?? '—'),

                                Placeholder::make('years_in_business')
                                    ->label('ব্যবসার বছর')
                                    ->content(fn ($record) => $record?->years_in_business ? $record->years_in_business . ' বছর' : '—'),

                                Placeholder::make('user_email')
                                    ->label('একাউন্ট ইমেইল')
                                    ->content(fn ($record) => $record?->user?->email ?? '—'),
                            ]),
                        ]),

                    Tab::make('ভেরিফিকেশন ডকুমেন্ট')
                        ->schema([
                            Section::make('আপলোডকৃত কাগজপত্র')
                                ->description('ডাউনলোড লিংক শুধুমাত্র Admin/Super Admin দেখতে পারবে')
                                ->schema([
                                    Grid::make(2)->schema([
                                        Placeholder::make('passport_copy')
                                            ->label('পাসপোর্ট কপি')
                                            ->content(function ($record) {
                                                if (! $record?->passport_copy) {
                                                    return 'আপলোড করা হয়নি';
                                                }
                                                return self::documentStatus($record->passport_copy);
                                            }),

                                        Placeholder::make('nid_copy')
                                            ->label('NID কপি')
                                            ->content(function ($record) {
                                                if (! $record?->nid_copy) {
                                                    return 'আপলোড করা হয়নি';
                                                }
                                                return self::documentStatus($record->nid_copy);
                                            }),

                                        Placeholder::make('agency_license')
                                            ->label('এজেন্সি লাইসেন্স')
                                            ->content(function ($record) {
                                                if (! $record?->agency_license) {
                                                    return 'প্রযোজ্য নয় / আপলোড করা হয়নি';
                                                }
                                                return self::documentStatus($record->agency_license);
                                            }),

                                        Placeholder::make('company_cr_copy')
                                            ->label('Company CR কপি')
                                            ->content(function ($record) {
                                                if (! $record?->company_cr_copy) {
                                                    return 'প্রযোজ্য নয় / আপলোড করা হয়নি';
                                                }
                                                return self::documentStatus($record->company_cr_copy);
                                            }),
                                    ]),
                                    Placeholder::make('download_hint')
                                        ->label('')
                                        ->content('নিচের Table Row Actions থেকে "পাসপোর্ট ডাউনলোড" / "NID ডাউনলোড" বাটনে ক্লিক করে ফাইল ডাউনলোড করুন।'),
                                ]),
                        ]),

                    Tab::make('ভেরিফিকেশন স্ট্যাটাস')
                        ->schema([
                            Grid::make(2)->schema([
                                Placeholder::make('is_verified_display')
                                    ->label('বর্তমান স্ট্যাটাস')
                                    ->content(fn ($record) => $record?->is_verified ? '✅ Verified' : '⏳ Pending'),

                                Placeholder::make('verified_at')
                                    ->label('ভেরিফাই তারিখ')
                                    ->content(fn ($record) => $record?->verified_at?->format('d M Y, h:i A') ?? '—'),

                                Placeholder::make('verified_by')
                                    ->label('ভেরিফাই করেছেন')
                                    ->content(fn ($record) => $record?->verifiedBy?->name ?? '—'),
                            ]),

                            Textarea::make('verification_notes')
                                ->label('Admin নোট (Reject কারণ থাকলে এখানে দেখাবে)')
                                ->rows(3)
                                ->disabled()
                                ->columnSpanFull(),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    protected static function documentStatus(string $path): string
    {
        $exists = Storage::disk('private')->exists($path);
        return $exists ? '✅ ফাইল আছে (' . basename($path) . ')' : '⚠️ ফাইল পাওয়া যায়নি';
    }
}
