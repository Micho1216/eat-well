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
        $user = Auth::user();
        $email = $user->email;
        $diff = Carbon::now()->diffInSeconds($user->otp_expires_at);

        if ($diff <= 0 || $diff > 180)
        {
            $minutes = 0;
            $seconds = 0;
        }
        else{
            $minutes = floor($diff/60);
            $seconds = floor($diff - $minutes*60);
        }
        
        return view('auth.verifyOtp', compact('email', 'minutes', 'seconds'));
    }

    public function check(VerifyOTPRequest $request)
    {
        $attrs = $request->validated();
        $otp = $attrs['otp'];

        $user = Auth::user();
        
        if($otp !== $user->otp)
        {
            return back()->withErrors(['otp' => 'Invalid OTP, please try again']);
        } 

        if(Carbon::now()->isAfter($user->otp_expires_at))
        {
            return back()->withErrors(['otp' => 'OTP has expired']);
        }

        $user = User::find($user->userId);
        $user->update([
            'email_verified_at' => Carbon::now(),
            'otp' => null,
            'otp_expires_at' => null,
        ]);

        return redirect()->route('home');
    }

    public function resendOtp()
    {
        $email = Auth::user()->email;
        $user = User::where('email', $email)->first();
        $user->notify(new OneTimePassword($user));

        return redirect()->back();
    }

}
