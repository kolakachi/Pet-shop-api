<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('order_statuses')->truncate();

        $statuses = ['Pending', 'Shipped', 'Delivered', 'Cancelled'];

        foreach ($statuses as $status) {
            OrderStatus::create([
                'uuid' => Str::uuid(),
                'title' => $status,
            ]);
        }
    }
}
