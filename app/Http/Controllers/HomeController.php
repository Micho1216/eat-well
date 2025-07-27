<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\User;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPUnit\Framework\isEmpty;

class HomeController extends Controller
{
    public function index()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $address = $this->getDefaultAddress($user);
        $vendors = $this->getRecommendedVendors($address);
        $favVendors = $user->favoriteVendors()->limit(8)->get();
        $order = $this->getOngoingOrder($user);
        $slotMap = $this->getOrderSlotMap($order);
        $wellpay = $user->wellpay ?? 0;

        logActivity('successfully', 'visited', 'Homepage');

        return view('customer.home', compact('vendors', 'favVendors', 'order', 'slotMap', 'wellpay'));
    }

    private function getDefaultAddress(User $user): ?Address
    {
        return Address::where('userId', $user->userId)
            ->where('is_default', true)
            ->first();
    }

    private function getRecommendedVendors(?Address $address)
    {
        if ($address) {
            $nearVendors = Vendor::where('provinsi', $address->provinsi)
                ->inRandomOrder()
                ->take(6)
                ->get();

            $countNear = $nearVendors->count();

            if ($countNear < 12) {
                $extraVendors = Vendor::where('provinsi', '!=', $address->provinsi)
                    ->orderBy('rating')
                    ->take(12 - $countNear)
                    ->get();

                return $nearVendors->concat($extraVendors);
            }

            return $nearVendors;
        }

        return Vendor::orderByDesc('rating')->limit(12)->get();
    }

    private function getOngoingOrder(User $user): ?Order
    {
        $order = Order::where('userId', $user->userId)
            ->where('isCancelled', false)
            ->whereDate('startDate', '<=', now())
            ->whereDate('endDate', '>=', now())
            ->orderBy('created_at')
            ->first();

        if ($order) {
            $order->load(['vendor', 'orderItems', 'deliveryStatuses']);
        }

        return $order;
    }

    private function getOrderSlotMap(?Order $order): array
    {
        if (!$order)
            return [];

        $itemsBySlot = $order->orderItems
            ->groupBy(fn($item) => strtolower($item->packageTimeSlot->value ?? $item->packageTimeSlot))
            ->map(function ($items) {
                return $items->groupBy(fn($item) => $item->package->name)
                    ->map(fn($grouped, $packageName) => [
                        'package' => $packageName,
                        'quantity' => $grouped->sum('quantity'),
                    ])->values();
            });

        $today = now()->toDateString();
        $deliveryStatusBySlot = $order->deliveryStatuses()
            ->whereDate('deliveryDate', $today)
            ->get()
            ->keyBy(fn($status) => strtolower($status->slot->value ?? $status->slot));

        $slotMap = [];

        foreach ($itemsBySlot as $slotKey => $packages) {
            $slotMap[$slotKey] = [
                'packages' => $packages,
                'deliveryStatus' => $deliveryStatusBySlot[$slotKey] ?? null,
            ];
        }

        return $slotMap;
    }
}
