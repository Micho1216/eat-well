<?php

namespace App\Http\Controllers;

use App\Http\Requests\ForgotPasswordEmailRequest;
use App\Http\Requests\ForgotPasswordStoreRequest;
use Illuminate\Contracts\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function request() : View
    {
        return view('auth.forgotPassword');
    }

    
    public function email(ForgotPasswordEmailRequest $request)
    {
        $attrs = $request->validated();
        
        $status = Password::sendResetLink(
            $attrs
        );
        
        return $status === Password::ResetLinkSent
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
    }

    public function reset(ForgotPasswordEmailRequest $request, String $token) : View
    {
        $attrs = $request->validated();
        $email = $attrs['email'];
        return view('auth.resetPassword', ['email' => $email, 'token' => $token]);
    }

    public function update(ForgotPasswordStoreRequest $request)
    {
        $attrs = $request->validated();

        $status = Password::reset(
            $attrs,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'email_verified_at' => Carbon::now()
                ])->setRememberToken(Str::random(60));
    
                $user->save();
                
                event(new PasswordReset($user));
            }
        );
        
        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
