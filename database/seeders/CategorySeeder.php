<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->tree() as $position => $category) {
            $children = $category['children'];
            unset($category['children']);

            $parent = Category::updateOrCreate(
                ['slug' => $category['slug']],
                // parent_id is passed explicitly so re-seeding repairs a root
                // that was accidentally nested from the admin panel.
                [...$category, 'parent_id' => null, 'position' => $position + 1],
            );

            foreach ($children as $childPosition => $child) {
                Category::updateOrCreate(
                    ['slug' => $child['slug']],
                    [...$child, 'parent_id' => $parent->id, 'position' => $childPosition + 1],
                );
            }
        }
    }

    /**
     * The seeded two-level category tree.
     *
     * @return list<array{
     *     slug: string,
     *     name: array{en: string, es: string},
     *     description: array{en: string, es: string},
     *     children: list<array{
     *         slug: string,
     *         name: array{en: string, es: string},
     *         description: array{en: string, es: string},
     *     }>,
     * }>
     */
    private function tree(): array
    {
        return [
            [
                'slug' => 'fashion',
                'name' => ['en' => 'Fashion', 'es' => 'Moda'],
                'description' => [
                    'en' => 'Wardrobe wonders you will never have to iron.',
                    'es' => 'Maravillas de armario que nunca tendrás que planchar.',
                ],
                'children' => [
                    [
                        'slug' => 'tops',
                        'name' => ['en' => 'Tops', 'es' => 'Prendas superiores'],
                        'description' => [
                            'en' => 'Layers that never wrinkle, because they never arrive.',
                            'es' => 'Capas que nunca se arrugan, porque nunca llegan.',
                        ],
                    ],
                    [
                        'slug' => 'dresses',
                        'name' => ['en' => 'Dresses', 'es' => 'Vestidos'],
                        'description' => [
                            'en' => 'One-piece answers to every invitation you declined.',
                            'es' => 'Respuestas de una pieza a cada invitación que rechazaste.',
                        ],
                    ],
                    [
                        'slug' => 'outerwear',
                        'name' => ['en' => 'Outerwear', 'es' => 'Abrigos'],
                        'description' => [
                            'en' => 'Coats warm enough for weather that never comes.',
                            'es' => 'Abrigos para un clima que nunca llega.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'beauty',
                'name' => ['en' => 'Beauty', 'es' => 'Belleza'],
                'description' => [
                    'en' => 'Glow-up essentials with zero shelf space required.',
                    'es' => 'Esenciales para brillar sin ocupar espacio en tu tocador.',
                ],
                'children' => [
                    [
                        'slug' => 'skincare',
                        'name' => ['en' => 'Skincare', 'es' => 'Cuidado facial'],
                        'description' => [
                            'en' => 'Routines with all of the ritual and none of the residue.',
                            'es' => 'Rutinas con todo el ritual y ningún residuo.',
                        ],
                    ],
                    [
                        'slug' => 'makeup',
                        'name' => ['en' => 'Makeup', 'es' => 'Maquillaje'],
                        'description' => [
                            'en' => 'Shades that suit you perfectly, guaranteed by absence.',
                            'es' => 'Tonos que te sientan perfectos, garantizado por ausencia.',
                        ],
                    ],
                    [
                        'slug' => 'fragrance',
                        'name' => ['en' => 'Fragrance', 'es' => 'Perfumes'],
                        'description' => [
                            'en' => 'Notes of citrus, cedar and pure imagination.',
                            'es' => 'Notas de cítricos, cedro e imaginación pura.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'accessories',
                'name' => ['en' => 'Accessories', 'es' => 'Accesorios'],
                'description' => [
                    'en' => 'The finishing touches that finish nothing, beautifully.',
                    'es' => 'Los toques finales que no rematan nada, con mucho estilo.',
                ],
                'children' => [
                    [
                        'slug' => 'bags',
                        'name' => ['en' => 'Bags', 'es' => 'Bolsos'],
                        'description' => [
                            'en' => 'Roomy enough for everything you were not going to carry.',
                            'es' => 'Amplios para todo lo que no ibas a llevar.',
                        ],
                    ],
                    [
                        'slug' => 'hats-and-scarves',
                        'name' => ['en' => 'Hats & Scarves', 'es' => 'Sombreros y bufandas'],
                        'description' => [
                            'en' => 'Warmth and shade in equal, imaginary measure.',
                            'es' => 'Abrigo y sombra en medidas igual de imaginarias.',
                        ],
                    ],
                    [
                        'slug' => 'sunglasses',
                        'name' => ['en' => 'Sunglasses', 'es' => 'Gafas de sol'],
                        'description' => [
                            'en' => 'Frames you will never sit on.',
                            'es' => 'Monturas sobre las que nunca te sentarás.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'jewelry',
                'name' => ['en' => 'Jewelry', 'es' => 'Joyería'],
                'description' => [
                    'en' => 'Sparkle you can admire without ever losing an earring.',
                    'es' => 'Brillo para admirar sin perder nunca un pendiente.',
                ],
                'children' => [
                    [
                        'slug' => 'necklaces',
                        'name' => ['en' => 'Necklaces', 'es' => 'Collares'],
                        'description' => [
                            'en' => 'Chains that never tangle in the bottom of a drawer.',
                            'es' => 'Cadenas que nunca se enredan en el fondo de un cajón.',
                        ],
                    ],
                    [
                        'slug' => 'earrings',
                        'name' => ['en' => 'Earrings', 'es' => 'Pendientes'],
                        'description' => [
                            'en' => 'Always a matching pair, forever.',
                            'es' => 'Siempre un par completo, para siempre.',
                        ],
                    ],
                    [
                        'slug' => 'rings',
                        'name' => ['en' => 'Rings', 'es' => 'Anillos'],
                        'description' => [
                            'en' => 'Your exact size, whatever it happens to be today.',
                            'es' => 'Justo tu talla, sea cual sea hoy.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'home-lifestyle',
                'name' => ['en' => 'Home & Lifestyle', 'es' => 'Hogar y Estilo'],
                'description' => [
                    'en' => 'Cozy upgrades for the home you already love.',
                    'es' => 'Mejoras acogedoras para el hogar que ya amas.',
                ],
                'children' => [
                    [
                        'slug' => 'decor',
                        'name' => ['en' => 'Decor', 'es' => 'Decoración'],
                        'description' => [
                            'en' => 'Pieces that go with everything, by never being there.',
                            'es' => 'Piezas que combinan con todo, por no estar nunca.',
                        ],
                    ],
                    [
                        'slug' => 'kitchen',
                        'name' => ['en' => 'Kitchen', 'es' => 'Cocina'],
                        'description' => [
                            'en' => 'Gadgets that never end up in the back of a cupboard.',
                            'es' => 'Cacharros que nunca acaban al fondo de un armario.',
                        ],
                    ],
                    [
                        'slug' => 'bedding',
                        'name' => ['en' => 'Bedding', 'es' => 'Ropa de cama'],
                        'description' => [
                            'en' => 'Linens that are always freshly washed.',
                            'es' => 'Sábanas siempre recién lavadas.',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'wellness',
                'name' => ['en' => 'Wellness', 'es' => 'Bienestar'],
                'description' => [
                    'en' => 'Self-care rituals, minus the clutter.',
                    'es' => 'Rituales de autocuidado, sin acumular nada.',
                ],
                'children' => [
                    [
                        'slug' => 'aromatherapy',
                        'name' => ['en' => 'Aromatherapy', 'es' => 'Aromaterapia'],
                        'description' => [
                            'en' => 'Calm delivered at the speed of thought.',
                            'es' => 'Calma servida a la velocidad del pensamiento.',
                        ],
                    ],
                    [
                        'slug' => 'fitness',
                        'name' => ['en' => 'Fitness', 'es' => 'Fitness'],
                        'description' => [
                            'en' => 'Equipment that never becomes a clothes rack.',
                            'es' => 'Equipo que nunca acaba de perchero.',
                        ],
                    ],
                    [
                        'slug' => 'sleep',
                        'name' => ['en' => 'Sleep', 'es' => 'Descanso'],
                        'description' => [
                            'en' => 'Eight restful hours, sold separately.',
                            'es' => 'Ocho horas de descanso, se venden por separado.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
