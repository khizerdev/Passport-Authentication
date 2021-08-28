<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login(Request $request)
    {
        try {

            if(Auth::attempt($request->only('email' , 'password'))) {
                $user = Auth::user();
                $token = $user->createToken('app')->accessToken;

                return response ([
                    'message' => 'Successfully Login',
                    'token' => $token,
                    'user' => $user
                ],200);
            }

        } catch(Exception $exception){
            return response([
                'message' => $exception->getMessage()
            ],400);
        }


        return response([
            'message' => 'Invalid Email or Password'
        ],401);

    } // end method



    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|min:4',
            'email' => 'required|unique:users',
            'password' => 'required|min:5|confirmed',
        ]);


        try {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken('app')->accessToken;

            return response ([
                'message' => 'Registration Successfull',
                'token' => $token,
                'user' => $user
            ],200);


        } catch(Exception $exception){
            return response([
                'message' => $exception->getMessage()
            ],400);
        }
    }
}
