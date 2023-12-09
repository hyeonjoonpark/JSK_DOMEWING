<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\FormController;
use App\Http\Controllers\Product\NameController;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use DOMDocument;
use DOMXPath;
use Exception;
use Str;

class TestController extends Controller
{
    public function index()
    {
        $products = $this->getProducts();
        $nameController = new NameController();
        foreach ($products as $product) {
            $newProductName = $nameController->index($product->productName);
            $this->updateNewProductName($product, $newProductName);
        }
        return true;
    }
    public function getProducts()
    {
        $products = DB::table('uploaded_products AS up')
            ->join('collected_products AS cp', 'up.productId', '=', 'cp.id')
            ->where('up.isActive', 'Y')
            ->where('cp.isActive', 'Y')
            ->select('*', 'up.id AS upID')
            ->get();
        return $products;
    }
    public function updateNewProductName($product, $newProductName)
    {
        try {
            DB::table('uploaded_products')
                ->where('id', $product->upID)
                ->update([
                    'newProductName' => $newProductName
                ]);
            return true;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}