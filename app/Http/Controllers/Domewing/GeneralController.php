<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GeneralController extends Controller
{
    public function loadBusinessPage(Request $request){

        $about_items = $this->getAboutItem();
        $partnerships = $this->getPartnership();

        return view('domewing.welcome', [
            'about_items'=>$about_items,
            'partnerships'=>$partnerships
        ]);
    }

    public function loadDomain(Request $request, $domain_name){

        $check_domain=DB::table('cms_domain')
                            ->where('domain_name', $domain_name)
                            ->where('is_active', 'ACTIVE')
                            ->first();

        if($check_domain){
            $image_banners = DB::table('cms_domain')->join('image_banner', 'cms_domain.domain_id', '=', 'image_banner.domain_id')->where('domain_name', $domain_name)->where('status', 'ACTIVE')->where('status', '!=', 'INACTIVE')->get();

            $theme_color = DB::table('cms_domain')
            ->join('theme_color', 'cms_domain.domain_id', '=', 'theme_color.domain_id')
            ->where('cms_domain.domain_name', $domain_name)
            ->select('theme_color.color_code')
            ->first();

            $categoriesTop = $this->getCategory();
            $product_items = $this->getProducts($check_domain->domain_id);

            return view('domewing.product_catalog', [
                'theme_color' => $theme_color,
                'image_banners' => $image_banners,
                'categoriesTop' => $categoriesTop,
                'product_items' => $product_items,
            ]);

        }else{
            return redirect('/domewing')->with('error', 'Domain not found');
        }
    }

    public function getProducts($id){
        $product_items = DB::table('cms_domain')
                            ->join('collected_products', 'cms_domain.user_id', '=', 'collected_products.userId')
                            ->join('uploaded_products', 'collected_products.id', '=', 'uploaded_products.productId')
                            ->where('cms_domain.domain_id', $id)
                            ->where('uploaded_products.isActive', 'Y')
                            ->select('uploaded_products.id as upload_id','collected_products.id', 'collected_products.productName as title', 'uploaded_products.newImageHref as image')
                            ->limit(10)
                            ->get();

        return $product_items;
    }

    public function getCategory(){
        $categoriesTop = [
            [
                'image' => 'media/Asset_Category_Baby_Product.svg',
                'title' => 'Baby Product',
            ],
            [
                'image' => 'media/Asset_Category_Cosmetics.svg',
                'title' => 'Cosmetics',
            ],
            [
                'image' => 'media/Asset_Category_Electronics.svg',
                'title' => 'Electronics',
            ],
            [
                'image' => 'media/Asset_Category_Fashion_Accessories.svg',
                'title' => 'Fashion Accessories',
            ],
            [
                'image' => 'media/Asset_Category_Fashion_Apparels.svg',
                'title' => 'Fashion Apparels',
            ],
            [
                'image' => 'media/Asset_Category_Food.svg',
                'title' => 'Food',
            ],
            [
                'image' => 'media/Asset_Category_Furniture.svg',
                'title' => 'Furniture',
            ],
            [
                'image' => 'media/Asset_Category_Health.svg',
                'title' => 'Health',
            ],
            [
                'image' => 'media/Asset_Category_Pet_Products.svg',
                'title' => 'Pet Products',
            ],
            [
                'image' => 'media/Asset_Category_Sports.svg',
                'title' => 'Sports',
            ],
        ];

        return $categoriesTop;
    }

    public function getAboutItem(){
        $about_items = [
            [
                'image' => 'media\Asset_About_Product_Search.svg',
                'title' => 'Comprehensive Product Search',
                'description' => 'Presenting users the best registered suppliers through keyword search results in the search engine.'
            ],
            [
                'image' => 'media\Asset_About_Tracking.svg',
                'title' => 'Order Tracking and Management',
                'description' => 'Manage and track orders by monitoring the order status of products listed by multiple wholesale businesses.'
            ],
            [
                'image' => 'media\Asset_About_Product_Listing.svg',
                'title' => 'Individual Product Listing',
                'description' => 'Listing and showcasing individual products to all wholesale businesses within the platform.'
            ],
            [
                'image' => 'media\Asset_About_Dashboard.svg',
                'title' => 'Dashboard with Analytical Charts',
                'description' => 'Get access to various analytical charts that offer insights into key business metrics, helping users make informed decisions'
            ],
            [
                'image' => 'media\Asset_About_Bulk_Listing.svg',
                'title' => 'Bulk Product Listing',
                'description' => 'Suppliers can efficiently upload and publish multiple products to all wholesale businesses     within the platform'
            ],
        ];

        return $about_items;
    }

    public function getPartnership(){
        $partnerships = [
            'https://images.pexels.com/photos/2235130/pexels-photo-2235130.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/430205/pexels-photo-430205.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/1162361/pexels-photo-1162361.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/176837/pexels-photo-176837.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/1365795/pexels-photo-1365795.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/351263/pexels-photo-351263.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/3081173/pexels-photo-3081173.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/4480519/pexels-photo-4480519.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/1031955/pexels-photo-1031955.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/5359802/pexels-photo-5359802.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/4389665/pexels-photo-4389665.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/9978709/pexels-photo-9978709.jpeg?auto=compress&cs=tinysrgb&w=1600',
            'https://images.pexels.com/photos/18868628/pexels-photo-18868628/free-photo-of-mugs-with-logo.jpeg?auto=compress&cs=tinysrgb&w=1600',

        ];

        return $partnerships;
    }

    public function contactUs(Request $request){
        $validator = Validator::make($request->all(),[
            'title' => 'required|min:2',
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|email',
            'phoneCodeHidden' => 'required',
            'phoneNumber' => 'required|min:6',
            'textMessage' => 'required',
        ], [
            'required' => 'This field is required.',
            'title.min' => 'Title must have at least 2 characters.',
            'email.email' => 'Please provide a valid email address.',
            'phoneCodeHidden.required' => 'Please Select a Phone Code.',
            'phoneNumber.min' => 'Phone number must have at least 6 numbers.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput()->with('errorsOccurred', true);
        }

        $title = $request->input('title');
        $fname = $request->input('fname');
        $lname = $request->input('lname');
        $email = $request->input('email');
        $phoneCodeHidden = $request->input('phoneCodeHidden');
        $phoneNumber = $request->input('phoneNumber');
        $text = $request->input('textMessage');

        $requestData = [
            'title' => $title,
            'first_name' => $fname,
            'last_name' => $lname,
            'email' => $email,
            'phone_code' => $phoneCodeHidden,
            'phone_number' => $phoneNumber,
            'message'=>$text,
            'created_at'=>now(),
        ];

        try{
            $submit = DB::table('contact-us')->insert($requestData);

            if($submit){
                return redirect()->back()->with('success', 'Message Sent Successfully!');
            }else{
                return redirect()->back()->with('error', 'Submission Failed. Please Try Again Later.');
            }
        }catch (Exception $e){
            return redirect()->back()->with('error', 'Submission Failed. Please Try Again Later.');
        }

    }

    public function searchProducts(Request $request){
        return view('domewing.search_result');
    }
}
