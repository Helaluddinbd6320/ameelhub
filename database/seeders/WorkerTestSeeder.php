<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Step 11.3 (AI Job Match) — টেস্টিং সহায়ক সিডার।
 *
 * ৫ জন varied active worker profile বানায় যাতে JobMatchService এর প্রতিটা
 * component (skill / location / salary / visa) আলাদা আলাদা combination-এ
 * টেস্ট করা যায়। এটা মূল DatabaseSeeder এ যুক্ত করা হয়নি — ম্যানুয়ালি চালান:
 *
 *   php artisan db:seed --class="Database\Seeders\WorkerTestSeeder"
 *
 * নোট: Worker মডেলে status/is_verified/approval_fee_charged ইত্যাদি $guarded,
 * তাই forceCreate()/forceFill()->save() ব্যবহার করা হয়েছে — সাধারণ create()
 * দিয়ে এই ফিল্ডগুলো silently drop হয়ে যেত।
 */
class WorkerTestSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🧪 টেস্ট Worker profiles বানানো হচ্ছে...');

        $testWorkers = [
            [
                // Job Post #1 (skill_category_id: 4, employer_city: Jedda, salary: 2000)
                // এর সাথে প্রায় পারফেক্ট ম্যাচ — সব dimension-ই ভালো স্কোর করা উচিত।
                'email'                 => 'test.worker1@ameelhub.test',
                'full_name_bn'          => 'রফিকুল ইসলাম (টেস্ট ১)',
                'full_name_en'          => 'RAFIQUL ISLAM (TEST 1)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Jedda',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => 1800,
                'visa_status'           => 'free_exit',
                'transfer_possible'     => false,
            ],
            [
                // একই skill, ভিন্ন শহর, বেতন প্রত্যাশা বেশি, iqama+transfer সম্ভব।
                'email'                 => 'test.worker2@ameelhub.test',
                'full_name_bn'          => 'করিম মিয়া (টেস্ট ২)',
                'full_name_en'          => 'KARIM MIA (TEST 2)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Riyadh',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => 2500,
                'visa_status'           => 'iqama',
                'transfer_possible'     => true,
            ],
            [
                // ভিন্ন skill, একই শহর — মাঝারি ম্যাচ।
                'email'                 => 'test.worker3@ameelhub.test',
                'full_name_bn'          => 'জামাল হোসেন (টেস্ট ৩)',
                'full_name_en'          => 'JAMAL HOSSAIN (TEST 3)',
                'skill_category_id'     => 1,
                'present_location_city' => 'Jedda',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => 1500,
                'visa_status'           => 'visit',
                'transfer_possible'     => false,
            ],
            [
                // দেশের বাইরে, ভিন্ন skill, উচ্চ বেতন প্রত্যাশা — কম ম্যাচ।
                'email'                 => 'test.worker4@ameelhub.test',
                'full_name_bn'          => 'সেলিম আহমেদ (টেস্ট ৪)',
                'full_name_en'          => 'SELIM AHMED (TEST 4)',
                'skill_category_id'     => 2,
                'present_location_city' => null,
                'is_in_saudi'           => false,
                'expected_salary_sar'   => 5000,
                'visa_status'           => 'new_visa',
                'transfer_possible'     => false,
            ],
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test1.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test2.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],

            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test32.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test23.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'te1st3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'tes4t3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            

            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'tes4t3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'test53.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'te6st3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'teqst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'tewst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'teest3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'terst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'teqst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 't1est3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'teszt3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'txest3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'tecst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'tevst3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
            [
                // একই skill, iqama কিন্তু transfer সম্ভব না, বেতন প্রত্যাশা নেই।
                'email'                 => 'te5st3.worker5@ameelhub.test',
                'full_name_bn'          => 'নাসির উদ্দিন (টেস্ট ৫)',
                'full_name_en'          => 'NASIR UDDIN (TEST 5)',
                'skill_category_id'     => 4,
                'present_location_city' => 'Dammam',
                'is_in_saudi'           => true,
                'expected_salary_sar'   => null,
                'visa_status'           => 'iqama',
                'transfer_possible'     => false,
            ],
            
        ];

        foreach ($testWorkers as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['full_name_en'],
                    'password'          => Hash::make('password'),
                    'role'              => 'worker',
                    'email_verified_at' => now(),
                ]
            );

            if (method_exists($user, 'hasRole') && ! $user->hasRole('worker')) {
                $user->assignRole('worker');
            }

            // UserObserver হয়তো registration-এর সময় draft Worker আগেই বানিয়ে ফেলেছে —
            // থাকলে সেটাকেই আপডেট করবো (ডুপ্লিকেট CV তৈরি না করে)।
            $worker = Worker::withTrashed()->where('worker_user_id', $user->id)->first();

            $attributes = [
                'uuid'                   => $worker->uuid ?? (string) str()->uuid(),
                'submitted_by_id'        => $user->id,
                'worker_user_id'         => $user->id,
                'full_name_bn'           => $data['full_name_bn'],
                'full_name_en'           => $data['full_name_en'],
                'nationality'            => 'Bangladeshi',
                'skill_category_id'      => $data['skill_category_id'],
                'present_location_city'  => $data['present_location_city'],
                'present_location_country' => 'Saudi Arabia',
                'is_in_saudi'            => $data['is_in_saudi'],
                'expected_salary_sar'    => $data['expected_salary_sar'],
                'visa_status'            => $data['visa_status'],
                'transfer_possible'      => $data['transfer_possible'],
                'medical_fit'            => true,
                // ── এগুলো $guarded — এই কারণেই forceFill/forceCreate লাগবে ──
                'status'                 => 'active',
                'is_verified'            => true,
                'approval_fee_charged'   => true,
            ];

            if ($worker) {
                $worker->forceFill($attributes)->save();
            } else {
                Worker::forceCreate($attributes);
            }

            $this->command->info("✓ {$data['full_name_en']} ({$data['email']}) — active CV তৈরি/আপডেট হয়েছে");
        }

        $this->command->newLine();
        $this->command->info('🎉 ৫টা টেস্ট Worker profile প্রস্তুত। পাসওয়ার্ড সবার জন্য: password');
    }
}