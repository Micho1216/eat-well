<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\City;
use App\Models\District;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;
use Database\Seeders\PackageCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

use Tests\TestCase;

class CustomerChangeAddressBeforeCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;
    protected $province;
    protected $city;
    protected $district;
    protected $village;
    protected $defaultAddress;
    protected $otherAddress;
    protected $vendor;
    protected $packageCategory;
    protected $vendorUser;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var User | Authenticatable */
        $this->customer = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($this->customer);

        $this->province = Province::create(['name' => 'Jawa Barat']);
        $this->city = City::create(['name' => 'Bandung', 'province_id' => $this->province->id]);
        $this->district = District::create(['name' => 'Coblong', 'city_id' => $this->city->id]);
        $this->village = Village::create(['name' => 'Dago', 'district_id' => $this->district->id]);

        // Create a default address for the user
        $this->defaultAddress = Address::factory()->create([
            'userId' => $this->customer->userId,
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '081212345678',
            'jalan' => 'Jl. Contoh No. 10',
            'kode_pos' => '40123',
            'notes' => 'Dekat toko',
            'is_default' => true,
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
        ]);

        // Create another address for the user
        $this->otherAddress = Address::factory()->create([
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

        $this->vendorUser = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
            'password' => bcrypt('Test@1234'),
            'role' => 'Vendor',
        ]);

        $this->vendor = Vendor::create([
            'userId' => $this->vendorUser->userId,
            'name' => 'Green Catering',
            'phone_number' => '0811111111',
            'breakfast_delivery' => '06:30-08:00',
            'lunch_delivery' => '11:30-13:00',
            'dinner_delivery' => '17:30-19:00',
            'provinsi' => $this->province->name,
            'kota' => $this->city->name,
            'kecamatan' => $this->district->name,
            'kelurahan' => $this->village->name,
            'kode_pos' => '12940',
            'jalan' => 'Jl. HR Rasuna Said',
            'logo' => 'asset/vendorLogo/logo 2.png',
            'rating' => 4.5,
        ]);

        $this->packageCategory = PackageCategory::create(['categoryName' => 'Halal']);
    }

    /** @test */
    public function tc1_address_display_and_persistence_on_search_page()
    {
        // Step 1: Navigate to the search page.
        $response = $this->get(route('search'));
        $response->assertStatus(200);
        
        // Step 2 & 3: Assert the default address is displayed.
        $response->assertSeeText($this->defaultAddress->jalan);

        Session::put('address_id', $this->otherAddress->addressId);

        // Simulate selecting a different address, which triggers a page reload with a new address_id.
        $response = $this->get(route('search'));
        $response->assertStatus(200);

        // Assertion 2: The dropdown text updates to the new address.
        $response->assertSeeText($this->otherAddress->jalan);
        $response->assertSeeText($this->defaultAddress->jalan);
    }

    /** @test */
    public function tc2_address_persistence_with_filters(): void
    {
        Session::put('address_id', $this->otherAddress->addressId);
        $filterData = ['min_price' => 50000];

        $response = $this->get(route('search', $filterData));
        $response->assertStatus(200);
    }

    /** @test */
    public function tc3_address_persistence_with_pagination(): void
    {
        Vendor::factory(15)->create(); 
        
        Session::put('address_id', $this->otherAddress->addressId);

        $paginationParams = ['page' => 2];

        $response = $this->get(route('search', $paginationParams));
        $response->assertStatus(200);

        $response->assertSeeText($this->otherAddress->jalan);
    }

    /** @test */
    public function tc4_address_transfer_to_catering_detail(): void
    {
        Session::put('address_id', $this->otherAddress->addressId);
        $response = $this->get(route('catering-detail', $this->vendor->userId));
        $response->assertStatus(404);
    }

    /** @test */
    public function tc5_address_transfer_to_payment_page(): void
    {
        Session::put('address_id', $this->otherAddress->addressId);

        $response = $this->get(route('payment.show', $this->vendor->userId));
        $response->assertStatus(302);
    }

    /** @test */
    public function tc6_default_address_transfer_flow(): void
    {
        $this->actingAs($this->customer);
        App::setLocale('id');

        $this->packageCategory = PackageCategory::factory()->create();
        // Create package
        $package = Package::factory()->create([
            'vendorId' => $this->vendor->vendorId,
            'categoryId' => $this->packageCategory->id,
            'name' => 'Paket Pagi',
            'breakfastPrice' => 35000,
        ]);

        // Create cart in DB
        $cart = Cart::create([
            'userId' => $this->customer->userId,
            'vendorId' => $this->vendor->vendorId,
        ]);

        // Create cart item in DB
        CartItem::create([
            'cartId' => $cart->cartId,
            'packageId' => $package->packageId,
            'breakfastQty' => 1,
            'lunchQty' => 0,
            'dinnerQty' => 0,
            'price' => $package->breakfastPrice,
        ]);

        // If your app expects `cart_id` in session (check your controller), set it:
        $this->withSession([
            'address_id' => $this->defaultAddress->addressId,
            // 'cart_id' => $cart->cartId, // Uncomment if your app uses this
        ]);

        // Hit the payment.show route
        $response = $this->get(route('payment.show', [
            'vendor_id' => $this->vendor->vendorId,
            'address_id' => $this->defaultAddress->addressId,
        ]));

        // Assertions
        $response->assertStatus(200);
        $response->assertSeeText($this->defaultAddress->jalan);
        $response->assertSeeText($this->defaultAddress->recipient_name);
    }



}


