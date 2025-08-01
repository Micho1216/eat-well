<?php

namespace App\Http\Controllers\Socialite;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Vendor;
use Exception;
use Illuminate\Support\Str;

class ProviderCallbackController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $provider)
    {
        $role = session()->get('role');
        if(!in_array($provider, ['google'])){
            return redirect(route('register'))->withErrors(['provider'=>'Invalid provider']);
        }

        try{
            $socialUser = Socialite::driver($provider)->user();
        }catch(\Exception $e){
            return redirect()->route('login');
        }

        $user = User::where('email', $socialUser->email)->first();

        if(!$user)
        {
            $user = User::create([
                'email' => $socialUser->email,
                'name' => $socialUser->name,
                'role' => ucfirst($role),
            ]);
        }
        
        if($user)
        {
            $user->provider_id = $socialUser->id;
            $user->provider_name = $provider;
            $user->provider_token = $socialUser->token;
            $user->provider_refresh_token = $socialUser->refreshToken;
            $user->save();
        }

        Auth::login($user, true);

        return redirect('/home');
    }
}
