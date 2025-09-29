<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    //Change Password
    public function showChangePassword()
    {
        return view('admin.settings.change_password');
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'old_password' => 'required',
                'new_password' => 'required|min:5',
                'confirm_password' => 'required | same:new_password'
            ]
        );
        if ($validator->passes()) {
            $admin = User::where('id', Auth::guard('admin')->user()->id)->first();
            if (!Hash::check($request->old_password, $admin->password)) {
                session()->flash('Fail', 'Incorrect Password!Please try again.');
                return response()->json([
                    'status' => true,
                ]);
            }
            User::where('id', $admin->id)->update(
                [
                    'password' => Hash::make($request->new_password)
                ]
            );
            session()->flash('Success', 'Password changed successfully.');
            return response()->json([
                'status' => true,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
}
