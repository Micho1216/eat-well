<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Province;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = Address::class;

    public function definition(): array
    {
        return [
            'provinsi' => fake()->state(),
            'kota' => fake()->city(),
            'kecamatan' => fake()->streetName(),
            'kelurahan' => fake()->streetName(),
            'kode_pos' => fake()->numerify('#####'),
            'jalan' => fake()->streetAddress(),
            'recipient_name' => fake()->name(),
            'recipient_phone' => fake()->regexify('[0-9]{10,15}'),
            'is_default' => false,
            // 'userId' => User::factory(),
            'notes' => fake()->optional()->sentence(10),
        ];
    }
}
