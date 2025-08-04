<?php


namespace Tests\Feature;

use App\Models\City;
use App\Models\District;
use App\Models\Province;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;

class VendorFirstPageTest extends TestCase
{
    // use RefreshDatabase;
    
    protected $count = 0;

    private function actingAsVendor()
    {
        /** @var User|Authenticatable $user */

        $user = User::factory()->create([
            'role' => 'Vendor',
        ]);

        $this->actingAs($user);

        return $user;
    }



    private function validPayload(array $overrides = []): array
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('vendor_logo' . $this->count . '.jpg');
        $this->count += 1;

        $province = Province::create(['name' => 'DKI Jakarta']);
        $city = City::create(['name' => 'Jakarta Selatan', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Setiabudi', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Karet', 'district_id' => $district->id]);

        return array_merge([
            'name' => 'Dummy',
            'breakfast_delivery' => '10:00 - 11:00',
            'lunch_delivery' => '12:00 - 13:00',
            'dinner_delivery' => '18:00 - 19:00',
            'phone_number' => '081234567890',
            'rating' => 0,
            'provinsi_name' => $province->name,
            'kota_name' => $city->name,
            'kecamatan_name' => $district->name,
            'kelurahan_name' => $village->name,
            'kode_pos' => '12345',
            'jalan' => 'Jalan Dummy',
            'logo' => $file,
       ], $overrides);
    }

    /** @test */
    public function tc1_valid_submission()
    {
        $this->actingAsVendor();


        $response = $this->post(route('vendor.store'), $this->validPayload());

        $response->assertRedirect('cateringHomePage');
        $this->assertDatabaseHas('vendors', ['name' => 'Dummy']);
    }

    /** @test */
    public function tc2_missing_logo()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['logo' => null]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['logo']);
    }

    /** @test */
    public function tc3_invalid_logo_type()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf')
        ]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['logo']);
    }

    /** @test */
    public function tc4_missing_vendor_name()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['name' => '']);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function tc5_invalid_breakfast_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startBreakfast' => '08:00',
            'closeBreakfast' => '07:00',
        ]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['closeBreakfast']);
    }

    /** @test */
    public function tc6_invalid_lunch_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startLunch' => '12:00',
            'closeLunch' => '12:00', // same time
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['closeLunch']);
    }

    /** @test */
    public function tc7_invalid_dinner_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startDinner' => '18:00',
            'closeDinner' => '17:30',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['closeDinner']);
    }

    /** @test */
    public function tc8_missing_province()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'provinsi_name' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['provinsi_name']);
    }

    /** @test */
    public function tc9_missing_city()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kota_name' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kota_name']);
    }

    /** @test */
    public function tc10_missing_district()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kecamatan_name' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kecamatan_name']);
    }

    /** @test */
    public function tc11_missing_village()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kelurahan_name' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kelurahan_name']);
    }

    /** @test */
    public function tc12_zip_code_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kode_pos' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kode_pos']);
    }

    /** @test */
    public function tc13_zip_code_invalid_length()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kode_pos' => '1234', 
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kode_pos']);
    }

    /** @test */
    public function tc14_phone_number_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** @test */
    public function tc15_phone_number_does_not_start_with_08()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '07123456789',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionDoesntHaveErrors(['phone_number']);
    }

    /** @test */
    public function tc16_phone_number_too_short()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '0812345' // only 7 digits
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** @test */
    public function tc17_phone_number_too_long()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '0812345678912345' // 16 digits
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** @test */
    public function tc18_address_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'jalan' => ''
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['jalan']);
    }

    /** @test */
    public function tc19_fix_province_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['provinsi_name' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['provinsi_name']);

        $payload['provinsi_name'] = 'DKI Jakarta';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['provinsi_name']);
    }

    /** @test */
    public function tc19_1_fix_city_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kota_name' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kota_name']);

        $payload['kota_name'] = 'Jakarta Selatan';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kota_name']);
    }

    /** @test */
    public function tc19_2_fix_kecamatan_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kecamatan_name' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kecamatan_name']);

        $payload['kecamatan_name'] = 'Setiabudi';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kecamatan_name']);
    }

    /** @test */
    public function tc19_3_fix_kelurahan_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kelurahan_name' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kelurahan_name']);

        $payload['kelurahan_name'] = 'Karet';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kelurahan_name']);
    }

    /** @test */
    public function tc20_optional_times_empty_but_required_valid()
    {
        $this->actingAsVendor();
        
        $payload = $this->validPayload([
            'breakfast_delivery' => '',
            'lunch_delivery' => '',
            'dinner_delivery' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertRedirect(route('vendor.home'));
        $this->assertDatabaseHas('vendors', ['name' => 'Dummy']);
    }

    /** @test */
    public function tc21_user_can_logout()
    {
         /** @var User|Authenticatable $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/'); // diarahkan ke homepage
        $this->assertGuest(); // user tidak lagi login
    }










}