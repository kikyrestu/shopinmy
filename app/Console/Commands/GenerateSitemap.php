<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the sitemap.xml file';

    public function handle(): void
    {
        $this->info('Generating sitemap...');

        $sitemap = Sitemap::create()
            ->add(Url::create('/')->setPriority(1.0));

        // Products
        Product::where('is_active', true)->get()->each(function ($product) use ($sitemap) {
            $sitemap->add(
                Url::create("/products/{$product->slug}")
                    ->setLastModificationDate($product->updated_at)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8)
            );
        });

        // Categories
        Category::all()->each(function ($category) use ($sitemap) {
            $sitemap->add(
                Url::create("/categories/{$category->slug}")
                    ->setPriority(0.6)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
            );
        });

        // CMS Pages
        Page::all()->each(function ($page) use ($sitemap) {
            $sitemap->add(
                Url::create("/pages/{$page->slug}")
                    ->setPriority(0.4)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
            );
        });

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✅ Sitemap generated at public/sitemap.xml');
    }
}
