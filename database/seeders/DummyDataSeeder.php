<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 1; $i <= 3; $i++) {
            $company = \App\Models\Company::create(['name' => 'Test Company ' . $i]);
            
            // Create Admin
            $admin = \App\Models\User::create([
                'name' => 'Admin ' . $i,
                'email' => 'admin'.$i.'@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role_id' => 2,
                'company_id' => $company->id
            ]);

            // Create Member
            $member = \App\Models\User::create([
                'name' => 'Member ' . $i,
                'email' => 'member'.$i.'@example.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role_id' => 3,
                'company_id' => $company->id
            ]);

            // Create URLs
            for ($j = 1; $j <= 4; $j++) {
                \App\Models\ShortUrl::create([
                    'short_code' => \Illuminate\Support\Str::random(6),
                    'original_url' => 'https://example.com/' . rand(1000, 9999),
                    'user_id' => $admin->id,
                    'company_id' => $company->id
                ]);
            }
        }
    }
}
