<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Admin user
        DB::table('users')->insert([
            'uuid' => Str::uuid()->toString(),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_admin' => true,
            'email' => 'admin@buckhill.co.uk',
            'password' => Hash::make('admin'),
            'avatar' => null,
            'address' => 'Admin Address',
            'phone_number' => '000-000-0000',
            'is_marketing' => false,
            'created_at' => now(),
            'updated_at' => now(),
            'last_login_at' => null,
        ]);

        // Regular users with dynamic emails
        for ($i = 0; $i < 10; $i++) {
            $timestamp = now()->timestamp;
            DB::table('users')->insert([
                'uuid' => Str::uuid()->toString(),
                'first_name' => 'User' . $i,
                'last_name' => 'Example' . $i,
                'is_admin' => false,
                'email' => 'user' . $i . '.' . $timestamp . '@example.com',
                'password' => Hash::make('userpassword'),
                'avatar' => null,
                'address' => '123 Example St',
                'phone_number' => '555-555-55' . $i,
                'is_marketing' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'last_login_at' => null,
            ]);
        }
    }
}
