<?php

namespace App\Http\Controllers\APIwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
    }
    public function getTable($sellerID)
    {
        $controller = new Controller();
        $seller = $controller->getSeller($sellerID);
        $sellerEngName = $seller->name_eng;
        $table = $sellerEngName . '_products';
        return $table;
    }
    public function getUnsetCategories(Request $request)
    {
        $sellerID = $request->sellerID;
        $table = $this->getTable($sellerID);
        try {
            $categories = DB::table($table)
                ->whereNull('categoryID')
                ->groupBy('categoryName')
                ->select('categoryName')
                ->get()
                ->pluck('categoryName')
                ->toArray();
            if (empty($categories)) {
                return [
                    'status' => false,
                    'return' => '모든 상품셋의 카테고리가 지정되었습니다.',
                ];
            }
            return [
                'status' => true,
                'return' => $categories,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage(),
            ];
        }
    }
    public function saveProducts(Request $request)
    {
        $sellerID = $request->sellerID;
        $table = $this->getTable($sellerID);
        $productIDs = $request->productIDs;
        $products = DB::table($table)
            ->whereIn('id', $productIDs)
            ->get();
        $ownerclanCategoryID = $request->ownerclanCategoryID;
        try {
            DB::table($table)
                ->whereIn('id', $productIDs)
                ->update([
                    'categoryID' => $ownerclanCategoryID,
                    'updatedAt' => now(),
                ]);
            $insertController = new InsertController();
            $success = 0;
            $error = 0;
            foreach ($products as $product) {
                $response = $insertController->moveProduct($product);
                if ($response['status'] == true) {
                    $success++;
                } else {
                    $error++;
                }
            }
            return [
                'status' => true,
                'return' => '"' . number_format($success) . '개의 상품들 중 ' . number_format($error) . '개를 제외한 모든 상품을 저장했습니다."',
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage(),
            ];
        }
    }
}
