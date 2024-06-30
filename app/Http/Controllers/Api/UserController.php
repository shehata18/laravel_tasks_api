<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        if (!Hash::check($request->password, $user->password )){
            return response()->json(['message'=>'your current password is incorrect'], 401);
        }

        $validatedData = $request->validate([
            'password' => 'required',
            'new_password' => 'required|confirmed',
            'new_password_confirmation' => 'required',

        ]);
        $user->password = bcrypt($validatedData['new_password']);
        if($user->save()){
            return response()->json([
                'message' => 'Password updated Successfully'
            ],401);
        }else{
            return response()->json([
                'message' => 'internal server error'
            ],500);
        }

    }

    public function updateProfile(Request $request)
    {
        $validatedDate = $request->validate([
            'name'=>'required',
            'email'=>'required|unique:users,email,'.auth()->id()
        ]);
        if(auth()->user()->update($validatedDate)){
            return response()->json([
                'message' => 'Data updated Successfully'
            ],401);
        }
        return response()->json(['message' => 'Server Error , please try again latter'],500);
    }

    }


