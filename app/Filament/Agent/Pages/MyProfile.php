<?php

namespace App\Filament\Agent\Pages;

use App\Models\AgentProfile;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use UnitEnum;

class MyProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-identification';

    protected string $view = 'filament.agent.pages.my-profile';

    public ?array $data = [];

    public AgentProfile $record;

    public static function getNavigationGroup(): string | UnitEnum | null
    {
        return __('messages.navigation.groups.profile');
    }

    public static function getNavigationLabel(): string
    {
        return __('messages.navigation.resources.my_profile');
    }

    public function getTitle(): string
    {
        return __('messages.navigation.resources.my_profile');
    }

    public function mount(): void
    {
        // 'is_verified' এখানে পাঠানো হয় না কারণ এটি $guarded — DB migration-এর
        // DEFAULT FALSE ইতিমধ্যে এটি হ্যান্ডেল করে।
        $this->record = AgentProfile::firstOrCreate(
            ['user_id' => auth()->id()],
            ['country' => 'Saudi Arabia']
        );

        // firstOrCreate() এর পর attributes array তে absent থাকা key গুলো
        // (যেমন is_verified) সঠিক cast সহ DB থেকে লোড করার জন্য refresh() জরুরি।
        // এটা ছাড়া $this->record->is_verified আসলে null রিটার্ন করে,
        // যা disabled(fn(): bool => ...) এর strict bool return type এ ক্র্যাশ করায়।
        $this->record->refresh();

        $this->form->fill($this->record->only([
            'agent_name_bn', 'agent_name_en', 'company_name', 'company_type',
            'office_address', 'city', 'country', 'phone_office', 'whatsapp_number',
            'years_in_business', 'passport_copy', 'nid_copy',
            'agency_license', 'company_cr_copy',
        ]));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('AgentProfileTabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('ব্যক্তিগত ও কোম্পানি তথ্য')
                            ->icon('heroicon-o-user')
                            ->schema([
                                TextInput::make('agent_name_bn')
                                    ->label('আপনার নাম (বাংলা)')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('agent_name_en')
                                    ->label('Name (English)')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('company_name')
                                    ->label('কোম্পানির নাম (যদি থাকে)')
                                    ->maxLength(200),
                                Select::make('company_type')
                                    ->label('প্রতিষ্ঠানের ধরন')
                                    ->required()
                                    ->options([
                                        'individual'          => 'ব্যক্তিগত (Individual)',
                                        'registered_company'  => 'নিবন্ধিত কোম্পানি',
                                        'recruitment_agency'  => 'রিক্রুটমেন্ট এজেন্সি',
                                    ]),
                                Textarea::make('office_address')
                                    ->label('অফিসের ঠিকানা')
                                    ->required()
                                    ->maxLength(500)
                                    ->columnSpanFull(),
                                TextInput::make('city')
                                    ->label('শহর')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('country')
                                    ->label('দেশ')
                                    ->default('Saudi Arabia')
                                    ->required()
                                    ->maxLength(100),
                                TextInput::make('phone_office')
                                    ->label('অফিস ফোন নম্বর')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('whatsapp_number')
                                    ->label('WhatsApp নম্বর')
                                    ->required()
                                    ->tel()
                                    ->maxLength(20),
                                TextInput::make('years_in_business')
                                    ->label('কত বছর ধরে এই ব্যবসায় আছেন')
                                    ->required()
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(80),
                            ])
                            ->columns(2),

                        Tab::make('ভেরিফিকেশন ডকুমেন্ট')
                            ->icon('heroicon-o-shield-check')
                            ->schema([
                                FileUpload::make('passport_copy')
                                    ->label('পাসপোর্ট কপি (ছবি/PDF)')
                                    ->disk('private')
                                    ->directory('agent-documents/passport')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(4096)
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file): string => (string) Str::ulid() . '.' . $file->getClientOriginalExtension()
                                    )
                                    ->required()
                                    ->columnSpanFull(),
                                FileUpload::make('nid_copy')
                                    ->label('জাতীয় পরিচয়পত্র (NID) কপি')
                                    ->disk('private')
                                    ->directory('agent-documents/nid')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(4096)
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file): string => (string) Str::ulid() . '.' . $file->getClientOriginalExtension()
                                    )
                                    ->required()
                                    ->columnSpanFull(),
                                FileUpload::make('agency_license')
                                    ->label('এজেন্সি লাইসেন্স (ঐচ্ছিক)')
                                    ->disk('private')
                                    ->directory('agent-documents/license')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(4096)
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file): string => (string) Str::ulid() . '.' . $file->getClientOriginalExtension()
                                    )
                                    ->columnSpanFull(),
                                FileUpload::make('company_cr_copy')
                                    ->label('কোম্পানি CR কপি (ঐচ্ছিক)')
                                    ->disk('private')
                                    ->directory('agent-documents/cr')
                                    ->visibility('private')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->maxSize(4096)
                                    ->getUploadedFileNameForStorageUsing(
                                        fn ($file): string => (string) Str::ulid() . '.' . $file->getClientOriginalExtension()
                                    )
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ])
            ->statePath('data')
            ->disabled(fn (): bool => $this->record->is_verified);
    }

    public function submit(): void
    {
        if ($this->record->is_verified) {
            Notification::make()
                ->title('আপনার প্রোফাইল ইতিমধ্যে ভেরিফাইড, পরিবর্তন করা যাবে না।')
                ->warning()
                ->send();

            return;
        }

        $data = $this->form->getState();

        $this->record->fill($data)->save();

        Notification::make()
            ->title('প্রোফাইল সংরক্ষণ করা হয়েছে এবং ভেরিফিকেশনের জন্য জমা দেওয়া হয়েছে')
            ->success()
            ->send();

        $this->record->refresh();
    }
}