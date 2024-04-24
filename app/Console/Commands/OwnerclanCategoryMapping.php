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
    protected $directory = 'assets/excel/ownerclan-mappings/';

    public function handle()
    {
        $this->setResourceLimits();
        $files = $this->getFiles($this->directory);
        $openMarkets = $this->processFiles($files);
        return $this->edit($openMarkets);
    }

    protected function edit($openMarkets)
    {
        echo "Start updating on DB.";
        foreach ($openMarkets as $openMarket) {
            foreach ($openMarket['vendors'] as $vendor) {
                $ownerclanCode = $openMarket['ownerclanCode'];
                $vendorEngName = $vendor['vendorEngName'];
                $vendorCode = $vendor['code'];
                $this->mapping($ownerclanCode, $vendorEngName, $vendorCode);
            }
        }
    }

    protected function setResourceLimits()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
    }

    protected function getFiles($directory)
    {
        $path = public_path($directory);
        return array_diff(scandir($path), ['.', '..']);
    }

    protected function processFiles(array $files)
    {
        $openMarkets = [];
        foreach ($files as $file) {
            $filePath = public_path($this->directory . $file);
            $extractedData = $this->extractExcel($filePath);
            $openMarkets = array_merge($openMarkets, $extractedData);
        }
        return $openMarkets;
    }

    protected function extractExcel($excelPath)
    {
        $excel = IOFactory::load($excelPath);
        $sheet = $excel->getSheet(0);
        return $this->extractDataFromSheet($sheet);
    }

    protected function extractDataFromSheet($sheet)
    {
        $openMarkets = [];
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($this->shouldSkipRow($index)) {
                continue;
            }
            $openMarket = $this->extractDataFromRow($sheet, $index);
            if (!in_array($openMarket, $openMarkets)) {
                $openMarkets[] = $openMarket;
            }
        }
        return $openMarkets;
    }

    protected function shouldSkipRow($index)
    {
        return $index < 3;
    }

    protected function extractDataFromRow($sheet, $index)
    {
        $ownerclanCode = $sheet->getCell('D' . $index)->getValue();
        $openMarketCodes = $sheet->getCell('F' . $index)->getValue();
        $vendors = $this->processOpenMarketCodes($openMarketCodes);
        return [
            'ownerclanCode' => $ownerclanCode,
            'vendors' => $vendors
        ];
    }
    private function processOpenMarketCodes($openMarketCodes)
    {
        $validOpenMarkets = ['auction', 'gmarket', 'st11', 'interpark', 'coupang'];
        $rows = explode("\n", $openMarketCodes);
        $vendors = [];
        foreach ($rows as $row) {
            $vendorInfo = explode(',', $row);
            $vendorEngName = $vendorInfo[0];
            if (in_array($vendorEngName, $validOpenMarkets)) {
                $vendor = [
                    'vendorEngName' => $vendorEngName,
                    'code' => $vendorInfo[1]
                ];
                $vendors[] = $vendor;
            }
        }
        return $vendors;
    }
    private function mapping($ownerclanCode, $vendorEngName, $vendorCode)
    {
        $ocId = DB::table('ownerclan_category')
            ->where('code', $ownerclanCode)
            ->first(['id']);
        if ($ocId === null) {
            return false;
        }
        $ocId = $ocId->id;
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
        $vcId = $vcId->id;
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
