<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class OwnerclanCategoryMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:ownerclan-category-mapping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $directory = 'assets/excel/ownerclan-mappings/';
        $files = scandir(public_path($directory));
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                $extractExcelResult = $this->extractExcel(public_path($directory . $file));
            }
        }
    }
    private function extractExcel($excelPath)
    {
        $excel = IOFactory::load($excelPath);
        $sheet = $excel->getSheet(0);
        $openMarkets = [];
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($index < 3) {
                continue;
            }
            $ownerclanCode = $sheet->getCell('D' . $index)->getValue();
            $openMarketCodes = $sheet->getCell('F' . $index)->getValue();
            $vendors = $this->processOpenMarketCodes();
            $openMarket = [
                'ownerclanCode' => $ownerclanCode,
                'openMarketCodes' => $openMarketCodes
            ];
            $openMarkets[] = $openMarket;
        }
    }
    private function processOpenMarketCodes($openMarketCodes)
    {
        $validOpenMarkets = ['auction', 'gmarket', 'st11', 'interpark', 'coupang'];
        $rows = explode("\n", $openMarketCodes);
        foreach ($rows as $row) {
            $vendorEngName = $row[0];
            $vendors = [];
            if (in_array($vendorEngName, $validOpenMarkets)) {
                $vendor = [
                    'vendorEngName' => $vendorEngName,
                    'code' => $row[1]
                ];
                $vendors[] = $vendor;
            }
        }
        return $vendors;
    }
    // private function processOpenMarkets()
    // {
    //     $validOpenMarkets = ['auction', 'gmarket', 'st11', 'interpark', 'coupang'];
    //     $processedCodes = explode("\n", $openMarketCodes);
    //     foreach ($processedCodes as $codeRow) {
    //         $openMarkets = explode(",", $codeRow);
    //         foreach ($openMarkets as $om) {
    //             $vendorEngName = $om[0];
    //             if (in_array($vendorEngName, $validOpenMarkets)) {
    //                 $vendorCode = $om[1];
    //                 $mappingResult = $this->mapping($ownerclanCode, $vendorEngName, $vendorCode);
    //                 if ($mappingResult === false) {
    //                     continue;
    //                 }
    //                 if ($mappingResult['status'] === false) {
    //                     continue;
    //                 }
    //             }
    //         }
    //     }
    // }
    private function mapping($ownerclanCode, $vendorEngName, $vendorCode)
    {
        $ocId = DB::table('ownerclan_category')
            ->where('code', $ownerclanCode)
            ->first(['id']);
        if ($ocId === null) {
            return false;
        }
        $ocExists = DB::table('category_mapping')
            ->where("ownerclan", $ocId)
            ->exists();
        if ($ocExists === false) {
            return false;
        }
        $vcId = DB::table($vendorEngName . '_category')
            ->where('code', $vendorCode)
            ->first(['id']);
        if ($vcId === null) {
            return false;
        }
        return $this->update($ocId, $vendorEngName, $vcId);
    }
    private function update($ocId, $vendorEngName, $vcId)
    {
        try {
            DB::table('category_mapping')
                ->where("ownerclan", $ocId)
                ->update([
                    $vendorEngName => $vcId
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
