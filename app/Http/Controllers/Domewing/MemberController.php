<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function loadAccountSettings(Request $request){
        return view('domewing.user_details');
    }
}
