<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    /**
     * Seed all required data before each test.
     */
    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->artisan('migrate:fresh');
    //     $this->seed();
    // }

    /**
     * Authenticate as an Admin user.
     */
    protected function authenticateAsAdmin()
    {
        $admin = User::where('role', 'Admin')->first();
        $this->actingAs($admin);
    }

    /**
     * TC 1 - Preview on Profit is correctly displayed
     * @test
     */
    // public function tc1_preview_on_profit_is_correctly_displayed()
    // {
    //     $this->authenticateAsAdmin();

    //     $totalSalesThisMonth = DB::table('orders')
    //         ->join('payments', 'orders.orderId', '=', 'payments.orderId')
    //         ->whereNotNull('payments.paid_at')
    //         ->whereMonth('payments.paid_at', now()->month)
    //         ->sum('orders.totalPrice');

    //     $expectedProfit = round($totalSalesThisMonth * 0.05, 2);
    //     dump($expectedProfit);

    //     $response = $this->get('/admin-dashboard');
    //     $response->assertStatus(200);
    //     $response->assertSee((string) number_format($expectedProfit, 0, ',', '.'));
    // }

    /** @test */
    public function tc1_preview_on_profit_is_correctly_displayed()
    {
        $this->authenticateAsAdmin();

        $totalSalesThisMonth = DB::table('orders')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('totalPrice');

        $expectedProfit = round($totalSalesThisMonth * 0.05, 2);

        $response = $this->get('/admin-dashboard');
        $response->assertStatus(200);
        $response->assertSee((string) number_format($expectedProfit, 0, ',', '.'));
    }


    /**
     * TC 2 - Preview on Total Sales is correctly displayed
     * @test
     */
    // public function tc2_preview_on_total_sales_is_correctly_displayed()
    // {
    //     $this->authenticateAsAdmin();

    //     $totalSales = DB::table('orders')
    //         ->join('payments', 'orders.orderId', '=', 'payments.orderId')
    //         ->whereNotNull('payments.paid_at')
    //         ->sum('orders.totalPrice');

    //     $response = $this->get('/admin-dashboard');
    //     $response->assertStatus(200);
    //     $response->assertSee((string) number_format($totalSales, 0, ',', '.'));
    // }
    /** @test */
    public function tc2_preview_on_total_sales_is_correctly_displayed()
    {
        $this->authenticateAsAdmin();

        $totalSales = DB::table('orders')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('totalPrice');

        $response = $this->get('/admin-dashboard');
        $response->assertStatus(200);
        $response->assertSee((string) number_format($totalSales, 0, ',', '.'));
    }


    /**
     * TC 3 - Preview on percentage of profit
     * @test
     */
    // public function tc3_preview_on_percentage_of_profit()
    // {
    //     $this->authenticateAsAdmin();

    //     $currentProfit = DB::table('orders')
    //         ->join('payments', 'orders.orderId', '=', 'payments.orderId')
    //         ->whereNotNull('payments.paid_at')
    //         ->whereMonth('payments.paid_at', now()->month)
    //         ->sum('orders.totalPrice');

    //     $currentProfit *= 0.05;

    //     $lastMonthProfit = DB::table('orders')
    //         ->join('payments', 'orders.orderId', '=', 'payments.orderId')
    //         ->whereNotNull('payments.paid_at')
    //         ->whereMonth('payments.paid_at', now()->subMonth()->month)
    //         ->sum('orders.totalPrice');

    //     $lastMonthProfit *= 0.05;

    //     $expectedChange = $lastMonthProfit == 0
    //         ? 100
    //         : round((($currentProfit - $lastMonthProfit) / $lastMonthProfit) * 100, 2);

    //     $response = $this->get('/admin-dashboard');
    //     $response->assertStatus(200);
    //     if (app()->getLocale() === 'en') {
    //         $response->assertSee('Increased');
    //     } elseif (app()->getLocale() === 'id') {
    //         $response->assertSee('Meningkat');
    //     }
    //     $response->assertSee((string) number_format(abs($expectedChange), 2));
    // }

    /** @test */
    public function tc3_preview_on_percentage_of_profit()
    {
        $this->authenticateAsAdmin();

        $orders = Order::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->get();

        // ini total sales bulan ini
        $currentMonthSales = $orders->sum(function ($order) {
            return (float) $order->totalPrice;
        });

        $lastMonthOrder = Order::whereMonth('created_at', Carbon::now()->subMonth()->month())
            ->whereYear('created_at', Carbon::now()->year)
            ->get();
        $lastMonthSales = $lastMonthOrder->sum(function ($lastMonthOrder) {
            return (float) $lastMonthOrder->totalPrice;
        });

        $currentProfit = $currentMonthSales * 0.05;
        $lastMonthProfit = $lastMonthSales * 0.05;

        dump($lastMonthProfit);



        $expectedChange = 0;
        if ($lastMonthProfit == 0) {
            $percentageprofit = 100;
        } else {
            $expectedChange = (($currentProfit - $lastMonthProfit) / $lastMonthProfit) * 100;
        }

        $response = $this->get('/admin-dashboard');
        $response->assertStatus(200);

        if (app()->getLocale() === 'en') {
            $response->assertSee('Increased');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSee('Meningkat');
        }

        $response->assertSee((string) number_format(abs($expectedChange), 2));
    }




    /** @test */
    public function tc4_preview_on_percentage_of_total_sales()
    {
        /** @var User|Authenticatable */
        $admin = User::factory()->create(['role' => 'Admin']);
        $this->actingAs($admin);

        $lastMonth = now()->subMonth();
        $thisMonth = now();

        // Last month: 1 paid order (100k)
        $lastMonthOrder = Order::factory()->create([
            'created_at' => $lastMonth,
            'totalPrice' => 100000,
        ]);
        \App\Models\Payment::factory()->create([
            'orderId' => $lastMonthOrder->orderId,
            'paid_at' => $lastMonth, // simulate paid last month
        ]);

        // This month: 1 paid order (200k)
        $thisMonthOrder = Order::factory()->create([
            'created_at' => $thisMonth,
            'totalPrice' => 200000,
        ]);
        \App\Models\Payment::factory()->create([
            'orderId' => $thisMonthOrder->orderId,
            'paid_at' => $thisMonth, // simulate paid this month
        ]);

        $response = $this->get(route('admin-dashboard'));

        $response->assertStatus(200);
        if (app()->getLocale() === 'en') {
            $response->assertSee('Increased');
        } elseif (app()->getLocale() === 'id') {
            $response->assertSee('Meningkat');
        }
        $response->assertSeeText('100.00 %');
    }
}
