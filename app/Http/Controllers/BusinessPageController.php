<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BusinessPageController extends Controller
{
    public function showBusinessPage(Request $request)
    {
        $partners = $this->getAllPartner();
        return view('business_page.index', ['partners' => $partners]);
    }

    public function getAllPartner()
    {
        $images = [
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://res.cloudinary.com/demo/image/upload/v1312461204/sample.jpg',
            'https://images.pexels.com/photos/19840881/pexels-photo-19840881/free-photo-of-take-me-to-the-stars-if-you-like-my-work-consider-supporting-me-at-https-www-patreon-com-marekpiwnicki.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
            'https://images.pexels.com/photos/20071905/pexels-photo-20071905/free-photo-of-moon-cake.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=1',
        ];
        return $images;
    }
}
