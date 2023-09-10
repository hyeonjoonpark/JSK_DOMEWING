<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isEmpty;

class ProductSearchController extends Controller
{
    public function productSearch(Request $request)
    {
        $keyword = $request->keyword;
        $validator = Validator::make($request->all(), [
            'keyword' => 'required|min:2|max:10',
            'vendorIds' => 'required|array'
        ], [
            'keyword.required' => '검색 키워드를 기입해주세요.',
            'vendorIds' => '검색 엔진에 활용할 업체들을 선택해주세요.',
            'min' => '검색 키워드는 최소 2글자입니다.',
            'max' => '검색 키워드는 최대 10글자입니다.'
        ]);
        if ($validator->fails()) {
            $data['status'] = -1;
            $data['return'] = $validator->errors()->first();
            return $data;
        }
        $vendorIds = $request->vendorIds;
        $vendors = $this->getVendorList($vendorIds);
        $data['return'] = [];
        set_time_limit(0); // 실행 시간 제한 해제
        foreach ($vendors as $vendor) {
            $nodeScriptPath = public_path('js/search/' . $vendor->name_eng . '.js');
            $command = "node $nodeScriptPath $keyword";
            $output = [];
            exec($command, $output, $returnCode);
            if ($returnCode === 0) {
                $tmpOutputs = json_decode(implode('', $output), true);
                foreach ($tmpOutputs as $tmpOutput) {
                    $data['return'][] = $tmpOutput;
                }
            }
        }
        if (empty($data['return'])) {
            $data['status'] = -1;
            $data['return'] = "검색 결과를 찾을 수 없습니다.";
        } else {
            $data['status'] = 1;
        }
        return $data;
    }
    public function getVendorList(array $vendorIds)
    {
        $vendors = DB::table('product_search')->join('vendors', 'vendors.id', '=', 'product_search.vendor_id')->where('product_search.is_active', 'Y')->whereIn('product_search.id', $vendorIds)->get();
        return $vendors;
    }
}