<?php

namespace App\Http\Controllers\Gdf;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Product\ProcessController;
use App\Http\Controllers\Productwing\SoldOutController;
use Illuminate\Support\Facades\DB;

class GdfController extends Controller
{
    private $controller;
    private $processController;
    private $soldOutController;
    const USER_ID = 15;
    const VENDOR_IDS = [5, 10];
    public function __construct()
    {
        $this->controller = new Controller();
        $this->processController = new ProcessController();
        $this->soldOutController = new SoldOutController();
    }
    public function main()
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $runGdfScriptResult = $this->runGdfScript();
        if ($runGdfScriptResult['status'] === false) {
            return $runGdfScriptResult;
        }
        $productHrefs = $runGdfScriptResult['return'];
        $productCodes = $this->getProductCodes($productHrefs);
        echo $productCodes;
        $b2bs = DB::table('vendors')
            ->whereIn('id', self::VENDOR_IDS)
            ->get();
        foreach ($productCodes as $productCode) {
            foreach ($b2bs as $b2b) {
                $b2bId = $b2b->id;
                $vendorEngName = $b2b->name_eng;
                $account = $this->processController->getAccount(self::USER_ID, $b2bId);
                $username = $account->username;
                $password = $account->password;
                $this->soldOutController->sendSoldOutRequest($productCode, $vendorEngName, $username, $password);
            }
        }
        return $this->inactiveProducts($productCodes);
    }
    private function inactiveProducts($productCodes)
    {
        try {
            DB::table('minewing_products')
                ->whereIn('productCode', $productCodes)
                ->update([
                    'isActive' => 'N',
                    'updatedAt' => now()
                ]);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $e->getMessage()
            ];
        }
    }
    private function runGdfScript()
    {
        $script = public_path('js/gdf/dometopia.js');
        $command = 'node ' . $script;
        exec($command, $output, $resultCode);
        if ($resultCode === 0 && isset($output[0])) {
            $productHrefs = json_decode($output[0], true);
            return [
                'status' => true,
                'return' => $productHrefs
            ];
        } else {
            return [
                'status' => false,
                'return' => '데이터 추출에 실패했습니다.'
            ];
        }
    }
    private function getProductCodes($productHrefs)
    {
        $tmpProductHrefs = [
            "https://dometopia.com/goods/view?no=136568&code=001700150001",
            "https://dometopia.com/goods/view?no=179372&code=00300036",
            "https://dometopia.com/goods/view?no=179373&code=00300036",
            "https://dometopia.com/goods/view?no=172960&code=010200220001",
            "https://dometopia.com/goods/view?no=134787&code=010200220001",
            "https://dometopia.com/goods/view?no=182122&code=01390019",
            "https://dometopia.com/goods/view?no=182120&code=01390019",
            "https://dometopia.com/goods/view?no=182119&code=01390019",
            "https://dometopia.com/goods/view?no=182117&code=01390019",
            "https://dometopia.com/goods/view?no=62704&code=01390019",
            "https://dometopia.com/goods/view?no=62686&code=01390019",
            "https://dometopia.com/goods/view?no=3128&code=002100120008",
            "https://dometopia.com/goods/view?no=182308&code=002100120008",
            "https://dometopia.com/goods/view?no=182309&code=002100120008",
            "https://dometopia.com/goods/view?no=109001&code=002100120009",
            "https://dometopia.com/goods/view?no=132646&code=002100120004",
            "https://dometopia.com/goods/view?no=141959&code=009700070003",
            "https://dometopia.com/goods/view?no=169977&code=001700570004",
            "https://dometopia.com/goods/view?no=160584&code=001700570004",
            "https://dometopia.com/goods/view?no=154000&code=002100130005",
            "https://dometopia.com/goods/view?no=153999&code=002100130005",
            "https://dometopia.com/goods/view?no=153998&code=002100130005",
            "https://dometopia.com/goods/view?no=107306&code=009700150002",
            "https://dometopia.com/goods/view?no=100842&code=00350038",
            "https://dometopia.com/goods/view?no=115426&code=003500380003",
            "https://dometopia.com/goods/view?no=162872&code=001700330001",
            "https://dometopia.com/goods/view?no=6187&code=000700160005",
            "https://dometopia.com/goods/view?no=107997&code=00870019",
            "https://dometopia.com/goods/view?no=92603&code=00870019",
            "https://dometopia.com/goods/view?no=185706&code=004200130007",
            "https://dometopia.com/goods/view?no=186615&code=004200130013",
            "https://dometopia.com/goods/view?no=185957&code=004200130013",
            "https://dometopia.com/goods/view?no=185956&code=004200130013",
            "https://dometopia.com/goods/view?no=185831&code=004200130013",
            "https://dometopia.com/goods/view?no=185830&code=004200130013",
            "https://dometopia.com/goods/view?no=185829&code=004200130013",
            "https://dometopia.com/goods/view?no=185828&code=004200130013",
            "https://dometopia.com/goods/view?no=185780&code=004200130013",
            "https://dometopia.com/goods/view?no=185779&code=004200130013",
            "https://dometopia.com/goods/view?no=185728&code=004200130013",
            "https://dometopia.com/goods/view?no=185727&code=004200130013",
            "https://dometopia.com/goods/view?no=185725&code=004200130013",
            "https://dometopia.com/goods/view?no=185724&code=004200130013",
            "https://dometopia.com/goods/view?no=185723&code=004200130013",
            "https://dometopia.com/goods/view?no=185722&code=004200130013",
            "https://dometopia.com/goods/view?no=185710&code=004200130013",
            "https://dometopia.com/goods/view?no=185709&code=004200130013",
            "https://dometopia.com/goods/view?no=185708&code=004200130013",
            "https://dometopia.com/goods/view?no=185707&code=004200130013",
            "https://dometopia.com/goods/view?no=185705&code=004200130013",
            "https://dometopia.com/goods/view?no=185704&code=004200130013",
        ];
        $mergedProductHrefs = array_merge($productHrefs, $tmpProductHrefs);
        $productCodes = DB::table('minewing_products')
            ->whereIn('productHref', $mergedProductHrefs)
            ->get(['productCode']);
        return $productCodes;
    }
}
