<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\QueryException;

//vingkong - use this to format date
use Carbon\Carbon;

class AdminController extends Controller
{
    public function dashboard()
    {
        $dashboardController = new DashboardController();
        $posts = $dashboardController->loadPosts();
        return view('admin/dashboard', ['posts' => $posts]);
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
        $remember_token = Auth::user()->remember_token;
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
        $remember_token = Auth::user()->remember_token;
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
        $marginRates = DB::table('margin_rate AS mr')
            ->join('vendors AS v', 'mr.vendorID', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->select('*', 'mr.id AS mrID')
            ->get();
        return view('admin/account-setting', [
            'marginRates' => $marginRates
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
            ->get();
        return view('admin/product_mining', [
            'sellers' => $sellers
        ]);
    }
    public function minewing(Request $request)
    {
        $products = DB::table('minewing_products')
            ->where('isActive', 'Y')
            ->get();
        return view('admin/product_minewing', [
            'products' => $products
        ]);
    }
    public function excelwing(Request $request)
    {
        $ownerclanCategories = DB::table('minewing_products AS mp')
            ->join('ownerclan_category AS oc', 'mp.categoryID', '=', 'oc.id')
            ->groupBy('mp.categoryID', 'oc.id', 'oc.name')
            ->select('oc.id', 'oc.name')
            ->get();

        $products = [];
        $selectedCategory = null; // 초기화
        $categoryID = $request->get('categoryID'); // Laravel Request 객체 사용

        if ($categoryID) {
            $products = DB::table('minewing_products')
                ->where('isActive', 'Y')
                ->where('categoryID', $categoryID)
                ->get();
            $selectedCategory = DB::table("ownerclan_category")
                ->where("id", $categoryID)
                ->first();
        }

        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'pr.vendor_id', '=', 'v.id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->select('v.id', 'v.name')
            ->get();

        return view('admin/excelwing', [
            'ownerclanCategories' => $ownerclanCategories,
            'products' => $products,
            'b2Bs' => $b2Bs,
            'selectedCategory' => $selectedCategory
        ]);
    }
}
