<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisteredUserStoreRequest;
use Illuminate\Support\Str;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;

class RegisteredUserController extends Controller
{
    public function create(String $role)
    {
        if($role == "customer") return view('auth.customerRegister');
        else if($role == "vendor") return view('auth.vendorRegister');
        else return redirect('/');
    }

    public function store(RegisteredUserStoreRequest $request, String $role)
    {
        $attrs = $request->validated();

        $attrs['role'] = Str::ucfirst($role);
        $user = User::create($attrs);

        $user->locale = App::currentLocale();
        $user->save();


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
