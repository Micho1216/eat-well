<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisteredUserStoreRequest;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\OneTimePassword;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

        if(!($role === 'customer' || $role === 'vendor'))
        {
            return redirect()->back()->withErrors([
                'name' => 'Please try again',
                'email' => 'Please try again',
                'password' => 'Please try again',
                'password_confirmed' => 'Please try again'
            ]);
        }
        $attrs['role'] = Str::ucfirst($role);
        $user = User::create($attrs);

        $user->locale = App::currentLocale();
        $user->save();

        
        if(!Auth::attempt($attrs, false))
        {
            loginLog($request->email, 'User Registered, but login failed : Error, user not found');
            return redirect()->back()->withErrors([
                'email' => 'Please try login from login page',
                'password' => 'Please try login from login page'
            ]);
        }
            
        $request->session()->regenerate();
        $user->notify(new OneTimePassword($user));

        logActivity('Successfully', 'Registered', $role . ' Account');
        return redirect()->route('auth.verify');
    }
}
