<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class CheckAuthenticatedUserPasswordResetToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(Auth::check())
        {
            $email = $request->email;
            $token = $request->token;

            $userEmail = Auth::user()->email;

            if($email !== $userEmail)
            {
                return redirect()->route('password.invalid');
            }

            $userResetToken = DB::table('password_reset_tokens')
                                ->where('email','=',$userEmail)
                                ->first();  
            
            if(!$userResetToken)
            {
                return redirect()->route('password.invalid');
            }
            
            if(!Hash::check($token, $userResetToken->token))
            {
                return redirect()->route('password.invalid');
            }

        }
        return $next($request);
    }
}
