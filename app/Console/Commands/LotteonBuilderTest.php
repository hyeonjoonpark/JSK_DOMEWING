<?php

namespace App\Console\Commands;

use App\Http\Controllers\Partners\Products\UploadedController;
use Illuminate\Console\Command;

class LotteonBuilderTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lotteon-builder-test';

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
        $originProductNo = 'LO2343784258';
        $uc = new UploadedController();
        print_r($uc->lotte_onDeleteRequest($originProductNo));
    }
}
