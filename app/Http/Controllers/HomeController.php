<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class HomeController extends Controller
{
    public function index()
    {
        /**
         * @var User|null $user
         */
        $user = Auth::user();

        if (!$user) {
            return redirect('login');
        }

        $address = Address::where('userId', $user->userId)
            ->where('is_default', true)
            ->first();

        if ($address) {
            // Get vendors in same provinsi
            $nearVendors = Vendor::where('provinsi', $address->provinsi)
                ->inRandomOrder()
                ->take(6)
                ->get();

            $countNear = $nearVendors->count();

            if ($countNear < 12) {
                // Get extra vendors not in same provinsi
                $extraVendors = Vendor::where('provinsi', '!=', $address->provinsi)
                    ->orderBy('rating')
                    ->take(12 - $countNear)
                    ->get();

                // Combine both
                $vendors = $nearVendors->concat($extraVendors);
            } else {
                $vendors = $nearVendors;
            }
        } else {
            $vendors = Vendor::orderByDesc('rating')->limit(12)->get();
        }

        $favVendors = $user->favoriteVendors()->limit(8)->get();

        $order = Order::where('userId', $user->userId)
            ->where('isCancelled', false)
            ->whereDate('startDate', '<=', Carbon::now())
            ->whereDate('endDate', '>=', Carbon::now())
            ->orderBy('created_at')
            ->first();
        $slotMap = [];

        if($order){
            $order->load(['vendor', 'orderItems', 'deliveryStatuses']);

            $itemsBySlot = $order->orderItems
                ->groupBy(function ($item) {
                    return strtolower($item->packageTimeSlot->value ?? $item->packageTimeSlot);
                })
                ->map(function ($items) {
                    return $items->groupBy(function ($item) {
                        return $item->package->name;
                    })->map(function ($groupedItems, $packageName) {
                        return [
                            'package' => $packageName,
                            'quantity' => $groupedItems->sum('quantity'),
                        ];
                    })->values();
                });

            $today = now()->toDateString();
            $deliveryStatusBySlot = $order->deliveryStatuses()
                ->whereDate('deliveryDate', $today)
                ->get()
                ->keyBy(function ($status) {
                    return strtolower($status->slot->value ?? $status->slot);
                });
    
            foreach ($itemsBySlot as $slotKey => $packages) {
                $slotMap[$slotKey] = [
                    'packages' => $packages,
                    'deliveryStatus' => $deliveryStatusBySlot[$slotKey] ?? null,
                ];
            }
            // dd($slotMap);
        }

        $wellpay = $user->wellpay ?? 0;

        logActivity('successfully', 'visited', 'Homepage');

        return view('customer.home', compact('vendors', 'favVendors', 'order', 'slotMap', 'wellpay'));
    }
}
