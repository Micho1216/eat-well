<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\CuisineType;
use App\Models\PackageCuisine;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PackagesImport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PackageController extends Controller
{
    // Menampilkan semua package
    public function index()
    {
        $vendorId = Auth::user()->vendor->vendorId;

        $packages = Package::with('cuisineTypes', 'category')
            ->where('vendorId', $vendorId)
            ->get();

        $cuisines = CuisineType::all();
        logActivity('Successfully', 'Visited', 'Manage Catering Package Page');
        return view('manageCateringPackage', compact('packages', 'cuisines', 'vendorId'));
    }

    // Menyimpan data package baru
    // use Illuminate\Validation\ValidationException;

    public function store(Request $request)
    {
        // dd($request);
        try {
        $validated = $request->validate([
            'categoryId' => 'required|integer|exists:package_categories,categoryId',
            'name' => 'required|string|max:255',

            'averageCalories' => 'nullable|numeric|gt:0',
            'breakfastPrice' => 'nullable|numeric|gt:0',
            'lunchPrice' => 'nullable|numeric|gt:0',
            'dinnerPrice' => 'nullable|numeric|gt:0',

            'menuPDFPath' => 'nullable|file|mimes:pdf',
            'imgPath' => 'nullable|image|mimes:jpeg,png,jpg',
        ], [
            'categoryId.required' => 'Package category is required',
            'categoryId.exists' => 'Selected category does not exist in the database',
            'name.required' => 'Package name required',
        ]);

        $venAcc = Auth::user();
        $validated['vendorId'] = $venAcc->vendor->vendorId;

            // Upload file PDF
            if ($request->hasFile('menuPDFPath')) {
                $menuFile = $request->file('menuPDFPath');
                $menuFileName = 'menu_' . time() . '.' . $menuFile->getClientOriginalExtension();
                $menuFile->move(public_path('asset/menus'), $menuFileName);
                $validated['menuPDFPath'] = $menuFileName;
            }

            // Upload gambar
            if ($request->hasFile('imgPath')) {
                $imgFile = $request->file('imgPath');
                $imgFileName = 'img_' . time() . '.' . $imgFile->getClientOriginalExtension();
                $imgFile->move(public_path('asset/menus'), $imgFileName);
                $validated['imgPath'] = $imgFileName;
            }

            // // Ambil cuisine_types terpisah
        // $cuisineTypes = $validated['cuisine_types'] ?? [];

        // // Hapus dari validated array
        // unset($validated['cuisine_types']);

        // Simpan ke database (tanpa cuisine_types)
        $newpackage = Package::create($validated);

        // if (!empty($cuisineTypes)) {
        //     $newpackage->cuisineTypes()->sync($cuisineTypes);
        // }
        
            logActivity('Successfully', 'Added', 'Catering Package');
            return redirect(route('manageCateringPackage'));
        } catch (ValidationException $e) {
            logActivity('Failed', 'Validation Error', 'Catering Package, Error : ' . $e->getMessage());

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        }
    }

    // Menghapus package berdasarkan ID
    public function destroy($id)
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        $package = Package::findOrFail($id);

        if (!$vendor || $package->vendorId !== $vendor->vendorId) {
            abort(403, 'Unauthorized');
        }

        $package->delete();

        logActivity('Successfully', 'Deleted', 'Catering Package');

        return response()->json([
            'success' => true,
            'message' => 'Package deleted successfully'
        ]);
    }


    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $vendorId = $user->vendor->vendorId;
        $package = Package::findOrFail($id);

        // Ownership check
        if ($vendorId != $package->vendorId) {
            abort(403, 'Unauthorized. You cannot edit a package that is not yours.');
        }


        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'categoryId' => 'required|integer|exists:package_categories,categoryId',

            'breakfastPrice' => 'nullable|decimal:0,2|gte:0',
            'lunchPrice' => 'nullable|decimal:0,2|gte:0',
            'dinnerPrice' => 'nullable|decimal:0,2|gte:0',
            'averageCalories' => 'nullable|decimal:0,2|gte:0',

            'menuPDFPath' => 'nullable|file|mimes:pdf',
            'imgPath' => 'nullable|image|mimes:jpeg,png,jpg',

            'cuisine_types' => 'nullable|array',
            'cuisine_types.*' => 'exists:cuisine_types,cuisineId',
        ], [
            'categoryId.required' => 'Package category is required',
            'categoryId.exists' => 'Selected category is invalid or not found',

            'name.required' => 'Package name required',
            'name.max' => 'Package name maximal 255 characters',

            'breakfastPrice.decimal' => 'Breakfast price must be numeric',
            'breakfastPrice.gte' => 'Breakfast price must be greater than or equal to 0',

            'lunchPrice.decimal' => 'Lunch price must be numeric',
            'lunchPrice.gte' => 'Lunch price must be greater than or equal to 0',

            'dinnerPrice.decimal' => 'Dinner price must be numeric',
            'dinnerPrice.gte' => 'Dinner price must be greater than or equal to 0',
        ]);
        // Upload file PDF ke public/asset/menus
        if ($request->hasFile('menuPDFPath')) {
            $menuFile = $request->file('menuPDFPath');
            $menuFileName = 'menu_' . time() . '.' . $menuFile->getClientOriginalExtension();
            $menuFile->move(public_path('asset/menus'), $menuFileName);
            $validated['menuPDFPath'] = $menuFileName;
        }

        // Upload gambar ke public/asset/menus
        if ($request->hasFile('imgPath')) {
            $imgFile = $request->file('imgPath');
            $imgFileName = 'img_' . time() . '.' . $imgFile->getClientOriginalExtension();
            $imgFile->move(public_path('asset/menus'), $imgFileName);
            $validated['imgPath'] = $imgFileName;
        }

        $package->update($validated);
        logActivity('Successfully', 'Updated', 'Catering Package');
        return redirect()->back()->with('success', 'Berhasil mengubah paket!');
    }

    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,csv'
        ]);

        Excel::import(new PackagesImport, $request->file('excel_file'));

        return redirect()->back()->with('success', 'Packages imported successfully!');
    }
}
