<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Voucher;
use App\Models\Page;

class HomeController extends Controller
{
    public function index()
    {
        $banners = Banner::where('is_active', true)->orderBy('sort')->get();
        
        $promoVoucher = Voucher::where('is_active', true)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        $featuredCategories = Category::withCount(['products' => function ($q) {
            $q->where('is_active', true);
        }])
            ->orderByDesc('products_count')
            ->take(12)
            ->get();

        $activeFlashSale = \App\Models\FlashSale::with(['products' => function ($q) {
                $q->with('primaryImage')->take(5);
            }])
            ->where('is_active', true)
            ->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->first();

        $recommendedProducts = Product::with('primaryImage')
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withSum('orderItems', 'qty')
            ->inRandomOrder()
            ->take(24)
            ->get();

        return view('storefront.home', compact('recommendedProducts', 'banners', 'promoVoucher', 'featuredCategories', 'activeFlashSale'));
    }
}
