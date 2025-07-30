<?php

namespace App\Http\Controllers\AccountSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCredentialStoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Address;
use App\Models\District;
use App\Models\City;
use App\Models\Village;
use App\Models\Province;

class CustomerFirstPageController extends Controller
{
    public function index() : View
    {
        $user = Auth::user();
        $profilePicture = $user->profile_picture;

        return view('customer.customerFirstPage',
            ['profilePicture' => $profilePicture]);
    }

    public function store(CustomerCredentialStoreRequest $request) : RedirectResponse
    {
        $attrs = $request->validated();
        $userId = Auth::user()->userId;
        
        $user = User::find($userId);

        $user->addresses()->create([
            'provinsi' => Province::find($attrs['province'])->name,
            'kota' => City::find($attrs['city'])->name,
            'kecamatan' => District::find($attrs['district'])->name,
            'kelurahan' => Village::find($attrs['village'])->name,
            'kode_pos' => $attrs['zipCode'],
            'jalan' => $attrs['address'],
            'recipient_name' => $attrs['name'],
            'recipient_phone' => $attrs['phoneNumber'],
            'is_default' => true,
            'userId' => $user->userId,
        ]);
        $user->save();
        return redirect()->route('home');

    }
}
