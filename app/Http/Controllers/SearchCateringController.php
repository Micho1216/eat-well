<?php

namespace App\Http\Controllers;

use App\Http\Requests\SetAddressRequest;
use App\Http\Requests\VendorSearchRequest;
use App\Models\Address;
use App\Models\PackageCategory;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SearchCateringController extends Controller
{
    public function search(VendorSearchRequest $request)
    {
        $user = Auth::user();

        // Get address from session if available
        $mainAddress = null;
        $addressId = session('address_id');

        if ($addressId && $user) {
            $mainAddress = Address::find($addressId);

            // Validate that the address belongs to the user
            if (!$mainAddress || $mainAddress->userId !== $user->userId) {
                $mainAddress = null;
            }
        }

        // Fallback: use user's default address if session address is missing or invalid
        if (!$mainAddress && $user) {
            if (method_exists($user, 'defaultAddress')) {
                $mainAddress = $user->defaultAddress;
            } else {
                $mainAddress = Address::where('userId', $user->userId)
                    ->where('is_default', 1)
                    ->first();
            }

            // Store it in session if found
            if ($mainAddress) {
                session(['address_id' => $mainAddress->id]);
            }
        }

        $validated = $request->validated();

        // Use validated input data
        $query = $validated['query'] ?? null;
        $minPrice = $validated['min_price'] ?? 0;
        $maxPrice = $validated['max_price'] ?? 999999999;
        $rating = $validated['rating'] ?? null;
        $categories = $validated['category'] ?? [];

        $all_categories = PackageCategory::all();

        $province = $mainAddress?->provinsi;

        $vendors = Vendor::query()
            // Add a province match priority column
            ->selectRaw("vendors.*, 
            CASE WHEN vendors.provinsi = ? THEN 1 ELSE 0 END AS province_priority", [$province])

            ->when($query, function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhereHas('packages', function ($q2) use ($query) {
                            $q2->where('name', 'like', "%{$query}%")
                                ->orWhereHas('category', function ($q3) use ($query) {
                                    $q3->where('categoryName', 'like', "%{$query}%");
                                });
                        });
                });
            })

            ->when($rating, function ($q) use ($rating) {
                $q->where('rating', '>=', $rating);
            })

            ->when($categories, function ($q) use ($categories) {
                $q->whereHas('packages.category', function ($q2) use ($categories) {
                    $q2->whereIn('categoryName', (array) $categories);
                });
            })

            ->when($minPrice || $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereHas('packages', function ($q2) use ($minPrice, $maxPrice) {
                    $q2->where(function ($q3) use ($minPrice, $maxPrice) {
                        if ($minPrice) {
                            $q3->where(function ($q4) use ($minPrice) {
                                $q4->where('breakfastPrice', '>=', $minPrice)
                                    ->orWhere('lunchPrice', '>=', $minPrice)
                                    ->orWhere('dinnerPrice', '>=', $minPrice);
                            });
                        }
                        if ($maxPrice) {
                            $q3->where(function ($q4) use ($maxPrice) {
                                $q4->where('breakfastPrice', '<=', $maxPrice)
                                    ->orWhere('lunchPrice', '<=', $maxPrice)
                                    ->orWhere('dinnerPrice', '<=', $maxPrice);
                            });
                        }
                    });
                });
            })

            // Sort by province priority first, then by name or any other logic
            ->orderByDesc('province_priority')
            ->orderBy('name')

            ->with(['packages.category', 'packages.cuisineTypes'])

            ->distinct()
            ->paginate(9)
            ->appends($request->query());

        // Pass paginated vendors to the view
        logActivity('Successfully', 'Visited', "Vendor Search Page and Searched for: {$query}");
        return view('customer.search', compact('vendors', 'all_categories', 'user', 'mainAddress'));
    }

    public function setAddress(SetAddressRequest $request)
    {
        $user = Auth::user();
        $addressId = $request->input('address_id');

        $address = Address::find($addressId);

        // Validate the address belongs to the logged-in user
        if ($address && $user && $address->userId === $user->userId) {
            session(['address_id' => $address->addressId]);
        }

        return redirect()->route('search');
    }
}
