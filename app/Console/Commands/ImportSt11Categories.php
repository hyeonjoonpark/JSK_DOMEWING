<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use SimpleXMLElement;

class ImportSt11Categories extends Command
{
    protected $signature = 'app:import-st11-categories';
    protected $description = 'Import categories from ST11 API';

    public function handle()
    {
        $response = Http::get('http://api.11st.co.kr/rest/cateservice/category');
        $xmlBody = iconv("EUC-KR", "UTF-8", $response->body()); // 인코딩 변환
        $xml = new SimpleXMLElement($xmlBody);

        $categories = $xml->children('ns2', true)->category;
        $this->processCategories($categories);
    }

    private function processCategories($categories, $path = '')
    {
        foreach ($categories as $category) {
            $currentPath = empty($path) ? (string) $category->dispNm : $path . '>' . (string) $category->dispNm;

            if ((string) $category->leafYn === 'Y') {
                // 최하위 카테고리인 경우 데이터베이스에 저장
                $this->insertCategory($currentPath, (string) $category->dispNo);
            } else {
                // 하위 카테고리가 존재하는 경우 재귀적으로 처리
                if (isset($category->subCategory)) {
                    $subCategories = $category->subCategory->children('ns2', true)->category;
                    $this->processCategories($subCategories, $currentPath);
                }
            }
        }
    }

    private function insertCategory($name, $code)
    {
        DB::table('st11_category')->insert([
            'name' => $name,
            'code' => $code
        ]);
    }
}
