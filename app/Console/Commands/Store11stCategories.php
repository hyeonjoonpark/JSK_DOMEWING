<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class Store11stCategories extends Command
{
    protected $signature = 'app:create-11st-category';
    protected $description = 'Fetches and stores categories from 11st API';

    public function handle()
    {
        $url = 'http://api.11st.co.kr/rest/cateservice/category'; // Use an environment variable
        $response = Http::get($url);

        if ($response->successful()) {
            try {
                $categories = $this->parseCategories($response->body());
                $this->insertCategories($categories);
                $this->info('Categories have been successfully fetched and inserted.');
            } catch (\Exception $e) {
                $this->error("Parsing or insertion failed: " . $e->getMessage());
            }
        } else {
            $this->error("Failed to fetch categories: " . $response->status());
        }
    }

    private function parseCategories($xml)
    {
        $categories = [];
        $xml = new SimpleXMLElement($xml);
        $xml->registerXPathNamespace('ns2', 'http://api.11st.co.kr/rest/cateservice');  // 네임스페이스 등록

        $this->buildCategoryPath($xml->xpath('//ns2:category'), $categories);  // XPath를 사용하여 ns2:category 요소 선택
        return $categories;
    }

    private function buildCategoryPath($xmlElements, &$categories, $path = '')
    {
        foreach ($xmlElements as $category) {
            $newPath = $path . ($path ? ' > ' : '') . $category->dispNm;
            if ($category->leafYn == 'Y') { // 최하위 노드만 추가
                $categories[] = [
                    'name' => $newPath,
                    'code' => (string)$category->dispNo
                ];
            } else {
                // 재귀적으로 하위 카테고리 탐색
                $this->buildCategoryPath($category->xpath('.//ns2:category'), $categories, $newPath);
            }
        }
    }

    private function insertCategories($categories)
    {
        try {
            print_r($categories);
            DB::table('st11_category')->upsert($categories, ['code'], ['name']); // Use upsert for performance improvement
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
