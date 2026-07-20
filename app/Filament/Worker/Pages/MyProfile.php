<?php

namespace App\Filament\Worker\Pages;

use App\Models\Worker;
use App\Models\SkillCategory;
use App\Services\WalletService;
use App\Services\CvApprovalService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Facades\Auth;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';

    protected string $view = 'filament.worker.pages.my-profile';

    public ?array $data = [];

    public Worker $worker;

    public function mount(): void
    {
        $this->worker = Worker::where('worker_user_id', Auth::id())->firstOrFail();

        $this->form->fill($this->worker->toArray());
    }

    /**
     * পেজ লক করুন যদি status pending বা active হয় (resubmit/edit ছাড়া)
     */
    public function isFormDisabled(): bool
    {
        return in_array($this->worker->status, ['pending', 'active', 'inactive', 'hired', 'featured']);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('CV Tabs')
                    ->tabs([
                        Tab::make('ব্যক্তিগত তথ্য')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('full_name_bn')
                                        ->label('পূর্ণ নাম (বাংলা)')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('full_name_en')
                                        ->label('Full Name (English)')
                                        ->required()
                                        ->maxLength(100),
                                    TextInput::make('father_name_bn')
                                        ->label('পিতার নাম')
                                        ->maxLength(100),
                                    TextInput::make('father_name_en')
                                        ->label("Father's Name")
                                        ->maxLength(100),
                                    TextInput::make('mother_name_bn')
                                        ->label('মাতার নাম')
                                        ->maxLength(100),
                                    DatePicker::make('date_of_birth')
                                        ->label('জন্ম তারিখ')
                                        ->required(),
                                    TextInput::make('place_of_birth')
                                        ->label('জন্মস্থান')
                                        ->maxLength(100),
                                    Select::make('gender')
                                        ->label('লিঙ্গ')
                                        ->required()
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
                                    Select::make('blood_group')
                                        ->label('রক্তের গ্রুপ')
                                        ->options(array_combine(
                                            $bg = ['A+','A-','B+','B-','AB+','AB-','O+','O-'],
                                            $bg
                                        )),
                                    TextInput::make('height_cm')
                                        ->label('উচ্চতা (সেমি)')
                                        ->numeric()
                                        ->minValue(50)->maxValue(250),
                                    TextInput::make('weight_kg')
                                        ->label('ওজন (কেজি)')
                                        ->numeric()
                                        ->minValue(20)->maxValue(250),
                                ]),
                                FileUpload::make('photo')
                                    ->label('প্রোফাইল ছবি')
                                    ->image()
                                    ->maxSize(2048)
                                    ->directory('worker-photos')
                                    ->visibility('public'),
                            ]),

                        Tab::make('ঠিকানা')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('permanent_division')->label('বিভাগ'),
                                    TextInput::make('permanent_district')->label('জেলা'),
                                    TextInput::make('permanent_upazila')->label('উপজেলা'),
                                    TextInput::make('permanent_village')->label('গ্রাম'),
                                    TextInput::make('permanent_post_code')->label('পোস্ট কোড'),
                                ]),
                                Section::make('জরুরি যোগাযোগ')->schema([
                                    Grid::make(3)->schema([
                                        TextInput::make('emergency_contact_name')->label('নাম'),
                                        Select::make('emergency_contact_relation')
                                            ->label('সম্পর্ক')
                                            ->options([
                                                'father'  => 'পিতা',
                                                'mother'  => 'মাতা',
                                                'wife'    => 'স্ত্রী',
                                                'brother' => 'ভাই',
                                                'other'   => 'অন্যান্য',
                                            ]),
                                        TextInput::make('emergency_contact_phone')->label('ফোন নম্বর')->tel(),
                                    ]),
                                ]),
                            ]),

                        Tab::make('পাসপোর্ট ও ইকামা')
                            ->schema([
                                Section::make('পাসপোর্ট')->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('passport_number')->label('পাসপোর্ট নম্বর'),
                                        TextInput::make('passport_issue_place')->label('ইস্যু স্থান'),
                                        DatePicker::make('passport_issue_date')->label('ইস্যু তারিখ'),
                                        DatePicker::make('passport_expiry')->label('মেয়াদ শেষ তারিখ'),
                                        TextInput::make('nid_number')->label('NID নম্বর'),
                                    ]),
                                ]),
                                Section::make('ইকামা (যদি সৌদিতে থাকেন)')->schema([
                                    Grid::make(2)->schema([
                                        TextInput::make('iqama_number')->label('ইকামা নম্বর'),
                                        DatePicker::make('iqama_expiry')->label('ইকামা মেয়াদ শেষ'),
                                        TextInput::make('iqama_profession_ar')->label('পেশা (Arabic)'),
                                        TextInput::make('iqama_profession_bn')->label('পেশা (বাংলা)'),
                                        TextInput::make('current_sponsor_name')->label('বর্তমান কফিল/কোম্পানি'),
                                        TextInput::make('current_sponsor_cr')->label('কোম্পানি CR নম্বর'),
                                    ]),
                                ]),
                            ]),

                        Tab::make('যোগাযোগ')
                            ->schema([
                                Grid::make(2)->schema([
                                    TextInput::make('phone_primary')->label('প্রাইমারি ফোন')->tel()->required(),
                                    TextInput::make('phone_whatsapp')->label('WhatsApp নম্বর')->tel(),
                                    TextInput::make('phone_saudi')->label('সৌদি নম্বর')->tel(),
                                    TextInput::make('email_personal')->label('ব্যক্তিগত ইমেইল')->email(),
                                ]),
                            ]),

                        Tab::make('দক্ষতা ও অভিজ্ঞতা')
                            ->schema([
                                Grid::make(2)->schema([
                                    Select::make('skill_category_id')
                                        ->label('প্রধান পেশা')
                                        ->options(fn () => SkillCategory::query()
                                            ->where('is_active', true)
                                            ->orderBy('sort_order')
                                            ->pluck('name_bn', 'id'))
                                        ->required()
                                        ->searchable(),
                                    TextInput::make('skill_sub_details')->label('পেশার বিস্তারিত'),
                                    TextInput::make('experience_years')->label('মোট অভিজ্ঞতা (বছর)')->numeric(),
                                    TextInput::make('experience_saudi_years')->label('সৌদিতে অভিজ্ঞতা (বছর)')->numeric(),
                                    TextInput::make('previous_companies')->label('আগের কোম্পানি'),
                                    Select::make('education_level')
                                        ->label('শিক্ষাগত যোগ্যতা')
                                        ->options([
                                            'none'      => 'নেই',
                                            'primary'   => 'প্রাথমিক',
                                            'secondary' => 'মাধ্যমিক',
                                            'hsc'       => 'উচ্চ মাধ্যমিক',
                                            'degree'    => 'ডিগ্রি',
                                        ]),
                                    TextInput::make('education_details')->label('পাশের বছর ও বিষয়'),
                                    Select::make('arabic_level')
                                        ->label('আরবি দক্ষতা')
                                        ->options(['none'=>'নেই','basic'=>'বেসিক','intermediate'=>'মাধ্যম','fluent'=>'সাবলীল']),
                                    Select::make('english_level')
                                        ->label('ইংরেজি দক্ষতা')
                                        ->options(['none'=>'নেই','basic'=>'বেসিক','intermediate'=>'মাধ্যম','fluent'=>'সাবলীল']),
                                    Toggle::make('driving_license')->label('ড্রাইভিং লাইসেন্স আছে?')->live(),
                                    TextInput::make('driving_license_type')
                                        ->label('লাইসেন্সের ধরন')
                                        ->visible(fn ($get) => $get('driving_license')),
                                    TextInput::make('computer_skills')->label('কম্পিউটার দক্ষতা'),
                                    TextInput::make('other_skills')->label('অন্যান্য দক্ষতা'),
                                ]),
                                TextInput::make('skill_video_youtube')
                                    ->label('কাজের ভিডিও (YouTube URL)')
                                    ->url(),
                            ]),

                        Tab::make('বর্তমান অবস্থা')
                            ->schema([
                                Grid::make(2)->schema([
                                    Toggle::make('is_in_saudi')->label('সৌদিতে আছেন কিনা')->live(),
                                    TextInput::make('present_location_city')
                                        ->label('বর্তমান শহর')
                                        ->visible(fn ($get) => $get('is_in_saudi')),
                                    Select::make('visa_status')
                                        ->label('ভিসা স্ট্যাটাস')
                                        ->options([
                                            'visit'        => 'ভিজিট',
                                            'iqama'        => 'ইকামা',
                                            'free_exit'    => 'ফ্রি এক্সিট',
                                            'final_exit'   => 'ফাইনাল এক্সিট',
                                            'new_visa'     => 'নতুন ভিসা',
                                            'not_in_saudi' => 'সৌদিতে নেই',
                                        ]),
                                    Toggle::make('transfer_possible')->label('কাফালা ট্রান্সফার সম্ভব?'),
                                    DatePicker::make('available_from')->label('কবে থেকে কাজ শুরু করতে পারবেন'),
                                    TextInput::make('expected_salary_sar')
                                        ->label('প্রত্যাশিত বেতন (SAR)')
                                        ->numeric(),
                                ]),
                                Section::make('স্বাস্থ্য তথ্য')->schema([
                                    Toggle::make('medical_fit')->label('মেডিকেল ফিট?')->default(true),
                                    Textarea::make('medical_notes')
                                        ->label('বিশেষ স্বাস্থ্য তথ্য')
                                        ->maxLength(500),
                                ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label('সংরক্ষণ করুন (Draft)')
                ->action('saveDraft')
                ->visible(fn () => !$this->isFormDisabled()),

            Action::make('submit')
                ->label('আবেদন জমা দিন')
                ->color('success')
                ->requiresConfirmation()
                ->modalDescription('জমা দিলে আপনার ওয়ালেট থেকে ১০ SAR কাটা হবে। আপনি কি নিশ্চিত?')
                ->action('submitForApproval')
                ->visible(fn () => in_array($this->worker->status, ['draft', 'rejected'])),
        ];
    }

    public function saveDraft(): void
    {
        $data = $this->form->getState();
        $this->worker->fill($data);
        $this->worker->save();

        Notification::make()
            ->title('CV ড্রাফট হিসেবে সংরক্ষিত হয়েছে')
            ->success()
            ->send();
    }

    public function submitForApproval(): void
    {
        // ডাবল-সাবমিশন প্রোটেকশন
        if (in_array($this->worker->status, ['pending', 'active', 'inactive', 'hired', 'featured'])) {
            Notification::make()
                ->title('আপনার CV ইতিমধ্যে জমা দেওয়া হয়েছে')
                ->warning()
                ->send();
            return;
        }

        // প্রথমে সব ডেটা সেভ করুন
        $data = $this->form->getState();
        $this->worker->fill($data);
        $this->worker->save();

        // ওয়ালেট ব্যালেন্স চেক
        $fee = (float) (\App\Models\Setting::where('key', 'cv_approval_fee')->value('value') ?? 10);
        $user = Auth::user();

        if ($user->available_balance < $fee) {
            Notification::make()
                ->title("Wallet এ {$fee} SAR নেই। রিচার্জ করুন।")
                ->danger()
                ->send();
            return;
        }

        try {
            // ফি কেটে status pending করুন (একই transaction-এ, service-এর ভেতরে)
            app(CvApprovalService::class)->deductFee($this->worker);
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('জমা দেওয়া যায়নি')
                ->body($e->getMessage())
                ->danger()
                ->send();
            return;
        }

        $this->worker->refresh();

        Notification::make()
            ->title('আপনার CV রিভিউয়ের জন্য জমা হয়েছে')
            ->success()
            ->send();

        $this->redirect(static::getUrl());
    }
}