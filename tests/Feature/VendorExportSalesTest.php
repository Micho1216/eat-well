<?php

namespace Tests\Feature;

use App\Models\CuisineType;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\UserSeeder;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class VendorExportSalesTest extends TestCase
{

    protected $vendorA;
    protected $vendorB;
    protected $vendorAUser;
    protected $vendorBUser;
    protected $customer;
    protected $packageA;
    protected $packageB;
    protected $order1;
    protected $order2;
    protected $unpaidOrder;
    protected $orderItem1;
    protected $payment1;
    protected $payment2;
    protected $paymentUnpaid;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');

        $this->seed(UserSeeder::class);
        $this->seed(AddressSeeder::class);
        $this->seed(PackageCategorySeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(CuisineTypeSeeder::class);

        $this->vendorAUser = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'password' => bcrypt('vendorabcd1234'),
            'role' => 'Vendor',
        ]);

        $this->vendorA = Vendor::factory()->create([
            'userId' => $this->vendorAUser->userId,
            'name' => 'Green Catering',
        ]);

        $this->vendorBUser = User::factory()->create([
            'email' => 'vendor2@mail.com',
            'password' => bcrypt('vendordcba321'),
            'role' => 'Vendor',
        ]);

        $this->vendorB = Vendor::factory()->create([
            'userId' => $this->vendorBUser->userId,
            'name' => 'Blue Catering',
        ]);

        $this->customer = User::first();

        $category = PackageCategory::first();
        $cuisine = CuisineType::first();
        $wellPay = PaymentMethod::where('name', 'WellPay')->first();
        $virtualAccount = PaymentMethod::where('name', 'Virtual Account')->first();

        $this->packageA = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Meal Plan A',
            'categoryId' => $category->categoryId,
            'breakfastPrice' => 50000,
            'lunchPrice' => null,
            'dinnerPrice' => null,
        ]);

        $this->packageB = Package::factory()->create([
            'vendorId' => $this->vendorA->vendorId,
            'name' => 'Meal Plan B',
            'categoryId' => $category->categoryId,
            'breakfastPrice' => null,
            'lunchPrice' => null,
            'dinnerPrice' => 70000,
        ]);


        $this->order1 = Order::factory()->create([
            'userId' => $this->customer->userId,
            'vendorId' => $this->vendorA->vendorId,
            'totalPrice' => 0,
        ]);

        $orderItem1 = OrderItem::create([
            'orderId' => $this->order1->orderId,
            'packageId' => $this->packageA->packageId,
            'quantity' => 3,
            'price' => $this->packageA->breakfastPrice,
            'packageTimeSlot' => 'Morning',
        ]);

        $this->payment1 = Payment::create([
            'methodId' => $wellPay->methodId,
            'orderId' => $this->order1->orderId,
            'paid_at' => Carbon::parse('2025-06-02'),
        ]);

        $totalPrice = $orderItem1->quantity * $this->packageA->breakfastPrice;
        $this->order1->totalPrice = $totalPrice;
        $this->order1->save();

        $this->order2 = Order::factory()->create([
            'userId' => $this->customer->userId,
            'vendorId' => $this->vendorA->vendorId,
            'totalPrice' => 0,
        ]);

        $orderItem2 = OrderItem::create([
            'orderId' => $this->order2->orderId,
            'packageId' => $this->packageB->packageId,
            'quantity' => 2,
            'price' => $this->packageB->dinnerPrice,
            'packageTimeSlot' => 'Evening',
        ]);


        $this->payment2 = Payment::create([
            'methodId' => $virtualAccount->methodId,
            'orderId' => $this->order2->orderId,
            'paid_at' => '2025-07-03',
        ]);
        $totalPrice = $orderItem2->quantity * $this->packageB->dinnerPrice;
        $this->order2->totalPrice = $totalPrice;
        $this->order2->save();

        $this->unpaidOrder = Order::factory()->create([
            'userId' => $this->customer->userId,
            'vendorId' => $this->vendorA->vendorId,
            'totalPrice' => 0,
        ]);
        $unpaidOrderItem = OrderItem::create([
            'orderId' => $this->unpaidOrder->orderId,
            'packageId' => $this->packageB->packageId,
            'quantity' => 4,
            'price' => $this->packageB->dinnerPrice,
            'packageTimeSlot' => 'Evening',
        ]);

        $this->paymentUnpaid = Payment::create([
            'methodId' => $virtualAccount->methodId,
            'orderId' => $this->unpaidOrder->orderId,
        ]);
        $totalPrice = $unpaidOrderItem->quantity * $this->packageB->dinnerPrice;
        $this->unpaidOrder->totalPrice = $totalPrice;
        $this->unpaidOrder->save();
    }

    /** @test */
    public function tc1_vendor_can_view_their_own_sales_data()
    {
        // Log in as Vendor A
        $this->actingAs($this->vendorAUser);

        $response = $this->get(route('sales.show'));


        $this->assertDatabaseHas('orders', [
            'orderId' => $this->order1->orderId,
            'vendorId' => $this->vendorA->vendorId,
        ]);

        $this->assertDatabaseHas('orders', [
            'orderId' => $this->order2->orderId,
            'vendorId' => $this->vendorA->vendorId,
        ]);

        $response->assertStatus(200);
        $response->assertSeeText($this->order1->orderId);
        $response->assertSeeText($this->order2->orderId);

        // Should include key details`
        $response->assertSee($this->customer->name); // customer name
        $response->assertSee('2025-06-02');
        $response->assertSee('2025-07-03');

        $response->assertSeeText('Rp ' . number_format($this->order1->totalPrice, 2, ',', '.'));
        $response->assertSeeText('Rp ' . number_format($this->order2->totalPrice, 2, ',', '.'));
        $response->assertSee($this->packageA->name);
        $response->assertSee($this->packageB->name);

        // Should NOT see unpaid order
        $id = "ORD{$this->unpaidOrder->orderId}";
        $response->assertDontSeeText($id);
        $response->assertDontSee($this->unpaidOrder->totalPrice);
    }

    /** @test */
    public function tc2_vendor_can_filter_sales_by_start_date_only()
    {
        $this->actingAs($this->vendorAUser);

        $response = $this->get(route('sales.show', [
            'startDate' => '2025-07-01',
        ]));

        $response->assertStatus(200);

        // Should include order2 (paid at 2025-07-03)
        $response->assertSeeText('ORD' . $this->order2->orderId);
        $response->assertSeeText('Rp ' . number_format($this->order2->totalPrice, 2, ',', '.'));

        // Should NOT include order1 (paid at 2025-06-02)
        $response->assertDontSeeText('ORD' . $this->order1->orderId);
        $response->assertDontSeeText('Rp ' . number_format($this->order1->totalPrice, 2, ',', '.'));

        // Should NOT include unpaid order (no paid_at)
        $response->assertDontSeeText('ORD' . $this->unpaidOrder->orderId);
        $response->assertDontSeeText('Rp ' . number_format($this->unpaidOrder->totalPrice, 2, ',', '.'));
    }

    /** @test */
    public function tc3_vendor_can_filter_sales_by_start_and_end_date_range()
    {
        $this->actingAs($this->vendorAUser);

        $response = $this->get(route('sales.show', [
            'startDate' => '2025-06-01',
            'endDate' => '2025-06-30',
        ]));

        $response->assertStatus(200);

        $response->assertSeeText('ORD' . $this->order1->orderId);
        $response->assertSeeText('Rp ' . number_format($this->order1->totalPrice, 2, ',', '.'));

        $response->assertDontSeeText('ORD' . $this->order2->orderId);
        $response->assertDontSeeText('Rp ' . number_format($this->order2->totalPrice, 2, ',', '.'));

        $response->assertDontSeeText('ORD' . $this->unpaidOrder->orderId);
        $response->assertDontSeeText('Rp ' . number_format($this->unpaidOrder->totalPrice, 2, ',', '.'));
    }

    /** @test */
    public function tc6_vendor_with_no_sales_sees_empty_state()
    {
        $this->actingAs($this->vendorBUser);


        $response = $this->get(route('sales.show'));


        $response->assertStatus(200);

        $response->assertSeeText('No sales yet.');

        $response->assertSeeHtml('<button class="btn btn-green" disabled>');
    }

    /** @test */
    public function tc7_vendor_inputs_invalid_date_filter()
    {
        // Log in as Vendor A
        $this->actingAs($this->vendorAUser);

        // Attempt to filter with an invalid date range (startDate > endDate)
        $response = $this->get(route('sales.show', [
            'startDate' => '2025-12-01',
            'endDate' => '2025-01-01',
        ]));

        $response->assertStatus(302);

        $response->assertRedirect(); // optional
        $response->assertSessionHasErrors([
            'endDate' => 'Invalid date range',
        ]);
    }
}
