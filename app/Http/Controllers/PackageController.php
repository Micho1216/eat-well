<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\UpdatePackageRequest;
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
    public function store(StorePackageRequest $request)
    {
        $validated = $request->validated();

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

        return redirect(route('manageCateringPackage'));
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


    public function update(UpdatePackageRequest $request, $id)
    {
        $user = Auth::user();
        $vendorId = $user->vendor->vendorId;
        $package = Package::findOrFail($id);

        // Ownership check
        if ($vendorId != $package->vendorId) {
            abort(403, 'Unauthorized. You cannot edit a package that is not yours.');
        }

        $validated = $request->validated();

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
