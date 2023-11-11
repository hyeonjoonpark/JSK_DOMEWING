<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function loadBusinessPage(Request $request){
        return view('domewing.welcome');
    }
}
