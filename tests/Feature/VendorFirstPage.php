<?php


namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;

class VendorFirstPage extends TestCase
{


    private function actingAsVendor()
    {
        /** @var \App\Models\User $user */
        // ambil provinsi yang ada
        $provinsi = \App\Models\Province::first();
        $kota = \App\Models\City::first();
        $kecamatan = \App\Models\District::first();
        $kelurahan = \App\Models\Village::first();


        $user = \App\Models\User::factory()->create([
            'role' => 'Vendor',
        ]);

        // ⬅ Buat vendor agar controller bisa update-nya, bukan insert
        \App\Models\Vendor::create([
            'userId' => $user->userId,
            'name' => 'Dummy',
            'breakfast_delivery' => '10:00 - 11:00',
            'lunch_delivery' => '12:00 - 13:00',
            'dinner_delivery' => '18:00 - 19:00',
            'logo' => 'logo.jpg',
            'phone_number' => '081234567890',
            'rating' => 0,
            'provinsi' => $provinsi->name,
            'kota' => $kota->name,
            'kecamatan' => $kecamatan->name,
            'kelurahan' => $kelurahan->name,
            'kode_pos' => '12345',
            'jalan' => 'Jalan Dummy',
        ]);

        $this->actingAs($user);

        return $user;
    }



    private function validPayload(array $overrides = []): array
    {
        $provinsi = \App\Models\Province::first();
        $kota = \App\Models\City::first();
        $kecamatan = \App\Models\District::first();
        $kelurahan = \App\Models\Village::first();
        return array_merge([
            'logo' => UploadedFile::fake()->image('logo.jpg'),
            'name' => 'Test Vendor',
            'startBreakfast' => '08:00',
            'closeBreakfast' => '09:00',
            'startLunch' => '12:00',
            'closeLunch' => '12:30',
            'startDinner' => '18:00',
            'closeDinner' => '19:00',
            'provinsi_name' => $provinsi->name,
            'kota_name' => $kota->name,
            'kecamatan_name' => $kecamatan->name,
            'kelurahan_name' => $kelurahan->name,
            'kode_pos' => '12345',
            'phone_number' => '081234567890',
            'jalan' => 'Jalan Vendor',
        ], $overrides);
    }

    /** TC 1: Submit with all valid fields filled correctly */
    public function test_tc1_valid_submission()
    {
        $this->actingAsVendor();
        Storage::fake('public');

        $response = $this->post(route('vendor.store'), $this->validPayload());

        $response->dump();

        $response->assertRedirect('cateringHomePage');
        $this->assertDatabaseHas('vendors', ['name' => 'Test Vendor']);
    }

    /** TC 2: Submit without vendor logo */
    public function test_tc2_missing_logo()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['logo' => null]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['logo']);
    }

    /** TC 3: Submit with invalid logo type (PDF) */
    public function test_tc3_invalid_logo_type()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'logo' => UploadedFile::fake()->create('logo.pdf', 100, 'application/pdf')
        ]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['logo']);
    }

    /** TC 4: Submit without vendor name */
    public function test_tc4_missing_vendor_name()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['name' => '']);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['name']);
    }

    /** TC 5: Breakfast end time before start time */
    public function test_tc5_invalid_breakfast_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startBreakfast' => '08:00',
            'closeBreakfast' => '07:00',
        ]);

        $response = $this->post(route('vendor.store'), $payload);

        $response->assertSessionHasErrors(['closeBreakfast']);
    }
    public function test_tc6_invalid_lunch_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startLunch' => '12:00',
            'closeLunch' => '12:00', // same time
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['closeLunch']);
    }

    public function test_tc7_invalid_dinner_time()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'startDinner' => '18:00',
            'closeDinner' => '17:30',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['closeDinner']);
    }

    public function test_tc8_missing_province()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'provinsi' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['provinsi']);
    }

    public function test_tc9_missing_city()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kota' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kota']);
    }

    public function test_tc10_missing_district()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kecamatan' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kecamatan']);
    }

    public function test_tc11_missing_village()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kelurahan' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kelurahan']);
    }

    public function test_tc12_zip_code_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kode_pos' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kode_pos']);
    }

    public function test_tc13_zip_code_invalid_length()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'kode_pos' => '1234', // 4 digits
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['kode_pos']);
    }

    public function test_tc14_phone_number_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '',
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    public function test_tc15_phone_number_does_not_start_with_08()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '07123456789', // invalid
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** TC 16: Phone number too short (<11 digits) */
    public function test_tc16_phone_number_too_short()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '0812345' // only 7 digits
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** TC 17: Phone number too long (>14 digits) */
    public function test_tc17_phone_number_too_long()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'phone_number' => '0812345678912345' // 16 digits
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['phone_number']);
    }

    /** TC 18: Address empty */
    public function test_tc18_address_empty()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload([
            'jalan' => ''
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertSessionHasErrors(['jalan']);
    }

    /** TC 19: Change dropdown after error, dynamic reload */
    // **Catatan:** PHPUnit tidak bisa menguji interaksi dinamis JavaScript.
    // Sebagai solusinya, hanya cek validasi server-side setelah perbaikan input.
    public function test_tc19_fix_province_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['provinsi' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['provinsi']);

        $payload['provinsi'] = '2';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['provinsi']);
    }

    public function test_tc19_1_fix_city_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kota' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kota']);

        $payload['kota'] = '2';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kota']);
    }

    public function test_tc19_2_fix_kecamatan_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kecamatan' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kecamatan']);

        $payload['kecamatan'] = '2';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kecamatan']);
    }

    public function test_tc19_3_fix_kelurahan_after_error_resolves_issue()
    {
        $this->actingAsVendor();

        $payload = $this->validPayload(['kelurahan' => '']);
        $response1 = $this->post(route('vendor.store'), $payload);
        $response1->assertSessionHasErrors(['kelurahan']);

        $payload['kelurahan'] = '2';
        $response2 = $this->post(route('vendor.store'), $payload);
        $response2->assertSessionDoesntHaveErrors(['kelurahan']);
    }

    /** TC 20: Optional times empty but rest valid */
    public function test_tc20_optional_times_empty_but_required_valid()
    {
        $this->actingAsVendor();
        Storage::fake('public');

        $payload = $this->validPayload([
            'startBreakfast' => null,
            'closeBreakfast' => null,
            'startLunch' => null,
            'closeLunch' => null,
            'startDinner' => null,
            'closeDinner' => null,
        ]);

        $response = $this->post(route('vendor.store'), $payload);
        $response->assertRedirect('cateringHomePage');
        $this->assertDatabaseHas('vendors', ['name' => 'Test Vendor']);
    }

    /** TC 21: API call for provinces/cities fails */
    // Karena ini terkait JS frontend dan axios/fetch, PHPUnit tidak menjangkau.
    // Solusi: tes unit untuk fallback server atau cek bahwa view jalan.
    // Akan saya tolak dari Feature Test ini, perlu diuji di Cypress atau Dusk.

    // tambahan testcase logout 
    public function test_user_can_logout()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('logout'));

        $response->assertRedirect('/'); // diarahkan ke homepage
        $this->assertGuest(); // user tidak lagi login
    }
}
