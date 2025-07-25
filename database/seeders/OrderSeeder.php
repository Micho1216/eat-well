<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\DeliveryStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Order::factory()
            ->count(100)
            ->create()
            ->each(function ($order) {
                $items = OrderItem::factory()
                    ->count(rand(1, 3))
                    ->forVendor($order->vendorId)
                    ->make(['orderId' => $order->orderId]);

                // Group by packageId and packageTimeSlot, sum quantities
                $grouped = [];
                foreach ($items as $item) {
                    // Always use the enum's value
                    $slot = $item->packageTimeSlot;
                    $key = $item->packageId . '-' . $slot;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = $item;
                    } else {
                        $grouped[$key]->quantity += $item->quantity;
                    }
                }

                $order->orderItems()->saveMany($grouped);

                // Calculate total price
                $total = $order->orderItems->sum(function ($item) {
                    return $item->price * $item->quantity;
                });

                $order->totalPrice = $total;
                $order->save();

                Payment::factory()->create([
                    'orderId' => $order->orderId,
                    'paid_at' => fake()->dateTimeBetween('-7 days', '-1 days'),
                ]);

                // Get unique time slots from orderItems
                $slots = $order->orderItems->pluck('packageTimeSlot')->unique()->toArray();

                // Use order startDate or today if not set
                $startDate = $order->startDate ? Carbon::parse($order->startDate) : now();

                for ($i = 0; $i < 7; $i++) {
                    $date = (clone $startDate)->addDays($i);
                    foreach ($slots as $slot) {
                        DeliveryStatus::factory()->create([
                            'orderId' => $order->orderId,
                            'deliveryDate' => $date,
                            'slot' => $slot,
                        ]);
                    }
                }
            });
    }
}
