<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $attrs = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        $remember = request()->has('remember');
        $user = User::where('email', $attrs['email'])->first();
        Session(['remember' => $remember]);
        Session(['email' => $attrs['email']]);
        if(!$user){
            loginLog($request->email, ' Login Failed : Error, credentials do not match');
            throw ValidationException::withMessages([
                'email' => 'Credentials do not match',
                'password' => 'Credentials do not match'
            ]);
        }
        
        if(!$user->email_verified_at || $user->enabled_2fa){
            $otp = rand(100000, 999999);
            $user->update([
                'otp' => $otp,
                'otp_expires_at' => Carbon::now()->addMinutes(3),
            ]);

            $email = $user->email;
            Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email){
                $message->to($email)->subject('Your OTP');
            });

            return redirect()->route('auth.verify');
        }
        
        Auth::login($user, $remember);
        loginLog($request->email, 'Successfully');
        return redirect()->route('home');
    }

    public function destroy()
    {
        logActivity('Successfully', 'Logged out', 'Eat-well');
        Auth::logout();
        return redirect('/');
    }
}
