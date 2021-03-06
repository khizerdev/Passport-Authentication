<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Support\Facades\Hash;
use Mail;
use App\Mail\ForgetPassword;

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


    public function forgot(Request $request)
    {
        $request->validate([
            'email' => 'required',
        ]);

        $email = $request->email;

        if(User::where('email' , $email)->doesntExist()){
            return response([
                'message' => 'Email Invalid'
            ],401);
        }

        $token = rand(10,10000);

        try {

            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token
            ]);


            Mail::to($email)->send(new ForgetPassword($token));

            return response ([
                'message' => 'Reset Password Mail has been sent to your email',
            ],200);

        } catch(Exception $exception){
            return response([
                'message' => $exception->getMessage()
            ],400);
        } // end method
    }


    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:5',
        ]);

        $email = $request->email;
        $token = $request->token;
        $password = Hash::make($request->password);

        $emailCheck = DB::table('password_resets')->where('email' , $email)->first();
        $pinCheck = DB::table('password_resets')->where('token' , $token)->first();

        if(!$emailCheck){
            return response([
                'message' => 'Email Not Found'
            ],401);
        }
        if(!$pinCheck){
            return response([
                'message' => 'Pin Code Invalid'
            ],401);
        }

        DB::table('users')->where('email' , $email)->update([
            'password' => $password
        ]);

        DB::table('password_resets')->where('email' , $email)->delete();

        return response([
            'message' => 'Password Changed Successfully'
        ]);
    }

}
