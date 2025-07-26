<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class VerifyOtpController extends Controller
{
    public function create()
    {
        $email = session('email');
        return view('auth.verifyOtp', compact('email'));
    }

    public function check(Request $request)
    {
        $attrs = $request->validate([
            'otp' => 'required',
            'email' => 'required'
        ]);
        $otp = $attrs['otp'];
        $email = $attrs['email'];
        $remember = Session('remember');

        $user = User::where('email', $email)->first();
        if($otp !== $user->otp)
        {
            return back()->withErrors(['otp' => 'Invalid OTP, please try again']);
        } 

        if(Carbon::now()->isAfter($user->otp_expires_at))
        {
            return back()->withErrors(['otp' => 'OTP has expired']);
        }

        $user->update([
            'email_verified_at' => Carbon::now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        Auth::login($user, $remember);

        return redirect()->route('home');
    }

    public function resendOtp(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();
        $otp = rand(100000, 999999);
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
        ]);

        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email){
            $message->to($email)->subject('Your OTP');
        });

        return back();
    }
}
