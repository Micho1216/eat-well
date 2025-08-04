<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\DeliveryStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Payment;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorUpdateDeliveryStatusTest extends TestCase
{
    protected $vendorAUser;
    protected $vendorA;
    protected $customerUser;
    protected $customerAddress;
    protected $package;
    public function setUp(): void
    {
        parent::setUp();
        // $this->withoutExceptionHandling();

        $this->artisan('migrate:fresh');

        // Seed supporting data
        $this->seed([
            CuisineTypeSeeder::class,
            PackageCategorySeeder::class,
            PaymentMethodSeeder::class,
        ]);

        // Create Vendor A User
        $this->vendorAUser = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
            'password' => bcrypt('Test@1234'),
            'role' => 'Vendor',
        ]);

        // Create Vendor A Catering Profile
        $this->vendorA = Vendor::factory()->create([
            'userId' => $this->vendorAUser->userId,
            'name' => 'Green Catering',
            'breakfast_delivery' => '06:30-08:00',
            'lunch_delivery' => '11:30-13:00',
            'dinner_delivery' => '17:30-19:00',
        ]);

        // Create a Package for Vendor A
        $this->package = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'categoryId' => PackageCategory::first()->categoryId,
            'name' => 'Protein Pack',
            'breakfastPrice' => 20000,
            'lunchPrice' => 30000,
            'dinnerPrice' => 25000,
        ]);

        // Create Customer User
        $this->customerUser = User::factory()->create([
            'email' => 'customer1@mail.com',
            'name' => 'Alice Customer',
            'password' => bcrypt('Cust12345'),
            'role' => 'Customer',
        ]);

        // Create Address for Customer
        $this->customerAddress = Address::factory()->create([
            'userId' => $this->customerUser->userId,
        ]);
    }

    protected function createOrderWithDeliveryStatus(array $options = [], array $timeSlots = ['Morning', 'Afternoon', 'Evening']): Order
    {
        $address = $this->customerAddress;

        $order = Order::create([
            'vendorId' => $options['vendorId'],
            'userId' => $options['userId'],
            'totalPrice' => 0,
            'startDate' => $options['startDate'],
            'endDate' => $options['endDate'],
            'isCancelled' => $options['is_cancelled'] ?? false,

            // Flattened address fields from address factory
            'provinsi' => $address->provinsi,
            'kota' => $address->kota,
            // 'kabupaten' => $address->kabupaten,
            'kecamatan' => $address->kecamatan,
            'kelurahan' => $address->kelurahan,
            'kode_pos' => $address->kode_pos,
            'jalan' => $address->jalan,
            'recipient_name' => $address->recipient_name,
            'recipient_phone' => $address->recipient_phone,
            'notes' => $address->notes ?? '',
        ]);

        // Use one complete package
        $package = Package::where('vendorId', $options['vendorId'])
            ->whereNotNull('breakfastPrice')
            ->whereNotNull('lunchPrice')
            ->whereNotNull('dinnerPrice')
            ->inRandomOrder()
            ->firstOrFail();

        $items = collect();

        foreach ($timeSlots as $slot) {
            $items->push(OrderItem::make([
                'orderId' => $order->orderId,
                'packageId' => $package->packageId,
                'packageTimeSlot' => $slot,
                'quantity' => rand(1, 3),
                'price' => match ($slot) {
                    'Morning' => $package->breakfastPrice,
                    'Afternoon' => $package->lunchPrice,
                    'Evening' => $package->dinnerPrice,
                },
            ]));
        }

        $order->orderItems()->saveMany($items);

        // Update total price
        $order->totalPrice = $items->sum(fn($item) => $item->price * $item->quantity);
        $order->save();

        // Create payment
        Payment::factory()->create([
            'orderId' => $order->orderId,
            'paid_at' => $options['paid_at'],
        ]);

        // Create delivery status for each specified time slot, for 7 days
        foreach ($timeSlots as $slot) {
            for ($i = 0; $i < 7; $i++) {
                $date = Carbon::parse($order->startDate)->copy()->addDays($i);
                DeliveryStatus::factory()->create([
                    'orderId' => $order->orderId,
                    'deliveryDate' => $date,
                    'slot' => $slot,
                ]);
            }
        }

        return $order;
    }


    /** @test */
    public function test_tc1_vendor_updates_today_status_to_delivered_and_arrived()
    {
        $this->actingAs($this->vendorAUser);

        $today = now()->startOfWeek();
        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $today,
            'endDate' => $today->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        // Update to Delivered
        $res1 = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'Delivered',
        ]);
        $res1->assertStatus(200)->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('delivery_statuses', [
            'orderId' => $order->orderId,
            'slot' => 'Morning',
            'status' => 'Delivered',
        ]);

        // Update to Arrived
        $res2 = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'Arrived',
        ]);
        $res2->assertStatus(200)->assertJsonFragment(['success' => true]);
        $this->assertDatabaseHas('delivery_statuses', [
            'orderId' => $order->orderId,
            'slot' => 'Morning',
            'status' => 'Arrived',
        ]);
    }

    /** @test */
    public function test_tc2_vendor_cannot_update_future_delivery_status()
    {
        $this->actingAs($this->vendorAUser);

        $nextWeek = now()->addWeek()->startOfWeek();

        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $nextWeek,
            'endDate' => $nextWeek->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        $response = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'Delivered',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_tc3_vendor_cannot_update_past_delivery_status()
    {
        $this->actingAs($this->vendorAUser);

        $lastWeek = now()->subWeek()->startOfWeek();

        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $lastWeek,
            'endDate' => $lastWeek->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        $response = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'Delivered',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function test_tc4_validation_error_when_status_missing()
    {
        $this->actingAs($this->vendorAUser);

        $today = now()->startOfWeek();

        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $today,
            'endDate' => $today->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        $response = $this->post("/delivery-status/{$order->orderId}/Morning", []); // Missing 'status'


        $response->assertStatus(302); // Redirect due to validation error

        // assert see has validation error
        $response->assertSessionHasErrors(['status']);

        $this->assertDatabaseMissing('delivery_statuses', [
            'orderId' => $order->orderId,
            'slot' => 'Morning',
            'status' => 'Delivered', // Make sure status didn’t update
        ]);
    }

    /** @test */
    public function test_tc5_customer_cannot_update_vendor_delivery_status()
    {
        $this->actingAs($this->customerUser); // Not vendor

        $today = now()->startOfWeek();

        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $today,
            'endDate' => $today->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        $response = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'Delivered',
        ]);

        $response->assertRedirect('/home');
    }

    /** @test */
    public function test_tc6_invalid_status_value_is_rejected()
    {
        $this->actingAs($this->vendorAUser);

        $today = now()->startOfWeek();

        $order = $this->createOrderWithDeliveryStatus([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $today,
            'endDate' => $today->copy()->addDays(6),
            'paid_at' => now(),
        ], ['Morning']);

        $response = $this->post("/delivery-status/{$order->orderId}/Morning", [
            'status' => 'DeliveredWrong', // Invalid
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
        $this->assertDatabaseMissing('delivery_statuses', [
            'orderId' => $order->orderId,
            'slot' => 'Morning',
            'status' => 'DeliveredWrong',
        ]);
    }
}
