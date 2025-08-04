<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoadCartRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function updateOrderSummary(UpdateCartRequest $request)
    {
        $selectedPackages = $request->input('packages', []);
        $userId = $request->input('user_id');
        $vendorId = $request->input('vendor_id');

        $cart = Cart::firstOrCreate(
            ['userId' => $userId, 'vendorId' => $vendorId],
            ['totalPrice' => 0]
        );

        $totalItems = 0;
        $totalPrice = 0;

        $packageIdsInRequest = array_keys($selectedPackages);

        if (empty($selectedPackages)) {
            $deletedAllCount = $cart->cartItems()->delete();

            $totalItems = 0;
            $totalPrice = 0;
        } else {
            $actualPackageIdsWithItems = [];

            $deletedCountInitial = $cart->cartItems()->whereNotIn('packageId', $packageIdsInRequest)->delete();

            foreach ($selectedPackages as $packageId => $packageData) {
                if (is_array($packageData) && isset($packageData['items']) && is_array($packageData['items'])) {
                    $itemsData = $packageData['items'];

                    $breakfastQty = (int) ($itemsData['breakfast'] ?? 0);
                    $lunchQty = (int) ($itemsData['lunch'] ?? 0);
                    $dinnerQty = (int) ($itemsData['dinner'] ?? 0);

                    if ($breakfastQty === 0 && $lunchQty === 0 && $dinnerQty === 0) {
                        $deletedCountZeroQty = CartItem::where('cartId', $cart->cartId)
                                        ->where('packageId', $packageId)
                                        ->delete();
                        continue;
                    }

                    $actualPackageIdsWithItems[] = $packageId;

                    $cartItem = CartItem::updateOrCreate(
                        ['cartId' => $cart->cartId, 'packageId' => $packageId],
                        [
                            'breakfastQty' => $breakfastQty,
                            'lunchQty' => $lunchQty,
                            'dinnerQty' => $dinnerQty,
                        ]
                    );

                    $package = Package::find($packageId);
                    if ($package) {
                        $totalItems += $breakfastQty + $lunchQty + $dinnerQty;
                        $totalPrice += ($breakfastQty * ($package->breakfastPrice ?? 0)) +
                                       ($lunchQty * ($package->lunchPrice ?? 0)) +
                                       ($dinnerQty * ($package->dinnerPrice ?? 0));
                    } else {
                        Log::warning("Package with ID {$packageId} not found in database.");
                    }

                } else {
                    Log::warning('Package data received with unexpected structure for packageId: ' . $packageId, ['packageData' => $packageData]);
                }
            }
        }

        $cart->update(['totalPrice' => $totalPrice]);

        $currentCartItemCount = $cart->cartItems()->count();

        if ($currentCartItemCount === 0) {
            $cart->delete();
        } else {
            Log::info('Main Cart ' . $cart->cartId . ' NOT deleted, still has ' . $currentCartItemCount . ' items.');
        }

        return response()->json([
            'totalItems' => $totalItems,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function loadCart(LoadCartRequest $request)
    {
        $userId = $request->input('user_id');
        $vendorId = $request->input('vendor_id');

        if (!$userId || !$vendorId) {
            return redirect()->route('landingPage');
        }

        $cart = Cart::with('cartItems.package')
            ->where('userId', $userId)
            ->where('vendorId', $vendorId)
            ->first();

        $initialPackages = [];
        $initialTotalItems = 0;
        $initialTotalPrice = 0;

        if ($cart) {
            foreach ($cart->cartItems as $cartItem) {
                $package = $cartItem->package;
                if ($package) {
                    $initialPackages[$package->packageId] = [
                        'id' => $package->packageId,
                        'items' => [
                            'breakfast' => $cartItem->breakfastQty,
                            'lunch' => $cartItem->lunchQty,
                            'dinner' => $cartItem->dinnerQty,
                        ],
                    ];
                    $initialTotalItems += $cartItem->breakfastQty + $cartItem->lunchQty + $cartItem->dinnerQty;
                    $initialTotalPrice += ($cartItem->breakfastQty * ($package->breakfastPrice ?? 0)) +
                        ($cartItem->lunchQty * ($package->lunchPrice ?? 0)) +
                        ($cartItem->dinnerQty * ($package->dinnerPrice ?? 0));
                }
            }
        }

        return response()->json([
            'packages' => $initialPackages,
            'totalItems' => $initialTotalItems,
            'totalPrice' => $initialTotalPrice,
        ]);
    }
}
