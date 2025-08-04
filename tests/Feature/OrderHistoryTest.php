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

        $vendorA = Vendor::factory()->create(['name' => 'Alpha Catering']);
        $vendorB = Vendor::factory()->create(['name' => 'Beta Catering']);

        $category = PackageCategory::first();

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

        OrderItem::create([
            'orderId' => $orderA->orderId,
            'packageId' => $packageA->packageId,
            'name' => $packageA->name, 
            'packageTimeSlot' => 'Afternoon',
            'price' => 30000,
            'quantity' => 3,
            'mealType' => 'Lunch',
        ]);
        OrderItem::create([
            'orderId' => $orderB->orderId,
            'packageId' => $packageB->packageId,
            'name' => $packageB->name, 
            'packageTimeSlot' => 'Evening',
            'price' => 50000,
            'quantity' => 4,
            'mealType' => 'Dinner', 
        ]);

        return compact('user', 'vendorA', 'vendorB', 'orderA', 'orderB', 'packageA', 'packageB');
    }

    /** @test */
    public function tc3_order_history_page_shows_existing_orders()
    {
        $user = User::where('email', 'customer1@gmail.com')->first();
        $this->actingAs($user);

        $vendor = Vendor::factory()->create();
        $package = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
        ]);

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

        OrderItem::factory()->create([
            'orderId' => $order->orderId,
            'packageId' => $package->packageId,
            'packageTimeSlot' => 'Afternoon',
            'price' => 50000,
            'quantity' => 2,
        ]);

        $response = $this->get(route('order-history'));
        dump($response->headers->get('Location'));

        $response->assertStatus(200);
        $response->assertSee($order->orderId);
        $response->assertSee($vendor->name);
        $response->assertSee($package->name);

    }

    /** @test */
    public function tc4_catering_detail_shows_no_package_selected_message_and_disables_checkout()
    {
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

        $response = $this->actingAs($user)->get('/orders?query=Deluxe');
        $response->assertStatus(200);
        $response->assertSee('Beta Catering'); 
        $response->assertSee('Deluxe Dinner'); 
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
        ]);;

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
            'logo' => 'vendor_logo.jpg',
        ]);

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

        $package = $vendor->packages()->first();

        OrderItem::factory()->create([
            'orderId' => $order->orderId,
            'packageId' => $package->packageId,
            'packageTimeSlot' => 'Afternoon',
            'price' => 50000,
            'quantity' => 2,
        ]);

        $response = $this->get(route('order-history'));

        $response->assertStatus(200);
        $response->assertSee($order->orderId);
        $response->assertSee($vendor->name);
        $response->assertSee($package->name);
    }
}
