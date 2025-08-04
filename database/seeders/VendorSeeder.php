<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class VendorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = Province::all();
        $cities = City::all()->groupBy('province_id');
        $districts = District::all()->groupBy('city_id');
        $villages = Village::all()->groupBy('district_id');

        $vendors = [
            [
                'userId' => User::factory()->create([
                    'email' => 'vendor1@mail.com',
                    'role' => "Vendor",
                    'password' => Hash::make('password')
                ])->userId,
                'name' => 'Nusantara Delights',
                'breakfast_delivery' => '06:30-09:30',
                'lunch_delivery' => '11:00-14:00',
                'dinner_delivery' => '17:30-20:00',
                'logo' => 'logo-aldenaire-catering.jpg',
                'phone_number' => '081234567890',
                'rating' => 4.7,
            ],
            [
                'userId' => User::factory()->create([
                    'email' => 'vendor2@mail.com',
                    'role' => "Vendor",
                    'password' => Hash::make('password')
                ])->userId,
                'name' => 'Tropical Bites',
                'breakfast_delivery' => '07:00-10:00',
                'lunch_delivery' => '12:00-15:00',
                'dinner_delivery' => '18:00-21:00',
                'logo' => 'logo 2.png',
                'phone_number' => '089876543210',
                'rating' => 4.3,
            ],
            [
                'userId' => User::factory()->create([
                    'email' => 'vendor3@mail.com',
                    'role' => "Vendor",
                    'password' => Hash::make('password')
                ])->userId,
                'name' => 'Sari Rasa Kitchen',
                'breakfast_delivery' => '06:00-08:30',
                'lunch_delivery' => '11:30-14:30',
                'dinner_delivery' => '17:00-19:30',
                'logo' => 'logo 3.png',
                'phone_number' => '082112345678',
                'rating' => 4.5,
            ],
        ];

        foreach ($vendors as $vendor) {
            $address = $this->getRandomAddress($provinces, $cities, $districts, $villages);
            Vendor::factory()->create(array_merge($vendor, $address));
        }

        foreach ($provinces as $province) {
            $provinceCities = $cities->get($province->id);
            if (!$provinceCities || $provinceCities->isEmpty())
                continue;

            $vendorCount = rand(1, 5);
            for ($i = 0; $i < $vendorCount; $i++) {
                $city = $provinceCities->random();
                $cityDistricts = $districts->get($city->id);
                if (!$cityDistricts || $cityDistricts->isEmpty())
                    continue;

                $district = $cityDistricts->random();
                $districtVillages = $villages->get($district->id);
                if (!$districtVillages || $districtVillages->isEmpty())
                    continue;

                $village = $districtVillages->random();

                $address = [
                    'provinsi' => $province->name,
                    'kota' => $city->name,
                    'kecamatan' => $district->name,
                    'kelurahan' => $village->name,
                    'kode_pos' => fake()->postcode(),
                    'jalan' => fake()->streetAddress(),
                ];

                Vendor::factory()->create($address);
            }
        }
    }

    private function getRandomAddress($provinces, $cities, $districts, $villages): array
    {
        $province = $provinces->random();
        $provinceCities = $cities->get($province->id);
        if (!$provinceCities || $provinceCities->isEmpty())
            return [];

        $city = $provinceCities->random();
        $cityDistricts = $districts->get($city->id);
        if (!$cityDistricts || $cityDistricts->isEmpty())
            return [];

        $district = $cityDistricts->random();
        $districtVillages = $villages->get($district->id);
        if (!$districtVillages || $districtVillages->isEmpty())
            return [];

        $village = $districtVillages->random();

        return [
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
            'kode_pos' => fake()->postcode(),
            'jalan' => fake()->streetAddress(),
        ];
    }
}
