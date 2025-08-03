<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisteredUserStoreRequest;
use Illuminate\Support\Str;
use App\Models\User;
use App\Notifications\OneTimePassword;
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

        $user->notify(new OneTimePassword($user));

        logActivity('Successfully', 'Registered', $role . ' Account');
        session(['email' => $user->email, 'remember' => false]);
        return redirect()->route('auth.verify');
    }
}
