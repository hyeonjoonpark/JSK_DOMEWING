<?php

namespace App\Http\Controllers\Mappingwing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategorySearchController extends Controller
{
    public function index(Request $request)
    {
        $vendorID = $request->vendorID;
        $keyword = $request->keyword;
        return $this->getSearchResult($vendorID, $keyword);
    }
    public function getSearchResult($vendorID, $keyword)
    {
        $vendor = DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('pr.is_active', 'Y')
            ->where('v.is_active', 'ACTIVE')
            ->where('pr.vendor_id', $vendorID)
            ->first();
        if ($vendor == null) {
            return [
                'status' => false,
                'return' => '업체 정보 불러오기에 실패했습니다. 다시 시도해 주십시오.'
            ];
        }
        $tableName = $vendor->name_eng . '_category';
        if ($vendor->nameType == 'SPLIT') {
            $categories = DB::table($tableName)
                ->select(DB::raw("CONCAT(
                IFNULL(lg, ''), '>', 
                IFNULL(md, ''), '>',
                IFNULL(sm, ''), '>',
                IFNULL(xs, '')
            ) AS name"), 'id')
                ->where('lg', 'like', '%' . $keyword . '%')
                ->orWhere('md', 'like', '%' . $keyword . '%')
                ->orWhere('sm', 'like', '%' . $keyword . '%')
                ->orWhere('xs', 'like', '%' . $keyword . '%')
                ->get();
        } else {
            $categories = DB::table($tableName)
                ->where('name', 'like', '%' . $keyword . '%')
                ->get();
        }
        return $categories;
    }
}
