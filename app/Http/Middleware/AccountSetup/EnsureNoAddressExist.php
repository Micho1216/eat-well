<?php

namespace App\Http\Middleware\AccountSetup;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EnsureNoAddressExist
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::user()->userId;
        $user = User::find($userId);
        $addresses = $user->addresses;

        if($addresses->first())
        {
            return redirect()->route('home');
        }
        return $next($request);
    }
}
