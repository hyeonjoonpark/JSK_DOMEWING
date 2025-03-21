<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $this->updateProject();

        return "Update successful";
    }
    protected function updateProject()
    {
        try {
            // Git에서 코드 업데이트 및 캐시 업데이트
            $this->updateCodeAndCache();
            // 캐시를 업데이트하고 로깅
            $this->updateCaches();
        } catch (\Exception $e) {
            Log::error("Error updating project: " . $e->getMessage());
        }
    }
    protected function updateCaches()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:cache');
    }
    protected function updateCodeAndCache()
    {
        $output = shell_exec("cd " . base_path() . " && git add . && git add . && git commit -m 'commit' && git pull");

        Log::info("Project updated: " . $output);
    }
}