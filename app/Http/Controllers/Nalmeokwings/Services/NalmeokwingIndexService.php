<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokwingIndexService extends Controller
{
    public function main()
    {
        $query = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->where('v.is_godwing', 1)
            ->orderByDesc('mp.createdAt');
        $numProducts = $query
            ->count();
        $products = $query
            ->paginate(500);
        return [
            'products' => $products,
            'numProducts' => $numProducts
        ];
    }
}
