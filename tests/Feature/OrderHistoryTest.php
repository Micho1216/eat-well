<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\App;
use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Package;
use App\Models\OrderItem;
use App\Models\PackageCategory;
// use Illuminate\Foundation\Testing\RefreshDatabase;

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
    /** @test */
    // Fix test method
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



        // Assert
        $response->assertStatus(200);
        $response->assertSee($order->orderId);
        $response->assertSee($vendor->name);
        $response->assertSee($package->name);
    }
}
