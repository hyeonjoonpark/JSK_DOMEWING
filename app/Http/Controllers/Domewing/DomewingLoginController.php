<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DomewingLoginController extends Controller
{
    public function login(Request $request){
        return view('domewing.auth.login');
    }
}
