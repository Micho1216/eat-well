<?php

namespace App\Http\Controllers\Socialite;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Facades\Socialite;

class ProviderRedirectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $provider, ?string $role = 'customer')
    {
        session()->put('role', $role);
        if(!in_array($provider, ['google'])){
            return redirect(route('register', ['role' => $role ]))->withErrors(['provider'=>'Invalid provider']);
        }

        try{
            return Socialite::driver($provider)->redirect();
        } catch(\Exception $e){
            return redirect(route('register', ['role' => $role ]))->withErrors(['provider'=>'Something went wrong']);
        }
    }
}
