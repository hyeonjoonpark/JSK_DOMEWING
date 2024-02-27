<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessPageController extends Controller
{
    public function showBusinessPage(Request $request)
    {
        return view('business_page.index');
    }
}
