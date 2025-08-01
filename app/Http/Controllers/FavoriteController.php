<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index()
    {
        /**
         * @var User|null $user
         */
        $user = Auth::user();
        $vendors = $user->favoriteVendors()->paginate(21);

        // logActivity('Successfully', 'Visited', 'Favorite Page');

        return view('favoritePage', compact('vendors'));
    }
    public function favorite(String $id)
    {
        /**
         * @var User | Authenticatable $user
         */
        $user = Auth::user();
        // Ensure relation exists and not duplicated
        if (!$user->favoriteVendors()->where('vendors.vendorId', '=', $id)->exists()) {
            $user->favoriteVendors()->attach($id);
        }

        $vendor = Vendor::findOrFail($id);

        logActivity('Successfully', 'Favorited', 'Catering : ' . $vendor->name);
        return response()->json(['favorited' => true]);
    }

    public function unfavorite(String $id)
    {
        /**
         * @var User | Authenticatable $user
         */
        $user = Auth::user();
        $user->favoriteVendors()->detach($id);

        $vendor = Vendor::findOrFail($id);

        logActivity('Successfully', 'Unfavorited', 'Catering : ' . $vendor->name);
        return response()->json(['favorited' => false]);
    }
}
