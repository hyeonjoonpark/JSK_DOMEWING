<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Validator::extend('unique_keywords', function ($attribute, $value, $parameters, $validator) {
            $keywords = explode(',', $value);
            $keywords = array_map('trim', $keywords);

            // 중복 검사
            return count($keywords) === count(array_unique($keywords));
        });
    }
}