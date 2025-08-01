<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\DeliveryStatus;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = Vendor::whereIn('name', [
            'Nusantara Delights',
            'Tropical Bites',
            'Sari Rasa Kitchen'
        ])->get();

        foreach ($vendors as $vendor) {
            $this->createOrdersForVendor($vendor);
        }
    }

    private function createOrdersForVendor($vendor)
    {
        $types = ['cancelled', 'finished', 'upcoming', 'active'];

        foreach ($types as $type) {
            for ($i = 0; $i < 3; $i++) {
                $dates = $this->getDatesForType($type);
                $isCancelled = $type === 'cancelled' ? 1 : 0;

                $order = Order::factory()->create([
                    'vendorId' => $vendor->vendorId,
                    'userId' => User::where('role', 'Customer')->inRandomOrder()->first()->userId,
                    'startDate' => $dates['start'],
                    'endDate' => $dates['end'],
                    'isCancelled' => $isCancelled,
                ]);

                $items = OrderItem::factory()
                    ->count(rand(1, 3))
                    ->forVendor($vendor->vendorId)
                    ->make(['orderId' => $order->orderId]);

                $grouped = [];
                foreach ($items as $item) {
                    $key = $item->packageId . '-' . $item->packageTimeSlot;
                    if (!isset($grouped[$key])) {
                        $grouped[$key] = $item;
                    } else {
                        $grouped[$key]->quantity += $item->quantity;
                    }
                }

                $order->orderItems()->saveMany($grouped);

                $total = $order->orderItems->sum(fn($item) => $item->price * $item->quantity);
                $order->totalPrice = $total;
                $order->save();

                if (!$isCancelled) {
                    $startDate = Carbon::parse($order->startDate);
                    Payment::factory()->create([
                        'orderId' => $order->orderId,
                        'paid_at' => $startDate->copy()->subDays(5),
                    ]);
                }

                $slots = $order->orderItems->pluck('packageTimeSlot')->unique()->toArray();

                foreach ($slots as $slot) {
                    for ($j = 0; $j < 7; $j++) {
                        $deliveryDate = Carbon::parse($order->startDate)->addDays($j);
                        DeliveryStatus::factory()->create([
                            'orderId' => $order->orderId,
                            'deliveryDate' => $deliveryDate,
                            'slot' => $slot,
                        ]);
                    }
                }
            }
        }
    }

    private function getDatesForType(string $type): array
    {
        $now = Carbon::now();

        switch ($type) {
            case 'cancelled':
                // Pick a Monday 2 or 3 weeks ago
                $start = $now->copy()->startOfWeek()->subWeeks(rand(2, 3));
                break;

            case 'finished':
                // Pick a Monday 1 week ago or earlier
                $start = $now->copy()->startOfWeek()->subWeek();
                break;

            case 'upcoming':
                // Pick a Monday 1–2 weeks in the future
                $start = $now->copy()->startOfWeek()->addWeeks(rand(1, 2));
                break;

            case 'active':
            default:
                // This week’s Monday (or last Monday if today is Sunday)
                $start = $now->copy()->startOfWeek(); // This week’s Monday
                break;
        }

        return [
            'start' => $start->toDateString(),
            'end' => $start->copy()->addDays(6)->toDateString(), // Sunday
        ];
    }

}
