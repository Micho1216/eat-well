<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;

class CheckPasswordResetToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $email = $request->email;
        $token = $request->token;

        $userToken = DB::table('password_reset_tokens')
                        ->where('email', '=', $email)
                        ->get()->first();

        if(!$userToken)
        {
            return redirect()->route('password.invalid');
        }

        if(!Hash::check($token, $userToken->token))
        {
            return redirect()->route('password.invalid');
        }

        $diffInHours = abs(Carbon::now()->diffInHours($userToken->created_at, false));
        if($diffInHours > 1)
        {
            return redirect()->route('password.invalid');
        }
        return $next($request);
    }
}
