<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DuplicateDomeggookMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:duplicate-domeggook-mapping';

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
        $domeggookCm = DB::table('category_mapping')
            ->get(['ownerclan', 'domeggook']);
        foreach ($domeggookCm as $code) {
            DB::table('category_mapping')
                ->where('ownerclan', $code->ownerclan)
                ->update([
                    'domeggook2' => $code->domeggook
                ]);
        }
    }
}
