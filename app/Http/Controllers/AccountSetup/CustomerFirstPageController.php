<?php

namespace App\Http\Controllers\AccountSetup;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerCredentialStoreRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Province;
use App\Models\City;
use App\Models\District;
use App\Models\Village;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class CustomerFirstPageController extends Controller
{
    public function index() : View
    {
        return view('customer.customerFirstPage');
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


        if(isset($attrs['profile'])) {
            $image = $attrs['profile'];
            $imageName = time().'.'.$image->getClientOriginalExtension();
            Storage::putFileAs('public/profiles', $image, $imageName);
            $user->profilePath = 'storage/profiles/'.$imageName;
            
            $user->save();
        }


        logActivity('Successfully', 'Registered', 'Customer Account with id : ' . $user->userId);
        return redirect()->route('home');
    }
}
