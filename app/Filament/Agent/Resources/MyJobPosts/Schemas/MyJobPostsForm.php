<?php

namespace App\Filament\Agent\Resources\MyJobPosts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class MyJobPostsForm
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
                                    ->label('আপনার ফি (SAR)')
                                    ->numeric()
                                    ->required()
                                    ->helperText('এই ফি Worker রা 0.5/1 SAR reveal fee দিয়ে দেখতে পাবে।'),

                                TextInput::make('fee_reveal_cost')
                                    ->label('Fee Reveal Cost (SAR)')
                                    ->numeric()
                                    ->default(0.50)
                                    ->required(),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}