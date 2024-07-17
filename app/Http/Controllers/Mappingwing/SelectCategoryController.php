<?php

namespace App\Http\Controllers\Mappingwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectCategoryController extends Controller
{
    public function mappedRequest(Request $request)
    {
        $ownerclanCategoryID = $request->ownerclanCategoryID;
        $isExist = DB::table('category_mapping')
            ->where('ownerclan', $ownerclanCategoryID)
            ->exists();
        if (!$isExist) {
            return [
                'status' => false,
                'return' => '올바른 카테고리를 선택해주세요.'
            ];
        }
        $mappedCategory = $this->mappedCategory($ownerclanCategoryID);
        return [
            'status' => true,
            'return' => $mappedCategory
        ];
    }
    public function mappedCategory($ownerclanCategoryID)
    {
        $b2Bs = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.is_active', 'Y')
            ->whereNot('v.id', '5')
            ->get();
        $row = DB::table('category_mapping')
            ->where('ownerclan', $ownerclanCategoryID)
            ->first();
        $mappedCategory = [];
        foreach ($b2Bs as $b2B) {
            $b2BEngName = $b2B->name_eng;
            $categoryTable = $b2BEngName . '_category';
            $b2BID = $b2B->vendor_id;
            $b2BName = $b2B->name;
            $categoryID = $row->$b2BEngName;
            if ($b2B->nameType == 'SPLIT') {
                $categoryName = DB::table($categoryTable)
                    ->where('id', $categoryID)
                    ->select(DB::raw("CONCAT(
                                        IFNULL(lg, ''), '>',
                                        IFNULL(md, ''), '>',
                                        IFNULL(sm, ''), '>',
                                        IFNULL(xs, '')
                                    ) AS name"))
                    ->first()
                    ->name;
            } else {
                $categoryName = DB::table($categoryTable)
                    ->where('id', $categoryID)
                    ->first()
                    ->name;
            }
            $mapped = [
                'b2BID' => $b2BID,
                'b2BName' => $b2BName,
                'categoryID' => $categoryID,
                'categoryName' => $categoryName
            ];
            $mappedCategory[] = $mapped;
        }
        return $mappedCategory;
    }
    public function request(Request $request)
    {
        $ownerclanCategoryID = $request->ownerclanCategoryID;
        $isExist = DB::table('category_mapping')
            ->where('ownerclan', $ownerclanCategoryID)
            ->exists();
        if (!$isExist) {
            return [
                'status' => false,
                'return' => '올바른 카테고리를 선택해주세요.'
            ];
        }
        $unmappedB2B = $this->index($ownerclanCategoryID);
        return $unmappedB2B;
    }
    public function index($ownerclanCategoryID)
    {
        try {
            $b2Bs = DB::table('product_register AS pr')
                ->join('vendors AS v', 'pr.vendor_id', '=', 'v.id')
                ->where('pr.is_active', 'Y')
                ->where('v.is_active', 'ACTIVE')
                ->where('v.type', 'OPEN_MARKET')
                ->get();
            $row = DB::table('category_mapping')
                ->where('category_mapping.ownerclan', $ownerclanCategoryID)
                ->first();
            $unmappedB2B = [];
            foreach ($b2Bs as $b2B) {
                $b2BEngName = $b2B->name_eng;
                if ($row->$b2BEngName == null)
                    $unmappedB2B[] = [
                        'vendorID' => $b2B->vendor_id,
                        'name' => $b2B->name
                    ];
            }
            return [
                'status' => true,
                'return' => $unmappedB2B
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
}
