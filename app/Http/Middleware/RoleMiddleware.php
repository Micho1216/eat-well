<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Enums\UserRole; // Pastikan enum ini sesuai lokasi & nama file
use Illuminate\Support\Str;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect('/login');
        }

        $role = Str::ucfirst($role);

        if($user->role->value != $role)
        {
            return match ($user->role) {
                UserRole::Admin => redirect('admin-dashboard'),
                UserRole::Vendor => redirect('/cateringHomePage'),
                UserRole::Customer => redirect('/home'),
                default => abort(403, 'Unauthorized role'),
            };
        }

        return $next($request);
    }
}
