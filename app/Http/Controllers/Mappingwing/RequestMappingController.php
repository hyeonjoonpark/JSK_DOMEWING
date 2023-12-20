<?php

namespace App\Http\Controllers\Mappingwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequestMappingController extends Controller
{
    public function index(Request $request)
    {
        $ownerclanCategoryID = $request->ownerclanCategoryID;
        $mappedCategories = $request->mappedCategory;
        foreach ($mappedCategories as $mappedCategory) {
            $this->updateCategoryMapping($mappedCategory, $ownerclanCategoryID);
        }
        return [
            'status' => true,
            'return' => '해당 카테고리를 성공적으로 매핑했습니다.'
        ];
    }
    public function updateCategoryMapping($mappedCategory, $ownerclanCategoryID)
    {
        $vendorID = $mappedCategory['vendorID'];
        $categoryID = $mappedCategory['categoryID'];
        $column = $this->getB2BEngName($vendorID);
        DB::table('category_mapping')
            ->where('ownerclan', $ownerclanCategoryID)
            ->update([
                $column->name_eng => $categoryID
            ]);
    }
    public function getB2BEngName($vendorID)
    {
        return DB::table('vendors')
            ->where('id', $vendorID)
            ->select('name_eng')
            ->first();
    }
}
