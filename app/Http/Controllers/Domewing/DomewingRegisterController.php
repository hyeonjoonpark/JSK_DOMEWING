<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DomewingRegisterController extends Controller
{
    public function register(Request $request){
        return view('domewing.auth.register');
    }
}
