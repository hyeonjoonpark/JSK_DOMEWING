<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class DashboardController extends Controller
{
    public function getGoogleSheetData()
    {
        $apiKey = 'AKfycbzB_F4PUpugy_WO0OQ_Y0LwR196Q2WDoVLEUy2lLybm9ZHQWgabRUHCr9aIQvSAYUBxnQ';
        $spreadsheetId = 'YOUR_SPREADSHEET_ID';
        $range = 'Sheet1'; // 시트 이름

        $client = new Client();
        $response = $client->get("https://sheets.googleapis.com/v4/spreadsheets/{$spreadsheetId}/values/{$range}?key={$apiKey}");

        $data = json_decode($response->getBody()->getContents(), true);

        // 시트 데이터 출력 또는 활용
        return view('google-sheets', ['data' => $data]);
    }
    public function createPost(Request $request)
    {
        $feedbackContent = $request->feedbackContent;
        $feedbackContent = nl2br($feedbackContent);
        $userId = Auth::id();
        DB::table('feedback_posts')->insert([
            'auth' => $userId,
            'content' => $feedbackContent
        ]);
        return Redirect::route('admin.dashboard');
    }
    public function loadPosts()
    {
        $posts = DB::table('feedback_posts')->where('is_active', 'Y')->get();
        return $posts;
    }
    public function setPostConfirmed(Request $request)
    {
        try {
            $postId = $request->id;
            DB::table('feedback_posts')->where('id', $postId)->update([
                'is_confirmed' => 'Y'
            ]);
            return 'success';
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    public function deletePost(Request $request)
    {
        $postId = $request->id;
        DB::table('feedback_posts')->where('id', $postId)->update(['is_active' => "N"]);
        return 'success';
    }
}