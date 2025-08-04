<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CustomerFirstPageTest extends TestCase
{
    // use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage for profile images
        Storage::fake('public');
    }

    public function test_customer_first_page_view_loads_successfully()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
             ->get(route('account-setup.customer-view'))
             ->assertStatus(200)
             ->assertViewIs('customer.customerFirstPage');
    }

    public function test_customer_address_and_profile_can_be_stored()
    {
        $user = User::factory()->create();

        // $province = Province::factory()->create(['name' => 'Jawa Barat']);
        // $city = City::factory()->create(['name' => 'Bandung']);
        // $district = District::factory()->create(['name' => 'Coblong']);
        // $village = Village::factory()->create(['name' => 'Dago']);

        $province  = Province::get()->first();
        $city      = City::get()->first();
        $district  = District::get()->first();
        $village   = Village::get()->first();

        dump($province, $city, $district, $village);
        $image = UploadedFile::fake()->image('avatar.jpg');
        $imageName = time().'.'.$image->getClientOriginalExtension();

        $data = [
            'name' => 'John Doe',
            'province' => $province->id,
            'city' => $city->id,
            'district' => $district->id,
            'village' => $village->id,
            'zipCode' => '40135',
            'address' => 'Jl. Dipatiukur No. 1',
            'phoneNumber' => '081234567890',
            'profile' => $image,
        ];

        $response = $this->actingAs($user)
                         ->post(route('account-setup.customer-store'), $data);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('addresses', [
            'userId' => $user->userId,
            'provinsi' => $province->name,
            'kota' => $city->name,
            'kecamatan' => $district->name,
            'kelurahan' => $village->name,
            'kode_pos' => '40135',
            'recipient_name' => 'John Doe',
        ]);

        // Assert profile image was stored
        $this->assertDatabaseHas('users', [
            'userId' => $user->userId,
            'profilePath' => 'storage/profiles/'.$imageName,
        ]);

    }

    public function test_customer_store_validation_fails_with_invalid_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
                         ->post(route('account-setup.customer-store'), [
                             'name' => '',
                             'zipCode' => '123', // too short
                             'phoneNumber' => 'abc', // invalid
                         ]);

        $response->assertSessionHasErrors(['name', 'province', 'city', 'district', 'village', 'address', 'zipCode', 'phoneNumber']);
    }
}
