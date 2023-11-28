<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
    public function loadAccountSettings(Request $request){
        return view('domewing.user_details');
    }

    public function loadBusinessPage(Request $request){
        return view('domewing.welcome');
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
}
