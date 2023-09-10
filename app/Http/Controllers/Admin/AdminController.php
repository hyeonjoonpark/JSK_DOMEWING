<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Support\Facades\DB;

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
        $vendors = $this->getAllVendors();
        return view('admin/product_search', ['vendors' => $vendors]);
    }
    public function getAllVendors()
    {
        $vendors = DB::table('vendors')->where('is_active', 'ACTIVE')->get();
        return $vendors;
    }
}