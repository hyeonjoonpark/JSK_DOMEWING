<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ThreemroFetchHref extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:threemro-fetch-href';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threemroProducts = DB::table('minewing_products')
            ->where('sellerID', 16)
            ->get();
        foreach ($threemroProducts as $index => $product) {
            if ($index < 5) {
                continue;
            }
            $productHref = $product->productHref;
            $parsedHref = parse_url($productHref);
            $query = $parsedHref['query'];
            parse_str($query, $params);
            $sellerProductCode = $params['goodsno'];
            $newProductHref = 'https://www.3mro.co.kr/shop/item.php?it_id=' . $sellerProductCode;
            DB::table('minewing_products')
                ->where('id', $product->id)
                ->update([
                    'productHref' => $newProductHref
                ]);
        }
    }
}
