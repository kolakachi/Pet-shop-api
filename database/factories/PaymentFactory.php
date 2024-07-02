<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        $type = $this->faker->randomElement(['credit_card', 'cash_on_delivery', 'bank_transfer']);

        return [
            'uuid' => $this->faker->uuid(),
            'type' => $type,
            'details' => json_encode($this->getPaymentDetails($type)),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function getPaymentDetails($type)
    {
        switch ($type) {
            case 'credit_card':
                return [
                    'holder_name' => $this->faker->name(),
                    'number' => $this->faker->creditCardNumber(),
                    'ccv' => $this->faker->numberBetween(100, 999),
                    'expire_date' => $this->faker->creditCardExpirationDateString(),
                ];
            case 'cash_on_delivery':
                return [
                    'first_name' => $this->faker->firstName(),
                    'last_name' => $this->faker->lastName(),
                    'address' => $this->faker->address(),
                ];
            case 'bank_transfer':
                return [
                    'swift' => $this->faker->swiftBicNumber(),
                    'iban' => $this->faker->iban(),
                    'name' => $this->faker->name(),
                ];
            default:
                return [];
        }
    }
}
