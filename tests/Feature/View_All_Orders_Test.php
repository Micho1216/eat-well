<?php

namespace Tests\Feature;


use App\Exports\AdminOrderExport;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class View_All_Orders_Test extends TestCase
{
    // use RefreshDatabase;

    /** @test */
    public function test_view_all_orders()
    {
        $response = $this->get('/');
        // user role = Admin

        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create([
            'role' => 'Admin',
        ]);
        $this->actingAs($user);
        $response = $this->get(route('view-order-history'));

        $query = Order::with(['payment', 'orderItems', 'vendor', 'user']);
        $orders = $query->orderBy('created_at')->paginate(20);

        $originalDate = '2025-07-14 00:00:00';
        $formatted = Carbon::parse($originalDate)->format('d M Y');
        // $response->assertSee($orders);


        foreach ($orders as $order) {
            $response->assertSee($order->orderId);
            $response->assertSee($order->user->name);
            $response->assertSee($order->vendor->name);
            // $response->assertSee($order->totalPrice);
            $response->assertSeeText(Carbon::parse($order->startDate)->translatedFormat('d M Y'));
            $response->assertSeeText(Carbon::parse($order->endDate)->translatedFormat('d M Y'));
        }
        $response->assertStatus(200);
    }

    public function test_if_no_orders()
    {
        // user role = Admin

        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create([
            'role' => 'Admin',
        ]);
        $query = Order::with(['payment', 'orderItems', 'vendor', 'user']);
        $orders = $query->orderBy('created_at')->paginate(20);


        $this->actingAs($user);
        $response = $this->get(route('view-order-history'));

        // Check if the response contains the message for no orders for locale = en
        if ($orders->isEmpty() && app()->getLocale() === 'en') {
            $response->assertSeeText('No sales yet');
        } elseif ($orders->isEmpty() && app()->getLocale() === 'id') {
            $response->assertSeeText('Belum ada penjualan');
        }
        // $response->assertSeeText('No sales yet.');
        $response->assertStatus(200);
    }



    public function test_login_as_user()
    {
        // user role = User
        /** @var \App\Models\User $user */
        $user = \App\Models\User::factory()->create([
            'role' => 'Customer',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('view-order-history'));
        $response->assertRedirect(route('home'));
        $response->assertStatus(302);
    }

    public function test_admin_can_filter_orders_by_date_range()
    {
        // Buat admin user
        $admin = User::factory()->create(['role' => 'Admin']);
        $this->actingAs($admin);

        // Order diluar rentang filter
        Order::factory()->create([
            'created_at' => '2025-07-01',
        ]);

        // Order dalam rentang filter
        $matchingOrder = Order::factory()->create([
            'created_at' => '2025-07-15',
        ]);

        // Panggil route dengan parameter filter
        $response = $this->get(route('view-order-history', [
            'startDate' => '2025-07-10',
            'endDate' => '2025-07-20',
        ]));

        $response->assertStatus(200);
        $response->assertSeeText($matchingOrder->orderId); // Order dalam range tampil
        $response->assertDontSeeText('01 Jul 2025'); // Order di luar range tidak tampil
    }


    public function test_admin_can_reset_filter_to_see_all_orders()
    {
        $admin = User::factory()->create(['role' => 'Admin']);
        $this->actingAs($admin);

        // Order lama, di luar filter range
        $oldOrder = Order::factory()->create([
            'created_at' => '2025-07-01',
        ]);

        // Order baru, dalam filter range
        $filteredOrder = Order::factory()->create([
            'created_at' => '2025-07-16',
        ]);

        $response = $this->get(route('view-order-history', [
            'startDate' => '2025-07-10',
            'endDate' => '2025-07-20',
        ]));

        dump($oldOrder->orderId);

        $response->assertStatus(200);
        $response->assertSeeText($filteredOrder->orderId);
        $response->assertDontSeeText($oldOrder->orderId);

        $response = $this->get(route('view-order-history'));

        $response->assertStatus(200);
        $response->assertSeeText($filteredOrder->orderId);
        $response->assertSeeText($oldOrder->orderId);
    }

    public function test_admin_can_export_orders_to_excel()
    {
        Excel::fake();
        /** @var \App\Models\User $admin */
        $admin = User::factory()->create(['role' => 'Admin']);
        $this->actingAs($admin);

        $order = Order::factory()->create([
            'created_at' => '2025-07-16',
        ]);

        $response = $this->get(route('admin.order.export', [
            'startDate' => '2025-07-10',
            'endDate' => '2025-07-20',
        ]));


        $response->assertStatus(200);

        Excel::assertDownloaded('admin_order_export.xlsx', function (AdminOrderExport $export) {
            return true;
        });
    }
}


