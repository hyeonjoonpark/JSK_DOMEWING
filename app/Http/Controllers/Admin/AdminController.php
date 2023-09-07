<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\DashboardController;

class AdminController extends Controller
{
    public function dashboard()
    {
        $dashboardController = new DashboardController();
        $posts = $dashboardController->loadPosts();
        return view('admin/dashboard', ['posts' => $posts]);
    }
}