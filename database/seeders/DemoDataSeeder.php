<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Demo Customer
        $customer = \App\Models\User::firstOrCreate(
            ['email' => 'customer@customer.com'],
            [
                'name' => 'Demo Customer',
                'password' => bcrypt('password'),
                'phone' => '+60123456789',
            ]
        );

        // 2. Categories
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics'],
            ['name' => 'Men\'s Fashion', 'slug' => 'mens-fashion'],
            ['name' => 'Women\'s Fashion', 'slug' => 'womens-fashion'],
            ['name' => 'Home & Living', 'slug' => 'home-living'],
            ['name' => 'Beauty', 'slug' => 'beauty'],
        ];

        foreach ($categories as $cat) {
            \App\Models\Category::firstOrCreate(['slug' => $cat['slug']], $cat);
        }

        // 3. Brands
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple'],
            ['name' => 'Samsung', 'slug' => 'samsung'],
            ['name' => 'Nike', 'slug' => 'nike'],
            ['name' => 'Adidas', 'slug' => 'adidas'],
            ['name' => 'Padini', 'slug' => 'padini'],
            ['name' => 'Habib', 'slug' => 'habib'],
        ];

        foreach ($brands as $brand) {
            \App\Models\Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }

        // 4. Products & Variants
        $elecId = \App\Models\Category::where('slug', 'electronics')->first()->id;
        $mensId = \App\Models\Category::where('slug', 'mens-fashion')->first()->id;
        
        $appleId = \App\Models\Brand::where('slug', 'apple')->first()->id;
        $nikeId = \App\Models\Brand::where('slug', 'nike')->first()->id;

        $products = [
            [
                'name' => 'iPhone 15 Pro Max',
                'slug' => 'iphone-15-pro-max',
                'description' => 'The ultimate iPhone with titanium design.',
                'price' => 5499.00,
                'category_id' => $elecId,
                'brand_id' => $appleId,
                'images' => ['https://ui-avatars.com/api/?name=iPhone+15&background=F3F4F6&color=374151&size=512'],
                'variants' => [
                    ['name' => 'Storage', 'value' => '256GB - Natural Titanium', 'price_modifier' => 0, 'stock' => 20],
                    ['name' => 'Storage', 'value' => '512GB - Natural Titanium', 'price_modifier' => 1000, 'stock' => 15],
                    ['name' => 'Storage', 'value' => '1TB - Natural Titanium', 'price_modifier' => 2000, 'stock' => 15],
                ]
            ],
            [
                'name' => 'Nike Air Force 1 \'07',
                'slug' => 'nike-air-force-1-07',
                'description' => 'The radiance lives on in the Nike Air Force 1 \'07.',
                'price' => 439.00,
                'category_id' => $mensId,
                'brand_id' => $nikeId,
                'images' => ['https://ui-avatars.com/api/?name=Nike+AF1&background=F3F4F6&color=374151&size=512'],
                'variants' => [
                    ['name' => 'Size', 'value' => 'US 8', 'price_modifier' => 0, 'stock' => 30],
                    ['name' => 'Size', 'value' => 'US 9', 'price_modifier' => 0, 'stock' => 40],
                    ['name' => 'Size', 'value' => 'US 10', 'price_modifier' => 0, 'stock' => 30],
                ]
            ],
            [
                'name' => 'Apple Watch Series 9',
                'slug' => 'apple-watch-series-9',
                'description' => 'Smarter. Brighter. Mightier.',
                'price' => 1899.00,
                'category_id' => $elecId,
                'brand_id' => $appleId,
                'images' => ['https://ui-avatars.com/api/?name=Watch+S9&background=F3F4F6&color=374151&size=512'],
                'variants' => []
            ]
        ];

        foreach ($products as $pData) {
            $variants = $pData['variants'];
            unset($pData['variants']);
            $pData['is_active'] = true;

            $product = \App\Models\Product::firstOrCreate(['slug' => $pData['slug']], $pData);

            foreach ($variants as $vData) {
                $vData['product_id'] = $product->id;
                \App\Models\ProductVariant::firstOrCreate([
                    'product_id' => $product->id, 
                    'name' => $vData['name'],
                    'value' => $vData['value']
                ], $vData);
            }

            // Generate some reviews
            if ($product->reviews()->count() == 0) {
                \App\Models\Review::create([
                    'product_id' => $product->id,
                    'user_id' => $customer->id,
                    'rating' => rand(4, 5),
                    'comment' => 'Great product! Highly recommended. Fast shipping to KL.',
                ]);
            }
        }

        // 5. Banners
        $banners = [
            [
                'title' => 'Raya Mega Sale 2026',
                'image' => 'https://ui-avatars.com/api/?name=Raya+Sale&background=10b981&color=ffffff&size=1024',
                'link' => '/products',
                'sort' => 1
            ],
            [
                'title' => 'New Apple Launch',
                'image' => 'https://ui-avatars.com/api/?name=Apple+Launch&background=111827&color=ffffff&size=1024',
                'link' => '/product/iphone-15-pro-max',
                'sort' => 2
            ]
        ];

        foreach ($banners as $banner) {
            \App\Models\Banner::firstOrCreate(['title' => $banner['title']], $banner);
        }
    }
}
