<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class LotteOnCategoryWithExcel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lotte-on-category-with-excel';
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
        $inputFileName =  'public/assets/excel/categories/LotteOnCategory.xlsx';
        if (!file_exists($inputFileName)) {
            $this->error("File not found: " . $inputFileName);
            return;
        }
        try {
            // 엑셀 파일 읽기
            $spreadsheet = IOFactory::load($inputFileName);
            $sheet = $spreadsheet->getActiveSheet();
            // 2번째 줄부터 데이터 읽기
            $highestRow = $sheet->getHighestRow();
            for ($row = 2; $row <= $highestRow; $row++) {
                $code = $sheet->getCell('A' . $row)->getValue();
                $name = $sheet->getCell('C' . $row)->getValue();
                // 데이터베이스에 데이터 삽입
                DB::table('lotte_on_category')->insert([
                    'code' => $code,
                    'name' => $name,
                ]);
            }
            $this->info("Data imported successfully from $inputFileName!");
        } catch (\Exception $e) {
            $this->error('Error loading file: ' . $e->getMessage());
        }
    }
}
