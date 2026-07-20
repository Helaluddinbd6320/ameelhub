<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // FEES
            ['key' => 'cv_approval_fee',       'value' => '10',    'group' => 'fees'],
            ['key' => 'job_fee_reveal_cost',   'value' => '0.5',   'group' => 'fees'],
            ['key' => 'contact_reveal_fee',    'value' => '5',     'group' => 'fees'],
            ['key' => 'deal_commission_pct',   'value' => '8',     'group' => 'fees'],
            ['key' => 'min_withdrawal_sar',    'value' => '50',    'group' => 'fees'],
            ['key' => 'withdrawal_fee_sar',    'value' => '0',     'group' => 'fees'],
            ['key' => 'referral_bonus_sar',    'value' => '20',    'group' => 'fees'],

            // RULES
            ['key' => 'selection_expire_hours','value' => '48',    'group' => 'rules'],
            ['key' => 'nok_daily_limit',       'value' => '10',    'group' => 'rules'],
            ['key' => 'nok_bulk_max',          'value' => '5',     'group' => 'rules'],
            ['key' => 'nok_expire_hours',      'value' => '48',    'group' => 'rules'],
            ['key' => 'job_auto_close_days',   'value' => '90',    'group' => 'rules'],

            // MILESTONES
            ['key' => 'milestone_1_pct',       'value' => '20',    'group' => 'milestones'],
            ['key' => 'milestone_1_title',     'value' => 'Worker সৌদি আরবে পৌঁছেছে',      'group' => 'milestones'],
            ['key' => 'milestone_1_title_en',  'value' => 'Worker arrived in Saudi Arabia', 'group' => 'milestones'],
            ['key' => 'milestone_2_pct',       'value' => '40',    'group' => 'milestones'],
            ['key' => 'milestone_2_title',     'value' => 'Company Iqama/Kafala সম্পন্ন',   'group' => 'milestones'],
            ['key' => 'milestone_2_title_en',  'value' => 'Iqama/Kafala completed',          'group' => 'milestones'],
            ['key' => 'milestone_3_pct',       'value' => '40',    'group' => 'milestones'],
            ['key' => 'milestone_3_title',     'value' => '১ মাস কাজ সম্পন্ন, সব ঠিকঠাক',  'group' => 'milestones'],
            ['key' => 'milestone_3_title_en',  'value' => '1 month work completed successfully', 'group' => 'milestones'],

            // GENERAL
            ['key' => 'site_name',             'value' => 'AmeelHub',                  'group' => 'general'],
            ['key' => 'site_url',              'value' => 'https://ameelhub.com',       'group' => 'general'],
            ['key' => 'contact_email',         'value' => 'info@ameelhub.com',          'group' => 'general'],
            ['key' => 'contact_phone',         'value' => '+966XXXXXXXXX',              'group' => 'general'],
            ['key' => 'default_locale',        'value' => 'bn',                         'group' => 'general'],

            // SOCIAL
            ['key' => 'facebook_url',          'value' => '',  'group' => 'social'],
            ['key' => 'instagram_url',         'value' => '',  'group' => 'social'],
            ['key' => 'whatsapp_support',      'value' => '',  'group' => 'social'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->info('✅ Settings সিড হয়েছে: ' . count($settings) . 'টি সেটিং');
    }
}