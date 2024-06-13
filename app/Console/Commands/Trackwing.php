<?php

namespace App\Console\Commands;

use App\Http\Controllers\TrackSoldOutController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Trackwing extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:trackwing';

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
        $this->info('Initiated Trackwing...');
        $this->info('Collecting target vendors...');
        $vendorIds = DB::table('trackwing')
            ->where('is_active', 'ACTIVE')
            ->pluck('vendor_id')
            ->toArray();
        $tsoc = new TrackSoldOutController();
        foreach ($vendorIds as $i => $vendorId) {
            $index = $i + 1;
            $this->info('#' . $index . ': Operating Trackwing for the vendor ID - ' . $vendorId);
            $request = new Request([
                'vendorId' => $vendorId
            ]);
            $tsocMainResult = $tsoc->main($request);
            $this->info('#' . $index . ': Saving the Trackwing result for the vendor ID - ' . $vendorId);
            $tempFileName = date('YmdHis') . '.json';
            file_put_contents(public_path('assets/json/trackwing-results/' . $tempFileName), json_encode($tsocMainResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
        }
        $this->info('Completed Trackwing!');
    }
}
