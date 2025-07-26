<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Package;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween("-2 week", "+1 week");
        $start = Carbon::parse($start)->startOfWeek(Carbon::MONDAY);
        $end = (clone $start)->modify("+6 days");

        // Only pick vendors who have at least one package
        $vendorId = Package::query()
            ->inRandomOrder()
            ->first()?->vendorId;

        $address = Address::inRandomOrder()->first();

        return [
            'userId' => $address->userId,
            'vendorId' => $vendorId,
            'totalPrice' => 0,
            'startDate' => $start,
            'endDate' => $end,
            'isCancelled' => $this->faker->boolean(10),
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            'kabupaten' => $address->kabupaten,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
            'notes' => $address->notes,
        ];
    }

    public function forUser(int $userId): static
    {
        return $this->state(function (array $attributes) use ($userId) {
            $address = Address::where('userId', $userId)->first();

            if (!$address) {
                throw new \Exception("User with ID {$userId} doesn't have an address.");
            }

            $vendorId = Package::query()
                ->inRandomOrder()
                ->first()?->vendorId;

            if (!$vendorId) {
                throw new \Exception("No vendor with packages found.");
            }

            return [
                'userId' => $userId,
                'vendorId' => $vendorId, // Insert random vendorId from Package
                'totalPrice' => 0,
                'provinsi' => $address->provinsi,
                'kota' => $address->kota,
                'kabupaten' => $address->kabupaten,
                'kecamatan' => $address->kecamatan,
                'kelurahan' => $address->kelurahan,
                'kode_pos' => $address->kode_pos,
                'jalan' => $address->jalan,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'notes' => $address->notes,
            ];
        });
    }

    public function forVendor(int $vendorId): static
    {
        return $this->state(function (array $attributes) use ($vendorId) {
            $start = $this->faker->dateTimeBetween("-1 year", "now");
            $end = (clone $start)->modify("+" . rand(1, 14) . "days");

            $address = Address::inRandomOrder()->first();

            return [
                'userId' => $address->userId,
                'vendorId' => $vendorId,
                'totalPrice' => 0,
                'startDate' => $start,
                'endDate' => $end,
                'isCancelled' => $this->faker->boolean(10),
                'provinsi' => $address->provinsi,
                'kota' => $address->kota,
                'kabupaten' => $address->kabupaten,
                'kecamatan' => $address->kecamatan,
                'kelurahan' => $address->kelurahan,
                'kode_pos' => $address->kode_pos,
                'jalan' => $address->jalan,
                'recipient_name' => $address->recipient_name,
                'recipient_phone' => $address->recipient_phone,
                'notes' => $address->notes,
            ];
        });
    }
}
