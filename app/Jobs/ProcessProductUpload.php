<?php

namespace App\Jobs;

use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\OpenMarkets\St11\UploadController as St11UploadController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessProductUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $products;
    protected $partner;
    protected $account;
    protected $vendorEngName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($products, $partner, $account, $vendorEngName)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
        $this->vendorEngName = $vendorEngName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->vendorEngName) {
            case 'smart_store':
                $spu = new SmartstoreProductUpload($this->products, $this->partner, $this->account);
                $spu->main();
                break;
            case 'coupang':
                $cuc = new CoupangUploadController($this->products, $this->partner, $this->account);
                $cuc->main();
                break;
            case 'st11':
                $st11UploadController = new St11UploadController();
                $st11UploadController->main($this->products, $this->partner, $this->account);
                break;
        }
    }
}
