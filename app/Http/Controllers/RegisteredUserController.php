<?php

namespace App\Http\Controllers;

use GuzzleHttp\Promise\Create;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class RegisteredUserController extends Controller
{
    public function create(String $role)
    {
        if($role == "customer") return view('auth.customerRegister');
        else if($role == "vendor") return view('auth.vendorRegister');
        else return redirect('/');
    }

    public function store(Request $request, String $role)
    {
        $attrs = $request->validate([
            'name' => ['required'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required']
        ]);

        $attrs['role'] = Str::ucfirst($role);
        $user = User::create($attrs);

        $otp = rand(100000, 999999);
        $email = $attrs['email'];

        $user = User::where('email', $email)->first();
        $user->update([
            'otp' => $otp,
            'otp_expires_at' => Carbon::now()->addMinutes(3),
        ]);

        Mail::send('emails.otp', ['otp' => $otp], function ($message) use ($email){
            $message->to($email)->subject('Your OTP');
        });

        logActivity('Successfully', 'Registered', $role . ' Account');
        session(['email' => $user->email, 'remember' => false]);
        return redirect()->route('auth.verify');
    }
}
