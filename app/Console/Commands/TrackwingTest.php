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
        $soldOutProducts = $tsoc->main($request);
        $this->info('Trackwing - TEST MODE has completed');
        print_r($soldOutProducts);
    }
}
