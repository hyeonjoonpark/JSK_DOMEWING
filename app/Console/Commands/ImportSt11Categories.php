<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;
use SimpleXMLElement;

class ImportSt11Categories extends Command
{
    protected $signature = 'app:import-st11-categories';
    protected $description = 'Import categories from ST11 API';

    public function handle()
    {
        $response = Http::get('http://api.11st.co.kr/rest/cateservice/category');
        $xml = new SimpleXMLElement($response->body());
        $categories = $xml->categorys->category;

        $this->storeCategories($categories);
    }

    private function storeCategories($categories, $path = '')
    {
        foreach ($categories as $category) {
            $currentPath = $path === '' ? $category->dispNm : $path . '>' . $category->dispNm;
            if ($category->leafYn == 'Y') {
                // It's a leaf node, store in the database
                DB::table('st11_category')->insert([
                    'name' => $currentPath,
                    'code' => (string) $category->dispNo
                ]);
            } else {
                // Not a leaf node, recurse
                $subcategories = $this->fetchSubCategories($category->dispNo);
                $this->storeCategories($subcategories, $currentPath);
            }
        }
    }

    private function fetchSubCategories($dispNo)
    {
        $response = Http::get("http://api.11st.co.kr/rest/cateservice/category?parentDispNo={$dispNo}");
        $xml = new SimpleXMLElement($response->body());
        return $xml->categorys->category;
    }
}
