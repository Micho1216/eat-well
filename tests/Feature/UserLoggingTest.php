<?php

namespace Tests\Feature;

use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Vendor;
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
        $this->actingAs($user);

        // Simulate user browsing and shopping
        $this->get('/caterings');

        // Get random package
        $package = Package::inRandomOrder()->first();
        $vendor = $package->vendor;
        $vendorId = $vendor->vendorId;
        $this->get("/catering-detail/{$vendorId}");
        $this->post('/update-order-summary', [
            'vendor_id' => $vendorId,
            'packages' => [
                $package->packageId => [ // packageId
                    'items' => [
                        'Breakfast' => $package->breakfastPrice ? 2 : 0,
                        'Lunch' => $package->lunchPrice ? 1 : 0,
                        'Dinner' => $package->dinnerPrice ? 1 : 0,
                ],
            ]
            ]
        ]);
        $this->get("/catering-detail/{$vendorId}/payment");

        $this->post('/checkout', [
            'vendor_id' => $vendorId,
            'payment_method_id' => 1, // Wellpay
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

        $expectedLogs = [
            ['method' => 'GET', 'url' => 'http://localhost/caterings'],
            ['method' => 'GET', 'url' => "http://localhost/catering-detail/{$vendorId}"],
            ['method' => 'POST', 'url' => 'http://localhost/update-order-summary'],
            ['method' => 'GET', 'url' => "http://localhost/catering-detail/{$vendorId}/payment"],
            ['method' => 'POST', 'url' => 'http://localhost/checkout'],
            ['method' => 'GET', 'url' => 'http://localhost/orders'],
            ['method' => 'POST', 'url' => 'http://localhost/logout'],
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

        $this->assertCount(4, $logs);

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

        $metId = PaymentMethod::inRandomOrder()->first()?->methodId;

        // Simulate admin actions
        $this->get('/admin-dashboard');
        $this->delete("/view-all-payment/delete/{$metId}");

        $logs = DB::table('user_activities')
            ->where('userId', $admin->userId)
            ->get();

        $expectedLogs = [
            ['method' => 'GET', 'url' => 'http://localhost/admin-dashboard'],
            ['method' => 'DELETE', 'url' => "http://localhost/view-all-payment/delete/{$metId}"],
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

    /** @test */
    public function tc7_check_log_display_limit_and_correctness()
    {
        // 1. Create users
        $customer = $this->createUserWithRole('Customer', 'Alibaba', 'ali@example.com', 'Password123');
        $vendorUser = $this->createUserWithRole('Vendor', 'Karen', 'karen@vendor.com', 'VendorPass123');
        $admin = $this->createUserWithRole('Admin', 'RootAdmin', 'admin@site.com', 'RootPass123');

        // -------- CUSTOMER LOGS (7) --------
        $this->actingAs($customer);

        $this->get('/caterings');
        $package = Package::inRandomOrder()->first();
        $vendor = $package->vendor;
        $vendorId = $vendor->vendorId;

        $this->get("/catering-detail/{$vendorId}");
        $this->post('/update-order-summary', [
            'vendor_id' => $vendorId,
            'packages' => [
                $package->packageId => [
                    'items' => [
                        'Breakfast' => $package->breakfastPrice ? 1 : 0,
                        'Lunch' => $package->lunchPrice ? 1 : 0,
                        'Dinner' => $package->dinnerPrice ? 1 : 0,
                    ]
                ]
            ]
        ]);
        $this->get("/catering-detail/{$vendorId}/payment");
        $this->post('/checkout', [
            'vendor_id' => $vendorId,
            'payment_method_id' => 1,
            'start_date' => now()->addDays(1)->format('Y-m-d'),
            'end_date' => now()->addDays(7)->format('Y-m-d'),
            'password' => 'Password123',
        ]);
        $this->get('/orders');
        $this->post('/logout');

        // -------- VENDOR LOGS (4) --------
        $this->actingAs($vendorUser);

        $vendorData = Vendor::factory(['userId' => $vendorUser->userId])->create();
        $categoryId = DB::table('package_categories')->first()->categoryId;

        $this->get('/cateringHomePage');
        $this->get('/manageCateringPackage');
        $this->post('/manageCateringPackage', [
            'categoryId' => $categoryId,
            'vendorId' => $vendorData->vendorId,
            'name' => 'Quick Bites Plan',
            'averageCalories' => 500,
            'breakfastPrice' => 30000,
            'lunchPrice' => 40000,
            'dinnerPrice' => 45000,
        ]);
        $this->get('/manageOrder');

        // -------- ADMIN LOG (1) --------
        $this->actingAs($admin);
        $this->get('/admin-dashboard/logging');

        // -------- VERIFY LOG TABLE --------
        $response = $this->get('/view-all-logs');
        $response->assertStatus(200);

        // Get 10 most recent logs from DB
        $recentLogs = DB::table('user_activities')->orderByDesc('accessed_at')->limit(10)->get();

        $this->assertCount(10, $recentLogs);

        foreach ($recentLogs as $log) {
            echo "[{$log->role}] {$log->method} {$log->url} at {$log->accessed_at}\n";
        }
        
        foreach ($recentLogs as $log) {
            $response->assertSee($log->name);
            $response->assertSee($log->role);
            $response->assertSee($log->method);
            $response->assertSee($log->url);
            $response->assertSee($log->ip_address);
            $response->assertSee((string) $log->accessed_at);
        }

    }

}
