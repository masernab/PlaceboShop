<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\Support\PlaceholderSvg;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $sequence = 0;

        foreach ($this->catalog() as $categorySlug => $products) {
            $category = Category::query()->where('slug', $categorySlug)->firstOrFail();

            foreach ($products as $data) {
                $sequence++;

                $product = Product::updateOrCreate(
                    ['slug' => $data['slug']],
                    [
                        'category_id' => $category->id,
                        'sku' => sprintf('PB-%04d', $sequence),
                        'name' => $data['name'],
                        'description' => $data['description'],
                        'price_cents' => $data['price_cents'],
                        'compare_at_price_cents' => $data['compare_at_price_cents'] ?? null,
                        'stock' => ($sequence * 7) % 40 + 5,
                        'is_active' => true,
                        'is_featured' => $data['is_featured'] ?? false,
                    ],
                );

                foreach ([0, 1] as $variant) {
                    $path = PlaceholderSvg::generate($data['slug'], $data['name']['en'], $variant);

                    $product->images()->updateOrCreate(
                        ['position' => $variant],
                        ['path' => $path, 'alt' => $data['name']['en']],
                    );
                }
            }
        }
    }

    /**
     * The curated bilingual catalog, keyed by category slug.
     *
     * @return array<string, list<array{
     *     slug: string,
     *     name: array{en: string, es: string},
     *     description: array{en: string, es: string},
     *     price_cents: int,
     *     compare_at_price_cents?: int,
     *     is_featured?: bool,
     * }>>
     */
    private function catalog(): array
    {
        return [
            'fashion' => [
                [
                    'slug' => 'cloud-nine-cardigan',
                    'name' => ['en' => 'Cloud Nine Cardigan', 'es' => 'Cárdigan Nube Nueve'],
                    'description' => [
                        'en' => 'A whisper-soft knit that wraps you like a compliment. Never pills, never sheds — mostly because it never arrives.',
                        'es' => 'Un punto suave como un susurro que te abraza como un cumplido. Nunca hace bolitas ni suelta pelusa: sobre todo porque nunca llega.',
                    ],
                    'price_cents' => 5900,
                    'compare_at_price_cents' => 7900,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'silk-illusion-scarf',
                    'name' => ['en' => 'Silk Illusion Scarf', 'es' => 'Pañuelo Ilusión de Seda'],
                    'description' => [
                        'en' => 'Hand-rolled edges and a watercolor print that goes with everything you already own.',
                        'es' => 'Bordes enrollados a mano y un estampado acuarela que combina con todo lo que ya tienes.',
                    ],
                    'price_cents' => 2900,
                ],
                [
                    'slug' => 'wrap-dress-daydream',
                    'name' => ['en' => 'Wrap Dress Daydream', 'es' => 'Vestido Cruzado Ensueño'],
                    'description' => [
                        'en' => 'The flattering wrap silhouette that works for brunch, weddings, and imaginary occasions alike.',
                        'es' => 'La silueta cruzada favorecedora que sirve para brunch, bodas y ocasiones imaginarias por igual.',
                    ],
                    'price_cents' => 8900,
                ],
                [
                    'slug' => 'feather-light-blazer',
                    'name' => ['en' => 'Feather-Light Blazer', 'es' => 'Blazer Ligero como Pluma'],
                    'description' => [
                        'en' => 'Sharp shoulders, soft drape, zero weight. Power dressing at its most literal.',
                        'es' => 'Hombros marcados, caída suave, peso cero. Vestir con poder en su sentido más literal.',
                    ],
                    'price_cents' => 12900,
                    'compare_at_price_cents' => 15900,
                ],
                [
                    'slug' => 'sunset-pleated-skirt',
                    'name' => ['en' => 'Sunset Pleated Skirt', 'es' => 'Falda Plisada Atardecer'],
                    'description' => [
                        'en' => 'Golden-hour gradient pleats that catch the light with every imagined twirl.',
                        'es' => 'Pliegues degradados de hora dorada que atrapan la luz en cada giro imaginado.',
                    ],
                    'price_cents' => 6900,
                ],
                [
                    'slug' => 'cashmere-ish-sweater',
                    'name' => ['en' => 'Cashmere-ish Sweater', 'es' => 'Suéter Casi Cachemira'],
                    'description' => [
                        'en' => 'Indistinguishable from real cashmere, because you will never actually touch it.',
                        'es' => 'Indistinguible de la cachemira real, porque en realidad nunca lo tocarás.',
                    ],
                    'price_cents' => 9900,
                ],
                [
                    'slug' => 'everyday-magic-tee',
                    'name' => ['en' => 'Everyday Magic Tee', 'es' => 'Camiseta Magia Diaria'],
                    'description' => [
                        'en' => 'The perfect white tee: opaque, structured, and permanently unstained.',
                        'es' => 'La camiseta blanca perfecta: opaca, estructurada y permanentemente inmaculada.',
                    ],
                    'price_cents' => 1900,
                ],
            ],
            'beauty' => [
                [
                    'slug' => 'glow-placebo-serum',
                    'name' => ['en' => 'Glow Placebo Serum', 'es' => 'Sérum Placebo Luminoso'],
                    'description' => [
                        'en' => 'Clinically proven to do nothing, gorgeously. The glow was inside you all along.',
                        'es' => 'Clínicamente probado para no hacer nada, con mucho glamour. El brillo siempre estuvo en ti.',
                    ],
                    'price_cents' => 4900,
                    'compare_at_price_cents' => 6400,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'velvet-matte-lipstick',
                    'name' => ['en' => 'Velvet Matte Lipstick', 'es' => 'Labial Mate Aterciopelado'],
                    'description' => [
                        'en' => 'A universally flattering rose that never smudges, transfers, or runs out.',
                        'es' => 'Un rosa que favorece a todo el mundo y nunca se corre, transfiere ni se acaba.',
                    ],
                    'price_cents' => 2400,
                ],
                [
                    'slug' => 'dream-cream-moisturizer',
                    'name' => ['en' => 'Dream Cream Moisturizer', 'es' => 'Crema Hidratante de Ensueño'],
                    'description' => [
                        'en' => 'Whipped hydration with a cloudlike texture your skin can only dream about.',
                        'es' => 'Hidratación batida con una textura de nube con la que tu piel solo puede soñar.',
                    ],
                    'price_cents' => 3900,
                ],
                [
                    'slug' => 'confidence-boost-palette',
                    'name' => ['en' => 'Confidence Boost Palette', 'es' => 'Paleta Sube-Ánimos'],
                    'description' => [
                        'en' => 'Twelve shades of pure self-assurance, from Monday Neutral to Friday Shimmer.',
                        'es' => 'Doce tonos de pura seguridad, del Neutro de Lunes al Brillo de Viernes.',
                    ],
                    'price_cents' => 5400,
                ],
                [
                    'slug' => 'midnight-recovery-mask',
                    'name' => ['en' => 'Midnight Recovery Mask', 'es' => 'Mascarilla Recuperación Nocturna'],
                    'description' => [
                        'en' => 'Works overnight so you do not have to. Wake up exactly as radiant as you went to bed.',
                        'es' => 'Trabaja de noche para que tú no tengas que hacerlo. Despierta exactamente igual de radiante que al acostarte.',
                    ],
                    'price_cents' => 3400,
                ],
                [
                    'slug' => 'rosewater-mist',
                    'name' => ['en' => 'Rosewater Mist', 'es' => 'Bruma de Agua de Rosas'],
                    'description' => [
                        'en' => 'A refreshing spritz of pure intention, bottled at the peak of bloom.',
                        'es' => 'Un rocío refrescante de pura intención, embotellado en plena floración.',
                    ],
                    'price_cents' => 1900,
                ],
                [
                    'slug' => 'lash-drama-mascara',
                    'name' => ['en' => 'Lash Drama Mascara', 'es' => 'Máscara Pestañas Dramáticas'],
                    'description' => [
                        'en' => 'Volume, length, and curl with absolutely no clumps — guaranteed by physics.',
                        'es' => 'Volumen, longitud y curvatura sin ningún grumo, garantizado por la física.',
                    ],
                    'price_cents' => 2900,
                    'compare_at_price_cents' => 3400,
                ],
            ],
            'accessories' => [
                [
                    'slug' => 'everything-tote',
                    'name' => ['en' => 'The Everything Tote', 'es' => 'Bolso Para Todo'],
                    'description' => [
                        'en' => 'Fits your laptop, your gym clothes, and all your unfulfilled potential.',
                        'es' => 'Cabe tu portátil, tu ropa de gimnasio y todo tu potencial por cumplir.',
                    ],
                    'price_cents' => 7400,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'golden-hour-sunglasses',
                    'name' => ['en' => 'Golden Hour Sunglasses', 'es' => 'Gafas de Sol Hora Dorada'],
                    'description' => [
                        'en' => 'Oversized frames that make every sidewalk feel like a runway.',
                        'es' => 'Monturas oversize que convierten cualquier acera en una pasarela.',
                    ],
                    'price_cents' => 3900,
                    'compare_at_price_cents' => 4900,
                ],
                [
                    'slug' => 'silk-scrunchie-trio',
                    'name' => ['en' => 'Silk Scrunchie Trio', 'es' => 'Trío de Coleteros de Seda'],
                    'description' => [
                        'en' => 'Three mulberry silk scrunchies that will never stretch out or disappear into the couch.',
                        'es' => 'Tres coleteros de seda de morera que nunca se darán de sí ni desaparecerán en el sofá.',
                    ],
                    'price_cents' => 1400,
                ],
                [
                    'slug' => 'structured-mini-bag',
                    'name' => ['en' => 'Structured Mini Bag', 'es' => 'Mini Bolso Estructurado'],
                    'description' => [
                        'en' => 'Holds a phone, a lipstick, and the moral high ground of packing light.',
                        'es' => 'Guarda un móvil, un labial y la superioridad moral de viajar ligera.',
                    ],
                    'price_cents' => 8900,
                ],
                [
                    'slug' => 'cozy-knit-beanie',
                    'name' => ['en' => 'Cozy Knit Beanie', 'es' => 'Gorro de Punto Acogedor'],
                    'description' => [
                        'en' => 'Chunky ribbed knit that never flattens your hair, because it never touches it.',
                        'es' => 'Punto grueso acanalado que nunca te aplasta el pelo, porque nunca lo toca.',
                    ],
                    'price_cents' => 1900,
                ],
                [
                    'slug' => 'statement-belt',
                    'name' => ['en' => 'Statement Belt', 'es' => 'Cinturón de Impacto'],
                    'description' => [
                        'en' => 'A sculptural gold buckle that pulls together outfits you have not bought yet.',
                        'es' => 'Una hebilla dorada escultural que remata conjuntos que aún no has comprado.',
                    ],
                    'price_cents' => 2900,
                ],
                [
                    'slug' => 'travel-jewelry-case',
                    'name' => ['en' => 'Travel Jewelry Case', 'es' => 'Estuche de Joyas de Viaje'],
                    'description' => [
                        'en' => 'Velvet-lined compartments for the jewelry collection of your dreams. Literally.',
                        'es' => 'Compartimentos forrados de terciopelo para la colección de joyas de tus sueños. Literalmente.',
                    ],
                    'price_cents' => 3400,
                ],
            ],
            'jewelry' => [
                [
                    'slug' => 'moonstone-pendant',
                    'name' => ['en' => 'Moonstone Pendant', 'es' => 'Colgante Piedra Lunar'],
                    'description' => [
                        'en' => 'A luminous moonstone on a fine gold chain, said to bring clarity to those who almost wear it.',
                        'es' => 'Una piedra lunar luminosa en fina cadena dorada; dicen que da claridad a quienes casi la llevan.',
                    ],
                    'price_cents' => 9900,
                    'compare_at_price_cents' => 12900,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'stacking-ring-set',
                    'name' => ['en' => 'Stacking Ring Set', 'es' => 'Set de Anillos Apilables'],
                    'description' => [
                        'en' => 'Five delicate bands to mix, match, and mentally rearrange forever.',
                        'es' => 'Cinco anillos delicados para combinar y reorganizar mentalmente para siempre.',
                    ],
                    'price_cents' => 4900,
                ],
                [
                    'slug' => 'pearl-drop-earrings',
                    'name' => ['en' => 'Pearl Drop Earrings', 'es' => 'Pendientes de Perla'],
                    'description' => [
                        'en' => 'Freshwater pearls that sway elegantly with every nod of approval.',
                        'es' => 'Perlas de agua dulce que se balancean con elegancia a cada gesto de aprobación.',
                    ],
                    'price_cents' => 5900,
                ],
                [
                    'slug' => 'delicate-chain-bracelet',
                    'name' => ['en' => 'Delicate Chain Bracelet', 'es' => 'Pulsera de Cadena Delicada'],
                    'description' => [
                        'en' => 'So fine it is barely there. In this case, exactly as advertised.',
                        'es' => 'Tan fina que apenas se nota. En este caso, exactamente como se anuncia.',
                    ],
                    'price_cents' => 3900,
                ],
                [
                    'slug' => 'celestial-hoop-earrings',
                    'name' => ['en' => 'Celestial Hoop Earrings', 'es' => 'Aros Celestiales'],
                    'description' => [
                        'en' => 'Star-studded gold hoops for orbiting your best self.',
                        'es' => 'Aros dorados con estrellas para orbitar tu mejor versión.',
                    ],
                    'price_cents' => 4400,
                ],
                [
                    'slug' => 'birthstone-charm',
                    'name' => ['en' => 'Birthstone Charm', 'es' => 'Dije de Piedra de Nacimiento'],
                    'description' => [
                        'en' => 'Your birthstone, or any stone you feel spiritually aligned with today.',
                        'es' => 'Tu piedra de nacimiento, o cualquier piedra con la que hoy sientas afinidad espiritual.',
                    ],
                    'price_cents' => 2900,
                ],
                [
                    'slug' => 'layered-necklace',
                    'name' => ['en' => 'Layered Necklace', 'es' => 'Collar en Capas'],
                    'description' => [
                        'en' => 'Three pre-layered chains that never tangle, a physical impossibility we proudly simulate.',
                        'es' => 'Tres cadenas superpuestas que nunca se enredan, una imposibilidad física que simulamos con orgullo.',
                    ],
                    'price_cents' => 6900,
                    'compare_at_price_cents' => 8400,
                ],
            ],
            'home-lifestyle' => [
                [
                    'slug' => 'serenity-scented-candle',
                    'name' => ['en' => 'Serenity Scented Candle', 'es' => 'Vela Aromática Serenidad'],
                    'description' => [
                        'en' => 'Notes of lavender, sandalwood, and a to-do list that finished itself.',
                        'es' => 'Notas de lavanda, sándalo y una lista de tareas que se completó sola.',
                    ],
                    'price_cents' => 2900,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'cloud-throw-blanket',
                    'name' => ['en' => 'Cloud Throw Blanket', 'es' => 'Manta Nube'],
                    'description' => [
                        'en' => 'The exact softness of a nap you have been postponing since Tuesday.',
                        'es' => 'La suavidad exacta de esa siesta que llevas posponiendo desde el martes.',
                    ],
                    'price_cents' => 5900,
                    'compare_at_price_cents' => 7400,
                ],
                [
                    'slug' => 'ceramic-mug-duo',
                    'name' => ['en' => 'Ceramic Mug Duo', 'es' => 'Dúo de Tazas de Cerámica'],
                    'description' => [
                        'en' => 'Two hand-glazed mugs for coffee dates, real or hypothetical.',
                        'es' => 'Dos tazas esmaltadas a mano para citas de café, reales o hipotéticas.',
                    ],
                    'price_cents' => 3400,
                ],
                [
                    'slug' => 'linen-cushion-cover',
                    'name' => ['en' => 'Linen Cushion Cover', 'es' => 'Funda de Cojín de Lino'],
                    'description' => [
                        'en' => 'Stonewashed linen in a shade of terracotta that ties the whole room together.',
                        'es' => 'Lino lavado a la piedra en un tono terracota que unifica toda la habitación.',
                    ],
                    'price_cents' => 2400,
                ],
                [
                    'slug' => 'gratitude-journal',
                    'name' => ['en' => 'Gratitude Journal', 'es' => 'Diario de Gratitud'],
                    'description' => [
                        'en' => '365 blank pages. Today you can be grateful for money not spent.',
                        'es' => '365 páginas en blanco. Hoy puedes agradecer el dinero no gastado.',
                    ],
                    'price_cents' => 1900,
                ],
                [
                    'slug' => 'dried-flower-bouquet',
                    'name' => ['en' => 'Dried Flower Bouquet', 'es' => 'Ramo de Flores Secas'],
                    'description' => [
                        'en' => 'Everlasting pampas and bunny tails that require zero watering. We deliver on that promise.',
                        'es' => 'Pampas y colas de conejo eternas que no requieren riego. Esa promesa sí la cumplimos.',
                    ],
                    'price_cents' => 3900,
                ],
                [
                    'slug' => 'brass-photo-frame',
                    'name' => ['en' => 'Brass Photo Frame', 'es' => 'Marco de Fotos de Latón'],
                    'description' => [
                        'en' => 'A warm brass frame for the photo you keep meaning to print.',
                        'es' => 'Un cálido marco de latón para esa foto que llevas tiempo queriendo imprimir.',
                    ],
                    'price_cents' => 2900,
                ],
            ],
            'wellness' => [
                [
                    'slug' => 'calm-tea-ritual-set',
                    'name' => ['en' => 'Calm Tea Ritual Set', 'es' => 'Set Ritual de Té Calma'],
                    'description' => [
                        'en' => 'Chamomile, glass teapot, and the unhurried afternoon to go with it. Batteries not included; neither is anything else.',
                        'es' => 'Manzanilla, tetera de cristal y la tarde sin prisas que la acompaña. Pilas no incluidas; nada más tampoco.',
                    ],
                    'price_cents' => 4400,
                    'is_featured' => true,
                ],
                [
                    'slug' => 'jade-face-roller',
                    'name' => ['en' => 'Jade Face Roller', 'es' => 'Rodillo Facial de Jade'],
                    'description' => [
                        'en' => 'Cool jade for depuffing, sculpting, and feeling like you have a routine.',
                        'es' => 'Jade fresco para desinflamar, esculpir y sentir que tienes una rutina.',
                    ],
                    'price_cents' => 2900,
                    'compare_at_price_cents' => 3900,
                ],
                [
                    'slug' => 'yoga-mat-sunrise',
                    'name' => ['en' => 'Sunrise Yoga Mat', 'es' => 'Esterilla de Yoga Amanecer'],
                    'description' => [
                        'en' => 'An ombré mat with perfect grip for poses you fully intend to learn.',
                        'es' => 'Una esterilla degradada con agarre perfecto para posturas que tienes toda la intención de aprender.',
                    ],
                    'price_cents' => 5900,
                ],
                [
                    'slug' => 'essential-oil-trio',
                    'name' => ['en' => 'Essential Oil Trio', 'es' => 'Trío de Aceites Esenciales'],
                    'description' => [
                        'en' => 'Lavender for sleep, citrus for energy, eucalyptus for pretending you are at a spa.',
                        'es' => 'Lavanda para dormir, cítricos para la energía y eucalipto para fingir que estás en un spa.',
                    ],
                    'price_cents' => 4900,
                ],
                [
                    'slug' => 'silk-sleep-mask',
                    'name' => ['en' => 'Silk Sleep Mask', 'es' => 'Antifaz de Seda'],
                    'description' => [
                        'en' => 'Total darkness, pure silk, and the eight hours of sleep it symbolizes.',
                        'es' => 'Oscuridad total, seda pura y las ocho horas de sueño que simboliza.',
                    ],
                    'price_cents' => 2400,
                ],
                [
                    'slug' => 'positive-affirmation-cards',
                    'name' => ['en' => 'Positive Affirmation Cards', 'es' => 'Cartas de Afirmaciones Positivas'],
                    'description' => [
                        'en' => 'Fifty-two reminders that you are doing amazing, including card #7: "You saved money today."',
                        'es' => 'Cincuenta y dos recordatorios de que lo estás haciendo genial, incluida la carta 7: «Hoy ahorraste dinero».',
                    ],
                    'price_cents' => 1900,
                ],
                [
                    'slug' => 'bath-soak-crystals',
                    'name' => ['en' => 'Bath Soak Crystals', 'es' => 'Sales de Baño'],
                    'description' => [
                        'en' => 'Eucalyptus mineral crystals for the deep soak your group chat will hear all about.',
                        'es' => 'Cristales minerales de eucalipto para ese baño profundo del que se enterará todo tu chat de amigas.',
                    ],
                    'price_cents' => 2900,
                ],
            ],
        ];
    }
}
