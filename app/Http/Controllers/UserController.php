<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileRequest;
use App\Http\Requests\TopUpWellPayRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function topUpWellPay(TopUpWellPayRequest $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => __('customer/wellpay.unauthorized')], 401);
        }

        $user = User::find(Auth::id());

        try {
            $amount = $request->input('amount');
            $password = $request->input('password');

            if (!Hash::check($password, $user->password)) {
                throw ValidationException::withMessages([
                    'password' => [__('customer/wellpay.incorrect_password')],
                ]);
            }

            $newBalance = $user->wellpay + $amount;
            $maxAllowedBalance = 1000000000;

            if ($newBalance > $maxAllowedBalance) {
                logActivity('Failed', 'top-up', 'WellPay, Error : Balance cannot exceed Rp ' . number_format($maxAllowedBalance, 0, ',', '.') . '.');
                return response()->json(['message' => __('customer/wellpay.max_balance') . number_format($maxAllowedBalance, 0, ',', '.') . '.'], 400);
            }

            $user->wellpay = $newBalance;
            $user->save();

            logActivity('successfully', 'top-up', 'WellPay');

            $locale = App::getLocale();
            $prefix = $locale === 'id' ? 'Isi saldo Rp ' : 'Top-up of Rp ';
            $sufix = $locale === 'id' ? ' berhasil' : ' success';

            return response()->json([
                'message' => $prefix . number_format($amount, 0, ',', '.') . $sufix . '!',
                'new_balance' => $newBalance,
            ], 200);
        } catch (ValidationException $e) {
            logActivity('Failed', 'top-up', 'WellPay, Error : ' . $e->getMessage());
            return response()->json([
                'message' => __('customer/wellpay.validation_err'),
                'errors' => $e->errors()
            ], 422); 
        } catch (\Exception $e) {
            logActivity('Failed', 'top-up', 'WellPay, Error : ' . $e->getMessage());
            return response()->json(['message' => __('customer/wellpay.err_occured') . $e->getMessage()], 500);
        }
    }

    public function showProfile()
    {
        $user = Auth::user();

        logActivity('Successfully', 'Visited', 'Manage Profile Page');
        return view('customer/manageProfile', compact('user'));
    }



    public function updateProfile(ProfileRequest $request)
    {
        $user = Auth::user();
        $userId = $user->userId;

        $updated_user = User::find($userId);
        
        

        $updated_user->name = $request->nameInput;

        if($user->name != $request->nameInput) {
            logActivity('Successfully', 'Updated', "Profile to :  {$updated_user->name}");
        }

        if ($request->filled('dateOfBirth')) {
            $updated_user->dateOfBirth = $request->input('dateOfBirth');
        }

        if ($request->hasFile('profilePicInput')) {
            $file = $request->file('profilePicInput');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('storage/profiles'), $filename);
            $updated_user->profilePath = 'storage/profiles/'.$filename;
            logActivity('Successfully', 'Updated', "Profile picture {$updated_user->name}");
        }


        $updated_user->genderMale = ($request->gender === 'male') ? 1 : 0;

        $updated_user->save();

        

        return redirect()->route('manage-profile')->with('success', 'Profile updated successfully!');
    }
}
