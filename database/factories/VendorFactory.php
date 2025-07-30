<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str as str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = Vendor::class;

    public function definition(): array
    {
        $breakfastDelivery = null;
        if (fake()->boolean(60)) { // 60% kemungkinan akan ada waktu pengiriman sarapan
            $startBreakfast = fake()->dateTimeBetween('07:00', '08:00');
            $endBreakfast = fake()->dateTimeBetween($startBreakfast->format('H:i'), '09:30');
            $breakfastDelivery = $startBreakfast->format('H:i') . ' - ' . $endBreakfast->format('H:i');
        }

        $lunchDelivery = null;
        if (fake()->boolean(90)) { // 90% kemungkinan akan ada waktu pengiriman makan siang
            $startLunch = fake()->dateTimeBetween('11:00', '11:30');
            $endLunch = fake()->dateTimeBetween($startLunch->format('H:i'), '13:00');
            $lunchDelivery = $startLunch->format('H:i') . ' - ' . $endLunch->format('H:i');
        }

        $dinnerDelivery = null;
        if (fake()->boolean(70)) { // 60% kemungkinan akan ada waktu pengiriman makan malam
            $startDinner = fake()->dateTimeBetween('17:30', '18:00');
            $endDinner = fake()->dateTimeBetween($startDinner->format('H:i'), '19:00');
            $dinnerDelivery = $startDinner->format('H:i') . ' - ' . $endDinner->format('H:i');
        }

        return [
            'userId' => User::factory()->create(['role' => 'Vendor', 'password' => Hash::make('password')])->userId,
            'name' => Str::words(fake()->company(), 2, ''),
            'breakfast_delivery' => $breakfastDelivery, 
            'lunch_delivery' => $lunchDelivery,         
            'dinner_delivery' => $dinnerDelivery,       
            'logo' => fake()->randomElement(['logo-aldenaire-catering.jpg', 'logo 2.png', 'logo 3.png', 'logo 4.png', 'logo 5.png']),
            'phone_number' => fake()->phoneNumber(),
            'rating' => fake()->randomFloat(1, 1, 5),
            'provinsi' => fake()->state(),
            'kota' => fake()->city(),   
            'kecamatan' => fake()->streetName(),
            'kelurahan' => fake()->streetName(),
            'kode_pos' => fake()->postcode(),
            'jalan' => fake()->streetAddress()
        ];
    }

    public function configure()
    {
        // Pastikan setiap Vendor memiliki minimal 1 Package setelah dibuat
        return $this->afterCreating(function (Vendor $vendor) {
            Package::factory()->for($vendor)->create();

            // dan bisa saja lebih dari 1 paket
            if ($this->faker->boolean(70)) {
                Package::factory()->count($this->faker->numberBetween(1, 3))->for($vendor)->create();
            }
        });
    }
}
