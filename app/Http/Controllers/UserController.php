<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Auth;

class UserController extends Controller
{
    //
    public function user(Request $request)
    {
        return Auth::user();

    } // end method


}
