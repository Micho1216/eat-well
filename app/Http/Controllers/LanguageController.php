<?php

namespace App\Http\Controllers;

use App\Http\Requests\LanguageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LanguageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LanguageRequest $request)
    {
        $lang = $request->validated()['lang'];
        
        $user = Auth::user();
        if($user)
        {
            $userId = $user->userId;
            $user = User::find($userId);
            $user->locale = $lang;
            $user->save();
        }
        else
        {
            Session::put('lang', $lang);
        }
        
        return redirect()->back();
    }
}
