<?php

namespace App\Filament\Admin\Resources\JobPostResource\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class JobPostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('জব তথ্য')
                    ->columnSpanFull()
                    ->tabs([

                        // ── TAB 1: Job Info ──
                        Tab::make('Job Info')
                            ->schema([
                                TextInput::make('job_title')
                                    ->label('জবের শিরোনাম')
                                    ->required()
                                    ->maxLength(200),

                                TextInput::make('job_title_ar')
                                    ->label('শিরোনাম (Arabic)')
                                    ->maxLength(200),

                                TextInput::make('employer_name')
                                    ->label('নিয়োগকর্তার নাম')
                                    ->required()
                                    ->maxLength(200),

                                Select::make('employer_type')
                                    ->label('নিয়োগকর্তার ধরন')
                                    ->required()
                                    ->options([
                                        'restaurant' => 'Restaurant',
                                        'hotel'      => 'Hotel',
                                        'factory'    => 'Factory',
                                        'house'      => 'House',
                                        'company'    => 'Company',
                                        'other'      => 'Other',
                                    ]),

                                TextInput::make('employer_city')
                                    ->label('শহর')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('employer_country')
                                    ->label('দেশ')
                                    ->default('Saudi Arabia')
                                    ->required()
                                    ->maxLength(100),

                                Select::make('skill_category_id')
                                    ->label('প্রয়োজনীয় পেশা')
                                    ->relationship('skillCategory', 'name_bn')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                TextInput::make('skill_sub_details')
                                    ->label('পেশার বিস্তারিত')
                                    ->maxLength(200),
                            ])
                            ->columns(2),

                        // ── TAB 2: Terms ──
                        Tab::make('Terms')
                            ->schema([
                                TextInput::make('vacancies')
                                    ->label('কতজন লাগবে')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->maxValue(255),

                                TextInput::make('salary_sar')
                                    ->label('বেতন (SAR)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('SAR'),

                                Toggle::make('accommodation')
                                    ->label('আবাসন আছে?'),

                                Toggle::make('food_included')
                                    ->label('খাবার আছে?'),

                                Toggle::make('transport_provided')
                                    ->label('যানবাহন আছে?'),

                                Toggle::make('overtime_available')
                                    ->label('ওভারটাইম আছে?'),

                                TextInput::make('contract_months')
                                    ->label('চুক্তির মেয়াদ (মাস)')
                                    ->numeric(),

                                TextInput::make('working_hours')
                                    ->label('দৈনিক কর্মঘণ্টা')
                                    ->numeric(),

                                TextInput::make('weekly_off')
                                    ->label('সাপ্তাহিক ছুটি')
                                    ->maxLength(50),

                                Textarea::make('description')
                                    ->label('বিস্তারিত বিবরণ')
                                    ->rows(4)
                                    ->columnSpanFull(),

                                Textarea::make('requirements')
                                    ->label('যোগ্যতা/শর্তাবলী')
                                    ->rows(4)
                                    ->columnSpanFull(),
                            ])
                            ->columns(2),

                        // ── TAB 3: Hidden Fee ──
                        Tab::make('Hidden Fee')
                            ->schema([
                                TextInput::make('agent_fee_sar')
                                    ->label('Agent Fee (SAR) — Encrypted')
                                    ->numeric()
                                    ->required()
                                    ->helperText('এই ফি Worker দের কাছে 0.5/1 SAR reveal fee দিয়ে দেখা যাবে।'),

                                TextInput::make('fee_reveal_cost')
                                    ->label('Fee Reveal Cost (SAR)')
                                    ->numeric()
                                    ->default(0.50)
                                    ->required(),
                            ])
                            ->columns(2),

                        // ── TAB 4: Lifecycle (Admin only) ──
                        Tab::make('Lifecycle')
                            ->schema([
                                Select::make('status')
                                    ->label('স্ট্যাটাস')
                                    ->options([
                                        'draft'    => 'Draft',
                                        'pending'  => 'Pending',
                                        'active'   => 'Active',
                                        'paused'   => 'Paused',
                                        'closed'   => 'Closed',
                                        'filled'   => 'Filled',
                                        'rejected' => 'Rejected',
                                    ])
                                    ->required(),

                                DatePicker::make('expires_at')
                                    ->label('মেয়াদ শেষের তারিখ')
                                    ->native(false),

                                Textarea::make('close_reason')
                                    ->label('বন্ধের কারণ')
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Placeholder::make('filled_count')
                                    ->label('পূর্ণ হয়েছে')
                                    ->content(fn ($record) => $record?->filled_count ?? 0),

                                Placeholder::make('view_count')
                                    ->label('মোট ভিউ')
                                    ->content(fn ($record) => $record?->view_count ?? 0),

                                Placeholder::make('approved_by')
                                    ->label('অনুমোদনকারী')
                                    ->content(fn ($record) => $record?->approvedBy?->name ?? '—'),

                                Placeholder::make('approved_at')
                                    ->label('অনুমোদনের তারিখ')
                                    ->content(fn ($record) => $record?->approved_at?->format('d M Y, h:i A') ?? '—'),

                                Placeholder::make('closed_by')
                                    ->label('বন্ধকারী')
                                    ->content(fn ($record) => $record?->closedBy?->name ?? '—'),

                                Placeholder::make('closed_at')
                                    ->label('বন্ধের তারিখ')
                                    ->content(fn ($record) => $record?->closed_at?->format('d M Y, h:i A') ?? '—'),
                            ])
                            ->columns(2)
                            ->visibleOn(['edit', 'view']),
                    ]),
            ]);
    }
}