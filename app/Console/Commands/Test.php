<?php

namespace App\Console\Commands;

use App\Http\Controllers\OpenMarkets\Coupang\ApiController;
use App\Http\Controllers\TrackSoldOutController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

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
        $tsoc = new TrackSoldOutController();
        $request = new Request([
            'vendorId' => 39
        ]);
        $soldOutProducts = $tsoc->main($request);
        $sopFile = public_path('js/track-sold-out/result.json');

        if (isset($soldOutProducts['data']) && isset($soldOutProducts['data']['soldOutProducts'])) {
            file_put_contents($sopFile, json_encode($soldOutProducts['data']['soldOutProducts'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->info('success');
        } else {
            $this->error('Data key is missing in the response.');
        }
    }
}
