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
        $vendors = DB::table('product_search')->join('vendors', 'vendors.id', '=', 'product_search.vendor_id')->where('product_search.is_active', 'Y')->get();
        return view('admin/product_search', ['vendors' => $vendors]);
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
                    ->where('is_active', 'ACTIVE')
                    ->get();

        foreach ($domains as $domain) {
            $domain->formatted_created_at = Carbon::parse($domain->created_at)->format('d M Y');
        }

        return view('admin/cms_dashboard', ['domains' => $domains]);
    }

    public function registerDomain(Request $request)
    {
        $companyName = $request->input('companyName');
        $domainName = $request->input('domainName');

        // Check if the domain already exists and is active
        $existingDomain = DB::table('cms_domain')
                            ->where('domain_name', $domainName)
                            ->where('is_active', 'ACTIVE')
                            ->first();

        if ($existingDomain) {
            return response()->json([
                'status' => -1,
                'message' => "Domain name already exists and is active"
            ]);
        }

        // Add your code to store the domain in the database here

        $saveDomain = DB::table('cms_domain')->insert([
            'company_name' => $companyName,
            'domain_name' => $domainName,
            'created_at' => now(),
        ]);

        if($saveDomain){
            return response()->json([
                'status' => 1,
                'message' => "Domain saved successfully"
            ]);
        }else{
            return response()->json([
                'status' => -1,
                'message' => "Opps, something went wrong. Please try again later."
            ]);
        }
    }

    public function contentManagementSystem(Request $request)
    {
        return view('admin/content_management_system');
    }

    public function uploadImageBanner(Request $request)
    {
        $image = $request->file('file');

        // Check if a file was uploaded
        if ($image) {

            $ext = $image->getClientOriginalExtension();
            $imageName = "IMG" . date('YmdHis') . "." . $ext;

            // Move the uploaded file to the public library directory
            $image->move(public_path('library'), $imageName);

            return redirect()->to('/admin/content_management_system')->with('success', 'File uploaded successfully!');

        } else {
            return redirect()->to('/admin/content_management_system')->with('error', 'No file uploaded.');
        }
    }
}
