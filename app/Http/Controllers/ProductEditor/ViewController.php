<?php

namespace App\Http\Controllers\ProductEditor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ViewController extends Controller
{
    public function index(Request $request)
    {
        return view('admin/product_editor');
    }
}
