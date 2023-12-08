<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Category
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $headerCategory = [
            [
                'image' => 'media/Asset_Category_Baby_Product.svg',
                'title' => 'Baby Product',
            ],
            [
                'image' => 'media/Asset_Category_Cosmetics.svg',
                'title' => 'Cosmetics',
            ],
            [
                'image' => 'media/Asset_Category_Electronics.svg',
                'title' => 'Electronics',
            ],
            [
                'image' => 'media/Asset_Category_Fashion_Accessories.svg',
                'title' => 'Fashion Accessories',
            ],
            [
                'image' => 'media/Asset_Category_Fashion_Apparels.svg',
                'title' => 'Fashion Apparels',
            ],
            [
                'image' => 'media/Asset_Category_Food.svg',
                'title' => 'Food',
            ],
            [
                'image' => 'media/Asset_Category_Furniture.svg',
                'title' => 'Furniture',
            ],
            [
                'image' => 'media/Asset_Category_Health.svg',
                'title' => 'Health',
            ],
            [
                'image' => 'media/Asset_Category_Pet_Products.svg',
                'title' => 'Pet Products',
            ],
            [
                'image' => 'media/Asset_Category_Sports.svg',
                'title' => 'Sports',
            ],
        ];

        view()->share(['headerCategory'=> $headerCategory]);

        return $next($request);

    }
}
