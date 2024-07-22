<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmeokwingIndexService extends Controller
{
    public function main(Request $request)
    {
        $query = $this->buildQuery($request);

        $numProducts = $query->count();
        $products = $query->paginate(500);

        return [
            'products' => $products,
            'numProducts' => $numProducts,
            'searchKeyword' => $request->input('searchKeyword'),
            'productCodes' => $request->input('productCodes')
        ];
    }

    protected function buildQuery(Request $request)
    {
        $query = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->leftJoin('partners AS p', 'p.id', '=', 'mp.partner_id')
            ->where('mp.isActive', 'Y')
            ->where('v.is_godwing', 1)
            ->select([
                'mp.productHref',
                'mp.productImage',
                'mp.productName',
                'mp.productCode',
                'mp.productPrice',
                'v.name AS v_name',
                'p.name AS p_name',
                'mp.createdAt'
            ])
            ->orderByDesc('mp.createdAt');

        $this->applyFilters($query, $request);

        return $query;
    }

    protected function applyFilters($query, Request $request)
    {
        if ($productCodes = $request->input('productCodes')) {
            $arrayProductCodes = explode(',', $productCodes);
            $query->whereIn('mp.productCode', $arrayProductCodes);
        }

        if ($searchKeyword = $request->input('searchKeyword')) {
            $query->where(function ($q) use ($searchKeyword) {
                $q->where('mp.productName', 'LIKE', '%' . $searchKeyword . '%')
                    ->orWhere('v.name', 'LIKE', '%' . $searchKeyword . '%')
                    ->orWhere('p.name', 'LIKE', '%' . $searchKeyword . '%');
            });
        }
    }
}
