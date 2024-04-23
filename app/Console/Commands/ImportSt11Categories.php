<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportSt11Categories extends Command
{
    protected $signature = 'app:import-st11-categories';
    protected $description = 'Import categories from ST11 API';

    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $excelPath = public_path('assets/excel/categories/OpenmarketCategory.xlsx');
        $spreadsheet = IOFactory::load($excelPath);
        $sheet = $spreadsheet->getSheet(4);
        $firstRow = true;
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($firstRow) {
                $firstRow = false;
                continue;
            }
            $code = $sheet->getCell('B' . $index)->getValue();
            $name = $sheet->getCell('C' . $index)->getValue();
            $this->store($code, $name);
        }
        echo "success";
    }
    private function store($code, $name)
    {
        DB::table('interpark_category')
            ->insert([
                'code' => $code,
                'name' => $name
            ]);
    }
}
