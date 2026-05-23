<?php

namespace App\Livewire\Storefront;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class ProductList extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public $search = '';

    #[Url(except: '')]
    public $category = '';

    #[Url(except: [])]
    public $brand = [];

    #[Url(except: '')]
    public $min_price = '';

    #[Url(except: '')]
    public $max_price = '';

    #[Url(except: 'latest')]
    public $sort = 'latest';

    public function updated($property)
    {
        if (in_array($property, ['search', 'category', 'brand', 'min_price', 'max_price', 'sort'])) {
            $this->resetPage();
        }
    }

    public function clearFilters()
    {
        $this->reset(['search', 'category', 'brand', 'min_price', 'max_price', 'sort']);
        $this->resetPage();
    }

    public function setSort($sortOption)
    {
        $this->sort = $sortOption;
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with('primaryImage')
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->withSum('orderItems', 'qty');

        if (!empty($this->search)) {
            $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $this->search);
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhereHas('brand', function ($q2) use ($searchTerm) {
                      $q2->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if (!empty($this->category)) {
            $categorySlug = $this->category;
            $query->whereHas('category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        if (!empty($this->brand)) {
            $query->whereIn('brand_id', (array) $this->brand);
        }

        if (!empty($this->min_price)) {
            $query->where('price', '>=', $this->min_price);
        }
        if (!empty($this->max_price)) {
            $query->where('price', '<=', $this->max_price);
        }

        switch ($this->sort) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'popular':
                $query->orderBy('order_items_sum_qty', 'desc');
                break;
            case 'rating':
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        $products = $query->paginate(20);

        $categories = Category::whereNull('parent_id')->with('children')->get();
        $brands = Brand::orderBy('name')->get();

        return view('livewire.storefront.product-list', compact('products', 'categories', 'brands'));
    }
}
