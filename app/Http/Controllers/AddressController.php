<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Auth::check()) {
            $user = Auth::user();
            return view('ManageAddress', compact('user'));
        } else {
            return redirect()->route('login');
        }
    }

    public function setDefaultAddress(Request $request)
    {
        $user = Auth::user();
        $loggedInUserId = $user->userId;

        $request->validate([
            'address_id' => [
                'required',
                'numeric',
                // Validasi 'exists':
                // Cek di tabel 'addresses', kolom 'addressId'
                // Pastikan alamat tersebut juga memiliki 'userId' yang cocok dengan user yang login
                Rule::exists('addresses', 'addressId')->where(function ($query) use ($loggedInUserId) {
                    $query->where('userId', $loggedInUserId);
                }),
            ],
        ]);

        $requestedAddressId = $request->input('address_id');

        try {
            // Nonaktifkan semua alamat utama pengguna saat ini
            Address::where('userId', $loggedInUserId)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            // Set alamat yang dipilih menjadi utama
            $newDefaultAddress = Address::where('userId', $loggedInUserId)
                ->where('addressId', $requestedAddressId)
                ->firstOrFail();
            $newDefaultAddress->is_default = true;
            $newDefaultAddress->save();

            return response()->json(['success' => true, 'message' => 'Alamat utama berhasil diatur.']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Alamat tidak ditemukan atau bukan milik Anda.'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan internal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('addAddress');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'provinsi_id' => 'required',
            'provinsi_name' => 'required|string|max:255',
            'kota_id' => 'required',
            'kota_name' => 'required|string|max:255',
            'kecamatan_id' => 'required',
            'kecamatan_name' => 'required|string|max:255',
            'kelurahan_id' => 'required',
            'kelurahan_name' => 'required|string|max:255',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'required|string|digits:5',
            'notes' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
        ]);

        $newAddress = new Address();
        $newAddress->userId = Auth::id();
        $newAddress->provinsi = $validatedData['provinsi_name'];
        $newAddress->kota = $validatedData['kota_name'];
        $newAddress->kecamatan = $validatedData['kecamatan_name'];
        $newAddress->kelurahan = $validatedData['kelurahan_name'];
        $newAddress->jalan = $validatedData['jalan'];
        $newAddress->kode_pos = $validatedData['kode_pos'];
        $newAddress->notes = $validatedData['notes'];
        $newAddress->recipient_name = $validatedData['recipient_name'];
        $newAddress->recipient_phone = $validatedData['recipient_phone'];
        $newAddress->is_default = 0;
        $newAddress->kabupaten = 'kabupaten';

        $newAddress->save();

        return redirect('/manage-address')->with('success', 'Alamat berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Address $address)
    {
        if (Auth::id() !== $address->userId) {
            return redirect()->route('manage-address')->with('error', 'Unauthorized action.');
        }

        return view('editAddress', compact('address'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Address $address)
    {
        if (Auth::id() !== $address->userId) {
            abort(403, 'Unauthorized action.');
        }

        $validatedData = $request->validate([
            'provinsi_id' => 'required',
            'provinsi_name' => 'required|string|max:255',
            'kota_id' => 'required',
            'kota_name' => 'required|string|max:255',
            'kecamatan_id' => 'required',
            'kecamatan_name' => 'required|string|max:255',
            'kelurahan_id' => 'required',
            'kelurahan_name' => 'required|string|max:255',
            'jalan' => 'required|string|max:255',
            'kode_pos' => 'required|string|digits:5',
            'notes' => 'nullable|string|max:255',
            'recipient_name' => 'required|string|max:100',
            'recipient_phone' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
        ]);

        $address->provinsi = $validatedData['provinsi_name'];
        $address->kota = $validatedData['kota_name'];
        $address->kecamatan = $validatedData['kecamatan_name'];
        $address->kelurahan = $validatedData['kelurahan_name'];
        $address->jalan = $validatedData['jalan'];
        $address->kode_pos = $validatedData['kode_pos'];
        $address->notes = $validatedData['notes'];
        $address->recipient_name = $validatedData['recipient_name'];
        $address->recipient_phone = $validatedData['recipient_phone'];
        $address->is_default = 0;
        $address->kabupaten = $validatedData['kota_name'];

        $address->save();

        return redirect('/manage-address')->with('update_success', 'Alamat berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Address $address)
    {
        if (Auth::id() !== $address->userId) {
            abort(403, 'Unauthorized action.');
        }

        // if ($address->is_default) {
        //     return redirect('/manage-address')->with('error', 'Tidak dapat menghapus alamat utama. Silakan atur alamat lain sebagai utama terlebih dahulu.');
        // }

        $address->delete();

        return redirect('/manage-address')->with('delete_success', 'Alamat berhasil dihapus.');
    }
}
