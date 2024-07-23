<?php

namespace App\Jobs;

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
use Illuminate\Support\Facades\Log;

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
        try {
            $uploadResult = $this->uploadProducts();
            $error = $uploadResult['error'] ?? null;
            $this->storeNotification($this->partner->id, $uploadResult['status'], $uploadResult['message'], $this->vendor->name, $error);
        } catch (\Exception $e) {
            $this->storeNotification($this->partner->id, false, '상품 업로드 과정에서 예기치 못한 에러가 발생했습니다.', $this->vendor->name, $e->getMessage());
        }
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
                break;
            default:
                throw new \Exception("Unknown vendor: {$this->vendor->name_eng}");
        }

        Log::info("Starting upload for vendor: {$this->vendor->name_eng}");
        return $uploader->main();
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
