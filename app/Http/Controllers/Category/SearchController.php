<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function index($keyword)
    {
        $category = $this->getCategory($keyword);
        return $category;
    }
    public function getCategory($keyword)
    {
        $category = DB::table('ownerclan_category')
            ->where('name', 'LIKE', '%' . $keyword . '%')
            ->select(['code', 'name'])
            ->get();
        return $category;
    }
}
