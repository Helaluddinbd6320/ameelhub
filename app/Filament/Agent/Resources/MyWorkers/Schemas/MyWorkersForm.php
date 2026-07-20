<?php

namespace App\Filament\Agent\Resources\MyWorkers\Schemas;

use App\Models\SkillCategory;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MyWorkersForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make('Worker CV')
                ->columnSpanFull()
                ->tabs([

                    // ─── TAB 1: Personal ──────────────────────────────────
                    Tab::make('ব্যক্তিগত তথ্য')
                        ->schema([
                            TextInput::make('full_name_bn')
                                ->label('পূর্ণ নাম (বাংলা)')
                                ->maxLength(100)
                                ->required(),
                            TextInput::make('full_name_en')
                                ->label('Full Name (English)')
                                ->maxLength(100)
                                ->required(),
                            TextInput::make('father_name_bn')
                                ->label('পিতার নাম (বাংলা)')
                                ->maxLength(100),
                            TextInput::make('father_name_en')
                                ->label('Father Name (English)')
                                ->maxLength(100),
                            TextInput::make('mother_name_bn')
                                ->label('মাতার নাম')
                                ->maxLength(100),
                            DatePicker::make('date_of_birth')
                                ->label('জন্ম তারিখ'),
                            TextInput::make('place_of_birth')
                                ->label('জন্মস্থান')
                                ->maxLength(100),
                            Select::make('gender')
                                ->label('লিঙ্গ')
                                ->options([
                                    'male'   => 'Male',
                                    'female' => 'Female',
                                ]),
                            Select::make('religion')
                                ->label('ধর্ম')
                                ->options([
                                    'islam'     => 'Islam',
                                    'hindu'     => 'Hindu',
                                    'christian' => 'Christian',
                                    'buddhist'  => 'Buddhist',
                                    'other'     => 'Other',
                                ]),
                            Select::make('marital_status')
                                ->label('বৈবাহিক অবস্থা')
                                ->options([
                                    'single'   => 'Single',
                                    'married'  => 'Married',
                                    'divorced' => 'Divorced',
                                    'widowed'  => 'Widowed',
                                ]),
                            TextInput::make('nationality')
                                ->label('জাতীয়তা')
                                ->default('Bangladeshi')
                                ->maxLength(50),
                            Select::make('blood_group')
                                ->label('রক্তের গ্রুপ')
                                ->options([
                                    'A+' => 'A+', 'A-' => 'A-',
                                    'B+' => 'B+', 'B-' => 'B-',
                                    'AB+' => 'AB+', 'AB-' => 'AB-',
                                    'O+' => 'O+', 'O-' => 'O-',
                                ]),
                            TextInput::make('height_cm')
                                ->label('উচ্চতা (সেমি)')
                                ->numeric()
                                ->minValue(100)
                                ->maxValue(250),
                            TextInput::make('weight_kg')
                                ->label('ওজন (কেজি)')
                                ->numeric()
                                ->minValue(30)
                                ->maxValue(200),
                            FileUpload::make('photo')
                                ->label('প্রোফাইল ছবি')
                                ->image()
                                ->directory('worker-photos')
                                ->maxSize(2048)
                                ->visibility('public')
                                // Step 10.8e Fix: Admin WorkerForm-এর সাথে সামঞ্জস্যপূর্ণ করা হলো —
                                // (1) browser-side (filepond) resize, কোনো সার্ভার-সাইড dependency
                                //     ছাড়াই — বড় মোবাইল ফটোও এখন ৬০০×৬০০-এর মধ্যে সীমাবদ্ধ থাকবে।
                                // (2) ULID filename — blueprint Section 9 / L10 security rule
                                //     ("File uploads: ULID filename") অনুযায়ী, আগে এখানে missing ছিল।
                                ->imageResizeMode('cover')
                                ->imageResizeTargetWidth('600')
                                ->imageResizeTargetHeight('600')
                                ->imageResizeUpscale(false)
                                ->getUploadedFileNameForStorageUsing(
                                    fn ($file) => Str::ulid() . '.' . $file->getClientOriginalExtension()
                                )
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    // ─── TAB 2: Address ───────────────────────────────────
                    Tab::make('ঠিকানা')
                        ->schema([
                            TextInput::make('permanent_division')
                                ->label('বিভাগ')
                                ->maxLength(50),
                            TextInput::make('permanent_district')
                                ->label('জেলা')
                                ->maxLength(50),
                            TextInput::make('permanent_upazila')
                                ->label('উপজেলা')
                                ->maxLength(50),
                            TextInput::make('permanent_village')
                                ->label('গ্রাম')
                                ->maxLength(100),
                            TextInput::make('permanent_post_code')
                                ->label('পোস্ট কোড')
                                ->maxLength(10),
                            TextInput::make('emergency_contact_name')
                                ->label('জরুরি যোগাযোগ ব্যক্তির নাম')
                                ->maxLength(100),
                            TextInput::make('emergency_contact_relation')
                                ->label('সম্পর্ক')
                                ->maxLength(50),
                            TextInput::make('emergency_contact_phone')
                                ->label('জরুরি যোগাযোগ নম্বর')
                                ->tel(),
                        ])
                        ->columns(2),

                    // ─── TAB 3: Passport & Iqama ──────────────────────────
                    Tab::make('পাসপোর্ট ও ইকামা')
                        ->schema([
                            TextInput::make('passport_number')
                                ->label('পাসপোর্ট নম্বর'),
                            DatePicker::make('passport_issue_date')
                                ->label('ইস্যু তারিখ'),
                            DatePicker::make('passport_expiry')
                                ->label('মেয়াদ শেষ তারিখ'),
                            TextInput::make('passport_issue_place')
                                ->label('ইস্যু স্থান')
                                ->maxLength(100),
                            TextInput::make('nid_number')
                                ->label('NID নম্বর'),

                            TextInput::make('iqama_number')
                                ->label('ইকামা নম্বর'),
                            DatePicker::make('iqama_expiry')
                                ->label('ইকামা মেয়াদ শেষ'),
                            TextInput::make('iqama_profession_ar')
                                ->label('পেশা (Arabic)')
                                ->maxLength(100),
                            TextInput::make('iqama_profession_bn')
                                ->label('পেশা (বাংলা)')
                                ->maxLength(100),
                            TextInput::make('current_sponsor_name')
                                ->label('বর্তমান কফিল/কোম্পানি')
                                ->maxLength(200),
                            TextInput::make('current_sponsor_cr')
                                ->label('কোম্পানির CR নম্বর')
                                ->maxLength(50),
                        ])
                        ->columns(2),

                    // ─── TAB 4: Contact ───────────────────────────────────
                    Tab::make('যোগাযোগ')
                        ->schema([
                            TextInput::make('phone_primary')
                                ->label('প্রাইমারি ফোন')
                                ->tel()
                                ->required(),
                            TextInput::make('phone_whatsapp')
                                ->label('WhatsApp নম্বর')
                                ->tel(),
                            TextInput::make('phone_saudi')
                                ->label('সৌদি নম্বর')
                                ->tel(),
                            TextInput::make('email_personal')
                                ->label('ব্যক্তিগত ইমেইল')
                                ->email()
                                ->maxLength(200),
                        ])
                        ->columns(2),

                    // ─── TAB 5: Skills & Current Status ──────────────────
                    Tab::make('দক্ষতা ও বর্তমান অবস্থা')
                        ->schema([
                            Select::make('skill_category_id')
                                ->label('প্রধান পেশা')
                                ->options(fn () => SkillCategory::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name_bn', 'id')
                                    ->toArray())
                                ->searchable()
                                ->required(),
                            TextInput::make('skill_sub_details')
                                ->label('পেশার বিস্তারিত')
                                ->maxLength(300),
                            TextInput::make('experience_years')
                                ->label('মোট অভিজ্ঞতা (বছর)')
                                ->numeric(),
                            TextInput::make('experience_saudi_years')
                                ->label('সৌদিতে অভিজ্ঞতা (বছর)')
                                ->numeric(),
                            Textarea::make('previous_companies')
                                ->label('আগে কোথায় কাজ করেছে')
                                ->maxLength(500)
                                ->columnSpanFull(),
                            Select::make('education_level')
                                ->label('শিক্ষাগত যোগ্যতা')
                                ->options([
                                    'none'      => 'None',
                                    'primary'   => 'Primary',
                                    'secondary' => 'Secondary',
                                    'hsc'       => 'HSC',
                                    'degree'    => 'Degree',
                                ]),
                            TextInput::make('education_details')
                                ->label('পাশের বছর ও বিষয়')
                                ->maxLength(200),
                            Select::make('arabic_level')
                                ->label('আরবি দক্ষতা')
                                ->options([
                                    'none'         => 'None',
                                    'basic'        => 'Basic',
                                    'intermediate' => 'Intermediate',
                                    'fluent'       => 'Fluent',
                                ]),
                            Select::make('english_level')
                                ->label('ইংরেজি দক্ষতা')
                                ->options([
                                    'none'         => 'None',
                                    'basic'        => 'Basic',
                                    'intermediate' => 'Intermediate',
                                    'fluent'       => 'Fluent',
                                ]),
                            Toggle::make('driving_license')
                                ->label('ড্রাইভিং লাইসেন্স আছে?')
                                ->live(),
                            TextInput::make('driving_license_type')
                                ->label('লাইসেন্স টাইপ (Light/Heavy/Both)')
                                ->maxLength(50)
                                ->visible(fn ($get) => $get('driving_license')),
                            TextInput::make('computer_skills')
                                ->label('কম্পিউটার দক্ষতা')
                                ->maxLength(200),
                            TextInput::make('other_skills')
                                ->label('অন্যান্য দক্ষতা')
                                ->maxLength(300),
                            TextInput::make('skill_video_youtube')
                                ->label('কাজের ভিডিও (YouTube URL)')
                                ->url()
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Toggle::make('is_in_saudi')
                                ->label('বর্তমানে সৌদিতে আছেন?')
                                ->live(),
                            TextInput::make('present_location_city')
                                ->label('বর্তমান শহর')
                                ->maxLength(100),
                            Select::make('visa_status')
                                ->label('ভিসা স্ট্যাটাস')
                                ->options([
                                    'visit'        => 'Visit',
                                    'iqama'        => 'Iqama',
                                    'free_exit'    => 'Free Exit',
                                    'final_exit'   => 'Final Exit',
                                    'new_visa'     => 'New Visa',
                                    'not_in_saudi' => 'Not in Saudi',
                                ]),
                            Toggle::make('transfer_possible')
                                ->label('Kafala Transfer সম্ভব?'),
                            DatePicker::make('available_from')
                                ->label('কবে থেকে কাজ করতে পারবেন'),
                            TextInput::make('expected_salary_sar')
                                ->label('প্রত্যাশিত বেতন (SAR)')
                                ->numeric(),

                            Toggle::make('medical_fit')
                                ->label('মেডিকেল ফিট?')
                                ->default(true),
                            Textarea::make('medical_notes')
                                ->label('বিশেষ স্বাস্থ্য তথ্য')
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ])
                        ->columns(2),

                    // ─── TAB 6: Approval Status (Read-only) ──────────────
                    // Placeholders only — never editable by agent.
                    // Guarded fields must only be written via forceFill() in services.
                    Tab::make('অনুমোদন স্ট্যাটাস')
                        ->schema([
                            Placeholder::make('status_display')
                                ->label('বর্তমান স্ট্যাটাস')
                                ->content(fn ($record) => $record?->status ?? '—'),
                            Placeholder::make('approved_at_display')
                                ->label('অনুমোদনের তারিখ')
                                ->content(fn ($record) => $record?->approved_at?->format('Y-m-d H:i') ?? '—'),
                            Placeholder::make('rejection_reason_display')
                                ->label('Rejection Reason')
                                ->content(fn ($record) => $record?->rejection_reason ?? '—')
                                ->columnSpanFull(),
                        ])
                        ->columns(2)
                        ->visible(fn ($record) => $record !== null),
                ]),
        ]);
    }
}