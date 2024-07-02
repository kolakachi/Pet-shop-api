<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('payments')->truncate();

        $paymentMethods = [
            [
                'type' => 'credit_card',
                'details' => json_encode([
                    'holder_name' => 'John Doe',
                    'number' => '1234567812345678',
                    'ccv' => 123,
                    'expire_date' => '12/23',
                ]),
            ],
            [
                'type' => 'cash_on_delivery',
                'details' => json_encode([
                    'first_name' => 'Jane',
                    'last_name' => 'Doe',
                    'address' => '123 Main St',
                ]),
            ],
            [
                'type' => 'bank_transfer',
                'details' => json_encode([
                    'swift' => 'ABC123',
                    'iban' => 'DE89370400440532013000',
                    'name' => 'John Smith',
                ]),
            ],
        ];

        foreach ($paymentMethods as $method) {
            Payment::create([
                'uuid' => Str::uuid(),
                'type' => $method['type'],
                'details' => $method['details'],
            ]);
        }
    }
}
