<?php

namespace App\Http\Controllers;

use App\Http\Requests\SessionRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Notifications\OneTimePassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(SessionRequest $request)
    {
        $attrs = $request->validated();
        $remember = request()->has('remember');

        $email = $attrs['email'];
        $user = User::where('email', $email)->first();
        
        if(!$user->password && $user->provider_id)
        {
            return redirect()->back()->with('use_provider', true);
        }

        if(!(Auth::attempt($attrs, $remember))){
            loginLog($request->email, ' Login Failed : Error, credentials do not match');
            return redirect()->back()->withErrors([
                'email' => 'Credentials do not match',
                'password' => 'Credentials do not match'
            ]);
        }
        
        $request->session()->regenerate();
        if(!$user->email_verified_at)
        {
            $user->notify(new OneTimePassword($user));
            return redirect()->route('auth.verify');
        }

        if($user->enabled_2fa){
            $user->notify(new OneTimePassword($user));
            return redirect()->route('auth.verify');
        }

        loginLog($request->email, 'Successfully');
        return redirect()->route('home');
    }

    public function destroy(Request $request) : RedirectResponse
    {
        logActivity('Successfully', 'Logged out', 'Eat-well');
        Auth::logout(); 
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
