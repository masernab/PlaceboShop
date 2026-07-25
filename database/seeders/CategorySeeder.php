<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'slug' => 'fashion',
                'name' => ['en' => 'Fashion', 'es' => 'Moda'],
                'description' => [
                    'en' => 'Wardrobe wonders you will never have to iron.',
                    'es' => 'Maravillas de armario que nunca tendrás que planchar.',
                ],
            ],
            [
                'slug' => 'beauty',
                'name' => ['en' => 'Beauty', 'es' => 'Belleza'],
                'description' => [
                    'en' => 'Glow-up essentials with zero shelf space required.',
                    'es' => 'Esenciales para brillar sin ocupar espacio en tu tocador.',
                ],
            ],
            [
                'slug' => 'accessories',
                'name' => ['en' => 'Accessories', 'es' => 'Accesorios'],
                'description' => [
                    'en' => 'The finishing touches that finish nothing, beautifully.',
                    'es' => 'Los toques finales que no rematan nada, con mucho estilo.',
                ],
            ],
            [
                'slug' => 'jewelry',
                'name' => ['en' => 'Jewelry', 'es' => 'Joyería'],
                'description' => [
                    'en' => 'Sparkle you can admire without ever losing an earring.',
                    'es' => 'Brillo para admirar sin perder nunca un pendiente.',
                ],
            ],
            [
                'slug' => 'home-lifestyle',
                'name' => ['en' => 'Home & Lifestyle', 'es' => 'Hogar y Estilo'],
                'description' => [
                    'en' => 'Cozy upgrades for the home you already love.',
                    'es' => 'Mejoras acogedoras para el hogar que ya amas.',
                ],
            ],
            [
                'slug' => 'wellness',
                'name' => ['en' => 'Wellness', 'es' => 'Bienestar'],
                'description' => [
                    'en' => 'Self-care rituals, minus the clutter.',
                    'es' => 'Rituales de autocuidado, sin acumular nada.',
                ],
            ],
        ];

        foreach ($categories as $position => $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [...$category, 'position' => $position + 1],
            );
        }
    }
}
