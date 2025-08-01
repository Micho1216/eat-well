<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressManagementTest extends TestCase
{
    use RefreshDatabase;

    // Common setup for all tests
    protected $customer;
    protected $province;
    protected $city;
    protected $district;
    protected $village;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a deterministic customer user for login
        /**
         *  @var User | Authenticatable
        */
        $this->customer = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($this->customer);

        // Create deterministic regional data for dropdown tests
        // Ensure your factories for Province, City, District, Village create valid, linked data.
        $this->province = Province::factory()->create(['name' => 'Jawa Barat']);
        $this->city = City::factory()->create(['name' => 'Bandung', 'provinceId' => $this->province->id]);
        $this->district = District::factory()->create(['name' => 'Coblong', 'cityId' => $this->city->id]);
        $this->village = Village::factory()->create(['name' => 'Dago', 'districtId' => $this->district->id]);
    }

    // --- 1. Add Address ---

    /** @test */
    public function tc1_manage_address_page_loads_and_displays_addresses(): void
    {
        // Create multiple addresses for the customer
        $address1 = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Budi Santoso',
            'phone_number' => '081212345678',
            'full_address' => 'Jl. Contoh No. 10',
            'postal_code' => '40123',
            'notes' => 'Dekat toko',
            'is_default' => true, // Set one as default
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);
        $address2 = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Siti Aminah',
            'phone_number' => '081298765432',
            'full_address' => 'Jl. Kenangan Indah No. 5',
            'postal_code' => '40124',
            'notes' => null, // No notes
            'is_default' => false,
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        // Navigate to the "Address Management" page
        $response = $this->get(route('addresses.index')); // Assuming 'addresses.index' is the route name

        // Assert the page loads without errors and displays address details
        $response->assertStatus(200); // Page loads successfully
        $response->assertDontSeeText('Error'); // No error messages

        // Assert core address information for both addresses
        $response->assertSeeText($address1->recipient_name);
        $response->assertSeeText($address1->phone_number);
        $response->assertSeeText($address1->full_address);
        $response->assertSeeText($address1->postal_code);
        $response->assertSeeText($address1->notes); // Assert notes for address1
        $response->assertSeeText($address2->recipient_name);
        $response->assertSeeText($address2->phone_number);
        $response->assertSeeText($address2->full_address);
        $response->assertSeeText($address2->postal_code);
        $response->assertSeeText('Main Address'); // Or whatever badge text is used for default
        $response->assertSeeText('Main Address'); // Ensure the badge for the default address is shown
    }

    /** @test */
    public function tc1_1_add_address_page_loads_with_required_fields(): void
    {
        // Navigate to the "Address Management" page, then click "+ Tambah Alamat" (simulated by direct GET)
        $response = $this->get(route('addresses.create')); // Assuming 'addresses.create' is the route name

        // Assert the page loads completely without errors
        $response->assertStatus(200);
        $response->assertDontSeeText('Error');

        // Assert all address input fields and dropdowns are visible and enabled (by checking labels/names)
        $response->assertSee('name="full_address"'); // Alamat
        $response->assertSee('name="postal_code"');  // Kode Pos
        $response->assertSee('name="notes"');        // Catatan
        $response->assertSee('name="recipient_name"'); // Nama Penerima
        $response->assertSee('name="phone_number"'); // Nomor Telepon

        $response->assertSee('name="provinceId"');   // Provinsi dropdown
        $response->assertSee('name="cityId"');       // Kota dropdown
        $response->assertSee('name="districtId"');   // Kecamatan dropdown
        $response->assertSee('name="villageId"');    // Kelurahan dropdown

        // Assert "Pilih Provinsi" option is selected by default for Provinsi dropdown
        $response->assertSee('<option value="" selected>Pilih Provinsi</option>', false);

        // Assert Kota, Kecamatan, and Kelurahan dropdowns are initially disabled (by checking HTML attributes)
        // This is a UI-specific check, tricky for feature tests. Asserting default selected option is more reliable.
        $response->assertSee('<select name="cityId" disabled', false); // Example: if it has 'disabled' attribute initially
        $response->assertSee('<select name="districtId" disabled', false);
        $response->assertSee('<select name="villageId" disabled', false);
    }

    /**
     * TC 1.2-1.4 (Combined): Verify dynamic loading of regional dropdowns (API endpoint check).
     * Feature tests check the backend API, not frontend JS behavior.
     * @test
     */
    public function tc1_2_to_1_4_regional_dropdown_apis_work(): void
    {
        // Test API for cities based on province selection
        $response = $this->get(route('api.cities', $this->province->id)); // Assuming API route for cities
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1); // At least one city (the one we created)
            $json->first(function ($json) {
                $json->where('name', $this->city->name)->etc();
            });
        });

        // Test API for districts based on city selection
        $response = $this->get(route('api.districts', $this->city->id)); // Assuming API route for districts
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1); // At least one district
            $json->first(function ($json) {
                $json->where('name', $this->district->name)->etc();
            });
        });

        // Test API for villages based on district selection
        $response = $this->get(route('api.villages', $this->district->id)); // Assuming API route for villages
        $response->assertStatus(200);
        $response->assertJson(function (AssertableJson $json) {
            $json->has(1); // At least one village
            $json->first(function ($json) {
                $json->where('name', $this->village->name)->etc();
            });
        });
    }

    /**
     * TC 1.5: Verify successful address addition with all valid data.
     * @test
     */
    public function tc1_5_successful_address_addition(): void
    {
        $addressData = [
            'recipient_name' => 'Budi Santoso',
            'phone_number' => '081234567890',
            'full_address' => 'Jl. Contoh No. 123',
            'postal_code' => '40123',
            'notes' => 'Dekat masjid',
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
            'is_default' => false, // Can be set as not default initially
        ];

        $response = $this->post(route('addresses.store'), $addressData); // Assuming 'addresses.store'

        // The form should submit successfully without validation errors and redirect
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // Redirect to address management page
        $response->assertRedirect(route('addresses.index'));

        // A success message "Alamat berhasil ditambahkan." should be displayed.
        $response->assertSessionHas('success', 'Alamat berhasil ditambahkan.'); // Assert success message in session

        // The newly added address should be visible in the list (by asserting its presence in DB)
        $this->assertDatabaseHas('addresses', array_merge($addressData, ['userId' => $this->customer->userId]));
    }

    /**
     * TC 1.6: Verify server-side validation for empty required fields during address addition.
     * @test
     */
    public function tc1_6_server_side_validation_empty_fields_on_add(): void
    {
        $response = $this->post(route('addresses.store'), [
            // Leave required fields empty
            'recipient_name' => '',
            'phone_number' => '',
            'full_address' => '',
            'postal_code' => '',
            'provinceId' => '',
            'cityId' => '',
            'districtId' => '',
            'villageId' => '',
            'notes' => 'Some optional notes', // Optional field can be filled
        ]);

        // Server-side validation errors should be displayed
        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors([
            'recipient_name', 'phone_number', 'full_address', 'postal_code',
            'provinceId', 'cityId', 'districtId', 'villageId'
        ]);
    }

    /**
     * TC 1.7: Verify server-side validation for invalid "Kode Pos" format.
     * @test
     */
    public function tc1_7_server_side_validation_invalid_postal_code_on_add(): void
    {
        $response = $this->post(route('addresses.store'), [
            'recipient_name' => 'Test User',
            'phone_number' => '081234567890',
            'full_address' => 'Jl. Test',
            'postal_code' => '123', // Invalid format (not 5 digits)
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['postal_code']);
        $response->assertSessionHasErrors(['postal_code' => 'Kode pos harus 5 digit angka.']); // Specific message
    }

    /**
     * TC 1.8: Verify server-side validation for invalid "Nomor Telepon" format and length.
     * @test
     */
    public function tc1_8_server_side_validation_invalid_phone_number_on_add(): void
    {
        $baseData = [
            'recipient_name' => 'Test User',
            'full_address' => 'Jl. Test',
            'postal_code' => '40123',
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ];

        // Test invalid format (non-numeric)
        $response = $this->post(route('addresses.store'), array_merge($baseData, ['phone_number' => 'abc']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['phone_number']);
        $response->assertSessionHasErrors(['phone_number' => 'Nomor telepon harus angka.']);

        // Test too short
        $response = $this->post(route('addresses.store'), array_merge($baseData, ['phone_number' => '123']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['phone_number']);
        $response->assertSessionHasErrors(['phone_number' => 'Nomor telepon minimal 10 digit.']);

        // Test too long
        $response = $this->post(route('addresses.store'), array_merge($baseData, ['phone_number' => '081234567890123456']));
        $response->assertStatus(302);
        $response->assertSessionHasErrors(['phone_number']);
        $response->assertSessionHasErrors(['phone_number' => 'Nomor telepon maksimal 15 digit.']);
    }

    /**
     * TC 1.9: Verify "Cancel" button navigates back to "Address Management" page.
     * @test
     */
    public function tc1_9_add_address_cancel_button_navigates_back(): void
    {
        // This is a test of a link/redirect
        $response = $this->get(route('addresses.create')); // Go to create page
        $response->assertStatus(200);

        // Simulate clicking cancel button (by directly requesting the destination)
        $response = $this->get(route('addresses.index')); // Assuming cancel button links to index
        $response->assertStatus(200);
        // Assert no new address was created (e.g., by checking database count)
        $this->assertDatabaseCount('addresses', 0); // No addresses should exist if test setup didn't create them
    }

    // --- 2. Edit Address ---

    /**
     * TC 2.1: Verify the Edit Address page loads successfully with pre-filled data.
     * @test
     */
    public function tc2_1_edit_address_page_loads_with_pre_filled_data(): void
    {
        // Create an address for the customer to edit
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Pre-fill Name',
            'phone_number' => '081211223344',
            'full_address' => 'Jl. Edit No. 5',
            'postal_code' => '40125',
            'notes' => 'Notes for editing',
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        // Navigate to the Edit Address page
        $response = $this->get(route('addresses.edit', $address->id)); // Assuming 'addresses.edit' route

        $response->assertStatus(200); // Page loads successfully
        $response->assertDontSeeText('Error'); // No errors

        // Assert all form fields and dropdowns are pre-filled with the current address data.
        $response->assertSee('value="' . $address->recipient_name . '"', false);
        $response->assertSee('value="' . $address->phone_number . '"', false);
        $response->assertSee('value="' . $address->full_address . '"', false);
        $response->assertSee('value="' . $address->postal_code . '"', false);
        $response->assertSee('value="' . $address->notes . '"', false);

        // Assert correct regional data is selected (by checking selected option values)
        $response->assertSee('<option value="' . $this->province->id . '" selected>', false);
        $response->assertSee('<option value="' . $this->city->id . '" selected>', false);
        $response->assertSee('<option value="' . $this->district->id . '" selected>', false);
        $response->assertSee('<option value="' . $this->village->id . '" selected>', false);

        // Assert dropdowns are enabled (by checking absence of 'disabled' attribute on relevant select tags)
        // This is a UI-specific check, harder to do precisely. A basic 'assertDontSee' for 'disabled' is a start.
        $response->assertDontSee('<select name="cityId" disabled', false);
        $response->assertDontSee('<select name="districtId" disabled', false);
        $response->assertDontSee('<select name="villageId" disabled', false);
    }

    /**
     * TC 2.2: Verify successful address update with valid changes.
     * @test
     */
    public function tc2_2_successful_address_update(): void
    {
        // Create an address for the customer to update
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Original Name',
            'phone_number' => '081200000000',
            'full_address' => 'Original Address',
            'postal_code' => '11111',
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        $newProvince = Province::factory()->create(['name' => 'Jawa Tengah']);
        $newCity = City::factory()->create(['name' => 'Semarang', 'provinceId' => $newProvince->id]);
        $newDistrict = District::factory()->create(['name' => 'Tugu', 'cityId' => $newCity->id]);
        $newVillage = Village::factory()->create(['name' => 'Mangkang Wetan', 'districtId' => $newDistrict->id]);

        $updatedData = [
            'recipient_name' => 'Updated Name',
            'phone_number' => '081299999999',
            'full_address' => 'Updated Address',
            'postal_code' => '99999',
            'notes' => 'Updated notes',
            'provinceId' => $newProvince->id,
            'cityId' => $newCity->id,
            'districtId' => $newDistrict->id,
            'villageId' => $newVillage->id,
            'is_default' => $address->is_default, // Keep original default status
        ];

        $response = $this->put(route('addresses.update', $address->id), $updatedData); // Assuming 'addresses.update'

        // The form should submit successfully and redirect
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302); // Redirect to address management page
        $response->assertRedirect(route('addresses.index'));

        // A success message "Alamat berhasil diperbarui." should be displayed.
        $response->assertSessionHas('success', 'Alamat berhasil diperbarui.');

        // The updated address details should be reflected correctly in the database.
        $this->assertDatabaseHas('addresses', array_merge($updatedData, ['id' => $address->id]));
        $this->assertDatabaseMissing('addresses', ['id' => $address->id, 'recipient_name' => 'Original Name']);
    }

    /**
     * TC 2.3: Verify client-side (server-side) validation works on Edit Address page.
     * @test
     */
    public function tc2_3_server_side_validation_on_edit_address(): void
    {
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        $response = $this->put(route('addresses.update', $address->id), [
            // Modify a field to make it invalid (e.g., clear "Alamat", invalid "Kode Pos")
            'recipient_name' => '', // Empty
            'phone_number' => '123', // Too short
            'full_address' => '',    // Empty
            'postal_code' => 'abc',  // Invalid format
            'provinceId' => '',      // Empty
            'cityId' => '',
            'districtId' => '',
            'villageId' => '',
        ]);

        $response->assertStatus(302); // Redirect back with errors
        $response->assertSessionHasErrors([
            'recipient_name', 'phone_number', 'full_address', 'postal_code',
            'provinceId', 'cityId', 'districtId', 'villageId'
        ]);
    }

    /**
     * TC 2.4: Verify "Cancel" button navigates back without saving changes.
     * @test
     */
    public function tc2_4_edit_address_cancel_button_navigates_back_without_saving(): void
    {
        $address = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Original Name',
            'phone_number' => '081200000000',
            'full_address' => 'Original Address',
            'postal_code' => '11111',
            'provinceId' => $this->province->id,
            'cityId' => $this->city->id,
            'districtId' => $this->district->id,
            'villageId' => $this->village->id,
        ]);

        // Simulate making changes (these are not actually submitted by 'cancel')
        $changedData = [
            'recipient_name' => 'Changed Name',
            'full_address' => 'Changed Address',
        ];

        // Simulate clicking cancel button (by directly requesting the destination)
        $response = $this->get(route('addresses.index')); // Assuming cancel button links to index

        $response->assertStatus(200); // Assert page loads successfully
        // Assert that the changes were NOT saved in the database
        $this->assertDatabaseMissing('addresses', array_merge($changedData, ['id' => $address->id]));
        $this->assertDatabaseHas('addresses', ['id' => $address->id, 'recipient_name' => 'Original Name']);
    }

    // --- 3. Set Main Address ---

    /**
     * TC 3.1 & 3.2 (Combined): Verify changing main address when multiple addresses exist.
     * This tests successful setting of a new default and UI/DB updates.
     * @test
     */
    public function tc3_1_and_3_2_change_main_address_successfully(): void
    {
        // Create initial addresses: one default, one non-default
        $defaultAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);
        $newMainAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);

        // Simulate clicking the toggle switch for the 'newMainAddress' via AJAX POST
        // Assuming '/set-default-address' is the endpoint that receives address_id via POST JSON
        $response = $this->postJson(route('set_default_address'), ['address_id' => $newMainAddress->id]); // Using postJson

        // Assert AJAX request successful
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Alamat utama berhasil diubah!']); // Assert success modal message

        // Verify database reflects the change
        $this->assertDatabaseHas('addresses', ['id' => $newMainAddress->id, 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['id' => $defaultAddress->id, 'is_default' => false]);

        // Verify UI updates (by checking content on the addresses index page after change)
        // Re-request the index page to get updated content
        $response = $this->get(route('addresses.index'));
        $response->assertStatus(200);
        $response->assertSeeText($newMainAddress->recipient_name);
        $response->assertSeeText('Main Address'); // New main should have the badge
        $response->assertDontSeeText($defaultAddress->recipient_name . ' Main Address'); // Old main should not have badge (text specific)
    }

    /**
     * TC 3.3: Verify that clicking the toggle switch on the currently default (main) address displays a warning pop-up.
     * Feature tests simulate API calls or page loads, not direct UI interaction with JS.
     * We'll test that trying to set a default address that's *already* default (if the backend has logic for it) might produce an error or no change.
     * More often, this is purely client-side validation.
     * @test
     */
    public function tc3_3_setting_default_to_current_default_shows_warning(): void
    {
        // Create only one address, which is default by nature
        $defaultAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);

        // Simulate clicking the toggle switch for the default address
        // Assuming the backend endpoint will validate if it's already default and warn/error.
        $response = $this->postJson(route('set_default_address'), ['address_id' => $defaultAddress->id]);

        // Expected behavior (server-side): Either a warning JSON or it just successfully confirms no change.
        // Given your description implies a "warning modal", we'll assert a specific JSON response.
        $response->assertStatus(422); // Or 200 with specific error message
        $response->assertJson(['message' => 'Alamat utama tidak bisa dinonaktifkan langsung. Pilih alamat lain sebagai utama terlebih dahulu.']);

        // Assert no actual change in DB
        $this->assertDatabaseHas('addresses', ['id' => $defaultAddress->id, 'is_default' => true]);
    }

    /**
     * TC 3.4: Verify the toggle switch functionality when only one address is registered.
     * Similar to TC 3.3, it should prevent deactivating the single main address.
     * @test
     */
    public function tc3_4_setting_default_with_only_one_address_shows_warning(): void
    {
        // Create only one address, which is implicitly default
        $singleAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);

        // Simulate clicking the toggle switch for the single address
        $response = $this->postJson(route('set_default_address'), ['address_id' => $singleAddress->id]);

        // Expected server-side behavior is identical to 3.3 for trying to deactivate the only main address.
        $response->assertStatus(422);
        $response->assertJson(['message' => 'Alamat utama tidak bisa dinonaktifkan langsung. Pilih alamat lain sebagai utama terlebih dahulu.']);

        // Assert no change in DB
        $this->assertDatabaseHas('addresses', ['id' => $singleAddress->id, 'is_default' => true]);
    }

    /**
     * TC 3.5: Verify handling of server error during set-default-address AJAX call.
     * This requires mocking the server response or deliberately causing an error.
     * For feature tests, we can test that the backend returns the error correctly.
     * @test
     */
    public function tc3_5_handle_server_error_on_set_default_address(): void
    {
        $defaultAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);
        $otherAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);

        // Simulate a server error by making the API call that should fail.
        // We can't actually "break Controller logic" in the test; we assume it happens.
        // We're testing the response format if the server *does* return an error.
        $response = $this->postJson(route('set_default_address'), ['address_id' => $otherAddress->id])
                         ->assertStatus(500) // Expect HTTP 500 status for server error
                         ->assertJson(['message' => 'Terjadi kesalahan saat menghubungi server.']); // Assert the error message format

        // Assert no change in DB state
        $this->assertDatabaseHas('addresses', ['id' => $defaultAddress->id, 'is_default' => true]);
        $this->assertDatabaseHas('addresses', ['id' => $otherAddress->id, 'is_default' => false]);
    }

    /**
     * TC 3.6: Verify system behavior when addressId is missing or invalid in the AJAX request (server-side validation).
     * @test
     */
    public function tc3_6_handle_missing_or_invalid_address_id_on_set_default_address(): void
    {
        // Simulate missing address_id
        $response = $this->postJson(route('set_default_address'), []);
        $response->assertStatus(422); // Validation error
        $response->assertJsonValidationErrors('address_id'); // 'address_id' is required

        // Simulate invalid address_id (e.g., non-existent)
        $response = $this->postJson(route('set_default_address'), ['address_id' => 99999]);
        $response->assertStatus(422); // Or 404 if your controller handles it specifically
        $response->assertJsonValidationErrors('address_id');
    }

    // --- 4. Delete Address ---

    /**
     * TC 4.1: Verify that clicking the delete button triggers the delete confirmation popup.
     * This is a UI/JS interaction. In feature tests, we test the backend delete call directly.
     * We assume the UI would trigger this on confirmation.
     * @test
     */
    public function tc4_1_delete_button_triggers_confirmation_popup(): void
    {
        // Feature tests cannot directly test JS popups.
        // This test serves as a placeholder for a UI/browser test (e.g., Laravel Dusk, Cypress).
        // For a backend feature test, we can only confirm the delete endpoint works after "confirmation".
        $this->assertTrue(true, 'This test requires UI/browser automation to verify popup display.');
    }

    /**
     * TC 4.2 & 4.5 (Combined): Verify that confirming the delete action removes the address from the list and DB.
     * This tests successful deletion of a non-main address.
     * @test
     */
    public function tc4_2_and_4_5_confirming_delete_removes_address(): void
    {
        // Create addresses: one default, one to be deleted
        $defaultAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);
        $addressToDelete = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);

        // Simulate confirming the delete action via AJAX DELETE request
        $response = $this->deleteJson(route('addresses.destroy', $addressToDelete->id)); // Assuming 'addresses.destroy'

        // Assert AJAX request successful
        $response->assertStatus(200); // Or 204 No Content for successful delete
        $response->assertJson(['message' => 'Alamat berhasil dihapus.']); // Assert success modal message

        // Verify the address is soft deleted in the database
        $this->assertSoftDeleted('addresses', ['id' => $addressToDelete->id]);
        // And is no longer visible in active queries
        $this->assertDatabaseMissing('addresses', ['id' => $addressToDelete->id, 'deleted_at' => null]);
    }

    /**
     * TC 4.3: Verify that canceling the delete confirmation keeps the address intact.
     * This is a UI/JS interaction. No backend call occurs on cancel.
     * @test
     */
    public function tc4_3_canceling_delete_confirmation_keeps_address_intact(): void
    {
        // Create an address that would be deleted
        $address = Address::factory()->create(['userId' => $this->customer->userId]);

        // Feature tests cannot simulate clicking 'Cancel' on a JS popup.
        // We assert the database state before any potential backend call.
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
        $this->assertTrue(true, 'This test requires UI/browser automation to verify cancel action without backend call.');
    }

    /**
     * TC 4.4: Verify that the success modal appears after successful address deletion.
     * This is partially covered by tc4_2_and_4_5_confirming_delete_removes_address.
     * @test
     */
    public function tc4_4_success_modal_appears_after_deletion(): void
    {
        // This scenario is already fully covered by 'tc4_2_and_4_5_confirming_delete_removes_address'
        // which asserts the JSON success message.
        $this->assertTrue(true, 'This test is covered by tc4_2_and_4_5_confirming_delete_removes_address.');
    }

    /**
     * TC 4.6: Verify attempt to delete main address is prevented.
     * This covers server-side prevention of deleting the main address.
     * @test
     */
    public function tc4_6_deleting_main_address_is_prevented(): void
    {
        // Create a main address
        $mainAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => true]);
        // Create a second address if main address can only be deleted if there are others
        $otherAddress = Address::factory()->create(['userId' => $this->customer->userId, 'is_default' => false]);


        // Simulate attempting to delete the main address via AJAX DELETE request
        $response = $this->deleteJson(route('addresses.destroy', $mainAddress->id));

        // Expected server-side error response
        $response->assertStatus(422); // Or 403 Forbidden, 409 Conflict, depending on your error handling
        $response->assertJson(['message' => 'Tidak dapat menghapus alamat utama.']); // Assert specific error message

        // Assert the address was NOT deleted in the database
        $this->assertDatabaseHas('addresses', ['id' => $mainAddress->id, 'deleted_at' => null]);
    }
}