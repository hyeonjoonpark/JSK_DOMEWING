<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class Translation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $languageId = session('languageId');

        // Check if the languageId is not set or null
        if (!$languageId) {
            // Set your default language ID here
            $defaultLanguageId = 2; // Replace this with your default language ID

            // Set the default languageId in the session
            session(['languageId' => $defaultLanguageId]);

            // Retrieve translation and language data for the default language
            $translation = DB::table('translation')
                ->join('language', 'translation.languageId', '=', 'language.id')
                ->where('translation.languageId', $defaultLanguageId)
                ->pluck('content', 'keyword')
                ->toArray();

            $language = DB::table('language')
                ->where('id', $defaultLanguageId)
                ->first();

            // Share translation and language data to views
            view()->share(['translation'=> $translation, 'language' => $language]);
        } else {
            $languageId = session('languageId');
            $translation = DB::table('translation')
                ->join('language', 'translation.languageId', '=', 'language.id')
                ->where('translation.languageId', $languageId)
                ->pluck('content', 'keyword')
                ->toArray();

            $language = DB::table('language')
                    ->where('id', $languageId)
                    ->first();

            view()->share(['translation'=> $translation, 'language' => $language]);
        }

        return $next($request);
    }
}
