<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        if (Review::query()->exists()) {
            return;
        }

        $reviewers = User::factory()->count(12)->create();

        foreach (Product::query()->get() as $product) {
            $count = random_int(0, 6);

            foreach ($reviewers->random($count) as $reviewer) {
                Review::factory()->create([
                    'product_id' => $product->id,
                    'user_id' => $reviewer->id,
                ]);
            }
        }
    }
}
