<?php

namespace App\Http\Controllers;

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

class 

VendorController extends Controller
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
    public function show(Vendor $vendor, Request $request)
    {
        // Memuat relasi User dan Address secara efisien jika Anda ingin menampilkannya
        $vendor->load(['user', 'previews']);
        // MEMUAT PAKET DENGAN RELASI CATEGORY DAN CUISINE_TYPES SECARA EFFICIENT
        // Pastikan Anda telah mendefinisikan relasi 'packages' di model Vendor dan relasi 'category' serta 'cuisineTypes' di model Package.
        $packages = $vendor->packages()->with(['category', 'cuisineTypes'])->get();
        $numSold = Order::where('vendorId', $vendor->vendorId)->count();

        $selectedAddressId = $request->query('address_id');

        Log::info('VendorController@show: selectedAddressId received = ' . $selectedAddressId);

        $selectedAddress = null;
        if ($selectedAddressId) {
            $selectedAddress = Address::find($selectedAddressId);
            // Opsional: Pastikan alamat ini milik user yang sedang login
            if ($selectedAddress && Auth::check() && $selectedAddress->userId !== Auth::id()) {
                $selectedAddress = null; // Abaikan jika bukan milik user
            }
        }

        // Fallback jika tidak ada address_id di query string atau tidak valid
        if (!$selectedAddress && Auth::check()) {
            $user = Auth::user();
            // Jika Anda punya relasi defaultAddress di model User:
            if (method_exists($user, 'defaultAddress')) {
                $selectedAddress = $user->defaultAddress;
            } else {
                // Atau cari manual jika tidak ada relasi defaultAddress (pastikan user->userId benar)
                $selectedAddress = Address::where('userId', $user->userId)
                    ->where('is_default', 1)
                    ->first();
            }
        }

        // Pastikan $selectedAddress tidak null sebelum diakses di view
        // Jika masih null, mungkin set default kosong atau tangani error
        if (!$selectedAddress) {
            // Anda bisa set default ke null atau membuat objek Address kosong
            $selectedAddress = (object)['addressId' => null, 'jalan' => 'No Address Selected'];
            // Atau redirect user untuk memilih alamat
            // return redirect()->route('search')->with('error', 'Please select a delivery address.');
        }

        logActivity('Successfully', 'Visited', 'Catering Detail Page');
        return view('cateringDetail', compact('vendor', 'packages', 'numSold', 'selectedAddress'));
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
        $vendor = Auth::user(); // Pastikan user login adalah vendor

        // Ambil review dari vendor yang sedang login
        $vendorReviews = VendorReview::where('vendorId', $vendor->vendorId)
            ->with(['user', 'order']) // Load relasi user dan order
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung jumlah order yang dijual oleh vendor
        $numSold = Order::where('vendorId', $vendor->vendorId)->count();

        return view('ratingAndReviewVendor', compact('vendor', 'vendorReviews', 'numSold'));
    }

    public function search(Request $request)
    {
        // validate request
        $validated = $request->validate([
            'query' => 'nullable|string|max:255',
            'min_price' => 'nullable|integer',
            'max_price' => 'nullable|integer',
            'rating' => 'nullable|numeric',
            'category' => 'nullable|array',
            'category.*' => 'string',
        ]);
        
        // Use validated input data
        $query = $validated['query'] ?? null;
        $minPrice = $validated['min_price'] ?? 0;
        $maxPrice = $validated['max_price'] ?? 999999999;
        $rating = $validated['rating'] ?? null;
        $categories = $validated['category'] ?? [];

        $all_categories = PackageCategory::all();

        $vendors = \App\Models\Vendor::query()
            // Search by vendor name or related models — grouped properly
            ->when($query, function ($q) use ($query) {
                $q->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhereHas('packages', function ($q2) use ($query) {
                            $q2->where('name', 'like', "%{$query}%")
                                ->orWhereHas('category', function ($q3) use ($query) {
                                    $q3->where('categoryName', 'like', "%{$query}%");
                                })
                                ->orWhereHas('cuisineTypes', function ($q4) use ($query) {
                                    $q4->where('cuisineName', 'like', "%{$query}%");
                                });
                        });
                });
            })

            // Filter by rating
            ->when($rating, function ($q) use ($rating) {
                $q->where('rating', '>=', $rating);
            })

            // Filter by category (package's category)
            ->when($categories, function ($q) use ($categories) {
                $q->whereHas('packages.category', function ($q2) use ($categories) {
                    $q2->whereIn('categoryName', (array) $categories);
                });
            })

            // Filter by price range (any package price in range)
            ->when($minPrice || $maxPrice, function ($q) use ($minPrice, $maxPrice) {
                $q->whereHas('packages', function ($q2) use ($minPrice, $maxPrice) {
                    $q2->where(function ($q3) use ($minPrice, $maxPrice) {
                        if ($minPrice) {
                            $q3->where(function ($q4) use ($minPrice) {
                                $q4->where('breakfastPrice', '>=', $minPrice)
                                    ->orWhere('lunchPrice', '>=', $minPrice)
                                    ->orWhere('dinnerPrice', '>=', $minPrice);
                            });
                        }
                        if ($maxPrice) {
                            $q3->where(function ($q4) use ($maxPrice) {
                                $q4->where('breakfastPrice', '<=', $maxPrice)
                                    ->orWhere('lunchPrice', '<=', $maxPrice)
                                    ->orWhere('dinnerPrice', '<=', $maxPrice);
                            });
                        }
                    });
                });
            })

            // Include relations
            ->with(['packages.category', 'packages.cuisineTypes'])

            // Avoid duplicate vendors due to joins
            ->distinct()

            // Paginate results and keep filters in URL
            ->paginate(9)
            ->appends($request->query());

        Auth::check();
        $user =  Auth::user();

        $mainAddress = null;
        $addressIdFromUrl = $request->query('address_id');

        if ($addressIdFromUrl) {
            // Coba temukan alamat berdasarkan ID dari URL
            $mainAddress = Address::find($addressIdFromUrl);
            // Validasi: pastikan alamat ini milik user yang sedang login
            if ($mainAddress && $user && $mainAddress->userId !== $user->userId) {
                $mainAddress = null; // Abaikan jika bukan milik user
            }
        }

        // Fallback: Jika tidak ada address_id di URL atau tidak valid, gunakan alamat default user
        if (!$mainAddress && $user) {
            if (method_exists($user, 'defaultAddress')) { // Jika ada relasi defaultAddress di model User
                $mainAddress = $user->defaultAddress;
            } else {
                // Alternatif jika tidak ada relasi defaultAddress (cari manual is_default = 1)
                $mainAddress = Address::where('userId', $user->userId)
                    ->where('is_default', 1)
                    ->first();
            }
        }

        // Pass paginated vendors to the view
        logActivity('Successfully', 'Visited', "Vendor Search Page and Searched for: {$query}");
        return view('customer.search', compact('vendors', 'all_categories', 'user', 'mainAddress'));
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

        logActivity('Successfully', 'Visited', 'Manage Profile Vendor Page');

        return view('manage-profile-vendor', compact(
            'user',
            'vendor',
            'breakfast_start',
            'breakfast_end',
            'lunch_start',
            'lunch_end',
            'dinner_start',
            'dinner_end'
        ));
    }


    public function updateProfile(Request $request)
    {
        try {
            // dd($request);
            $user = Auth::user();
            $userId = $user->userId;
            $vendor = Vendor::where('userId', $userId)->first();

            $validator = Validator::make($request->all(), [
                'nameInput'=> [
                    'bail',
                    'required',
                    'string',
                    'max:255',
                    'unique:vendors,name,' . $vendor->vendorId . ',vendorId',
                    'not_regex:/<[^>]*script.*?>.*?<\/[^>]*script.*?>/i',
                    'not_regex:/<[^>]+>/i',
                ],
                'telpInput' => 'bail|required|string|max:255|starts_with:08',
                'profilePicInput' => 'nullable|image|mimes:jpg,jpeg,png',
            ], [
                'nameInput.required' => 'Vendor name must be filled !.',
                'telpInput.required' => 'Telp number must be filled !',
                'telpInput.starts_with' => 'Telp number must be start with 08',
                'nameInput.unique' => 'Vendor name is already taken !',
                'profilePicInput.image'   => 'Profile picture must be an image.',
                'profilePicInput.mimes'   => 'Profile picture must be a file of type: jpg, jpeg, png.',
                'nameInput.not_regex' => 'HTML or script tags are not allowed in the vendor name.',
            ]);

            if ($validator->fails()) {
                //  logActivity('Failed', 'Updated', 'Vendor Profile, Due to Validation Error: ' . $e->getMessage());
                $errors = implode(', ', $validator->errors()->all());
                logActivity('Failed', 'Updated', 'Vendor Profile, Validation Errors: ' . $errors);
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $vendor->name = $request->nameInput;
            $vendor->phone_number = $request->telpInput;

            $vendor->breakfast_delivery = $request->breakfast_time_start . '-' . $request->breakfast_time_end;
            $vendor->lunch_delivery = $request->lunch_time_start . '-' . $request->lunch_time_end;
            $vendor->dinner_delivery = $request->dinner_time_start . '-' . $request->dinner_time_end;


            if ($request->hasFile('profilePicInput')) {
                $file = $request->file('profilePicInput');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('asset/profile'), $filename);
                $vendor->logo = 'asset/profile/' . $filename;
                logActivity('Successfully', 'Added', 'Profile pict inManage Profile Vendor Page');
            }

            $vendor->save();

            logActivity('Successfully', 'Updated', 'Manage Profile Vendor Page');
            return redirect()->route('manage-profile-vendor')->with('success', 'Profile updated successfully!');
        } catch (\Exception $e) {
            logActivity('Failed', 'Updated', 'Vendor Profile, Due to Validation Error: ' . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Failed to update profile.']);
        }
    }

    public function store(VendorStoreRequest $request){
        // validating
        $userId = Auth::id();

        $vendor = Vendor::create([
            'userId' => $userId
        ]);

        // upload logo
        $logoPath = null;
        
        $file = $request->file('logo');

        $filename = time().'_'.$file->getClientOriginalName();

        $file->storeAs('public/vendor_logos', $filename);

        $logoPath = 'vendor_logos/'.$filename;

        $vendor->update([
            'logo' => $logoPath,
        ]);
        
       // Convert and combine delivery times from 12-hour format (like "05:30 PM") to "HH:MM-HH:MM"
        $breakfast = $request->startBreakfast && $request->closeBreakfast
            ? $request->startBreakfast. '-' .$request->closeBreakfast
            : null;

        $lunch = $request->startLunch && $request->closeLunch
            ? $request->startLunch. '-' .$request->closeLunch
            : null;

        $dinner = $request->startDinner && $request->closeDinner
            ? $request->startDinner . '-' . $request->closeDinner
            : null;

        /** @var User|Authenticable $user */
        
        // Store the vendor
        $vendor->update([
            'name'=> $request['name'],
            'logo' => $logoPath, 
            'phone_number'=> $request['phone_number'],
            'breakfast_delivery'=> $breakfast,
            'lunch_delivery'=> $lunch,
            'dinner_delivery'=> $dinner,
            'provinsi'=> $request['provinsi'],
            'kota'=> $request['kota'],
            'kabupaten'=> $request['kota'],
            'kecamatan'=> $request['kecamatan'],
            'kelurahan'=> $request['kelurahan'],
            'kode_pos' => $request['kode_pos'],
            'jalan' => $request['jalan'],
            'rating' => 0.0,
        ]);
        // ]);

        return redirect('cateringHomePage');
    }
}