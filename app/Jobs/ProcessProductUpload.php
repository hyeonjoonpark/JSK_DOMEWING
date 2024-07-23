<?php

namespace App\Jobs;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OpenMarkets\Coupang\CoupangUploadController;
use App\Http\Controllers\OpenMarkets\LotteOn\LotteOnUploadController;
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

    public function __construct($products, $partner, $account, $vendor, $tableName)
    {
        $this->products = $products;
        $this->partner = $partner;
        $this->account = $account;
        $this->vendor = $vendor;
        $this->tableName = $tableName;
    }

    public function handle()
    {
        $uploadResult = $this->uploadProducts();
        if (isset($uploadResult['error'])) {
            $error = $uploadResult['error'];
        } else {
            $error = '?';
        }
        $this->storeNotification($this->partner->id, $uploadResult['status'], $uploadResult['message'], $this->vendor->name, $error);
    }

    protected function uploadProducts()
    {
        switch ($this->vendor->name_eng) {
            case 'smart_store':
                $uploader = new SmartstoreProductUpload($this->products, $this->partner, $this->account);
                break;
            case 'coupang':
                $uploader = new CoupangUploadController($this->products, $this->partner, $this->account);
                break;
            case 'st11':
                $uploader = new St11UploadController();
                break;
            case 'lotte_on':
                $uploader = new LotteOnUploadController($this->products, $this->partner, $this->account);
            default:
                throw new \Exception("Unknown vendor: {$this->vendor->name_eng}");
        }

        return $uploader->main($this->products, $this->partner, $this->account);
    }

    protected function storeNotification($partnerId, $status, $data, $vendorName, $error = null)
    {
        DB::table('notifications')->insert([
            'partner_id' => $partnerId,
            'status' => $status ? 'TRUE' : 'FALSE',
            'data' => "<b>$vendorName: $this->tableName 테이블 업로드 결과</b><br>$data",
            'error' => json_encode($error, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        ]);
    }
}
