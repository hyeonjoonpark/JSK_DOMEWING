<?php

namespace App\Http\Controllers\SellwingApis;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorListController extends Controller
{
    public function main(Request $request)
    {
        $exsitingVendorIds = $request->existingVendorIds;
        return $this->requestVendorList($exsitingVendorIds);
    }
    private function requestVendorList($exsitingVendorIds)
    {
        $newVendors = DB::table('product_search AS ps')
            ->join('vendors AS v', 'v.id', '=', 'ps.vendor_id')
            ->where('ps.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->whereNotIn('v.id', $exsitingVendorIds)
            ->get(['ps.vendor_id', 'v.name', 'ps.shipping_fee', 'ps.additional_shipping_fee']);
        return [
            'status' => true,
            'return' => $newVendors
        ];
    }
}
