<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductDetailController extends Controller
{
    public function index(Request $request)
    {
        $platformStr = $request->platform;
        $vendor = DB::table('vendors')->where('name', $platformStr)->select('name_eng')->first();
        $href = $request->href;
        $script = public_path('js/details/' . $vendor->name_eng . '.js');
        $command = "node " . escapeshellarg($script) . " " . escapeshellarg($href);
        try {
            set_time_limit(0);
            exec($command, $output, $returnCode);
            $data = json_decode($output[0], true);
            return $this->getResponseData(1, $data);
        } catch (\Exception $e) {
            return $this->getResponseData(-1, $e->getMessage());
        }
    }
    public function bulk(Request $request)
    {
        $products = $request->products;
        $processedProducts = [];
        foreach ($products as $product) {
            $productDetail = "";
            $vendor = DB::table('vendors')->where('name', $product['platform'])->select('name_eng')->first();
            $href = $product['href'];
            $script = public_path('js/details/' . $vendor->name_eng . '.js');
            $command = "node " . escapeshellarg($script) . " " . escapeshellarg($href);
            set_time_limit(0);
            exec($command, $output, $returnCode);
            $productDetail = json_decode($output[0], true);
            $processedProduct = [
                'name' => $product['name'],
                'price' => $product['price'],
                'href' => $product['href'],
                'detail' => $productDetail['productDetail'],
                'image' => $product['image'],
                'platform' => $product['platform']
            ];
            $processedProducts[] = $processedProduct;
        }
        return $this->getResponseData(1, $processedProducts);
    }
    protected function getResponseData($status, $return)
    {
        return ['status' => $status, 'return' => $return];
    }
}
