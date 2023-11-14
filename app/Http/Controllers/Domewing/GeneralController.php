<?php

namespace App\Http\Controllers\Domewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeneralController extends Controller
{
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
                'image' => 'https://cdn1.npcdn.net/image/1668578748821c8af71ec3b8f7d3b415bbad340051.jpg?md5id=9866b8a83d35abdd89ed76d565d71f75&new_width=1150&new_height=2500&w=-62170009200',
                'title' => 'Bed 1',
            ],
            [
                'image' => 'https://image.invaluable.com/housePhotos/clars/88/752188/H0054-L341207066.jpg',
                'title' => 'Drawer 1',
            ],
            [
                'image' => 'https://image.made-in-china.com/202f0j00ecNhLPGbfilF/32mm-S-Speed-Sample-Provided-Furniture-Adjustable-Office-Desk-with-Low-Price.webp',
                'title' => 'Desk 2',
            ],
            [
                'image' => 'https://cdn0.rubylane.com/_podl/item/1518436/TVC3364A/Goodyear-Salesman-Sample-Miniature-Airfoam-Couch-pic-1A-2048%3A10.10-93eee4a0-f.webp',
                'title' => 'Sofa',
            ],
            [
                'image' => 'https://images.squarespace-cdn.com/content/v1/5ca3410da09a7e6e7ed285ea/1653477235891-25RV6FR3GT1EZILWOBX4/ArrowCoffeeTable2.jpg?format=750w',
                'title' => 'Sofa Table',
            ],
            [
                'image' => 'https://www.furniturebrands4u.co.uk/image/cache/catalog/clearance/ercol-clearance/marino-chr-c686-june-23/ercol-marino-chair-c686-320x320h.jpg',
                'title' => 'Chair',
            ],
        ];

        return $product_items;
    }
}
