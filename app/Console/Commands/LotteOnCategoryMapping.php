<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class LotteOnCategoryMapping extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:lotte-on-category-mapping';

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
        $ownerClanCategories = DB::table('ownerclan_category')->get();
        foreach ($ownerClanCategories as $ownerClanCategory) {
            $lotteOnCategory = DB::table('lotte_on_category')
                ->where('name', $ownerClanCategory->name)
                ->first();
            if ($lotteOnCategory) {
                DB::table('category_mapping')
                    ->where('ownerclan_id', $ownerClanCategory->id)
                    ->update([
                        'lotte_on_category_id' => $lotteOnCategory->id,
                    ]);

                $this->info("Mapped ownerclan_category.id {$ownerClanCategory->id} to lotte_on_category.id {$lotteOnCategory->id}");
            } else {
                $this->warn("No matching lotte_on_category found for ownerclan_category.id {$ownerClanCategory->id} with name {$ownerClanCategory->name}");
            }
        }

        $this->info('Mapping completed!');
    }
}
