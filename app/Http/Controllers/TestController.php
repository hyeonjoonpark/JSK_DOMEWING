<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Product\NameController;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    protected $nameController;

    /**
     * Inject NameController to utilize its functionalities.
     *
     * @param NameController $nameController
     */
    public function __construct(NameController $nameController)
    {
        $this->nameController = $nameController;
    }

    /**
     * Process and update keywords for each product in the database.
     *
     * @return string
     */
    public function index()
    {
        $threeMROProducts = DB::table('minewing_products')
            ->where('sellerID', 16)
            ->where('isActive', 'Y')
            ->get();

        foreach ($threeMROProducts as $product) {
            $processedKeywords = $this->processKeywords($product->productKeywords);
            $this->updateProductKeywords($product->id, $processedKeywords);
        }

        return 'success';
    }

    /**
     * Process the keywords by limiting their length.
     *
     * @param string $keywords
     * @return string
     */
    protected function processKeywords($keywords)
    {
        $keywordsArray = explode(',', $keywords);
        $newKeywords = array_map(function ($keyword) {
            return mb_substr($keyword, 0, 10, "UTF-8");
        }, $keywordsArray);

        return implode(',', $newKeywords);
    }

    /**
     * Update the keywords of a product in the database.
     *
     * @param int $productID
     * @param string $newKeywords
     */
    protected function updateProductKeywords($productID, $newKeywords)
    {
        DB::table('minewing_products')
            ->where('id', $productID)
            ->update(['productKeywords' => $newKeywords]);
    }
}
