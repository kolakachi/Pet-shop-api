<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the users table
        DB::table('users')->truncate();

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

        // Create order statuses and payments before creating users
        $this->call(OrderStatusSeeder::class);
        $this->call(PaymentSeeder::class);

        $orderStatuses = OrderStatus::all();
        $payments = Payment::all();

        // Regular users with dynamic emails
        for ($i = 0; $i < 10; $i++) {
            $timestamp = now()->timestamp;
            $userId = DB::table('users')->insertGetId([
                'uuid' => Str::uuid()->toString(),
                'first_name' => 'User'.$i,
                'last_name' => 'Example'.$i,
                'is_admin' => false,
                'email' => 'user'.$i.'.'.$timestamp.'@example.com',
                'password' => Hash::make('userpassword'),
                'avatar' => null,
                'address' => '123 Example St',
                'phone_number' => '555-555-55'.$i,
                'is_marketing' => false,
                'created_at' => now(),
                'updated_at' => now(),
                'last_login_at' => null,
            ]);

            // Ensure each user has at least 10 orders
            for ($j = 0; $j < 10; $j++) {
                Order::create([
                    'uuid' => Str::uuid()->toString(),
                    'user_id' => $userId,
                    'order_status_id' => $orderStatuses->random()->id,
                    'payment_id' => $payments->random()->id,
                    'products' => json_encode([
                        ['product' => Str::uuid()->toString(), 'quantity' => rand(1, 5)],
                    ]),
                    'address' => json_encode([
                        'billing' => '123 Billing St',
                        'shipping' => '456 Shipping Ln',
                    ]),
                    'delivery_fee' => rand(5, 20),
                    'amount' => rand(50, 200),
                    'shipped_at' => now()->addDays(rand(1, 10)),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
