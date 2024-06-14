<?php

namespace App\Console\Commands;

use App\Http\Controllers\TrackSoldOutController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Async\Pool;

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
        set_time_limit(0);
        ini_set('memory_allow', '-1');
        $this->info('Initiated Trackwing...');
        $this->info('Collecting target vendors...');

        $vendors = DB::table('trackwing AS t')
            ->join('vendors AS v', 'v.id', '=', 't.vendor_id')
            ->where('t.is_active', 'ACTIVE')
            ->get(['v.id', 'v.name_eng']);

        $tsoc = new TrackSoldOutController();
        $pool = Pool::create();

        foreach ($vendors as $i => $vendor) {
            $index = $i + 1;
            $vendorId = $vendor->id;
            $vendorName = $vendor->name_eng;
            $this->info('#' . $index . ': Queuing Trackwing for vendor ' . $vendorName . ' (ID: ' . $vendorId . ') ----------' . now());

            $pool[] = async(function () use ($tsoc, $vendorId, $index, $vendorName) {
                $request = new Request([
                    'vendorId' => $vendorId
                ]);
                $tsocMainResult = $tsoc->main($request);

                $tempFileName = date('YmdHis') . '_' . $vendorName . '.json';
                file_put_contents(public_path('assets/json/trackwing-results/' . $tempFileName), json_encode($tsocMainResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                return $vendorId;
            })->then(function ($vendorId) use ($index, $vendorName) {
                $this->info('#' . $index . ': Completed Trackwing for vendor ' . $vendorName . ' (ID: ' . $vendorId . ')');
            })->catch(function ($exception) use ($index, $vendorName, $vendorId) {
                $this->error('#' . $index . ': Error processing vendor ' . $vendorName . ' (ID: ' . $vendorId . '): ' . $exception->getMessage());
            });
        }

        $pool->wait();

        $this->info('Completed Trackwing!');
    }
}
