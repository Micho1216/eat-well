<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\Vendor;
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

        logActivity('Successfully', 'Visited', 'Favorite Page');

        return view('favoritePage', compact('vendors'));
    }
    public function favorite(String $id)
    {
        // dd("DUAR");
        $user = Auth::check() ? Auth::user() : User::where('userId', '=', '5')->inRandomOrder()->first();
        // Ensure relation exists and not duplicated
        if (!$user->favoriteVendors()->where('vendors.vendorId', '=', $id)->exists()) {
            $user->favoriteVendors()->attach($id);
        }

        logActivity('Successfully', 'Favorited', 'Catering');
        return response()->json(['favorited' => true]);
    }

    public function unfavorite(String $id)
    {
        $user = Auth::check() ? Auth::user() : User::where('userId', '=', '5')->inRandomOrder()->first();
        $user->favoriteVendors()->detach($id);
        logActivity('Successfully', 'Unfavorited', 'Catering');
        return response()->json(['favorited' => false]);
    }
}
