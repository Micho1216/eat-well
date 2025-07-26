<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        if (Auth::check()) {
            // Mengambil user yang sedang login
            $user = User::find(Auth::id());

            $wellpay = $user->wellpay ?? 0; // Ambil saldo, default 0 jika kolom 'balance' null/tidak ada

            // Meneruskan data saldo ke view 'dashboard'
            return view('customer.home', compact('user', 'wellpay'));
        }

        // Jika user belum login, redirect ke halaman login
        return redirect()->route('landingPage');
    }

    public function topUpWellPay(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized. Please login first.'], 401);
        }

        $user = User::find(Auth::id());

        try {
            // Validasi input dari request
            $request->validate([
                'amount' => 'required|integer|min:1000|max:20000000',
                'password' => 'required|string',
            ], [
                'amount.required' => 'Top-up amount is required.',
                'amount.integer' => 'Top-up amount must be a number.',
                'amount.min' => 'The minimum top-up amount is Rp 1.000.',
                'amount.max' => 'The maximum top-up amount is Rp 20.000.000.',
                'password.required' => 'Please enter your password.',
            ]);

            $amount = $request->input('amount');
            $password = $request->input('password');

            // Verifikasi password
            if (!Hash::check($password, $user->password)) {
                // Menggunakan ValidationException agar error bisa ditangkap di JavaScript pada `errors.password`
                throw ValidationException::withMessages([
                    'password' => ['Incorrect password.'],
                ]);
            }

            // Periksa batas saldo maksimum setelah top-up
            $newBalance = $user->wellpay + $amount;
            $maxAllowedBalance = 1000000000;

            if ($newBalance > $maxAllowedBalance) {
                logActivity('Failed', 'top-up', 'WellPay, Error : Balance cannot exceed Rp ' . number_format($maxAllowedBalance, 0, ',', '.') . '.');
                return response()->json(['message' => 'Your balance cannot exceed Rp ' . number_format($maxAllowedBalance, 0, ',', '.') . '.'], 400);
            }

            // Update saldo di database
            $user->wellpay = $newBalance;
            $user->save();

            logActivity('successfully', 'top-up', 'WellPay');

            // Berikan respons sukses
            return response()->json([
                'message' => 'Top-up of Rp ' . number_format($amount, 0, ',', '.') . ' successful!',
                'new_balance' => $newBalance, // Kirim saldo baru kembali ke frontend
            ], 200);
        } catch (ValidationException $e) {
            // Tangkap error validasi dan kirimkan ke frontend
            logActivity('Failed', 'top-up', 'WellPay, Error : ' . $e->getMessage());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $e->errors()
            ], 422); // Status code 422 Unprocessable Entity
        } catch (\Exception $e) {
            // Tangkap error lainnya (misalnya error database)
            logActivity('Failed', 'top-up', 'WellPay, Error : ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }

    public function showProfile()
    {
        // Mengambil user yang sedang login
        $user = Auth::user();


        logActivity('Successfully', 'Visited', 'Manage Profile Page');
        return view('manageProfile', compact('user'));
    }



    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();
        $userId = $user->userId;

        $updated_user = User::find($userId);

        $updated_user->name = $request->nameInput;

        if ($request->filled('dateOfBirth')) {
            $updated_user->dateOfBirth = $request->input('dateOfBirth');
        }

        if ($request->hasFile('profilePicInput')) {
            $file = $request->file('profilePicInput');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('asset/profile'), $filename);
            $updated_user->profilePath = $filename;
        }

        $updated_user->genderMale = ($request->gender === 'male') ? 1 : 0;

        $updated_user->save();

        logActivity('Successfully', 'Updated', "Profile to {$updated_user->name}");

        return redirect()->route('manage-profile');
    }
}
