<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\FormController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DOMDocument;
use DOMXPath;

class TestController extends Controller
{
    public function index()
    {
        $uploadedProducts = $this->getUploadedProducts();
        $fc = new FormController();
        $response = $fc->domeggook($uploadedProducts, 15);
        return $response;
    }

    private function getUploadedProducts()
    {
        return DB::table('uploaded_products')
            ->join('collected_products', 'uploaded_products.productId', '=', 'collected_products.id')
            ->select('*', 'uploaded_products.id as uploadedProductID')
            ->get();
    }

    private function processProductDetail($productDetail)
    {
        $doc = $this->loadHtmlDocument($productDetail);
        $images = $this->extractImages($doc);

        return $this->createImageHtml($images);
    }

    private function loadHtmlDocument($htmlContent)
    {
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($htmlContent);
        libxml_clear_errors();

        return $doc;
    }

    private function extractImages(DOMDocument $doc)
    {
        $xpath = new DOMXPath($doc);
        return $xpath->query("//img");
    }

    private function createImageHtml($images)
    {
        $html = '<center>';
        foreach ($images as $img) {
            $html .= $this->getImageHtml($img);
        }
        $html .= '</center>';

        return $html;
    }

    private function getImageHtml($img)
    {
        $src = $img->getAttribute('src');
        $imageContent = file_get_contents($src);
        $imageExtension = pathinfo($src, PATHINFO_EXTENSION);
        $imageName = uniqid() . '.' . $imageExtension;
        $savePath = public_path('images/product/details') . '/' . $imageName;
        file_put_contents($savePath, $imageContent);
        $tmpSrc = "https://www.sellwing.kr/images/product/" . $imageName;

        return '<img src="' . $tmpSrc . '" alt="">';
    }
    // public function index()
    // {
    //     $products = DB::table('collected_products')
    //         ->where('id', '>=', 1100)
    //         ->where('id', '<=', 4039)
    //         ->get();
    //     foreach ($products as $product) {
    //         $output = [];
    //         $parsedUrl = parse_url($product->productHref);
    //         $domain = $parsedUrl['host'];
    //         // 'www.' 제거
    //         $domain = str_replace('www.', '', $domain);
    //         if ($domain == 'dometopia.com') {
    //             $domain = 'dometopia.js';
    //         }
    //         if ($domain == 'babonara.co.kr') {
    //             $domain = 'babonara.js';
    //         }
    //         if ($domain == 'metaldiy.com') {
    //             $domain = 'metaldiy.js';
    //         }
    //         if ($domain == 'domeggook.com') {
    //             $domain = 'domemedb.js';
    //         }
    //         $script = public_path('js/price/' . $domain);
    //         $command = "node " . escapeshellarg($script) . " " . escapeshellarg($product->productHref);
    //         set_time_limit(0);
    //         exec($command, $output, $returnCode);
    //         if ($returnCode == 0 && isset($output[0])) {
    //             // 성공적으로 처리된 경우, 상품 정보를 추가하고 반복문을 종료
    //             $productPrice = json_decode($output[0], true);
    //             DB::table('collected_products')->where('id', $product->id)->update([
    //                 'productPrice' => $productPrice['productPrice'],
    //                 'updatedAt' => now()
    //             ]);
    //         }
    //         echo "Success";
    //     }
    // }
}
