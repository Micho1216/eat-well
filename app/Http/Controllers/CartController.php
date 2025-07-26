<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Package;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function updateOrderSummary(Request $request)
    {
        $selectedPackages = $request->input('packages', []);
        // $userId = auth()->id();
        $userId = Auth::id();
        // $userId = 1;
        $vendorId = $request->input('vendor_id');

        Log::info('--- updateOrderSummary Dijalankan ---');
        Log::info('User ID: ' . $userId . ', Vendor ID: ' . $vendorId);
        Log::info('Selected Packages (Raw Input from Frontend):', $selectedPackages);

        if (!$userId) {
            Log::warning('User not authenticated, returning 401.');
            // return response()->json(['message' => 'User not authenticated.'], 401);
            return redirect()->route('landingPage');
        }
        if (!$vendorId) {
            Log::warning('Vendor ID missing, returning 400.');
            // return response()->json(['message' => 'Vendor ID is missing.'], 400);
            return redirect()->route('landingPage');
        }

        $cart = Cart::firstOrCreate(
            ['userId' => $userId, 'vendorId' => $vendorId],
            ['totalPrice' => 0]
        );
        Log::info('Cart found/created. Cart ID: ' . $cart->cartId);

        $totalItems = 0;
        $totalPrice = 0;

        $packageIdsInRequest = array_keys($selectedPackages);
        Log::info('Package IDs in current request:', $packageIdsInRequest);

        // --- LOGIKA PENGHAPUSAN SEMUA ITEM ---
        if (empty($selectedPackages)) {
            // Jika frontend mengirim objek 'packages' kosong, hapus semua cart items
            $deletedAllCount = $cart->cartItems()->delete();
            Log::info('Frontend sent empty packages. All CartItems deleted: ' . $deletedAllCount);
            $totalItems = 0; // Setel ulang total karena semua dihapus
            $totalPrice = 0; // Setel ulang total karena semua dihapus
        } else {
            // Logika hanya jika ada paket yang dipilih
            $actualPackageIdsWithItems = [];

            // Hapus cart items yang ada di DB tapi tidak ada di request (berarti sudah dihapus di frontend)
            $deletedCountInitial = $cart->cartItems()->whereNotIn('packageId', $packageIdsInRequest)->delete();
            Log::info('CartItems deleted (not in request, if any): ' . $deletedCountInitial);

            foreach ($selectedPackages as $packageId => $packageData) {
                Log::info('Processing Package ID: ' . $packageId . ', Data:', $packageData);

                if (is_array($packageData) && isset($packageData['items']) && is_array($packageData['items'])) {
                    $itemsData = $packageData['items'];

                    $breakfastQty = (int) ($itemsData['breakfast'] ?? 0);
                    $lunchQty = (int) ($itemsData['lunch'] ?? 0);
                    $dinnerQty = (int) ($itemsData['dinner'] ?? 0);

                    Log::info("Quantities for Package {$packageId}: B={$breakfastQty}, L={$lunchQty}, D={$dinnerQty}");

                    // Jika semua quantity 0 untuk paket ini (walaupun harusnya sudah dihapus di frontend, ini safety net)
                    if ($breakfastQty === 0 && $lunchQty === 0 && $dinnerQty === 0) {
                        $deletedCountZeroQty = CartItem::where('cartId', $cart->cartId)
                                        ->where('packageId', $packageId)
                                        ->delete();
                        Log::info("CartItem for Package {$packageId} deleted (zero qty safety): " . $deletedCountZeroQty);
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
                    Log::info("CartItem for Package {$packageId} updated/created. Current Qty: B{$cartItem->breakfastQty}, L{$cartItem->lunchQty}, D{$cartItem->dinnerQty}");

                    $package = Package::find($packageId);
                    if ($package) {
                        $totalItems += $breakfastQty + $lunchQty + $dinnerQty;
                        $totalPrice += ($breakfastQty * ($package->breakfastPrice ?? 0)) +
                                       ($lunchQty * ($package->lunchPrice ?? 0)) +
                                       ($dinnerQty * ($package->dinnerPrice ?? 0));
                        Log::info("Calculated total for Package {$packageId}: Items={$totalItems}, Price={$totalPrice}");
                    } else {
                        Log::warning("Package with ID {$packageId} not found in database.");
                    }

                } else {
                    Log::warning('Package data received with unexpected structure for packageId: ' . $packageId, ['packageData' => $packageData]);
                }
            }
        }

        // Update the cart's total price
        $cart->update(['totalPrice' => $totalPrice]);
        Log::info('Cart totalPrice updated to: ' . $totalPrice);

        // --- Cek ulang jumlah item setelah semua pemrosesan ---
        $currentCartItemCount = $cart->cartItems()->count();
        Log::info('Current Cart item count BEFORE checking for main cart delete: ' . $currentCartItemCount);

        if ($currentCartItemCount === 0) {
            $cart->delete();
            Log::info('Main Cart ' . $cart->cartId . ' DELETED SUCCESSFULLY from controller.');
        } else {
            Log::info('Main Cart ' . $cart->cartId . ' NOT deleted, still has ' . $currentCartItemCount . ' items.');
        }

        Log::info('--- updateOrderSummary Selesai ---');
        return response()->json([
            'totalItems' => $totalItems,
            'totalPrice' => $totalPrice,
        ]);
    }

    public function loadCart(Request $request)
    {
        // $userId = auth()->id();
        // $userId = 1;
        $userId = Auth::id();
        $vendorId = $request->input('vendor_id');

        if (!$userId || !$vendorId) {
            // return response()->json(['message' => 'User not authenticated or vendor ID missing.'], 401);
            return redirect()->route('landingPage');
        }

        // Eager load cartItems to avoid N+1 problem
        $cart = Cart::with('cartItems.package') // Load package data too
            ->where('userId', $userId)
            ->where('vendorId', $vendorId)
            ->first();

        $initialPackages = [];
        $initialTotalItems = 0;
        $initialTotalPrice = 0;

        if ($cart) {
            foreach ($cart->cartItems as $cartItem) {
                $package = $cartItem->package; // Access loaded package
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
