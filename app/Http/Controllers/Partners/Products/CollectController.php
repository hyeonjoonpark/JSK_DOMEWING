<?php

namespace App\Http\Controllers\Partners\Products;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CollectController extends Controller
{
    public function index(Request $request)
    {
        $partnerId = Auth::guard('partner')->id();
        $hasTable = DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->exists();
        if ($hasTable === false) {
            return redirect('/partner/products/manage');
        }

        $lgCategory = $request->input('lgCategory', -1);
        $mdCategory = $request->input('mdCategory', -1);
        $smCategory = $request->input('smCategory', -1);
        $xsCategory = $request->input('xsCategory', -1);

        $combinedCategories = array_filter([$lgCategory, $mdCategory, $smCategory, $xsCategory], function ($category) {
            return $category !== -1 && $category !== '-1';
        });

        $categoryName = implode('>', $combinedCategories);

        $productCodesStr = $request->input('productCodesStr', '');
        $searchKeyword = $request->input('searchKeyword', '');
        $controller = new Controller();
        $marginValue = $controller->getMarginValue();
        $nalmeokwingMargin = DB::table('sellwing_config')
            ->where('id', 3)
            ->value(DB::raw("value / 100 + 1"));
        $query = DB::table('minewing_products AS mp')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'mp.categoryID')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->whereNot('categoryID', null)
            ->where('v.is_godwing', 0)
            ->select('mp.productCode', 'mp.productImage', 'mp.productName', DB::raw("mp.productPrice * CASE WHEN v.type = 'B2B' THEN {$nalmeokwingMargin} ELSE {$marginValue} END AS productPrice"), 'mp.shipping_fee', 'oc.name');
        if ($searchKeyword) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('mp.productName', 'like', "%$searchKeyword%")
                    ->orWhere('mp.productPrice', 'like', "%$searchKeyword%")
                    ->orWhere('mp.productCode', 'like', "%$searchKeyword%");
            });
        }

        if ($productCodesStr) {
            $productCodesArr = explode(',', $productCodesStr);
            $productCodesArr = array_map('trim', $productCodesArr);
            $query->whereIn('mp.productCode', $productCodesArr);
        }

        if ($categoryName !== '') {
            $query->where('oc.name', 'like', '%' . $categoryName . '%');
        }

        $products = $query->orderBy('createdAt', 'DESC')->paginate(500);
        $categories = $this->getCategories();
        $lgCategories = $categories['lgCategories'];
        $mdCategories = [];
        $smCategories = [];
        $xsCategories = [];
        if ($lgCategory !== -1 && $lgCategory !== '-1') {
            foreach ($categories['mdCategories'] as $category) {
                if ($category['parent'] === $lgCategory && !in_array($category['this'], $mdCategories)) {
                    $mdCategories[] = $category['this'];
                }
            }
        }
        if ($mdCategory !== -1 && $mdCategory !== '-1') {
            foreach ($categories['smCategories'] as $category) {
                if ($category['parent'] === $mdCategory && !in_array($category['this'], $smCategories)) {
                    $smCategories[] = $category['this'];
                }
            }
        }
        if ($smCategory !== -1 && $smCategory !== '-1') {
            foreach ($categories['xsCategories'] as $category) {
                if ($category['parent'] === $smCategory && !in_array($category['this'], $xsCategories)) {
                    $xsCategories[] = $category['this'];
                }
            }
        }
        return view('partner/products_collect', [
            'products' => $products,
            'searchKeyword' => $searchKeyword,
            'productCodesStr' => $productCodesStr,
            'categories' => $categories,
            'partnerTables' => $this->getPartnerTables(Auth::guard('partner')->id()),
            'lgCategory' => $lgCategory,
            'mdCategory' => $mdCategory,
            'smCategory' => $smCategory,
            'xsCategory' => $xsCategory,
            'categoryName' => $categoryName,
            'lgCategories' => $lgCategories,
            'smCategories' => $smCategories,
            'mdCategories' => $mdCategories,
            'xsCategories' => $xsCategories,
        ]);
    }
    public function getCategories()
    {
        $categories = DB::table('minewing_products AS mp')
            ->join('ownerclan_category AS oc', 'mp.categoryID', '=', 'oc.id')
            ->distinct()
            ->pluck('oc.name')
            ->toArray();
        $lgCategories = [];
        $mdCategories = [];
        $smCategories = [];
        $xsCategories = [];
        foreach ($categories as $category) {
            $parts = explode('>', $category);
            if (!empty($parts[0]) && !in_array($parts[0], $lgCategories)) {
                $lgCategories[] = $parts[0];
            }
            if (!empty($parts[1]) && !in_array($parts[1], $mdCategories)) {
                $mdCategories[] = [
                    'parent' => $parts[0],
                    'this' => $parts[1]
                ];
            }
            if (!empty($parts[2]) && !in_array($parts[2], $smCategories)) {
                $smCategories[] = [
                    'parent' => $parts[1],
                    'this' => $parts[2]
                ];
            }
            if (!empty($parts[3]) && !in_array($parts[3], $xsCategories)) {
                $xsCategories[] = [
                    'parent' => $parts[2],
                    'this' => $parts[3]
                ];
            }
        }
        return [
            'lgCategories' => $lgCategories,
            'mdCategories' => $mdCategories,
            'smCategories' => $smCategories,
            'xsCategories' => $xsCategories
        ];
    }
    private function getPartnerTables($partnerId)
    {
        return DB::table('partner_tables')
            ->where('partner_id', $partnerId)
            ->where('is_active', 'Y')
            ->get();
    }
}
