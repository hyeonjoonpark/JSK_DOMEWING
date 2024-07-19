<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokwingMatchService extends Controller
{
    /**
     * 날먹윙 매칭 페이지를 위해 데이터들을 취합하는 메소드입니다.
     * @return array
     */
    public function main()
    {
        $numGodwingProducts = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->where('v.is_godwing', 'Y')
            ->count();
        $numUnmatchedProducts = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->where('v.is_godwing', 'Y')
            ->where('partner_id', null)
            ->count();
        return [
            'numGodwingProducts' => $numGodwingProducts,
            'numUnmatchedProducts' => $numUnmatchedProducts
        ];
    }
}
