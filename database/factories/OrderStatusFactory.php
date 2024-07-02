<?php

namespace Database\Factories;

use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderStatusFactory extends Factory
{
    protected $model = OrderStatus::class;

    public function definition()
    {
        return [
            'uuid' => $this->faker->uuid(),
            'title' => $this->faker->word(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
