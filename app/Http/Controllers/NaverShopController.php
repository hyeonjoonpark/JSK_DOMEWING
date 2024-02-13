<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class NaverShopController extends Controller
{
    public function index()
    {
        $productHrefs = [
            "https://dometopia.com/goods/view?no=179372&code=00300036",
            "https://dometopia.com/goods/view?no=179373&code=00300036",
            "https://dometopia.com/goods/view?no=172960&code=010200220001",
            "https://dometopia.com/goods/view?no=182127&code=01390019",
            "https://dometopia.com/goods/view?no=182117&code=01390019",
            "https://dometopia.com/goods/view?no=182085&code=01390019",
            "https://dometopia.com/goods/view?no=153803&code=01390019",
            "https://dometopia.com/goods/view?no=62704&code=01390019",
            "https://dometopia.com/goods/view?no=62686&code=01390019",
            "https://dometopia.com/goods/view?no=3128&code=002100120008",
            "https://dometopia.com/goods/view?no=182308&code=002100120008",
            "https://dometopia.com/goods/view?no=182309&code=002100120008",
            "https://dometopia.com/goods/view?no=141959&code=009700070003",
            "https://dometopia.com/goods/view?no=169977&code=001700570004",
            "https://dometopia.com/goods/view?no=160584&code=001700570004",
            "https://dometopia.com/goods/view?no=154000&code=002100130005",
            "https://dometopia.com/goods/view?no=115426&code=00350038",
            "https://dometopia.com/goods/view?no=115426&code=003500380003",
            "https://dometopia.com/goods/view?no=162872&code=001700330001",
            "https://dometopia.com/goods/view?no=110121&code=00250012",
            "https://dometopia.com/goods/view?no=107997&code=00870019",
            "https://dometopia.com/goods/view?no=92603&code=00870019",
        ];
        DB::table('minewing_products')
            ->whereIn('productHref', $productHrefs)
            ->update([
                'isActive' => 'N'
            ]);
        return true;
    }
}
