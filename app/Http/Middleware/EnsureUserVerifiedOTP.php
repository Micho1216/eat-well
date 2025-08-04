<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureUserVerifiedOTP
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!Auth::check())
        {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if($user->otp)
        {
            return redirect()->route('auth.verify');
        }

        if($user->otp_expires_at)
        {
            return redirect()->route('auth.verify');
        }

        if(!$user->email_verified_at && !$user->provider_id)
        {
            return redirect()->route('auth.verify');
        }
        
        return $next($request);
    }
}
