<?php

namespace App\Http\Controllers\Minewing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UniqueProductHrefsController extends Controller
{
    public function index(Request $request)
    {
        set_time_limit(0);
        // Get unique product hrefs directly.
        $uniqueProductHrefs = array_unique($request->productHrefs);

        // Get existing active product hrefs from the database in one query.
        $existingProductHrefs = DB::table('minewing_products')
            ->whereIn('productHref', $uniqueProductHrefs)
            ->where('isActive', 'Y')
            ->pluck('productHref')
            ->toArray();

        // Use array_diff to find new product hrefs.
        $newProductHrefs = array_diff($uniqueProductHrefs, $existingProductHrefs);

        return $newProductHrefs; // Reset keys and return
    }
}
