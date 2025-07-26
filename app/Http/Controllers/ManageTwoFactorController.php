<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ManageTwoFactorController extends Controller
{
    public function index()
    {
        $userId = Auth::user()->userId;
        $user = User::find($userId);
        $current_2fa_status = $user->enabled_2fa;
        $user->enabled_2fa = !$current_2fa_status; 
        $user->save();

        return redirect()->back();
    }
}
