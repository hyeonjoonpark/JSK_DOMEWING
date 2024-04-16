<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportSt11Categories extends Command
{
    protected $signature = 'app:import-st11-categories';
    protected $description = 'Import categories from ST11 API';

    const BASE_ENDPOINT = 'http://api.11st.co.kr/rest/cateservice/category';

    public function handle()
    {
        $this->info('Starting import of categories...');
        $categories = $this->fetchCategories();

        if ($categories) {
            $this->importCategories($categories);
        } else {
            $this->error('Failed to fetch or parse categories.');
        }
    }

    private function fetchCategories($parentDispNo = null)
    {
        $params = $parentDispNo ? ['parentDispNo' => $parentDispNo] : [];
        $response = Http::get(self::BASE_ENDPOINT, $params);
        if (!$response->successful()) {
            $this->error('API request failed with status: ' . $response->status() . ' and body: ' . $response->body());
            return null;
        }


        if ($response->successful()) {
            return simplexml_load_string($response->body());
        }

        $this->error('API request failed: ' . $response->status());
        return null;
    }

    private function importCategories($categories, $parentName = '')
    {
        foreach ($categories->category as $category) {
            $fullName = trim($parentName . '>' . $category->dispNm, '>');
            $this->info('Processing category: ' . $fullName);

            if ($category->leafYn == 'Y') {
                $this->insertCategory($fullName, $category->dispNo);
            } else {
                $childCategories = $this->fetchCategories($category->dispNo);
                if ($childCategories) {
                    $this->importCategories($childCategories, $fullName);
                }
            }
        }
    }

    private function insertCategory($name, $code)
    {
        $result = DB::table('st11_category')->insert([
            'name' => $name,
            'code' => (string)$code
        ]);

        if ($result) {
            $this->info('Inserted: ' . $name);
        } else {
            $this->error('Insert failed for: ' . $name);
        }
    }
}
