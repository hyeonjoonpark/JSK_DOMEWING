<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class CreateCategoryTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-category-table {vendorId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '새로운 B2B 카테고리셋 테이블을 생성하는 커맨드입니다.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $vendorId = $this->argument('vendorId');
        $vendor = $this->getVendor($vendorId);
        $vendorEngName = $vendor->name_eng;
        $excelFile = public_path('assets/excel/categories/' . $vendorEngName . '.xlsx');
        $tableName = $vendorEngName . '_category';
        $categoryType = $vendor->nameType;
        $rows = $this->extractExcel($excelFile);
        $errorRows = [];
        foreach ($rows as $row) {
            if ($categoryType === 'COMBINED') {
                $insertCategories = $this->combinedCategories($tableName, $row);
            } else {
                $insertCategories = $this->splittedCategories($tableName, $row);
            }
            if ($insertCategories['status'] === false) {
                $errorRows[] = $insertCategories['return'];
            }
        }
        print_r($errorRows);
    }
    private function extractExcel($excelFile)
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($excelFile);
        $sheet = $spreadsheet->getActiveSheet(0);
        $rows = $sheet->toArray();
        return $rows;
    }
    private function splittedCategories($tableName, $row)
    {
        $code = $row[0] ? $row[0] : null;
        $lg = isset($row[1]) ? $row[1] : null;
        $md = isset($row[2]) ? $row[2] : null;
        $sm = isset($row[3]) ? $row[3] : null;
        $xs = isset($row[4]) ? $row[4] : null;
        try {
            DB::table($tableName)
                ->insert([
                    'code' => $code,
                    'lg' => $lg,
                    'md' => $md,
                    'sm' => $sm,
                    'xs' => $xs
                ]);
            return [
                'status' => true
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $row
            ];
        }
    }
    private function combinedCategories($tableName, $row)
    {
        try {
            $categoryCode = $row[0]; // 첫 번째 열이 카테고리 코드
            $categoryName = $row[1]; // 두 번째 열이 카테고리 이름
            DB::insert('INSERT INTO ' . $tableName . ' (code, name)
            VALUES (?, ?)', [$categoryCode, $categoryName]);
            return [
                'status' => true,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'return' => $row
            ];
        }
    }
    private function getVendor($vendorId)
    {
        return DB::table('product_register AS pr')
            ->join('vendors AS v', 'v.id', '=', 'pr.vendor_id')
            ->where('v.id', $vendorId)
            ->first();
    }
}
