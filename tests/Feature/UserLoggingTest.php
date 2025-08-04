<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\City;
use App\Models\District;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\PaymentMethod;
use App\Models\Province;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Village;
use Database\Seeders\CuisineTypeSeeder;
use Database\Seeders\PackageCategorySeeder;
use Database\Seeders\PackageSeeder;
use Database\Seeders\PaymentMethodSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\VendorSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserLoggingTest extends TestCase
{
    // use RefreshDatabase;
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate:fresh');

        $province = Province::create(['name' => 'Jawa Barat']);
        $city = City::create(['name' => 'Bandung', 'province_id' => $province->id]);
        $district = District::create(['name' => 'Coblong', 'city_id' => $city->id]);
        $village = Village::create(['name' => 'Dago', 'district_id' => $district->id]);
        $this->seed(UserSeeder::class);
        $this->seed(PackageCategorySeeder::class);
        $this->seed(PaymentMethodSeeder::class);
        $this->seed(CuisineTypeSeeder::class);
        $this->seed(VendorSeeder::class);
        $this->seed(PackageSeeder::class);
    }

    protected function createUserWithRole(string $role, string $name, string $email, string $password): User
    {
        return User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);
    }

    /**
     * TC1 - Verify logging for Customer role during shopping activity
     * @test
     */
    public function tc1_verify_logging_for_customer_role_during_shopping_activity()
    {
        $user = $this->createUserWithRole('Customer', 'Alibaba', 'ali@example.com', 'Password123');
        $address = Address::factory()->create([
            'userId' => $user->userId,
            'provinsi' => 'DKI Jakarta',
            'is_default' => true,
        ]);
        session(['address_id' => $address->addressId]);
        $this->actingAs($user);

        // Simulate user browsing and shopping
        $this->get('/caterings');

        // Get random package
        $package = Package::inRandomOrder()->first();
        $vendor = $package->vendor;
        $vendorId = $vendor->vendorId;
        $this->get("/catering-detail/{$vendorId}");
        session(['selected_vendor_id' => $vendorId]);
        $response = $this->post('/update-order-summary', [
            'vendor_id' => $vendorId,
            'packages' => [
                $package->packageId => [ // packageId
                    'items' => [
                        'breakfast' => $package->breakfastPrice ? 2 : 0,
                        'lunch' => $package->lunchPrice ? 1 : 0,
                        'dinner' => $package->dinnerPrice ? 1 : 0,
                    ],
                ]
            ]
        ]);

        $this->get("/payment");

        $response = $this->post('/checkout', [
            'payment_method_id' => 1,
            'start_date' => now()->addWeek()->startOfWeek()->format('Y-m-d'),
            'end_date' => now()->addWeek()->startOfWeek()->addDays(6)->format('Y-m-d'),
            'password' => 'Password123',
        ]);

        $this->get('/orders');
        $this->post('/logout');

        // Assert logs are recorded
        $logs = DB::table('user_activities')
            ->where('userId', $user->userId)
            ->get();

        dump($logs->pluck('url')->toArray());


        $expectedLogs = [
            ['method' => 'GET', 'url' => 'http://localhost/caterings'],
            ['method' => 'GET', 'url' => 'http://localhost/payment'],
            ['method' => 'POST', 'url' => 'http://localhost/checkout'],
        ];


        $this->assertCount(count($expectedLogs), $logs);

        // Loop through and verify each expected log
        foreach ($logs as $index => $log) {
            $this->assertEquals('Customer', $log->role);
            $this->assertEquals('Alibaba', $log->name);
            $this->assertEquals($expectedLogs[$index]['method'], $log->method);
            $this->assertEquals($expectedLogs[$index]['url'], $log->url);
            $this->assertNotNull($log->ip_address);
            $this->assertNotNull($log->accessed_at);
        }
    }

    /**
     * TC2 - Verify logging for Vendor role when managing products
     * @test
     */
    public function tc2_verify_logging_for_vendor_role_when_managing_products()
    {
        $vendorAcc = $this->createUserWithRole('Vendor', 'Karen', 'karen@vendor.com', 'VendorPass123');
        $this->actingAs($vendorAcc);

        $catering = Vendor::factory([
            'userId' => $vendorAcc->userId,
            'name' => 'Karen Store',
        ])->create();

        // Simulate vendor actions
        $this->get('/cateringHomePage');
        $this->get('/manageCateringPackage');

        // Prepare the request
        $categoryId = DB::table('package_categories')->first()->categoryId;
        $vendorId = $catering->vendorId;
        $cuisineType = DB::table('cuisine_types')->first();

        // Perform POST request
        $response = $this->post('/manageCateringPackage', [
            'categoryId' => $categoryId,
            'vendorId' => $vendorId,
            'name' => 'Deluxe Vegan Plan',
            'averageCalories' => 550,
            'breakfastPrice' => 40000,
            'lunchPrice' => 50000,
            'dinnerPrice' => 60000,
        ]);

        // Assert redirect to the management page
        $response->assertRedirect(route('manageCateringPackage'));

        // Assert package inserted to database
        $this->assertDatabaseHas('packages', [
            'name' => 'Deluxe Vegan Plan',
            'categoryId' => $categoryId,
            'vendorId' => $vendorId,
        ]);

        $this->get('/manageOrder');

        // Assert logs are recorded
        $logs = DB::table('user_activities')
            ->where('userId', $vendorAcc->userId)
            ->get();

        $this->assertCount(1, $logs);

        foreach ($logs as $log) {
            $this->assertEquals('Vendor', $log->role);
            $this->assertEquals('Karen', $log->name);
            $this->assertNotNull($log->ip_address);
            $this->assertNotNull($log->accessed_at);
        }
    }

    /**
     * TC3 - Verify logging for Admin on critical action like banning a user
     * @test
     */
    public function tc3_verify_logging_for_admin_on_critical_action()
    {
        $admin = $this->createUserWithRole('Admin', 'RootAdmin', 'admin@site.com', 'RootPass123');
        $this->actingAs($admin);

        $catId = PackageCategory::inRandomOrder()->first()?->categoryId;

        // Simulate admin actions
        $this->get('/admin-dashboard');
        $this->delete("/categories/{$catId}");

        $logs = DB::table('user_activities')
            ->where('userId', $admin->userId)
            ->get();

        $expectedLogs = [
            ['method' => 'GET', 'url' => 'http://localhost/admin-dashboard'],
            ['method' => 'DELETE', 'url' => "http://localhost/categories/{$catId}"],
        ];

        $this->assertCount(count($expectedLogs), $logs);

        foreach ($logs as $index => $log) {
            $this->assertEquals('Admin', $log->role);
            $this->assertEquals('RootAdmin', $log->name);
            $this->assertEquals($expectedLogs[$index]['method'], $log->method);
            $this->assertEquals($expectedLogs[$index]['url'], $log->url);
            $this->assertNotNull($log->ip_address);
            $this->assertNotNull($log->accessed_at);
        }
    }

    /** @test */
    public function tc4_guest_users_do_not_generate_activity_logs()
    {
        // Visit /home as a guest (unauthenticated)
        $responseHome = $this->get('/home');
        $responseHome->assertRedirect('/login');

        // Visit /admin-dashboard as a guest
        $responseAdmin = $this->get('/admin-dashboard');
        $responseAdmin->assertRedirect('/login');

        // Make sure no logs are generated in the user_activities table
        $logs = DB::table('user_activities')->get();
        $this->assertCount(0, $logs, 'Guest access should not generate activity logs');
    }

    /** @test */
    public function tc5_admin_can_access_user_activity_log_page()
    {
        $admin = $this->createUserWithRole('Admin', 'AdminUser', 'admin@example.com', 'AdminPass123');
        $this->actingAs($admin);

        $response = $this->get('/view-all-logs');
        $response->assertStatus(200);

        $response->assertSee('User');
        $response->assertSee('Role');
        $response->assertSee('URL');
        $response->assertSee('Method');
        $response->assertSee('IP');
        $response->assertSee('Time');
    }

    /** @test */
    public function tc6_non_admin_user_cannot_access_user_activity_log_page()
    {
        $customer = $this->createUserWithRole('Customer', 'AliCustomer', 'ali@cust.com', 'Cust123');
        $this->actingAs($customer);

        $response = $this->get('/view-all-logs');
        $response->assertRedirect('/home'); // or any defined fallback route for unauthorized users
    }
}
