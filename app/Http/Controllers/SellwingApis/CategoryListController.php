<?php

namespace App\Http\Controllers\SellwingApis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryListController extends Controller
{
    public function index(Request $request)
    {
        $categories = DB::table('ownerclan_category')
            ->get(['id', 'name']);
        return [
            'status' => true,
            'return' => $categories
        ];
    }
}
