<?php

namespace App\Http\Controllers\Nalmeokwings\Services;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Partners\Products\ManageController;
use App\Http\Controllers\Partners\Products\PartnerTableController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NalmewingCombineService extends Controller
{
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        $productCodes = $this->getProductCodes();
        $chunkedProductCodes = array_chunk($productCodes, 500);
        $processChunkedProductsErrors = $this->processChunkedProducts($chunkedProductCodes);
        return [
            'status' => true,
            'message' => '총 ' . number_format(count($processChunkedProductsErrors)) . '개의 테이블에 대한 오류가 발생했습니다.',
            'error' => $processChunkedProductsErrors
        ];
    }
    /**
     * @return array
     */
    protected function getProductCodes()
    {
        return DB::table('minewing_products AS mp')
            ->join('vendors AS v', 'v.id', '=', 'mp.sellerID')
            ->where('v.is_godwing', 'Y')
            ->where('mp.partner_id', null)
            ->inRandomOrder()
            ->pluck('mp.productCode')
            ->toArray();
    }
    /**
     * @param array $chunkedProductCodes
     * @return array
     */
    protected function processChunkedProducts(array $chunkedProductCodes)
    {
        $ptc = new PartnerTableController();
        $mc = new ManageController();
        $errors = [];
        foreach ($chunkedProductCodes as $productCodes) {
            $partnerId = $this->getPartnerId();
            DB::beginTransaction();
            $combineResult = $this->combine($partnerId, $productCodes);
            if (!$combineResult['status']) {
                $errors[] = [
                    'error' => $combineResult['error'],
                    'data' => $combineResult['data']
                ];
                DB::rollBack();
                continue;
            }
            $partnerTableName = '갓윙 ' . date('Y-m-d H:i:s');
            $ptcAddResult = $ptc->add($partnerId, $partnerTableName);
            if (!$ptcAddResult['status']) {
                $errors[] = [
                    'error' => $combineResult['error']
                ];
                DB::rollBack();
                continue;
            }
            $partnerTableId = $ptcAddResult['data']['tableId'];
            $partnerTableToken = $this->getPartnerTableToken($partnerTableId);
            $mcAddRequest = new Request([
                'productCodes' => $productCodes,
                'partnerTableToken' => $partnerTableToken
            ]);
            $mcAddResult = $mc->add($mcAddRequest);
            if (!$mcAddResult['status']) {
                $errors[] = $mcAddResult['error'];
                DB::rollBack();
                continue;
            }
            DB::commit();
        }
        return $errors;
    }
    protected function getPartnerTableToken(int $partnerTableId)
    {
        return DB::table('partner_tables')
            ->where('id', $partnerTableId)
            ->value('token');
    }
    /**
     * @return int
     */
    protected function getPartnerId()
    {
        return DB::table('partners AS p')
            ->join('minewing_products AS mp', 'mp.partner_id', '=', 'p.id')
            ->select('p.id', DB::raw('COUNT(*) as product_count'))
            ->groupBy('p.id')
            ->orderBy('product_count')
            ->value('p.id');
    }
    /**
     * @param int $partnerId
     * @param array $productCodes
     * @return array
     */
    protected function combine(int $partnerId, array $productCodes)
    {
        try {
            DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->update([
                    'partner_id' => $partnerId
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage(),
                'data' => [
                    'productCodes' => $productCodes,
                    'partnerId' => $partnerId
                ]
            ];
        }
    }
}
