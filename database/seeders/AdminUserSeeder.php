<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin তৈরি
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@ameelhub.com'],
            [
                'name'              => 'Chapai Admin',
                'email'             => 'admin@ameelhub.com',
                'password'          => Hash::make('Admin@12345!'),
                'role'              => 'super_admin',
                'email_verified_at' => now(),
                'available_balance' => 0.00,
                'held_balance'      => 0.00,
            ]
        );

        // Spatie role assign
        $superAdmin->assignRole('super_admin');

        $this->command->info('✅ Super Admin তৈরি হয়েছে');
        $this->command->line('   Email   : admin@ameelhub.com');
        $this->command->line('   Password: Admin@12345!');
        $this->command->warn('   ⚠️  Production এ Deploy এর পর পাসওয়ার্ড পরিবর্তন করুন!');

        // Test Agent (optional — development only)
        if (app()->isLocal()) {
            $agent = User::updateOrCreate(
                ['email' => 'agent@ameelhub.com'],
                [
                    'name'              => 'Test Agent',
                    'email'             => 'agent@ameelhub.com',
                    'password'          => Hash::make('Agent@12345!'),
                    'role'              => 'agent',
                    'email_verified_at' => now(),
                    'available_balance' => 100.00,
                    'held_balance'      => 0.00,
                ]
            );
            $agent->assignRole('agent');

            // Test Worker
            $worker = User::updateOrCreate(
                ['email' => 'worker@ameelhub.com'],
                [
                    'name'              => 'Test Worker',
                    'email'             => 'worker@ameelhub.com',
                    'password'          => Hash::make('Worker@12345!'),
                    'role'              => 'worker',
                    'email_verified_at' => now(),
                    'available_balance' => 50.00,
                    'held_balance'      => 0.00,
                ]
            );
            $worker->assignRole('worker');

            $this->command->info('✅ Test Users তৈরি হয়েছে (local only)');
            $this->command->line('   Agent  : agent@ameelhub.com / Agent@12345!');
            $this->command->line('   Worker : worker@ameelhub.com / Worker@12345!');
        }
    }
}