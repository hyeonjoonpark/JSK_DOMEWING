<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\OpenMarkets\St11\UploadController as St11UploadController;
use App\Http\Controllers\SmartStore\SmartstoreProductUpload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessProductUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $products;
    protected $partner;
    protected $account;
    protected $vendor;
    protected $tableName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($products, $partner, $account, $vendor, $tableName)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
        $this->vendor = $vendor;
        $this->tableName = $tableName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        switch ($this->vendor->name_eng) {
            case 'smart_store':
                $spu = new SmartstoreProductUpload($this->products, $this->partner, $this->account);
                $uploadResult = $spu->main();
                break;
            case 'coupang':
                $cuc = new CoupangUploadController($this->products, $this->partner, $this->account);
                $uploadResult = $cuc->main();
                break;
            case 'st11':
                $st11UploadController = new St11UploadController();
                $uploadResult = $st11UploadController->main($this->products, $this->partner, $this->account);
                break;
        }
        $partnerId = $this->partner->id;
        $status = $uploadResult['status'];
        $data = $uploadResult['message'];
        $this->storeNotification($partnerId, $status, $data);
    }
    protected function storeNotification($partnerId, $status, $data)
    {
        $nc = new NotificationController();
        DB::table('notifications')
            ->insert([
                'partner_id' => $partnerId,
                'status' => $status,
                'data' => $data
            ]);
    }
}
