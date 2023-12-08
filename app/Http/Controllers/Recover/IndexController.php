<?php

namespace App\Http\Controllers\Recover;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Recover\ImageController;

class IndexController extends Controller
{
    public function index()
    {
        $products = $this->getProducts();
        $imageController = new ImageController();
        foreach ($products as $product) {
            $newImageFileName = $this->getFileNameFromURL($product->newImageHref);
            $imageController->index($product->productImage, $newImageFileName);
            $newDetailFileNames = $this->extractImageFilenames($product->newProductDetail);
            $imageController->processProductDetail($product->productDetail, $newDetailFileNames);
        }
    }
    public function getProducts()
    {
        $products = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->join('vendors AS v', 'cp.sellerID', '=', 'v.id')
            ->where('cp.isActive', 'Y')
            ->where('up.isActive', 'Y')
            ->get();
        return $products;
    }
    public function getFileNameFromURL($uRL)
    {
        $path = parse_url($uRL, PHP_URL_PATH);
        $filename = basename($path);
        return $filename;
    }
    function extractImageFilenames($html)
    {
        $pattern = '/<img[^>]+src="([^"]+)"/';
        preg_match_all($pattern, $html, $matches);

        $filenames = array();
        foreach ($matches[1] as $url) {
            $filenames[] = basename($url);
        }

        return $filenames;
    }
}
