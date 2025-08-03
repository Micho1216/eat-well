<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResendOTPRequest;
use App\Http\Requests\VerifyOTPRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Notifications\OneTimePassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Event\TestData\MoreThanOneDataSetFromDataProviderException;

class VerifyOtpController extends Controller
{
    public function create()
    {
        $email = session('email');
        return view('auth.verifyOtp', compact('email'));
    }

    public function check(VerifyOTPRequest $request)
    {
        $attrs = $request->validated();
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

    public function resendOtp(ResendOTPRequest $request)
    {
        $attrs = $request->validated();
        $email = $attrs['email'];
        $user = User::where('email', $email)->first();

        $user->notify(new OneTimePassword($user));

        return back();
    }
}
