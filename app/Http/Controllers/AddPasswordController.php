<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAddPasswordRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AddPasswordController extends Controller
{
    public function index(Request $request)
    {   
        if($request->user()->password)
        {
            return redirect()->route('home')->with('pressedTopup', true);
        }

        return view('addPassword');
    }

    public function store(StoreAddPasswordRequest $request) : RedirectResponse
    {
        $attrs = $request->validated();
        
        $user = User::find(Auth::user()->userId);
        $user->password = $attrs['password'];
        $user->save();

        return redirect()->route('home')->with('pressedTopup', true);
    }
}
