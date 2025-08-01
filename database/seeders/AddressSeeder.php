<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AddressSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->where('role', 'LIKE', 'Customer')->get();
        $provinces = Province::all();
        $cities = City::all()->groupBy('province_id');
        $districts = District::all()->groupBy('city_id');
        $villages = Village::all()->groupBy('district_id');

        foreach ($users as $user) {
            $province = $provinces->random();

            $provinceCities = $cities->get($province->id);
            if (!$provinceCities || $provinceCities->isEmpty())
                continue;
            $city = $provinceCities->random();

            $cityDistricts = $districts->get($city->id);
            if (!$cityDistricts || $cityDistricts->isEmpty())
                continue;
            $district = $cityDistricts->random();

            $districtVillages = $villages->get($district->id);
            if (!$districtVillages || $districtVillages->isEmpty())
                continue;
            $village = $districtVillages->random();

            // 1 alamat utama per user
            Address::factory()->create([
                'userId' => $user->userId,
                'is_default' => true,
                'provinsi' => $province->name,
                'kota' => $city->name,
                'kecamatan' => $district->name,
                'kelurahan' => $village->name,
            ]);

            // 1-3 alamat tambahan
            $additionalCount = rand(1, 3);
            for ($i = 0; $i < $additionalCount; $i++) {
                $province = $provinces->random();
                $provinceCities = $cities->get($province->id);
                if (!$provinceCities || $provinceCities->isEmpty())
                    continue;
                $city = $provinceCities->random();

                $cityDistricts = $districts->get($city->id);
                if (!$cityDistricts || $cityDistricts->isEmpty())
                    continue;
                $district = $cityDistricts->random();

                $districtVillages = $villages->get($district->id);
                if (!$districtVillages || $districtVillages->isEmpty())
                    continue;
                $village = $districtVillages->random();

                Address::factory()->create([
                    'userId' => $user->userId,
                    'is_default' => false,
                    'provinsi' => $province->name,
                    'kota' => $city->name,
                    'kecamatan' => $district->name,
                    'kelurahan' => $village->name,
                ]);
            }
        }
    }
}
