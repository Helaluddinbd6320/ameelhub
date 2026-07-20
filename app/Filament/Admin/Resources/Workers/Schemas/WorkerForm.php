<?php

namespace App\Filament\Admin\Resources\Workers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class WorkerForm
{
    /**
     * Filament v4/v5-এ TextInput::stripTags() মেথডটি নেই (v2-তে ছিল, রিমুভ হয়ে গেছে)।
     * তাই এই helper দিয়ে dehydrateStateUsing() ব্যবহার করে একই কাজ করা হচ্ছে।
     * যেকোনো TextInput-এ চাইলে ->dehydrateStateUsing(self::stripTags()) যোগ করতে পারো।
     */
    protected static function stripTags(): \Closure
    {
        return fn ($state) => is_string($state) ? strip_tags($state) : $state;
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Tabs::make()->tabs([

                // ════════════════════════════════════════
                // TAB 1 — ব্যক্তিগত তথ্য
                // ════════════════════════════════════════
                Tab::make('ব্যক্তিগত তথ্য')->schema([

                    FileUpload::make('photo')
                        ->label('প্রোফাইল ছবি')
                        ->image()
                        ->maxSize(2048)
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->disk('public')
                        ->directory('worker-photos')
                        // Step 10.8e Fix: আপলোডের আগেই browser-এ (filepond) resize হয় —
                        // কোনো সার্ভার-সাইড image library (Intervention/GD) লাগে না, তাই
                        // Namecheap shared hosting-এর জন্য নিরাপদ। এতে বড় মোবাইল ফটো
                        // (৪০০০×৩০০০px) অপ্রয়োজনীয় bandwidth/storage নষ্ট করবে না।
                        ->imageResizeMode('cover')
                        ->imageResizeTargetWidth('600')
                        ->imageResizeTargetHeight('600')
                        ->imageResizeUpscale(false)
                        ->getUploadedFileNameForStorageUsing(
                            fn ($file) => Str::ulid() . '.' . $file->getClientOriginalExtension()
                        )
                        ->columnSpanFull(),

                    TextInput::make('full_name_bn')
                        ->label('বাংলা নাম')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('full_name_en')
                        ->label('English Name')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('father_name_bn')
                        ->label('পিতার নাম (বাংলা)')
                        ->maxLength(100),

                    TextInput::make('father_name_en')
                        ->label("Father's Name (English)")
                        ->maxLength(100),

                    TextInput::make('mother_name_bn')
                        ->label('মাতার নাম')
                        ->maxLength(100),

                    DatePicker::make('date_of_birth')
                        ->label('জন্ম তারিখ')
                        ->native(false)
                        ->maxDate(now()->subYears(18)),

                    TextInput::make('place_of_birth')
                        ->label('জন্মস্থান')
                        ->maxLength(100),

                    Select::make('gender')
                        ->label('লিঙ্গ')
                        ->options([
                            'male'   => 'পুরুষ',
                            'female' => 'মহিলা',
                        ]),

                    Select::make('religion')
                        ->label('ধর্ম')
                        ->options([
                            'islam'     => 'ইসলাম',
                            'hindu'     => 'হিন্দু',
                            'christian' => 'খ্রিস্টান',
                            'buddhist'  => 'বৌদ্ধ',
                            'other'     => 'অন্যান্য',
                        ]),

                    Select::make('marital_status')
                        ->label('বৈবাহিক অবস্থা')
                        ->options([
                            'single'   => 'অবিবাহিত',
                            'married'  => 'বিবাহিত',
                            'divorced' => 'তালাকপ্রাপ্ত',
                            'widowed'  => 'বিধবা/বিপত্নীক',
                        ]),

                    TextInput::make('nationality')
                        ->label('জাতীয়তা')
                        ->default('Bangladeshi')
                        ->maxLength(50),

                    Select::make('blood_group')
                        ->label('রক্তের গ্রুপ')
                        ->options([
                            'A+'  => 'A+',  'A-'  => 'A-',
                            'B+'  => 'B+',  'B-'  => 'B-',
                            'AB+' => 'AB+', 'AB-' => 'AB-',
                            'O+'  => 'O+',  'O-'  => 'O-',
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

                ])->columns(2),

                // ════════════════════════════════════════
                // TAB 2 — পাসপোর্ট ও ইকামা
                // ════════════════════════════════════════
                Tab::make('পাসপোর্ট ও ইকামা')->schema([

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
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('passport_number')
                        ->label('পাসপোর্ট নম্বর')
                        ->maxLength(20),

                    TextInput::make('passport_issue_place')
                        ->label('পাসপোর্ট ইস্যু স্থান')
                        ->maxLength(100),

                    DatePicker::make('passport_issue_date')
                        ->label('পাসপোর্ট ইস্যু তারিখ')
                        ->native(false),

                    DatePicker::make('passport_expiry')
                        ->label('পাসপোর্ট মেয়াদ শেষ')
                        ->native(false),

                    TextInput::make('nid_number')
                        ->label('NID নম্বর')
                        ->maxLength(20),

                    TextInput::make('iqama_number')
                        ->label('ইকামা নম্বর')
                        ->maxLength(20),

                    DatePicker::make('iqama_expiry')
                        ->label('ইকামা মেয়াদ শেষ')
                        ->native(false),

                    TextInput::make('iqama_profession_ar')
                        ->label('ইকামার পেশা (Arabic)')
                        ->maxLength(100),

                    TextInput::make('iqama_profession_bn')
                        ->label('ইকামার পেশা (বাংলা)')
                        ->maxLength(100),

                    TextInput::make('current_sponsor_name')
                        ->label('কফিল/কোম্পানির নাম')
                        ->maxLength(200),

                    TextInput::make('current_sponsor_cr')
                        ->label('Company CR নম্বর')
                        ->maxLength(50),

                ])->columns(2),

                // ════════════════════════════════════════
                // TAB 3 — যোগাযোগ
                // ════════════════════════════════════════
                Tab::make('যোগাযোগ')->schema([

                    TextInput::make('phone_primary')
                        ->label('প্রাইমারি ফোন')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('phone_whatsapp')
                        ->label('WhatsApp নম্বর')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('phone_saudi')
                        ->label('সৌদি নম্বর')
                        ->tel()
                        ->maxLength(20),

                    TextInput::make('email_personal')
                        ->label('ব্যক্তিগত ইমেইল')
                        ->email()
                        ->maxLength(200),

                ])->columns(2),

                // ════════════════════════════════════════
                // TAB 4 — দক্ষতা ও অভিজ্ঞতা
                // ════════════════════════════════════════
                Tab::make('দক্ষতা ও অভিজ্ঞতা')->schema([

                    Select::make('skill_category_id')
                        ->label('প্রধান পেশা')
                        ->relationship('skillCategory', 'name_bn')
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('skill_sub_details')
                        ->label('পেশার বিস্তারিত')
                        ->maxLength(300),

                    TextInput::make('experience_years')
                        ->label('মোট অভিজ্ঞতা (বছর)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(50),

                    TextInput::make('experience_saudi_years')
                        ->label('সৌদিতে অভিজ্ঞতা (বছর)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(50),

                    TextInput::make('previous_companies')
                        ->label('আগে কোথায় কাজ করেছে')
                        ->maxLength(500),

                    Select::make('education_level')
                        ->label('শিক্ষাগত যোগ্যতা')
                        ->options([
                            'none'      => 'কোনোটি নয়',
                            'primary'   => 'প্রাথমিক',
                            'secondary' => 'মাধ্যমিক (SSC)',
                            'hsc'       => 'উচ্চ মাধ্যমিক (HSC)',
                            'degree'    => 'স্নাতক বা উপরে',
                        ]),

                    TextInput::make('education_details')
                        ->label('পাশের বছর ও বিষয়')
                        ->maxLength(200),

                    Select::make('arabic_level')
                        ->label('আরবি দক্ষতা')
                        ->options([
                            'none'         => 'কোনোটি নয়',
                            'basic'        => 'সাধারণ',
                            'intermediate' => 'মধ্যম',
                            'fluent'       => 'দক্ষ',
                        ]),

                    Select::make('english_level')
                        ->label('ইংরেজি দক্ষতা')
                        ->options([
                            'none'         => 'কোনোটি নয়',
                            'basic'        => 'সাধারণ',
                            'intermediate' => 'মধ্যম',
                            'fluent'       => 'দক্ষ',
                        ]),

                    Toggle::make('driving_license')
                        ->label('ড্রাইভিং লাইসেন্স আছে?')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('driving_license_type')
                        ->label('লাইসেন্সের ধরন (Light / Heavy / Both)')
                        ->maxLength(50)
                        ->visible(fn ($get) => (bool) $get('driving_license')),

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
                        ->placeholder('https://youtube.com/watch?v=...'),

                    Toggle::make('is_in_saudi')
                        ->label('বর্তমানে সৌদিতে আছেন?')
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('present_location_city')
                        ->label('বর্তমান শহর')
                        ->maxLength(100)
                        ->visible(fn ($get) => (bool) $get('is_in_saudi')),

                    TextInput::make('present_location_country')
                        ->label('দেশ')
                        ->default('Saudi Arabia')
                        ->maxLength(100)
                        ->visible(fn ($get) => (bool) $get('is_in_saudi')),

                    Select::make('visa_status')
                        ->label('ভিসার অবস্থা')
                        ->options([
                            'visit'        => 'ভিজিট ভিসা',
                            'iqama'        => 'ইকামা',
                            'free_exit'    => 'ফ্রি এগজিট',
                            'final_exit'   => 'ফাইনাল এগজিট',
                            'new_visa'     => 'নতুন ভিসা',
                            'not_in_saudi' => 'সৌদিতে নেই',
                        ]),

                    Toggle::make('transfer_possible')
                        ->label('Kafala ট্রান্সফার সম্ভব?'),

                    DatePicker::make('available_from')
                        ->label('কবে থেকে কাজ করতে পারবেন')
                        ->native(false),

                    TextInput::make('expected_salary_sar')
                        ->label('প্রত্যাশিত বেতন (SAR)')
                        ->numeric()
                        ->prefix('SAR')
                        ->minValue(0),

                    Toggle::make('medical_fit')
                        ->label('মেডিকেল ফিট?')
                        ->default(true)
                        ->columnSpanFull(),

                    Textarea::make('medical_notes')
                        ->label('বিশেষ স্বাস্থ্য তথ্য')
                        ->maxLength(500)
                        ->rows(3)
                        ->columnSpanFull(),

                ])->columns(2),

                // ════════════════════════════════════════
                // TAB 5 — স্ট্যাটাস ও অ্যাডমিন
                // ════════════════════════════════════════
                Tab::make('স্ট্যাটাস ও অ্যাডমিন')->schema([

                    Select::make('status')
                        ->label('CV স্ট্যাটাস')
                        ->options([
                            'draft'    => 'Draft',
                            'pending'  => 'Pending (অনুমোদন অপেক্ষায়)',
                            'active'   => 'Active (অনুমোদিত)',
                            'inactive' => 'Inactive',
                            'hired'    => 'Hired',
                            'featured' => 'Featured',
                            'rejected' => 'Rejected',
                        ])
                        ->required()
                        ->live(),

                    Textarea::make('rejection_reason')
                        ->label('Reject এর কারণ (Worker দেখতে পাবে)')
                        ->maxLength(1000)
                        ->rows(3)
                        ->visible(fn ($get) => $get('status') === 'rejected')
                        ->columnSpanFull(),

                    Toggle::make('is_verified')
                        ->label('Verified?')
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('এই ফিল্ড guarded — Form থেকে সেভ হয় না।'),

                    Toggle::make('is_featured')
                        ->label('Featured?')
                        ->disabled()
                        ->dehydrated(false)
                        ->live()
                        ->helperText('এই ফিল্ড guarded — Table এর "Feature করুন" / "Unfeature করুন" action ব্যবহার করুন।'),

                    DatePicker::make('featured_until')
                        ->label('Featured মেয়াদ শেষ')
                        ->native(false)
                        ->disabled()
                        ->dehydrated(false)
                        ->visible(fn ($get) => (bool) $get('is_featured')),

                    Textarea::make('cv_notes')
                        ->label('Admin Internal Note (Worker দেখবে না)')
                        ->maxLength(2000)
                        ->rows(4)
                        ->columnSpanFull(),

                ])->columns(2),

            ])->columnSpanFull(),
        ]);
    }
}