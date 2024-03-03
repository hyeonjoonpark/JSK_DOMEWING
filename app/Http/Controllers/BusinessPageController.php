<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DateTime;

class BusinessPageController extends Controller
{
    public function showBusinessPage(Request $request)
    {
        $partners = $this->getAllPartner();
        $testimonials = $this->getAllTestimonials();
        return view('business_page.index', ['partners' => $partners, 'testimonials' => $testimonials]);
    }

    public function getAllPartner()
    {
        $images = DB::table('partnership')->where('status', 'Y')->inRandomOrder()->get();
        return $images;
    }

    public function getAllTestimonials()
    {
        $testimonials = DB::table('testimonial')->where('status', 'Y')->get();

        foreach ($testimonials as $record) {
            $dateTime = new DateTime($record->created_at);

            // Format the date
            $record->formatted_date = $dateTime->format('Y년 m월 d일, ');
            $record->formatted_date .= ($dateTime->format('A') === 'AM' ? '오전' : '오후') . $dateTime->format('h시 i분');
        }

        return $testimonials;
    }
}
