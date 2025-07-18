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

        if(!$user) {
            return redirect('login')->with("error");
        }

        if($user->role === UserRole::Vendor) {
            return redirect("/cateringHomePage")->with("error");
        }

        if($user->role === UserRole::Admin) {
            return redirect("/admin-dashboard")->with("error");
        }

        $vendors = $user->favoriteVendors()->paginate(21);

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

        return response()->json(['favorited' => true]);
    }

    public function unfavorite(String $id)
    {
        $user = Auth::check() ? Auth::user() : User::where('userId', '=', '5')->inRandomOrder()->first();
        $user->favoriteVendors()->detach($id);
        return response()->json(['favorited' => false]);
    }
}
