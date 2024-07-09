<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CreateNickName extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-nick-name';

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
        $vendors = DB::table('vendors')->get();
        foreach ($vendors as $vendor) {
            $nickName = $this->generateNickName();
            DB::table('vendors')
                ->where('id', $vendor->id)
                ->update(['nickname' => $nickName]);
        }
    }
    private function generateNickName()
    {
        $response = Http::post('https://www.rivestsoft.com/nickname/getRandomNickname.ajax', [
            'format' => 'json',
            'lang' => 'ko',
        ]);
        $data = $response->json();
        $nickName = $data['data'];
        return $nickName;
    }
}
