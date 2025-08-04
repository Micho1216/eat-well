<?php

namespace Tests\Feature;

use App\Models\Address;
use Illuminate\Support\Facades\App;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Package;
use App\Models\OrderItem;
use App\Models\PackageCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class OrderHistoryTest extends TestCase
{
    protected function setupSearchOrdersTest(): array
    {
        /**
         * @var User|Authenticatable $user
         */
        $user = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($user); // Log in the user

        // Create vendors
        $vendorA = Vendor::factory()->create(['name' => 'Alpha Catering']);
        $vendorB = Vendor::factory()->create(['name' => 'Beta Catering']);

        // Create a PackageCategory before creating any Packages (as PackageFactory depends on it)
        $category = PackageCategory::first();

        // Create orders for the user with different vendors
        $orderA = Order::factory()->create([
            'userId' => $user->userId,
            'vendorId' => $vendorA->vendorId,
        ]);
        $orderB = Order::factory()->create([
            'userId' => $user->userId,
            'vendorId' => $vendorB->vendorId,
        ]);

        $packageA = Package::factory()->create([
            'vendorId' => $vendorA->vendorId,
            'categoryId' => $category->categoryId,
            'name' => 'Special Lunch'
        ]);
        $packageB = Package::factory()->create([
            'vendorId' => $vendorB->vendorId,
            'categoryId' => $category->categoryId,
            'name' => 'Deluxe Dinner'
        ]);

        // Create order items with all required fields
        OrderItem::create([
            'orderId' => $orderA->orderId,
            'packageId' => $packageA->packageId,
            'name' => $packageA->name, // Store the actual package name here for searchability
            'packageTimeSlot' => 'Afternoon',
            'price' => 30000,
            'quantity' => 3,
            'mealType' => 'Lunch', // Added mealType for completeness
        ]);
        OrderItem::create([
            'orderId' => $orderB->orderId,
            'packageId' => $packageB->packageId,
            'name' => $packageB->name, // Store the actual package name here
            'packageTimeSlot' => 'Evening',
            'price' => 50000,
            'quantity' => 4,
            'mealType' => 'Dinner', // Added mealType for completeness
        ]);

        return compact('user', 'vendorA', 'vendorB', 'orderA', 'orderB', 'packageA', 'packageB');
    }

    /** @test */
    public function tc1_orders_are_displayed_in_order_history()
    {
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendor = Vendor::factory()->create();
        $orders = Order::factory()->count(2)->create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
        ]);
    
        $response = $this->actingAs($user)->get('/orders');
    
        foreach ($orders as $order) {
            // $response->assertSee($order->order);
            $response->assertSee($order->vendor->name);
        }
        
    }

    /** @test */
    public function tc2_filter_orders_by_status(){
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendorActive = Vendor::factory()->create(['name'=> 'Active Vendor']);
        $vendorFinished = Vendor::factory()->create(['name' => 'Finished Vendor']);
        $vendorCancelled = Vendor::factory()->create(['name' => 'Cancelled Vendor']);

        $activeOrder = Order::factory()->create([
        'userId' => $user->userId,
        'vendorId' => $vendorActive->vendorId,
        'isCancelled' => 0,
        'startDate' => now()->subDays(2),
        'endDate' => now()->addDays(2),
    ]);
    $finishedOrder = Order::factory()->create([
        'userId' => $user->userId,
        'vendorId' => $vendorFinished->vendorId,
        'isCancelled' => 0,
        'startDate' => now()->subDays(10),
        'endDate' => now()->subDays(1),
    ]);
    $cancelledOrder = Order::factory()->create([
        'userId' => $user->userId,
        'vendorId' => $vendorCancelled->vendorId,
        'isCancelled' => 1,
        'startDate' => now()->subDays(5),
        'endDate' => now()->addDays(5),
    ]);

    // Active tab
    $response = $this->actingAs($user)->get('/orders?status=active');
    $response->assertSee((string)$activeOrder->vendor->name);
    $response->assertDontSee((string)$finishedOrder->vendor->name);
    $response->assertDontSee((string)$cancelledOrder->vendor->name);

    // Finished tab
    $response = $this->actingAs($user)->get('/orders?status=finished');
    $response->assertSee((string)$finishedOrder->vendor->name);
    $response->assertDontSee((string)$activeOrder->vendor->name);
    $response->assertDontSee((string)$cancelledOrder->vendor->name);

    // Cancelled tab
    $response = $this->actingAs($user)->get('/orders?status=cancelled');
    $response->assertSee((string)$cancelledOrder->vendor->name);
    $response->assertDontSee((string)$activeOrder->vendor->name);
    $response->assertDontSee((string)$finishedOrder->vendor->name);
    }

    /** @test */
    public function tc3_order_history_page_shows_existing_orders()
    {
        // Arrange: Buat user dan login
        $user = User::where('email', 'customer1@gmail.com')->first();
        $this->actingAs($user);

        // Buat vendor & package
        $vendor = Vendor::factory()->create();
        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
        ]);

          // Buat order dengan field lengkap
        $order = Order::factory()->create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
            'totalPrice' => 200000,
            'startDate' => now()->subDays(2),
            'endDate' => now(),
            'isCancelled' => false,
            'provinsi' => 'DKI JAKARTA',
            'kota' => 'JAKARTA SELATAN',
            'kecamatan' => 'Mampang Prapatan',
            'kelurahan' => 'Bangka',
            'kode_pos' => '12730',
            'jalan' => 'Jl. Kemang Raya No.10',
            'recipient_name' => 'Budi Santoso',
            'recipient_phone' => '08123456789',
        ]);

        // Buat order item dengan field lengkap
        OrderItem::factory()->create([
            'orderId' => $order->orderId,
            'packageId' => $package->packageId,
            'packageTimeSlot' => 'Afternoon',
            'price' => 50000,
            'quantity' => 2,
        ]);

        // Act
        $response = $this->get(route('order-history'));
        dump($response->headers->get('Location'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee($order->orderId);
        $response->assertSee($vendor->name);
        $response->assertSee($package->name);

    }

    /** @test */
    public function tc4_catering_detail_shows_no_package_selected_message_and_disables_checkout()
    {
        // 1. Log in as a customer (ensure their cart is empty for this test)
        $user = User::query()->where('role', 'like', 'Customer')->first();
        if (!$user) {
            $user = User::factory()->create(['role' => 'Customer']);
        }
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);
        Session::put('address_id', $address->addressId);


        if ($user->cart) {
            $user->cart->cartItems()->delete();
            $user->cart->delete(); 
        }

        $vendor = Vendor::factory()->create([
            'name' => 'Delicious Bites Catering',
            'phone_number' => '0897765443321',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Sudirman No. 1',
            'logo' => 'vendor_logo.jpg',
        ]);
        App::setLocale('en');

        /**
        * @var User|Authenticatable $user
        */
        $response = $this->actingAs($user)->get("/catering-detail/{$vendor->vendorId}");

        $response->assertStatus(200);
        $response->assertSeeText('No Package Selected Yet.');
    }


    /** @test */
    public function tc5_search_orders_by_vendor_name()
    {
        ['user' => $user, 'vendorA' => $vendorA, 'vendorB' => $vendorB] = $this->setupSearchOrdersTest();
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);

        // Search by vendor name "Alpha"
        $response = $this->actingAs($user)->get('/orders?query=Alpha');
        $response->assertStatus(200);
        $response->assertSee('Alpha Catering');
        $response->assertDontSee('Beta Catering');
    }

    /** @test */
    public function tc6_search_orders_by_package_name(){
        ['user' => $user, 'vendorA' => $vendorA, 'vendorB' => $vendorB, 'packageA' => $packageA, 'packageB' => $packageB] = $this->setupSearchOrdersTest();
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);

        // Search by package name "Deluxe"
        $response = $this->actingAs($user)->get('/orders?query=Deluxe');
        $response->assertStatus(200);
        $response->assertSee('Beta Catering'); // Vendor of Deluxe Dinner
        $response->assertSee('Deluxe Dinner'); // Package name
        $response->assertDontSee('Alpha Catering');
        $response->assertDontSee('Special Lunch');
    }

    /** @test */
    public function tc7_search_orders_by_unmatched_keyword(){
        App::setLocale('en');
        ['user' => $user] = $this->setupSearchOrdersTest();
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);
        // Search with a keyword that matches nothing
        $response = $this->actingAs($user)->get('/orders?query=NotExist');
        $response->assertStatus(200);

        if (app()->getLocale() === 'en') {
            $response->assertSeeText('No orders found');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSeeText('Pesanan tidak ditemukan');
        }
    }

    /** @test */
    public function tc8_view_catering_button_redirects_to_vendor_detail()
    {
        // 1. Get the first user with role "customer" (deterministic creation for the test)
        /**
        * @var User|Authenticatable $customer
        */
        $customer = User::factory()->create(['role' => 'Customer']);
        $this->actingAs($customer); // Log in the customer

        $address = Address::factory()->create([
            'userId' => $customer->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);

        // Create a vendor that the "view catering" button would link to
       $vendor = Vendor::factory()->create([
            'name' => 'Delicious Bites Catering',
            'phone_number' => '0897765443321',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Sudirman No. 1',
            'logo' => 'vendor_logo.jpg', // assume your seeder or logic handles fake image
        ]);;

        // 2. Procedure: Simulate clicking on the "view catering" button.
        $response = $this->actingAs($customer)->get(route('catering-detail', $vendor));
        $response->assertStatus(200); 
        $response->assertSeeText($vendor->name);
   }


    /** @test */
    public function tc9_order_detail_page_shows_package_details()
    {
        $user = User::query()->where('role', 'like', 'Customer')->first();
        $vendor = Vendor::factory()->create([
            'name' => 'Murazik LLC',
            'phone_number' => '0897765443321',
            'provinsi' => 'DKI Jakarta',
            'kota' => 'Jakarta Selatan',
            'kecamatan' => 'Setiabudi',
            'kelurahan' => 'Karet',
            'kode_pos' => '12920',
            'jalan' => 'Jl. Sudirman No. 1',
            'logo' => 'vendor_logo.jpg', // assume your seeder or logic handles fake image
        ]);

        $order = Order::factory()->create([
            'userId' => $user->userId,
            'vendorId' => $vendor->vendorId,
        ]);
        $package = Package::factory()->create(['name' => 'est asperiores eveniet']);
        OrderItem::create([
            'orderId' => $order->orderId,
            'packageId' => $package->packageId,
            'packageTimeSlot' => 'Afternoon',
            'price' => 545288.95,
            'quantity' => 7,
        ]);

        $response = $this->actingAs($user)->get("/orders/{$order->orderId}");
        $response->assertSee('Murazik LLC');
        $response->assertSee('est asperiores eveniet');
        $response->assertSee('Afternoon');
        $response->assertSee((string)$order->orderId);
    }

}

