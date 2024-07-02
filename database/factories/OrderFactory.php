<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'user_id' => User::factory(),  // Creates a new User if not provided
            'order_status_id' => OrderStatus::factory(),  // Creates a new OrderStatus if not provided
            'payment_id' => Payment::factory(),  // Creates a new Payment if not provided
            'products' => json_encode([
                [
                    'product' => Str::uuid()->toString(),
                    'quantity' => $this->faker->numberBetween(1, 5),
                ],
            ]),
            'address' => json_encode([
                'billing' => $this->faker->address(),
                'shipping' => $this->faker->address(),
            ]),
            'delivery_fee' => $this->faker->randomFloat(2, 0, 20),
            'amount' => $this->faker->randomFloat(2, 20, 200),
            'created_at' => now(),
            'updated_at' => now(),
            'shipped_at' => null,  // Can be set to a date if needed
        ];
    }
}
