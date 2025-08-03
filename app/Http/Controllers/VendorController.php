<?php

namespace App\Http\Controllers;

use App\Http\Requests\VendorSearchRequest;
use App\Http\Requests\ProfileRequest;
use App\Http\Requests\UpdateProfileVendorRequest;
use App\Models\Address;
use App\Http\Requests\VendorStoreRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Vendor;
use App\Models\User;
use App\Models\VendorReview;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class VendorController extends Controller
{

    public function display()
    {
        return view('cateringHomePage');
    }


    public function index()
    {
        // dd(Auth()->user);
        // $vendors = Vendor::all();
        // $categories = PackageCategory::all();

        // if(!$vendors->name){
        //     return redirect('cateringHomePage');
        // }
        return view('vendors.index', compact('vendors', 'categories'));
    }

    /**
     * Menampilkan detail dari satu vendor/catering spesifik.
     * Menggunakan Route Model Binding.
     *
     * @param  \App\Models\Vendor  $vendor
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(Vendor $vendor)
    {
        $vendor->load(['user', 'previews']);
        session()->put(['selected_vendor_id' => $vendor->vendorId]);
        $packages = $vendor->packages()->with(['category'])->get();
        $numSold = Order::where('vendorId', $vendor->vendorId)->count();

        $user = Auth::user();
        $selectedAddressId = session('address_id');
        $selectedAddress = null;
        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
            if (!$selectedAddress || $selectedAddress->userId !== $user->userId) {
                $selectedAddress = null;
            }
        }

        if (!$selectedAddress && $user) {
            if (method_exists($user, 'defaultAddress')) {
                $selectedAddress = $user->defaultAddress;
            } else {
                $selectedAddress = Address::where('userId', $user->userId)
                    ->where('is_default', 1)
                    ->first();
            }
        }

        $tooFar = false;
        if($vendor->provinsi !== $selectedAddress->provinsi) $tooFar = true;

        // logActivity('Successfully', 'Visited', 'Catering Detail Page');
        return view('cateringDetail', compact('vendor', 'packages', 'numSold', 'selectedAddress', 'tooFar'));
    }

    public function review(Vendor $vendor)
    {

        $vendorReviews = VendorReview::where('vendorId', $vendor->vendorId)
            ->with(['user', 'order']) // Load user dan order
            ->orderBy('created_at', 'desc')
            ->get();

        $numSold = Order::where('vendorId', $vendor->vendorId)->count();

        return view('ratingAndReview', compact('vendor', 'vendorReviews', 'numSold'));
    }

    public function reviewVendor()
    {
        // Ambil vendor yang sedang login
        $vendor = Auth::user()->vendor; // Pastikan user login adalah vendor

        // Ambil review dari vendor yang sedang login
        $vendorReviews = VendorReview::where('vendorId', $vendor->vendorId)
            ->with(['user', 'order']) // Load relasi user dan order
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung jumlah order yang dijual oleh vendor
        $numSold = Order::where('vendorId', $vendor->vendorId)->count();

        return view('ratingAndReviewVendor', compact('vendor', 'vendorReviews', 'numSold'));
    }

    public function manageProfile()
    {
        $user = Auth::user();
        $vendor = Vendor::where('userId', $user->userId)->first();

        $breakfast_start = $breakfast_end = null;
        $lunch_start = $lunch_end = null;
        $dinner_start = $dinner_end = null;

        if ($vendor->breakfast_delivery) {
            [$breakfast_start, $breakfast_end] = explode('-', $vendor->breakfast_delivery);
        }

        if ($vendor->lunch_delivery) {
            [$lunch_start, $lunch_end] = explode('-', $vendor->lunch_delivery);
        }

        if ($vendor->dinner_delivery) {
            [$dinner_start, $dinner_end] = explode('-', $vendor->dinner_delivery);
        }

        $address = $vendor->provinsi . ' ' . $vendor->kota . ' ' . $vendor->kecamatan . ' ' . $vendor->kelurahan . ' ' . $vendor->jalan . ' ' . $vendor->kode_pos;
        // logActivity('Successfully', 'Visited', 'Manage Profile Vendor Page');

        return view('manage-profile-vendors', compact(
            'user',
            'vendor',
            'breakfast_start',
            'breakfast_end',
            'lunch_start',
            'lunch_end',
            'dinner_start',
            'dinner_end',
            'address',
        ));
    }


    public function updateProfile(UpdateProfileVendorRequest $request)
    {

        // dd($request);
        $user = Auth::user();
        $userId = $user->userId;
        $vendor = Vendor::where('userId', $userId)->first();

        if ($vendor->name != $request->nameInput)
        {
            logActivity('Successfully', 'Updated', 'Catering Name to ' . $request->nameInput);
        }

        $vendor->name = $request->nameInput;
        $vendor->phone_number = $request->telpInput;

        $vendor->breakfast_delivery = $request->breakfast_time_start . '-' . $request->breakfast_time_end;
        $vendor->lunch_delivery = $request->lunch_time_start . '-' . $request->lunch_time_end;
        $vendor->dinner_delivery = $request->dinner_time_start . '-' . $request->dinner_time_end;


        if ($request->hasFile('profilePicInput')) {
            $file = $request->file('profilePicInput');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('asset/vendorLogo'), $filename);
            $vendor->logo = $filename;
            logActivity('Successfully', 'Added', 'Profile pict inManage Profile Vendor Page');
        }

        $vendor->save();

        logActivity('Successfully', 'Updated', 'Manage Profile Vendor Page');
        return redirect()->route('manage-profile-vendor')->with('success', 'Profile updated successfully!');
    }

    public function store(VendorStoreRequest $request)
    {
        // validating
        $userId = Auth::id();

        $vendor = Vendor::create([
            'userId' => $userId
        ]);

        // upload logo
        // $file = $request->file('logo');

        // $filename = time() . '_' . $file->getClientOriginalName();

        // $file->move(public_path('asset/vendorLogo/', $filename));

        $file = $request->file('logo');
        $filename = time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('asset/vendorLogo'), $filename);
        $vendor->logo = $filename;
        $vendor->save(); 
        logActivity('Successfully', 'Added', 'Profile pict ');


        // $vendor->update([
        //     'logo' => $filename,
        // ]);

        // $vendor->save();

        // Convert and combine delivery times from 12-hour format (like "05:30 PM") to "HH:MM-HH:MM"
        $breakfast = $request->startBreakfast && $request->closeBreakfast
            ? $request->startBreakfast . '-' . $request->closeBreakfast
            : null;

        $lunch = $request->startLunch && $request->closeLunch
            ? $request->startLunch . '-' . $request->closeLunch
            : null;

        $dinner = $request->startDinner && $request->closeDinner
            ? $request->startDinner . '-' . $request->closeDinner
            : null;

        // Store the vendor
        $vendor->update([
            'name' => $request['name'],
            'phone_number' => $request['phone_number'],
            'breakfast_delivery' => $breakfast,
            'lunch_delivery' => $lunch,
            'dinner_delivery' => $dinner,
            'provinsi' => $request['provinsi_name'],
            'kota' => $request['kota_name'],
            'kecamatan' => $request['kecamatan_name'],
            'kelurahan' => $request['kelurahan_name'],
            'kode_pos' => $request['kode_pos'],
            'jalan' => $request['jalan'],
            'rating' => 0.0,
        ]);
        // ]);

        return redirect('cateringHomePage');
    }

    // utk akun user vendor
    public function manage_profile()
    {
        // Mengambil user yang sedang login
        $user = Auth::user();


        // logActivity('Successfully', 'Visited', 'Manage Profile Page');
        return view('manageProfileVendorUser', compact('user'));
    }


    public function updateProfileUser(ProfileRequest $request)
    {
        $user = Auth::user();
        $userId = $user->userId;

        $oldName = $user->name;

        $updated_user = User::find($userId);

        $updated_user->name = $request->nameInput;

        if ($request->filled('dateOfBirth')) {
            $updated_user->dateOfBirth = $request->input('dateOfBirth');
        }

        if ($request->hasFile('profilePicInput')) {
            $file = $request->file('profilePicInput');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            Storage::putFileAs('public/profiles', $file, $filename);
            $updated_user->profilePath = 'storage/profiles/'.$filename;
        }

        $updated_user->genderMale = ($request->gender === 'male') ? 1 : 0;

        $updated_user->save();

        // dd($updated_user->name);

        // dd($oldName);

        logActivity('Successfully', 'Updated', "Profile to :  {$updated_user->name}");

        return redirect()->route('manage-profile-vendor-account')->with('success', 'Profile updated successfully!');
    }
}
