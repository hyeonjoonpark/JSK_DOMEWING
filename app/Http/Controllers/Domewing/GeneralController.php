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
            $product_items = $this->getProduct();

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

    public function getProduct(){
        $product_items = [
            [
                'image' => 'https://www.fluidbranding.ie/media/catalog/product/cache/5d8e184087b91b8c6788f82b1ec6b2b1/1/2/12155DUR_Durham-ColourCoat-Mug.jpg',
                'title' => 'Direct Mug',
            ],
            [
                'image' => 'https://marketplace.canva.com/print-mockup/bundle/E2C1MVoPF2T/surface:marketplace/surface:marketplace/EAFI6d4WLz4/1/0/1600w/canva-blue-minimalist-floral-mug-Kfl3ugdeswE.jpg?sig=9c962e01dc53dd8083a1d30fc26686f5&width=400',
                'title' => 'Lovely Cup',
            ],
            [
                'image' => 'https://www.ikea.com/my/en/images/products/faergklar-mug-glossy-beige__1010305_pe828022_s5.jpg?f=xl',
                'title' => 'Simple Cup',
            ],
            [
                'image' => 'https://www.nitori.my/cdn/shop/products/896769801_512x512.jpg?v=1616037232',
                'title' => 'Greeny Looking Cup',
            ],
            [
                'image' => 'https://teapsy.co.uk/cdn/shop/products/FairyMugPackshot_grande.png?v=1674757458',
                'title' => 'Cute Pink Cup',
            ],
            [
                'image' => 'https://theobroma.in/cdn/shop/files/Mug_Green_2.jpg?v=1687724641',
                'title' => 'Customise Drawing Cup',
            ],
        ];

        return $product_items;
    }
}
