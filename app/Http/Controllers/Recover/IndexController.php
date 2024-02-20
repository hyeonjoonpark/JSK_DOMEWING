<?php

namespace App\Http\Controllers\Recover;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Exception;

class IndexController extends Controller
{
    public function index()
    {
        set_time_limit(0);
        $products = $this->getProducts(3);
        $errors = $this->processProducts($products);
        return $errors;
    }

    private function processProducts($products)
    {
        $errors = [];
        foreach ($products as $product) {
            $response = $this->getFileNames($product);
            if (!$response['status']) {
                $productHref = $product->productHref;
                $scrapeProductImageFiles = $this->scrapeProductImageFiles($productHref);
                if (!$scrapeProductImageFiles['status']) {
                    $errors[] = $productHref;
                    continue;
                }
                $this->saveImages($scrapeProductImageFiles['return']);
            }
        }
        return $errors;
    }

    private function scrapeProductImageFiles($productHref)
    {
        $scriptPath = public_path('js/recovery/dometopia.js');
        $command = "node " . escapeshellarg($scriptPath) . " " . escapeshellarg($productHref);
        try {
            exec($command, $output, $returnCode);
            if ($returnCode !== 0 || !isset($output[0])) {
                throw new Exception("Error executing command for product: $productHref");
            }
            $result = json_decode($output[0], true);
            if ($result === false || $result === 'false') {
                return ['status' => true, 'return' => $productHref];
            }
            return ['status' => true, 'return' => $result];
        } catch (Exception $e) {
            return ['status' => false, 'return' => $e->getMessage() . ' ' . $productHref];
        }
    }

    private function getFileNames($product)
    {
        $productImage = $product->productImage;
        $productImageFileName = basename($productImage);
        $productDetail = $product->productDetail;
        $productDetailFileNames = $this->extractImageFilenames($productDetail);

        $productImageDir = public_path('images/CDN/product/' . $productImageFileName);
        if (!is_file($productImageDir)) {
            return ['status' => false, 'return' => ['productImage' => $productImageFileName, 'productDetailImages' => $productDetailFileNames]];
        }
        return ['status' => true];
    }

    private function saveImages($productContents)
    {
        $productImageFile = $productContents['productImage'];
        $productDetailFiles = $productContents['productDetail'];

        $this->processImage($productImageFile, 'product');
        foreach ($productDetailFiles as $productDetailFile) {
            $this->processImage($productDetailFile, 'detail');
        }
    }

    private function processImage($imageUrl, $path)
    {
        $newWidth = 1000;
        $newHeight = 1000;
        $savePath = public_path('images/CDN/' . $path . '/');
        try {
            $image = ($path === 'product') ? Image::make($imageUrl)->resize($newWidth, $newHeight) : Image::make($imageUrl);
            $savePathWithFile = $savePath . basename($imageUrl);
            $image->save($savePathWithFile);
        } catch (Exception $e) {
            throw new Exception("Error processing image: " . $e->getMessage());
        }
    }

    private function extractImageFilenames($html)
    {
        $pattern = '/<img[^>]+src="([^"]+)"/';
        preg_match_all($pattern, $html, $matches);

        $filenames = [];
        foreach ($matches[1] as $index => $url) {
            if ($index !== 0) {
                $filenames[] = basename($url);
            }
        }
        return $filenames;
    }

    private function getProducts($sellerID)
    {
        return DB::table('minewing_products')
            ->where('sellerID', $sellerID)
            ->where('isActive', 'Y')
            ->select('productImage', 'productDetail', 'productHref')
            ->get();
    }
}
