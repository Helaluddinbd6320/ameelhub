<?php

namespace App\Filament\Admin\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\Select as FormSelect;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Blueprint Section 17 — Admin Panel Pages:
 * "Settings → All settings (fees, commission, milestones, limits, site info)"
 *
 * এই পেজ আগে কখনো বানানো হয়নি — Setting model (key-value + cache wrap) আগে থেকেই
 * ছিল, কিন্তু Admin-এর জন্য এডিট করার কোনো UI ছিল না। এই পেজ সেই gap পূরণ করছে।
 *
 * ACCESS: শুধু super_admin/admin — fee/commission/milestone percentage-এর মতো
 * sensitive সেটিংস staff-কে দেখানো/এডিট করতে দেওয়া হয়নি (10.7d audit-এর Option B
 * নীতির সাথে সামঞ্জস্যপূর্ণ — Withdrawal approve/reject ও document download-ও
 * একইভাবে staff থেকে বাদ দেওয়া হয়েছিল)।
 */
class Settings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.admin.pages.settings';

    public ?array $data = [];

    public static function getNavigationLabel(): string
    {
        return 'সেটিংস';
    }

    public function getTitle(): string
    {
        return 'সেটিংস — ফি, কমিশন, নিয়ম ও সাইট তথ্য';
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'সিস্টেম';
    }

    /**
     * শুধু super_admin/admin এই পেজ দেখতে/এডিট করতে পারবে — staff বাদ
     * (10.7d audit-এ Withdrawal/Document-download-এর জন্য নেওয়া একই সিদ্ধান্তের ধারাবাহিকতা)।
     */
    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    /**
     * এই পেজ যেসব key ম্যানেজ করে তার সম্পূর্ণ তালিকা — SettingsSeeder-এর সাথে
     * সামঞ্জস্যপূর্ণ রাখতে হবে (নতুন key সিডারে যোগ হলে এখানেও যোগ করতে হবে)।
     */
    protected function settingKeys(): array
    {
        return [
            // FEES
            'cv_approval_fee', 'job_fee_reveal_cost', 'contact_reveal_fee',
            'deal_commission_pct', 'min_withdrawal_sar', 'withdrawal_fee_sar',
            'referral_bonus_sar',
            // RULES
            'selection_expire_hours', 'nok_daily_limit', 'nok_bulk_max',
            'nok_expire_hours', 'job_auto_close_days',
            // MILESTONES
            'milestone_1_pct', 'milestone_1_title', 'milestone_1_title_en',
            'milestone_2_pct', 'milestone_2_title', 'milestone_2_title_en',
            'milestone_3_pct', 'milestone_3_title', 'milestone_3_title_en',
            // GENERAL
            'site_name', 'site_url', 'contact_email', 'contact_phone', 'default_locale',
            // SOCIAL
            'facebook_url', 'instagram_url', 'whatsapp_support',
        ];
    }

    public function mount(): void
    {
        $current = [];

        foreach ($this->settingKeys() as $key) {
            $current[$key] = Setting::get($key);
        }

        $this->form->fill($current);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make()->tabs([

                    // ════════════════════════════════════════
                    // TAB 1 — ফি (Fees)
                    // ════════════════════════════════════════
                    Tab::make('ফি (Fees)')->schema([

                        TextInput::make('cv_approval_fee')
                            ->label('CV অনুমোদন ফি (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('job_fee_reveal_cost')
                            ->label('Job Fee Reveal খরচ (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('contact_reveal_fee')
                            ->label('Contact Reveal ফি (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('deal_commission_pct')
                            ->label('Chapai কমিশন (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('min_withdrawal_sar')
                            ->label('সর্বনিম্ন Withdrawal (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('withdrawal_fee_sar')
                            ->label('Withdrawal ফি (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                        TextInput::make('referral_bonus_sar')
                            ->label('Referral Bonus (SAR)')
                            ->numeric()
                            ->minValue(0)
                            ->required(),

                    ])->columns(2),

                    // ════════════════════════════════════════
                    // TAB 2 — নিয়ম (Rules)
                    // ════════════════════════════════════════
                    Tab::make('নিয়ম (Rules)')->schema([

                        TextInput::make('selection_expire_hours')
                            ->label('Selection মেয়াদ (ঘণ্টা)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('nok_daily_limit')
                            ->label('প্রতিদিন Nok সীমা (প্রতি জব প্রতি এজেন্ট)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('nok_bulk_max')
                            ->label('Bulk Nok সর্বোচ্চ সংখ্যা')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('nok_expire_hours')
                            ->label('Nok মেয়াদ (ঘণ্টা)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                        TextInput::make('job_auto_close_days')
                            ->label('Job Auto-Close (দিন)')
                            ->numeric()
                            ->minValue(1)
                            ->required(),

                    ])->columns(2),

                    // ════════════════════════════════════════
                    // TAB 3 — মাইলস্টোন
                    // ════════════════════════════════════════
                    Tab::make('মাইলস্টোন')->schema([

                        TextInput::make('milestone_1_pct')
                            ->label('মাইলস্টোন ১ — শতাংশ (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('milestone_1_title')
                            ->label('মাইলস্টোন ১ — শিরোনাম (বাংলা)')
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('milestone_1_title_en')
                            ->label('Milestone 1 — Title (English)')
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('milestone_2_pct')
                            ->label('মাইলস্টোন ২ — শতাংশ (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('milestone_2_title')
                            ->label('মাইলস্টোন ২ — শিরোনাম (বাংলা)')
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('milestone_2_title_en')
                            ->label('Milestone 2 — Title (English)')
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('milestone_3_pct')
                            ->label('মাইলস্টোন ৩ — শতাংশ (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required(),

                        TextInput::make('milestone_3_title')
                            ->label('মাইলস্টোন ৩ — শিরোনাম (বাংলা)')
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('milestone_3_title_en')
                            ->label('Milestone 3 — Title (English)')
                            ->maxLength(200)
                            ->required(),

                    ])->columns(2),

                    // ════════════════════════════════════════
                    // TAB 4 — সাধারণ তথ্য
                    // ════════════════════════════════════════
                    Tab::make('সাধারণ তথ্য')->schema([

                        TextInput::make('site_name')
                            ->label('সাইটের নাম')
                            ->maxLength(100)
                            ->required(),

                        TextInput::make('site_url')
                            ->label('সাইট URL')
                            ->url()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('contact_email')
                            ->label('যোগাযোগ ইমেইল')
                            ->email()
                            ->maxLength(200)
                            ->required(),

                        TextInput::make('contact_phone')
                            ->label('যোগাযোগ ফোন')
                            ->tel()
                            ->maxLength(30)
                            ->required(),

                        FormSelect::make('default_locale')
                            ->label('ডিফল্ট ভাষা')
                            ->options([
                                'bn' => 'বাংলা',
                                'en' => 'English',
                                'ar' => 'العربية',
                            ])
                            ->required(),

                    ])->columns(2),

                    // ════════════════════════════════════════
                    // TAB 5 — সোশ্যাল
                    // ════════════════════════════════════════
                    Tab::make('সোশ্যাল')->schema([

                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255)
                            ->nullable(),

                        TextInput::make('whatsapp_support')
                            ->label('WhatsApp Support নম্বর')
                            ->tel()
                            ->maxLength(30)
                            ->nullable(),

                    ])->columns(2),

                ])->columnSpanFull(),
            ])
            ->statePath('data');
    }

    /**
     * ফর্ম validate করে (numeric/url/email rules সহ) প্রতিটা key
     * Setting::set() দিয়ে সেভ করে — যা স্বয়ংক্রিয়ভাবে সংশ্লিষ্ট cache key-ও
     * invalidate করে দেয় (Setting model দেখুন)।
     */
    public function save(): void
    {
        $state = $this->form->getState();

        foreach ($state as $key => $value) {
            Setting::set($key, (string) $value);
        }

        Notification::make()
            ->title('সেটিংস সংরক্ষণ করা হয়েছে')
            ->success()
            ->send();
    }
}