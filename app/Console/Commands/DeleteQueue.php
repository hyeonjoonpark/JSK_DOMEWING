<?php

namespace App\Console\Commands;

use App\Http\Controllers\DeleteQueueController;
use Illuminate\Console\Command;

class DeleteQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:delete-queue';

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
        $dqc = new DeleteQueueController();
        print_r($dqc->destroy());
    }
}
