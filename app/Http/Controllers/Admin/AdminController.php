<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Productwing\SoldOutController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Product\ProcessController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\Schema;

class AdminController extends Controller
{
    public function dashboard()
    {
        $labels = [];
        $recharges = [];
        $sales = [];
        $currentDate = new DateTime();
        for ($i = 6; $i >= 0; $i--) {
            $date = (clone $currentDate)->modify("-$i days");
            $startDatetime = $date->format('Y-m-d 00:00:00');
            $endDatetime = $date->format('Y-m-d 23:59:59');
            $sale = DB::table('wing_transactions AS wt')
                ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
                ->where('o.type', 'PAID')
                ->where('wt.status', 'APPROVED')
                ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
                ->sum('wt.amount');
            $recharge = DB::table('wing_transactions AS wt')
                ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
                ->where('wt.status', 'APPROVED')
                ->whereBetween('wt.created_at', [$startDatetime, $endDatetime])
                ->sum('wt.amount');
            $labels[] = $date->format('d') . '일';
            $sales[] = $sale;
            $recharges[] = $recharge;
        }
        $maxTarget = max($sales) > max($recharges) ? max($sales) : max($recharges);
        $max = ceil($maxTarget / 500000) * 500000;
        $thisMonthStart = date('Y-m-01 00:00:00');
        $thisMonthEnd = date('Y-m-t 23:59:59');
        $lastMonthStart = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $lastMonthEnd = date('Y-m-t 23:59:59', strtotime('-1 month'));
        $thisMonthSaleTotal = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('wt.amount');
        $lastMonthSaleTotal = DB::table('wing_transactions AS wt')
            ->join('orders AS o', 'wt.id', '=', 'o.wing_transaction_id')
            ->where('o.type', 'PAID')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('wt.amount');
        $thisMonthRechargeTotal = DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$thisMonthStart, $thisMonthEnd])
            ->sum('wt.amount');
        $lastMonthRechargeTotal = DB::table('wing_transactions AS wt')
            ->join('deposit_details AS dd', 'wt.id', '=', 'dd.wing_transaction_id')
            ->where('wt.status', 'APPROVED')
            ->whereBetween('wt.created_at', [$lastMonthStart, $lastMonthEnd])
            ->sum('wt.amount');
        return view('admin/dashboard', [
            'labels' => $labels,
            'sales' => $sales,
            'recharges' => $recharges,
            'max' => $max,
            'thisMonthSaleTotal' => $thisMonthSaleTotal,
            'lastMonthSaleTotal' => $lastMonthSaleTotal,
            'thisMonthRechargeTotal' => $thisMonthRechargeTotal,
            'lastMonthRechargeTotal' => $lastMonthRechargeTotal,
        ]);
    }
    public function productSearch()
    {
        $searchVendors = DB::table('product_search')
            ->join('vendors', 'vendors.id', '=', 'product_search.vendor_id')
            ->where('product_search.is_active', 'Y')
            ->get();
        $uploadVendors = DB::table('product_register')
            ->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')
            ->where('product_register.is_active', 'Y')
            ->get();
        return view('admin/product_search', [
            'searchVendors' => $searchVendors,
            'uploadVendors' => $uploadVendors
        ]);
    }
    public function productRegister()
    {
        $vendors = DB::table('product_register')->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')->where('product_register.is_active', 'Y')->get();
        $productInformation = DB::table('product_information')->get();
        $remember_token = Auth::guard('user')->user()->remember_token;
        return view('admin/product_register', [
            'vendors' => $vendors,
            'productInformation' => $productInformation,
            'remember_token' => $remember_token
        ]);
    }
    public function searchToRegister(Request $request)
    {
        $vendors = DB::table('product_register')->join('vendors', 'vendors.id', '=', 'product_register.vendor_id')->where('product_register.is_active', 'Y')->get();
        $productInformation = DB::table('product_information')->get();
        $remember_token = Auth::guard('user')->user()->remember_token;
        return view('admin/product_register', [
            'vendors' => $vendors,
            'productInformation' => $productInformation,
            'remember_token' => $remember_token,
            'name' => $request->name,
            'price' => $request->price,
            'image' => $request->image
        ]);
    }

    public function cmsDashboard(Request $request)
    {
        $domains = DB::table('cms_domain')
            ->select('cms_domain.*', 'users.*', 'cms_domain.created_at as domain_created_at', 'cms_domain.updated_at as domain_updated_at')
            ->join('users', 'users.id', '=', 'cms_domain.user_id')
            ->where('cms_domain.is_active', 'ACTIVE')
            ->get();

        foreach ($domains as $domain) {
            $domain->formatted_created_at = Carbon::parse($domain->domain_created_at)->format('d M Y');
            $domain->formatted_updated_at = Carbon::parse($domain->domain_updated_at)->format('d M Y');
        }

        return view('admin/cms_dashboard', ['domains' => $domains]);
    }

    public function accountSetting(Request $request)
    {
        $controller = new Controller();
        $b2Bs = $controller->getActiveB2Bs();
        $partnersExcelwingMargin = DB::table('sellwing_config')
            ->where('title', 'partners_excelwing_margin')
            ->first();
        $nalmeokwing_margin = DB::table('sellwing_config')
            ->where('title', 'nalmeokwing_margin')
            ->first();
        $vendors = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('ps.is_active', 'Y')
            ->get();
        $vendorCommissions = DB::table('vendor_commissions AS vc')
            ->join('vendors AS v', 'v.id', '=', 'vc.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->get(['v.name_eng', 'vc.commission', 'v.name']);
        return view('admin/account-setting', [
            'b2Bs' => $b2Bs,
            'vendors' => $vendors,
            'vendorCommissions' => $vendorCommissions,
            'partnersExcelwingMargin' => $partnersExcelwingMargin,
            'nalmeokwing_margin' => $nalmeokwing_margin
        ]);
    }

    public function productManage(Request $request)
    {
        $products = DB::table('collected_products')->where('isActive', 'Y')->get();
        return view('admin/product_manage', [
            'products' => $products
        ]);
    }

    public function uploadedProducts(Request $request)
    {
        $uploadedProducts = DB::table('uploaded_products')
            ->join('collected_products', 'collected_products.id', '=', 'uploaded_products.productId')
            ->where(['uploaded_products.isActive' => 'Y'])
            ->select(
                'uploaded_products.*',
                'collected_products.*',
                'uploaded_products.createdAt as uploadedCreatedAt',
                'collected_products.createdAt as collectedCreatedAt'
            )
            ->orderBy('uploaded_products.createdAt', 'desc')
            ->get();
        return view('admin/product_uploaded', [
            'uploadedProducts' => $uploadedProducts
        ]);
    }

    public function productKeywords(Request $request)
    {
        return view('admin/product_keywords');
    }
    public function productMining(Request $request)
    {
        $sellers = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('ps.is_active', 'Y')
            ->where('ps.has_api', 'N')
            ->where('ps.new_minewing', 'N')
            ->get();
        return view('admin/product_mining', [
            'sellers' => $sellers
        ]);
    }
    public function minewing(Request $request)
    {
        // Initialize variables from the request with default values if not present
        $searchKeyword = $request->input('searchKeyword', '');
        $productCodesStr = $request->input('productCodesStr', null);

        // Base query for products
        $query = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'Y')
            ->select([
                'mp.id AS productID', 'mp.productCode', 'mp.productImage',
                'mp.productName', 'mp.productPrice', 'mp.productHref',
                'v.name', 'mp.createdAt'
            ]);

        // Apply search keyword condition
        if (!empty($searchKeyword)) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('mp.productName', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.productPrice', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.productCode', 'like', "%{$searchKeyword}%")
                    ->orWhere('v.name', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.createdAt', 'like', "%{$searchKeyword}%");
            });
        }

        // Apply product codes condition
        if (!empty($productCodesStr)) {
            $productCodesArr = explode(',', $productCodesStr);
            $productCodesArr = array_map('trim', $productCodesArr);
            $query->whereIn('mp.productCode', $productCodesArr);
        }

        // Execute the query with ordering and pagination
        $products = $query->orderBy('mp.createdAt', 'DESC')->paginate(500);

        // Query for B2Bs without changing, as it seems unrelated to the product's query
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->where('v.type', 'B2B')
            ->whereNot('v.id', 57)
            ->get();

        // Return view with data
        return view('admin/product_minewing', compact('products', 'searchKeyword', 'productCodesStr', 'b2bs'));
    }
    public function searchProductCodes(Request $request)
    {
        $productCodesStr = $request->productCodes;
        $productCodesArr = explode(',', $productCodesStr);
        $productCodesArr = array_map('trim', $productCodesArr);
        $products = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->whereIn('mp.productCode', $productCodesArr)
            ->where('isActive', 'Y')
            ->orderBy('mp.createdAt', 'DESC')->paginate(500);
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.type', 'B2B')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->get();

        return view('admin/product_minewing', [
            'products' => $products,
            'productCodesStr' => $productCodesStr,
            'searchKeyword' => '',
            'b2bs' => $b2bs
        ]);
    }
    public function searchSoldOutCodes(Request $request)
    {
        $productCodesStr = $request->productCodes;
        $productCodesArr = explode(',', $productCodesStr);
        $productCodesArr = array_map('trim', $productCodesArr);
        $products = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->whereIn('mp.productCode', $productCodesArr)
            ->where('isActive', 'N')
            ->orderBy('mp.createdAt', 'DESC')->paginate(500);
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('v.type', 'B2B')
            ->where('pr.is_active', 'Y')
            ->get();

        return view('admin/product_sold_out', [
            'products' => $products,
            'productCodesStr' => $productCodesStr,
            'searchKeyword' => '',
            'b2bs' => $b2bs
        ]);
    }
    public function soldOut(Request $request)
    {
        // Initialize variables from the request with default values if not present
        $searchKeyword = $request->input('searchKeyword', '');
        $productCodesStr = $request->input('productCodesStr', null);

        // Base query for products
        $query = DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('mp.isActive', 'N')
            ->select([
                'mp.id AS productID', 'mp.productCode', 'mp.productImage',
                'mp.productName', 'mp.productPrice', 'mp.productHref',
                'v.name', 'mp.createdAt'
            ]);

        // Apply search keyword condition
        if (!empty($searchKeyword)) {
            $query->where(function ($query) use ($searchKeyword) {
                $query->where('mp.productName', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.productPrice', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.productCode', 'like', "%{$searchKeyword}%")
                    ->orWhere('v.name', 'like', "%{$searchKeyword}%")
                    ->orWhere('mp.createdAt', 'like', "%{$searchKeyword}%");
            });
        }

        // Apply product codes condition
        if (!empty($productCodesStr)) {
            $productCodesArr = explode(',', $productCodesStr);
            $productCodesArr = array_map('trim', $productCodesArr);
            $query->whereIn('mp.productCode', $productCodesArr);
        }

        // Execute the query with ordering and pagination
        $products = $query->orderBy('mp.createdAt', 'DESC')->paginate(500);

        // Query for B2Bs without changing, as it seems unrelated to the product's query
        $b2bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->where('v.type', 'B2B')
            ->whereNot('v.id', 57)
            ->get();

        // Return view with data
        return view('admin.product_sold_out', compact('products', 'searchKeyword', 'productCodesStr', 'b2bs'));
    }
    public function legacy(Request $request)
    {
        $searchKeyword = '';
        if (isset($request->searchKeyword)) {
            $searchKeyword = $request->searchKeyword;
        }
        $products = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'cp.id', '=', 'up.productId')
            ->join('vendors AS v', 'v.id', '=', 'cp.sellerID')
            ->where('up.isActive', 'Y')
            ->where(function ($query) use ($searchKeyword) {
                $query->where('up.newProductName', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('cp.productPrice', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('v.name', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('up.productId', 'like', '%' . $searchKeyword . '%')
                    ->orWhere('cp.createdAt', 'like', '%' . $searchKeyword . '%');
            })
            ->select('cp.id AS productID', 'cp.id AS productCode', 'up.newImageHref AS productImage', 'up.newProductName AS productName', 'cp.productPrice', 'cp.productHref', 'v.name', 'cp.createdAt')
            ->orderBy('cp.createdAt', 'DESC')->limit(1000)->get();
        $searchKeyword = '';
        return view('admin/product_legacy', [
            'products' => $products,
            'searchKeyword' => $searchKeyword
        ]);
    }
    public function excelwing(Request $request)
    {
        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'pr.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->select('v.id', 'v.name')
            ->get();
        $sellers = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('ps.is_active', 'Y')
            ->get();
        $response = $this->getUnmappedCategories();
        $unmappedCategories = $response['unmappedCategories'];
        // if (count($unmappedCategories) > 0) {
        //     return redirect('admin/mappingwing/unmapped');
        // }
        $duplicates = DB::table('minewing_products')
            ->select('productName', DB::raw('COUNT(*) as count'))
            ->where('isActive', 'Y')
            ->groupBy('productName')
            ->havingRaw('COUNT(*) > 1')
            ->exists();
        // if ($duplicates === true) {
        //     return redirect('admin/namewing');
        // }
        return view('admin/excelwing', [
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
            ->whereIn('v.id', [5, 40, 51, 54, 65, 66, 67, 73])
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
    public function unmapped()
    {
        $response = $this->getUnmappedCategories();
        if (count($response['unmappedCategories']) > 0) {
            return view('admin/mappingwing', [
                'b2Bs' => $response['b2Bs'],
                'unmappedCategories' => $response['unmappedCategories'],
                'warning' => true
            ]);
        }
        return view('admin/mappingwing', [
            'b2Bs' => $response['b2Bs'],
            'unmappedCategories' => $response['unmappedCategories'],
            'warning' => false
        ]);
    }
    public function mapped()
    {
        $categoryMapping = DB::table('category_mapping AS cm')
            ->join('ownerclan_category AS oc', 'oc.id', '=', 'cm.ownerclan');
        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->get();
        foreach ($b2Bs as $b2B) {
            $b2BEngName = $b2B->name_eng;
            $categoryMapping->whereNot($b2BEngName, null);
        }
        $categoryMapping = $categoryMapping->get();
        return view('admin/mapped', [
            'categoryMapping' => $categoryMapping
        ]);
    }
    public function orderwing(Request $request)
    {
        $controller = new Controller();
        $b2Bs = $controller->getActiveB2Bs();
        $b2BHrefs = array_map(function ($b2B) {
            return $b2B->vendor_href;
        }, $b2Bs->toArray());
        $vendors = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('ps.is_active', 'Y')
            ->get();
        $vendorHrefs = array_map(function ($vendor) {
            return $vendor->vendor_href;
        }, $vendors->toArray());
        $hrefs = array_merge($b2BHrefs, $vendorHrefs);
        return view('admin/orderwing', [
            'b2bs' => $b2Bs,
            'hrefs' => $hrefs,
            'vendors' => $vendors
        ]);
    }
    public function openMarket()
    {
        $vendors = DB::table('vendors as v')
            ->join('minewing_products as mp', 'mp.sellerID', '=', 'v.id')
            ->join('carts as c', 'c.product_id', '=', 'mp.id')
            ->join('orders as o', 'o.cart_id', '=', 'c.id')
            ->whereNotIn('o.type', ['CANCELLED'])
            ->where(function ($query) {
                $query->where('v.type', 'SELLER')
                    ->orWhere('v.id', 5);
            })
            ->select('v.id', 'v.name', 'v.is_active', 'v.type')
            ->groupBy('v.id', 'v.name', 'v.is_active', 'v.type')
            ->orderBy('v.type', 'asc')
            ->get();
        $columns = Schema::getColumnListing('delivery_companies');
        $query = DB::table('delivery_companies');
        foreach ($columns as $column) {
            $query->whereNotNull($column);
        }
        $currentDate = new DateTime();
        $startOn = $currentDate->modify('-1 week')->format('Y-m-d');
        $endOn = $currentDate->modify('+1 week')->format('Y-m-d');
        $deliveryCompanies =  $query->get();
        return view('admin/open_market', [
            'vendors' => $vendors,
            'deliveryCompanies' => $deliveryCompanies,
            'startOn' => $startOn,
            'endOn' => $endOn
        ]);
    }

    public function apiwing(Request $request)
    {
        $sellers = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('ps.has_api', 'Y')
            ->get()
            ->toArray();
        $products = [];
        if (isset($request->sellerID)) {
            $sellerID = $request->sellerID;
            $controller = new Controller();
            $seller = $controller->getSeller($sellerID);
            $table = $seller->name_eng . '_products';
            $products = DB::table($table)
                ->whereNull('updatedAt')
                ->groupBy('categoryName')
                ->get()
                ->toArray();
        }
        return view('admin/apiwing', [
            'products' => $products,
            'sellers' => $sellers
        ]);
    }
    public function newMinewing(Request $request)
    {
        $sellers = DB::table('product_search AS ps')
            ->join('vendors AS v', 'ps.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('ps.is_active', 'Y')
            ->where('ps.has_api', 'N')
            ->where('ps.new_minewing', 'Y')
            ->get();
        return view('admin/new_minewing', [
            'sellers' => $sellers
        ]);
    }
}
