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
        $translation = DB::table('translation')
            ->join('language', 'translation.languageId', '=', 'language.id')
            ->where('translation.languageId', $languageId)
            ->pluck('content', 'keyword')
            ->toArray();

        $language = DB::table('language')
                ->where('id', $languageId)
                ->first();

        view()->share(['translation'=> $translation, 'language' => $language]);

        return $next($request);
    }
}
