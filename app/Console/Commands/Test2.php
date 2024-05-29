<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class Test2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test2';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display 100 random IDs from the JSON array in result.json';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // result.json 파일 경로 설정
        $filePath = public_path('js/track-sold-out/result.json');

        // 파일이 존재하는지 확인
        if (!File::exists($filePath)) {
            $this->error("The file result.json does not exist.");
            return 1;
        }

        // 파일 내용 읽기
        $fileContents = File::get($filePath);

        // JSON 파싱
        $data = json_decode($fileContents, true);

        // JSON 배열에서 100개의 랜덤 ID 값을 선택하고 출력
        if (is_array($data) && count($data) > 0) {
            $randomIds = [];
            for ($i = 0; $i < 100; $i++) {
                $randomItem = $data[array_rand($data)];
                if (isset($randomItem['id'])) {
                    $randomIds[] = $randomItem['id'];
                } else {
                    $this->error("No id field found in the selected item.");
                }
            }
            $this->info("Random IDs: " . implode(', ', $randomIds));
        } else {
            $this->error("The JSON data is not an array or is empty.");
        }

        return 0;
    }
}
   // if (is_array($data) && count($data) > 0) {
        //     $randomItem = $data[array_rand($data)];
        //     if (isset($randomItem['id'])) {
        //         $this->info("Random ID: " . $randomItem['id']);
        //     } else {
        //         $this->error("No id field found in the selected item.");
        //     }
        // } else {
        //     $this->error("The JSON data is not an array or is empty.");
        // }

        // return 0;
