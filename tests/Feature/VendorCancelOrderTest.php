<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\CuisineType;
use App\Models\DeliveryStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorCancelOrderTest extends TestCase
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

    protected function createOrderWithItemsAndPayment(array $options = []): Order
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

        $slots = ['Morning', 'Afternoon', 'Evening'];
        $items = collect();

        foreach ($slots as $slot) {
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

        // Create 7-day delivery status entries
        foreach ($slots as $slot) {
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


    /**
     * @test
     */
    public function test_tc1_vendor_cancels_upcoming_order()
    {
        $this->actingAs($this->vendorAUser);

        $nextWeek = now()->addWeek()->startOfWeek();
        $endWeek = $nextWeek->copy()->endOfWeek();

        $order = $this->createOrderWithItemsAndPayment([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $nextWeek,
            'endDate' => $endWeek,
            'paid_at' => now(),
        ]);

        $response = $this->post("/orders/{$order->orderId}/cancel");

        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('orders', [
            'orderId' => $order->orderId,
            'isCancelled' => true,
        ]);

        // Assert all delivery statuses for this order are soft deleted
        foreach ($order->deliveryStatuses as $status) {
            $this->assertDatabaseHas('delivery_statuses', [
                'statusId' => $status->statusId,
                'orderId' => $order->orderId,
                'status' => 'Cancelled',
            ]);

        }
    }

    /**
     * @test
     */
    public function test_tc2_vendor_cannot_cancel_ongoing_order()
    {
        $this->actingAs($this->vendorAUser);

        $today = now()->startOfWeek();
        $end = $today->copy()->addDays(6);

        $order = $this->createOrderWithItemsAndPayment([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $today,
            'endDate' => $end,
            'paid_at' => now(),
        ]);

        $response = $this->post("/orders/{$order->orderId}/cancel");

        $response->assertStatus(400); // or whatever your controller uses for blocked
        $response->assertJson([
            'success' => false,
        ]);

        $this->assertDatabaseHas('orders', [
            'orderId' => $order->orderId,
            'isCancelled' => false,
        ]);
    }

    /**
     * @test
     */
    public function test_tc3_vendor_cannot_cancel_completed_order()
    {
        $this->actingAs($this->vendorAUser);

        $lastWeek = now()->subWeek()->startOfWeek();
        $order = $this->createOrderWithItemsAndPayment([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $lastWeek,
            'endDate' => $lastWeek->copy()->addDays(6),
            'paid_at' => now(),
        ]);

        $response = $this->post("/orders/{$order->orderId}/cancel");

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Completed orders cannot be canceled.',
        ]);

        $this->assertDatabaseHas('orders', [
            'orderId' => $order->orderId,
            'isCancelled' => false,
        ]);
    }

    /**
     * @test
     */
    public function test_tc4_canceled_order_not_in_upcoming_list()
    {
        $this->actingAs($this->vendorAUser);

        $nextWeek = now()->addWeek()->startOfWeek();
        $order = $this->createOrderWithItemsAndPayment([
            'vendorId' => $this->vendorA->vendorId,
            'userId' => $this->customerUser->userId,
            'startDate' => $nextWeek,
            'endDate' => $nextWeek->copy()->addDays(6),
            'paid_at' => now(),
        ]);

        // Cancel the order
        $cancelRes = $this->post("/orders/{$order->orderId}/cancel");
        $cancelRes->assertStatus(200);
        $cancelRes->assertJson(['success' => true]);

        // Now request upcoming orders
        $response = $this->get('/manageOrder');

        $response->assertStatus(200);

        // Check that the canceled order is not rendered in HTML
        $formattedOrderId = 'INV' . str_pad($order->orderId, 3, '0', STR_PAD_LEFT);
        $this->assertStringNotContainsString($formattedOrderId, $response->getContent());
    }
}
