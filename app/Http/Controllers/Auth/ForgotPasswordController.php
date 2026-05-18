<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ForgotPasswordController extends Controller
{
    /**
     * Show the single combined form
     */
    public function showVerifyForm()
    {
        return view('verify');
    }

    /**
     * Process Identity Check and Password Update in one go
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'ec_number' => 'required|string',
            'id_number' => 'required|string',
            'password'  => 'required|string|min:8|confirmed',
        ]);

        // Find user by both EC Number and National ID
        // Note: 'national_id' is the correct column name from your SQL file
        $user = User::where('ec_number', $request->ec_number)
                    ->where('national_id', $request->id_number)
                    ->first();

        if (!$user) {
            return back()
                ->withInput($request->only('ec_number', 'id_number'))
                ->with('error', 'The identity details provided do not match our records.');
        }

        // Update the password immediately
        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('login')->with('success', 'Password updated successfully. Please login with your new password.');
    }
}
