<?php

namespace App\Http\Controllers\Partners;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    public function partnerOpenMarket(Request $request)
    {
        $partnerId = Auth::guard('partner')->id();
        // 연동된 도매윙 계정이 있는지 검사.
        $hasSync = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasSync === false) {
            return redirect('/partner/account-setting/dowewing-integration/');
        }
        $controller = new Controller();
        $openMarkets = $controller->getActiveOpenMarkets();
        return view('partner/open_market', [
            'openMarkets' => $openMarkets
        ]);
    }
    public function excelwing(Request $request)
    {
        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'pr.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->where('v.type', 'B2B')
            ->select('v.id', 'v.name')
            ->get();
        $sellers = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('ps.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->where(function ($query) {
                $query->where('ps.id', '<=', 43)
                    ->orWhere('v.id', '=', 64);
            })
            ->get();

        return view('partner/excel_export', [
            'b2Bs' => $b2Bs,
            'sellers' => $sellers
        ]);
    }
    public function getUnmappedCategories()
    {
        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'pr.vendor_id', '=', 'v.id')
            ->where('pr.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->get();
        $unmappedCategories = DB::table('category_mapping')
            ->join('ownerclan_category', 'category_mapping.ownerclan', '=', 'ownerclan_category.id')
            ->where($b2Bs[0]->name_eng);
        foreach ($b2Bs as $index => $b2B) {
            if ($index != 0) {
                $unmappedCategories = $unmappedCategories->orWhereNull($b2B->name_eng);
            }
        }
        $unmappedCategories = $unmappedCategories->get();
        return [
            'b2Bs' => $b2Bs,
            'unmappedCategories' => $unmappedCategories
        ];
    }
    public function excelUploadIndex()
    {
        $partnerId = Auth::guard('partner')->id();
        // 연동된 도매윙 계정이 있는지 검사.
        $hasSync = DB::table('partner_domewing_accounts')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasSync === false) {
            return redirect('/partner/account-setting/dowewing-integration/');
        }
        return view('partner/excel_upload');
    }
}
