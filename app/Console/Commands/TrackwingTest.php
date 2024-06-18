<?php

namespace App\Console\Commands;

use App\Http\Controllers\TrackSoldOutController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TrackwingTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trackwing-test {vendorId}';

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
        $vendorId = $this->argument('vendorId');
        $this->info('Trackwing - TEST MODE has initiated...');
        $this->info('Target vendor ID: ' . $vendorId);
        $tsoc = new TrackSoldOutController();
        $request = new Request([
            'vendorId' => $vendorId
        ]);
        $this->info('Operating Trackwing main engine...');
        $soldOutProductIds = $tsoc->test($request);
        $soldOutProductIdsFilePath = public_path('/assets/json/trackwing-results/test/');
        if (!is_dir($soldOutProductIdsFilePath)) {
            mkdir($soldOutProductIdsFilePath);
        }
        file_put_contents($soldOutProductIdsFilePath, json_encode($soldOutProductIds, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        $this->info('Trackwing - TEST MODE has completed');
    }
}
