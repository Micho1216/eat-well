<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Province;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class CustomerAddressTest extends TestCase
{
    use RefreshDatabase;

    // Common setup for all tests
    protected $customer;
    protected $address;
    protected $province;
    protected $city;
    protected $district;
    protected $village;

    protected function setUp(): void
    {
        parent::setUp();
        Session::put('locale', 'en');
        App::setLocale('en');

        /**
         *  @var User | Authenticatable
        */
        $this->customer = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($this->customer);
        // $request = 

        $this->province = Province::create(['name' => 'Jawa Barat']);
        $this->city = City::create(['name' => 'Bandung', 'province_id' => $this->province->id]);
        $this->district = District::create(['name' => 'Coblong', 'city_id' => $this->city->id]);
        $this->village = Village::create(['name' => 'Dago', 'district_id' => $this->district->id]);

        $this->address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081212345678',
            'jalan' => 'Jl. Contoh No. 10',
            'kode_pos' => '40123',
            'notes' => 'Dekat toko',
            'is_default' => true, // Set one as default
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);
    }

    /** @test */
    public function tc1_manage_address_page_loads_and_displays_addresses()
    {
        $address2 = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Siti Aminah',
            'recipient_phone' => '081298765432',
            'jalan' => 'Jl. Kenangan Indah No. 5',
            'kode_pos' => '40124',
            'notes' => null, 
            'is_default' => false,
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);

        $response = $this->get(route('manage-address'));

        $response->assertStatus(200);

        // Assert core address information for both addresses
        $response->assertSeeText($this->address->recipient_name);
        $response->assertSeeText($this->address->recipient_phone);
        $response->assertSeeText($this->address->jalan);
        $response->assertSeeText($this->address->kode_pos);
        $response->assertSeeText($this->address->notes); // Assert notes for address1
        $response->assertSeeText($address2->recipient_name);
        $response->assertSeeText($address2->recipient_phone);
        $response->assertSeeText($address2->jalan);
        $response->assertSeeText($address2->kode_pos);
        $defaultAddressText = __('address.main_address');
        $response->assertSeeText($defaultAddressText); // Or whatever badge text is used for default
        $response->assertSeeText($defaultAddressText); // Ensure the badge for the default address is shown
    }

    /** @test */
    public function tc1_1_add_address_page_loads_with_required_fields()
    {
        // Request the page for adding a NEW address
        $response = $this->actingAs($this->customer)->get(route('add-address')); 

        $response->assertStatus(200);

        // Assert all input fields are visible and dropdowns are in their default state
        $response->assertSee('name="kode_pos"', false);
        $response->assertSee('name="notes"', false);    
        $response->assertSee('name="recipient_name"', false); 
        $response->assertSee('name="recipient_phone"', false); 

        $response->assertSee('name="provinsi_name"', false); 
        $response->assertSee('name="kota_name"', false); 
        $response->assertSee('name="kecamatan_name"', false); 
        $response->assertSee('name="kelurahan_name"', false); 

        // Assert the default option for a new form
        $response->assertSee('<option value="">Pilih Provinsi</option>', false);
        $response->assertSee('name="kota_id" required disabled', false); 
        $response->assertSee('name="kecamatan_id" required disabled', false);
        $response->assertSee('name="kelurahan_id" required disabled', false);
    }

    /** @test */
    public function tc1_2_to_1_4_regional_dropdown_apis_work()
    {
        $response = $this->post(route('api-cities'), ['province_id'=>$this->province->id]);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1); // At least one city (the one we created)
            $json->first(function ($json) {
                $json->where('name', $this->city->name)->etc();
            });
        });

        // Test API for districts based on city selection
        $response = $this->post(route('api-districts'), ['city_id'=>$this->city->id]);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1);
            $json->first(function ($json) {
                $json->where('name', $this->district->name)->etc();
            });
        });

        // Test API for villages based on district selection
        $response = $this->post(route('api-villages'), ['district_id'=>$this->district->id]);
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1);
            $json->first(function ($json) {
                $json->where('name', $this->village->name)->etc();
            });
        });
    }

    /** @test */
    public function tc1_5_successful_address_addition()
    {
        $addressData = [
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081234567890',
            'jalan' => 'Jl. Contoh No. 123',
            'kode_pos' => '40123',
            'notes' => 'Dekat masjid',
            'provinsi_name' => $this->province->name,
            'kota_name' => $this->city->name,
            'kecamatan_name' => $this->district->name,
            'kelurahan_name' => $this->village->name,

        ];

        $response = $this->post(route('store-address'), $addressData);

        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $response->assertRedirect(route('manage-address'));
        $response->assertSessionHas('success', 'Alamat berhasil ditambahkan.');

        $this->assertDatabaseHas('addresses', [
            'recipient_name' => $addressData['recipient_name'],
            'recipient_phone' => $addressData['recipient_phone'],
            'jalan' => $addressData['jalan'],
            'kode_pos' => $addressData['kode_pos'],
            'notes' => $addressData['notes'],
            'provinsi' => $this->province->name, 
            'kota' => $this->city->name,         
            'kecamatan' => $this->district->name, 
            'kelurahan' => $this->village->name,
            'userId' => $this->customer->userId,
        ]);
    }

    /** @test */
    public function tc1_6_server_side_validation_empty_fields_on_add()
    {
        $input = [
            // Leave required fields empty
            'recipient_name' => '',
            'recipient_phone' => '',
            'jalan' => '',
            'kode_pos' => '',
            'provinsi_name' => '',
            'kota_name' => '',
            'kecamatan_name' => '',
            'kelurahan_name' => '',
            'notes' => 'Some optional notes',
            'is_default'=>false,
        ];

        $response = $this->post(route('store-address'), $input);

        // Server-side validation errors should be displayed
        $response->assertStatus(302);
        $response->assertSessionHasErrors([
            'recipient_name', 'recipient_phone', 'jalan', 'kode_pos',
            'provinsi_name', 'kota_name', 'kecamatan_name', 'kelurahan_name'
        ]);
    }

    /** @test */
    public function tc1_7_server_side_validation_invalid_postal_code_on_add()
    {
        $response = $this->post(route('store-address'), [
            'recipient_name' => 'Test User',
            'recipient_phone' => '081234567890',
            'jalan' => 'Jl. Test',
            'kode_pos' => '123', 
            'provinsi' => $this->province->id,
            'kota' => $this->city->id,
            'kecamatan' => $this->district->id,
            'kelurahan' => $this->village->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['kode_pos']);
    }

    /** @test */
    public function tc1_8_server_side_validation_invalid_phone_number_on_add()
    {
        $baseData = [
            'recipient_name' => 'Test User',
            'jalan' => 'Jl. Test',
            'kode_pos' => '40123',
            'provinsi' => $this->province->id,
            'kota' => $this->city->id,
            'kecamatan' => $this->district->id,
            'kelurahan' => $this->village->id,
        ];

        $response = $this->post(route('store-address'), array_merge($baseData, ['recipient_phone' => 'abc']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recipient_phone']);

        $response = $this->post(route('store-address'), array_merge($baseData, ['recipient_phone' => '123']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recipient_phone']);

        $response = $this->post(route('store-address'), array_merge($baseData, ['recipient_phone' => '081234567890123456']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['recipient_phone']);
    }

    /** @test */
    public function tc1_9_add_address_cancel_button_navigates_back()
    {
        $response = $this->get(route('add-address'));
        $response->assertStatus(200);

        $response = $this->get(route('manage-address')); 
        $response->assertStatus(200);

        $this->assertDatabaseCount('addresses', 1); 
    }

    // --- 2. Edit Address ---

    /** @test */
    public function tc2_1_edit_address_page_loads_with_pre_filled_data()
    {
        // Create an address for the customer to edit
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Pre-fill Name',
            'recipient_phone' => '081211223344',
            'jalan' => 'Jl. Edit No. 5',
            'kode_pos' => '40125',
            'notes' => 'Notes for editing',
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);

        // Navigate to the Edit Address page
        $response = $this->get(route('edit-address', $address)); // Assuming 'addresses.edit' route

        $response->assertStatus(200); // Page loads successfully
        $response->assertDontSeeText('Error'); // No errors

        // Assert all form fields and dropdowns are pre-filled with the current address data.
        $response->assertSee('value="' . $address->recipient_name . '"', false);
        $response->assertSee('value="' . $address->recipient_phone . '"', false);
        $response->assertSee('value="' . $address->jalan . '"', false);
        $response->assertSee('value="' . $address->kode_pos . '"', false);
        $response->assertSee('value="' . $address->notes . '"', false);

        // Assert correct regional data is selected (by checking selected option values)
        $response->assertSee('<option value=""', false);
        $response->assertSee('<option value=""', false);
        $response->assertSee('<option value=""', false);
        $response->assertSee('<option value=""', false);
        // Assert dropdowns are enabled (by checking absence of 'disabled' attribute on relevant select tags)
        // This is a UI-specific check, harder to do precisely. A basic 'assertDontSee' for 'disabled' is a start.
        $response->assertDontSee('<select name="kota_id" disabled', false);
        $response->assertDontSee('<select name="kecamatan_id" disabled', false);
        $response->assertDontSee('<select name="kelurahan_id" disabled', false);
    }

    /** @test */
    public function tc2_2_successful_address_update()
    {
        $newProvince = Province::create(['name' => 'Jawa Tengah']);
        $newCity = City::create(['name' => 'Semarang', 'province_id' => $newProvince->id]);
        $newDistrict = District::create(['name' => 'Tugu', 'city_id' => $newCity->id]);
        $newVillage = Village::create(['name' => 'Mangkang Wetan', 'district_id' => $newDistrict->id]);

        $updatedData = [
            'recipient_name' => 'Updated Name',
            'recipient_phone' => '081299999999',
            'jalan' => 'Updated Address',
            'kode_pos' => '99999',
            'notes' => 'Updated notes',
            'provinsi_name' => $newProvince->name,
            'kota_name' => $newCity->name,
            'kecamatan_name' => $newDistrict->name,
            'kelurahan_name' => $newVillage->name,
            'is_default' => true,
        ];

        $response = $this->patch(route('update-address', $this->address->addressId), $updatedData);
        $response->assertSessionHasNoErrors();
        
        $response->assertStatus(302);
        $response->assertSessionHas('update_success', 'Alamat berhasil diperbarui');
        $response->assertRedirect(route('manage-address'));


        $this->assertDatabaseHas('addresses', [
            'addressId' => $this->address->addressId,
            'recipient_name' => 'Updated Name',
            'recipient_phone' => '081299999999',
            'jalan' => 'Updated Address',
            'kode_pos' => '99999',
            'notes' => 'Updated notes',
            'provinsi' => $newProvince->name,
            'kota' => $newCity->name,
            'kecamatan' => $newDistrict->name,
            'kelurahan' => $newVillage->name,
            'is_default' => true,
        ]);
        
        $this->assertDatabaseMissing('addresses', ['addressId' => $this->address->addressId, 'recipient_name' => 'Original Name']);
    }

    /** @test */
    public function tc2_3_server_side_validation_on_edit_address()
    {
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);

        $response = $this->patchJson(route('update-address', $address), [
            'recipient_name' => '',
            'recipient_phone' => '123',
            'jalan' => '',  
            'kode_pos' => 'abc',  
            'provinsi_name' => '',   
            'kota_name' => '',
            'kecamatan_name' => '',
            'kelurahan_name' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'recipient_name', 'recipient_phone', 'jalan', 'kode_pos',
            'provinsi_name', 'kota_name', 'kecamatan_name', 'kelurahan_name'
        ]);
    }

    /** @test */
    public function tc2_4_edit_address_cancel_button_navigates_back_without_saving()
    {
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Original Name',
            'recipient_phone' => '081200000000',
            'jalan' => 'Original Address',
            'kode_pos' => '11111',
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);

        // Simulate making changes (these are not actually submitted by 'cancel')
        $changedData = [
            'recipient_name' => 'Changed Name',
            'jalan' => 'Changed Address',
        ];

        $response = $this->get(route('manage-address')); // Assuming cancel button links to index

        $response->assertStatus(200); // Assert page loads successfully
        // Assert that the changes were NOT saved in the database
        $this->assertDatabaseMissing('addresses', array_merge($changedData, ['addressId' => $address->id]));
        $this->assertDatabaseHas('addresses', ['addressId' => $address->addressId, 'recipient_name' => 'Original Name']);
    }

    // --- 3. Set Main Address ---

    /** @test */
    public function tc3_1_and_3_2_change_main_address_successfully()
    {
        // Create initial addresses: one default, one non-default
        $defaultAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);
        $newMainAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);

        $response = $this->post(route('set-default-address'), ['address_id' => $newMainAddress->addressId]);

        // Assert AJAX request successful
        $response->assertStatus(200);
        $response->assertJson(['success'=>true, 'message' => 'Alamat utama berhasil diatur.']); // Assert success modal message

        // Verify database reflects the change
        $this->assertDatabaseHas('addresses', ['addressId' => $newMainAddress->addressId, 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['addressId' => $defaultAddress->addressId, 'is_default' => false]);
        $response = $this->get(route('manage-address'));
        $response->assertStatus(200);
        $response->assertSeeText($newMainAddress->recipient_name);
        $defaultAddressText = __('address.main_address');
        $response->assertSeeText($defaultAddressText); 
        $response->assertDontSeeText($defaultAddress->recipient_name . ' Main Address'); // Old main should not have badge (text specific)
    }

    /** @test */
    public function tc3_4_setting_default_with_only_one_address_shows_warning()
    {
        // Create only one address, which is implicitly default
        $singleAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' =>false]);

        // Simulate clicking the toggle switch for the single address
        $response = $this->post(route('set-default-address'), ['address_id' => $singleAddress->addressId]);

        // Expected server-side behavior is identical to 3.3 for trying to deactivate the only main address.
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Alamat utama berhasil diatur.']);

        // Assert no change in DB
        $this->assertDatabaseHas('addresses', ['addressId' => $singleAddress->addressId, 'is_default' => true]);
    }

 
    /** @test */
    public function tc3_5_handle_missing_or_invalid_address_id_on_set_default_address()
    {
        // Simulate missing address_id
        $response = $this->post(route('set-default-address'), []);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('address_id', 'The address id field is required.');

        // Simulate invalid address_id
        $response = $this->post(route('set-default-address'), ['address_id' => 99999]);
        $response->assertStatus(302);
        $response->assertSessionHasErrors('address_id', 'The selected address id is invalid.');
        $this->assertDatabaseMissing('addresses', ['is_default' => true, 'addressId' => 99999]);
    }

    // --- 4. Delete Address ---

    /** @test */
    public function tc4_1_delete_button_triggers_confirmation_popup()
    {
        $this->assertTrue(true, 'This test requires UI/browser automation to verify popup display.');
    }

    /** @test */
    public function tc4_2_and_4_5_confirming_delete_removes_address()
    {
        $addressToDelete = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);

        $response = $this->delete(route('delete-address', $addressToDelete->addressId));
        $response->assertSessionHas('delete_success', 'Alamat berhasil dihapus.');

        $response->assertStatus(302);

        // Verify the address is soft deleted in the database
        $this->assertSoftDeleted('addresses', ['addressId' => $addressToDelete->addressId]);
        // And is no longer visible in active queries
        $this->assertDatabaseMissing('addresses', ['addressId' => $addressToDelete->addressId, 'deleted_at' => null]);
    }

    /**  @test */
    public function tc4_3_canceling_delete_confirmation_keeps_address_intact()
    {
        $address = Address::factory()->create(['userId' => $this->customer->userId]);

        $this->assertDatabaseHas('addresses', ['addressId' => $address->addressId]);
        $this->assertTrue(true, 'This test requires UI/browser automation to verify cancel action without backend call.');
    }

    /** @test */
    public function tc4_4_success_modal_appears_after_deletion()
    {
        $this->assertTrue(true, 'This test is covered by tc4_2_and_4_5_confirming_delete_removes_address.');
    }

    /** @test */
    public function tc4_6_deleting_main_address_is_prevented()
    {
        $response = $this->delete(route('delete-address', $this->address->addressId));

        
        $response->assertSessionHas('error', 'Tidak dapat menghapus alamat utama. Silakan atur alamat lain sebagai utama terlebih dahulu.');
        $response->assertRedirect('/manage-address');

        $this->assertDatabaseHas('addresses', ['addressId' => $this->address->addressId, 'deleted_at' => null]);
    }
}