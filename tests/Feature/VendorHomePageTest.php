<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\DeliveryStatus;
use App\Models\District;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;
use Carbon\Carbon;
use Database\Seeders\AddressSeeder;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\UserSeeder;
use Illuminate\Contracts\Auth\Authenticatable;
// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class VendorHomePageTest extends TestCase
{
    private function createVendorUserWithAddress(array $userData = [])
    {
        /** @var User $user */
        $user = User::factory()->create(array_merge([
            'email' => 'vendor@example.com',
            'role' => 'Vendor',
        ], $userData));

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $address = $user->addresses()->create([
            'province_id' => $province->id,
            'city_id' => $city->id,
            'district_id' => $district->id,
            'village_id' => $village->id,
            'detail' => 'Jl. Sample Address',
            'provinsi' => 'Jawa Barat',
            'kota' => 'Bandung',
            'kecamatan' => 'Coblong',
            'kelurahan' => 'Dago',
            'kode_pos' => '40135',
            'jalan' => 'Jl. Sample Address',
            'recipient_name' => 'Sample Recipient',
            'recipient_phone' => '08123456789',
        ]);


        session(['address_id' => $address->addressId]);

        $vendor = Vendor::factory()->create([
            'userId' => $user->userId,
            'name' => $user->name ?? 'Vendor',
            'breakfast_delivery' => '08:30-09:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
        ]);

        return [$user, $vendor];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');
        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);

        $this->seed([
            AddressSeeder::class,
            CuisineTypeSeeder::class,
            PackageCategorySeeder::class,
            PaymentMethodSeeder::class
        ]);
    }

    /**
     * Summary of tc1_display_welcome_and_delivery_schedule
     * @test
     */
    public function tc1_display_welcome_and_delivery_schedule()
    {

        /**
         * @var User | Authenticatable $user
         */
        $user = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
            'role' => 'Vendor',
        ]);


        $vendor = Vendor::factory()->create([
            'userId' => $user->userId,
            'name' => 'Green Catering',
            'breakfast_delivery' => '06:30-07:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
        ]);

        $response = $this->actingAs($user)->get('/cateringHomePage');

        if (app()->getLocale() == 'en') {
            $response->assertSeeText('Welcome, Green Catering');
        } else {
            $response->assertSeeText('Selamat Datang, Green Catering!');
        }
    }

    /**
     * @test
     */
    /** @test */
    // public function tc2_list_orders_per_slot()
    // {
    //     $this->withoutExceptionHandling();

    //     [$user, $vendor] = $this->createVendorUserWithAddress([
    //         'email' => 'vendor1@mail.com',
    //         'name' => 'Green Catering',
    //     ]);

    //     $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
    //     $endOfWeek = now()->endOfWeek(Carbon::SUNDAY);

    //     $breakfastPackage = Package::factory()->create([
    //         'vendorId' => $vendor->vendorId,
    //         'name' => 'Protein Pack A',
    //         'breakfastPrice' => 50000,
    //     ]);

    //     $lunchPackage = Package::factory()->create([
    //         'vendorId' => $vendor->vendorId,
    //         'name' => 'Vegan Delight',
    //         'lunchPrice' => 60000,
    //     ]);

    //     $dinnerPackage = Package::factory()->create([
    //         'vendorId' => $vendor->vendorId,
    //         'name' => 'Energy Bowl',
    //         'dinnerPrice' => 70000,
    //     ]);

    //     $order = Order::factory()->create([
    //         'vendorId' => $vendor->vendorId,
    //         'startDate' => $startOfWeek,
    //         'endDate' => $endOfWeek,
    //     ]);

    //     $orderItems = collect([
    //         OrderItem::factory()->create([
    //             'orderId' => $order->orderId,
    //             'packageId' => $breakfastPackage->packageId,
    //             'packageTimeSlot' => 'Morning',
    //             'price' => $breakfastPackage->breakfastPrice,
    //             'quantity' => 2,
    //         ]),
    //         OrderItem::factory()->create([
    //             'orderId' => $order->orderId,
    //             'packageId' => $lunchPackage->packageId,
    //             'packageTimeSlot' => 'Afternoon',
    //             'price' => $lunchPackage->lunchPrice,
    //             'quantity' => 1,
    //         ]),
    //         OrderItem::factory()->create([
    //             'orderId' => $order->orderId,
    //             'packageId' => $dinnerPackage->packageId,
    //             'packageTimeSlot' => 'Evening',
    //             'price' => $dinnerPackage->dinnerPrice,
    //             'quantity' => 3,
    //         ]),
    //     ]);

    //     $order->orderItems()->saveMany($orderItems);

    //     $order->update([
    //         'totalPrice' => $orderItems->sum(fn($item) => $item->price * $item->quantity),
    //     ]);

    //     Payment::factory()->create([
    //         'orderId' => $order->orderId,
    //         'paid_at' => now()->subDay(),
    //     ]);

    //     foreach (['Morning', 'Afternoon', 'Evening'] as $slot) {
    //         for ($i = 0; $i < 7; $i++) {
    //             DeliveryStatus::factory()->create([
    //                 'orderId' => $order->orderId,
    //                 'deliveryDate' => $startOfWeek->copy()->addDays($i),
    //                 'slot' => $slot,
    //             ]);
    //         }
    //     }

    //     $this->actingAs($user)
    //         ->get('/cateringHomePage')
    //         ->assertSee($breakfastPackage->name)
    //         ->assertSee($lunchPackage->name)
    //         ->assertSee($dinnerPackage->name);
    // }

    public function tc2_list_orders_per_slot()
    {
        $this->withoutExceptionHandling();

        // 1. Buat user vendor
        [$user, $vendor] = $this->createVendorUserWithAddress([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
        ]);

        // 2. Tanggal khusus agar deliveryDate match dengan hari ini
        $today = now()->startOfDay(); // pastikan cocok dengan hari ini
        $endDate = $today->copy()->addDays(6); // buat 1 minggu

        // 3. Buat 3 paket berbeda untuk setiap slot
        $breakfastPackage = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'name' => 'Protein Pack A',
            'breakfastPrice' => 50000,
        ]);

        $lunchPackage = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'name' => 'Vegan Delight',
            'lunchPrice' => 60000,
        ]);

        $dinnerPackage = Package::factory()->create([
            'vendorId' => $vendor->vendorId,
            'name' => 'Energy Bowl',
            'dinnerPrice' => 70000,
        ]);

        // 4. Buat order
        $order = Order::factory()->create([
            'vendorId' => $vendor->vendorId,
            'startDate' => $today,
            'endDate' => $endDate,
        ]);

        // 5. Buat order items (langsung create, bukan make)
        $orderItems = collect([
            OrderItem::factory()->create([
                'orderId' => $order->orderId,
                'packageId' => $breakfastPackage->packageId,
                'packageTimeSlot' => 'Morning',
                'price' => $breakfastPackage->breakfastPrice,
                'quantity' => 2,
            ]),
            OrderItem::factory()->create([
                'orderId' => $order->orderId,
                'packageId' => $lunchPackage->packageId,
                'packageTimeSlot' => 'Afternoon',
                'price' => $lunchPackage->lunchPrice,
                'quantity' => 1,
            ]),
            OrderItem::factory()->create([
                'orderId' => $order->orderId,
                'packageId' => $dinnerPackage->packageId,
                'packageTimeSlot' => 'Evening',
                'price' => $dinnerPackage->dinnerPrice,
                'quantity' => 3,
            ]),
        ]);

        // 6. Update total price
        $order->update([
            'totalPrice' => $orderItems->sum(fn($item) => $item->price * $item->quantity),
        ]);

        // 7. Buat payment agar order dianggap "Paid"
        Payment::factory()->create([
            'orderId' => $order->orderId,
            'paid_at' => now()->subDay(),
        ]);

        // 8. Buat delivery status untuk hari ini (wajib)
        foreach (['Morning', 'Afternoon', 'Evening'] as $slot) {
            DeliveryStatus::factory()->create([
                'orderId' => $order->orderId,
                'deliveryDate' => $today->toDateString(), // pastikan cocok dengan now()
                'slot' => $slot,
            ]);
        }

        // 9. Hit endpoint dan test view
        $response = $this->actingAs($user)->get('/cateringHomePage');

        $response->assertSee($breakfastPackage->name);
        $response->assertSee($lunchPackage->name);
        $response->assertSee($dinnerPackage->name);
    }



    /**
     * @test
     */
    public function tc3_no_orders_show_placeholder()
    {
        $this->withoutExceptionHandling();

        // 1. Create user
        /**
         * @var User | Authenticatable $user
         */
        $user = User::factory()->create([
            'email' => 'vendor1@mail.com',
            'name' => 'Green Catering',
            'role' => 'Vendor',
        ]);

        // 2. Create vendor with defined delivery slots
        $vendor = Vendor::factory()->create([
            'userId' => $user->userId,
            'name' => 'Green Catering',
            'breakfast_delivery' => '06:30-07:30',
            'lunch_delivery' => '11:30-12:30',
            'dinner_delivery' => '17:30-18:30',
        ]);

        // 3. Assert placeholder is shown when there's no order
        if (app()->getLocale() == 'en') {
            $this->actingAs($user)
                ->get('/cateringHomePage')
                ->assertSee('No orders yet')
                ->assertSee('Served from 06:30-07:30')
                ->assertSee('Served from 11:30-12:30')
                ->assertSee('Served from 17:30-18:30');
        }
        if (app()->getLocale() == 'id') {
            $this->actingAs($user)
                ->get('/cateringHomePage')
                ->assertSee('Belum ada pesanan')
                ->assertSee('Disajikan dari 06:30-07:30')
                ->assertSee('Disajikan dari 11:30-12:30')
                ->assertSee('Disajikan dari 17:30-18:30');
        }
    }

    /**
     * @test
     */
    /** @test */
    // public function tc4_sales_chart_displayed_correctly()
    // {
    //     $this->withoutExceptionHandling();

    //     [$user, $vendor] = $this->createVendorUserWithAddress([
    //         'email' => 'vendor1@mail.com',
    //     ]);

    //     $weekStarts = collect(range(0, 3))->map(fn($i) => now()->startOfMonth()->addWeeks($i));
    //     $amounts = [100000, 200000, 0, 400000];

    //     foreach ($weekStarts as $i => $start) {
    //         $order = Order::factory()->create([
    //             'vendorId' => $vendor->vendorId,
    //             'startDate' => $start,
    //             'endDate' => $start->copy()->endOfWeek(Carbon::SUNDAY),
    //         ]);

    //         $package = Package::factory()->create([
    //             'vendorId' => $vendor->vendorId,
    //             'lunchPrice' => $amounts[$i],
    //         ]);

    //         OrderItem::factory()->create([
    //             'orderId' => $order->orderId,
    //             'packageId' => $package->packageId,
    //             'packageTimeSlot' => 'Afternoon',
    //             'price' => $amounts[$i],
    //             'quantity' => 1,
    //         ]);

    //         $order->update(['totalPrice' => $amounts[$i]]);

    //         Payment::factory()->create([
    //             'orderId' => $order->orderId,
    //             'paid_at' => $start->copy()->addDay(),
    //         ]);
    //     }

    //     $this->actingAs($user)->get('/cateringHomePage')
    //         ->assertSee('const chartData = [95000,190000,0,380000]', false);
    // }


    /**
     * @test
     */
    /** @test */
    public function tc5_sales_chart_shows_zero_when_no_payment()
    {
        $this->withoutExceptionHandling();

        [$user, $vendor] = $this->createVendorUserWithAddress([
            'email' => 'vendor1@mail.com',
        ]);

        Order::factory()->count(3)->create([
            'vendorId' => $vendor->vendorId,
            'startDate' => now()->startOfWeek(),
            'endDate' => now()->endOfWeek(),
        ]);

        $this->actingAs($user)
            ->get('/cateringHomePage')
            ->assertSee(['0', '0', '0', '0'], false);
    }


    /**
     * @test
     */
    public function tc6_restrict_access_for_non_vendor_and_guest()
    {
        // 1. Create a Customer user
        /**
         * @var User | Authenticatable $customer
         */
        $customer = User::factory()->create([
            'email' => 'customer@mail.com',
            'name' => 'Test Customer',
            'role' => 'Customer',
        ]);

        // 2. Try accessing as Guest (not logged in)
        $this->get('/cateringHomePage')
            ->assertRedirect('/login');

        // 3. Try accessing vendor dashboard as a Customer
        $this->actingAs($customer)
            ->get('/cateringHomePage')
            ->assertRedirect('/home');
    }

    /**
     * @test
     */
    public function tc7_redirect_vendor_without_catering_data()
    {
        $this->withoutExceptionHandling();
        // 1. Register new Vendor user
        /**
         * @var User | Authenticatable $user
         */
        $user = User::factory()->create([
            'email' => 'vendorNoData@mail.com',
            'name' => 'No Catering Yet',
            'password' => bcrypt('Test@1234'),
            'role' => 'Vendor',
        ]);

        // 2. Do not create vendor/catering data

        // 3. Login and access Catering Home Page
        $this->actingAs($user)
            ->get('/cateringHomePage')
            ->assertRedirect('/vendor-first-page');
    }
}
