<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Storefront\HomeController;
use App\Http\Controllers\Storefront\ProductController;
use App\Livewire\Storefront\ProductDetail;
use App\Livewire\Storefront\CartView;
use App\Livewire\Storefront\CheckoutView;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/flash-sale', \App\Livewire\Storefront\FlashSaleView::class)->name('flash-sale.index');
Route::get('/bundles', \App\Livewire\Storefront\BundleListView::class)->name('bundles.index');
Route::get('/product/{slug}', ProductDetail::class)->name('product.show');
Route::get('/cart', CartView::class)->name('cart.index');
Route::get('/checkout', CheckoutView::class)->name('checkout.index');
Route::get('/checkout/success/{order}', function (\App\Models\Order $order) {
    if (auth()->check() && $order->user_id !== auth()->id()) {
        abort(403);
    }
    if (!auth()->check() && session('last_order_id') !== $order->id) {
        abort(403);
    }
    $order->load('payment');
    return view('storefront.checkout.success', compact('order'));
})->name('checkout.success');

Route::get('/payment/process/{order}', [\App\Http\Controllers\PaymentController::class, 'process'])->name('payment.process');
Route::get('/payment/callback/billplz', [\App\Http\Controllers\PaymentController::class, 'billplzCallback'])->name('payment.callback.billplz');
Route::get('/payment/callback/stripe', [\App\Http\Controllers\PaymentController::class, 'stripeCallback'])->name('payment.callback.stripe');

// Webhooks (without CSRF)
Route::post('/webhook/stripe', [\App\Http\Controllers\StripeWebhookController::class, 'handle'])
    ->name('webhook.stripe')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/webhook/billplz', [\App\Http\Controllers\PaymentController::class, 'billplzWebhook'])
    ->name('webhook.billplz')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

// Fallback route to serve payment proofs directly (Bypasses broken symlinks in Docker/VPS)
Route::get('/storage/payment-proofs/{filename}', function ($filename) {
    $path = storage_path('app/public/payment-proofs/' . $filename);
    if (!file_exists($path)) {
        abort(404);
    }
    return response()->file($path);
})->where('filename', '.*');

Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::get('/', \App\Livewire\Storefront\Dashboard\Overview::class)->name('dashboard');
    Route::get('/orders', \App\Livewire\Storefront\Dashboard\OrderHistory::class)->name('dashboard.orders');
    Route::get('/orders/{order}', \App\Livewire\Storefront\Dashboard\OrderDetail::class)->name('dashboard.orders.show');
    Route::get('/orders/{order}/track', \App\Livewire\Storefront\Dashboard\TrackOrder::class)->name('dashboard.orders.track');
    Route::get('/addresses', \App\Livewire\Storefront\Dashboard\AddressBook::class)->name('dashboard.addresses');
    Route::get('/wishlist', \App\Livewire\Storefront\Dashboard\WishlistPage::class)->name('dashboard.wishlist');
    Route::get('/loyalty', \App\Livewire\Storefront\Dashboard\LoyaltyView::class)->name('dashboard.loyalty');
    Route::get('/profile', \App\Livewire\Storefront\Dashboard\ProfileEdit::class)->name('dashboard.profile');
});

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['ms', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('locale.switch');

Route::get('/pages/{slug}', function (string $slug) {
    $page = \App\Models\Page::where('slug', $slug)->firstOrFail();
    return view('storefront.pages.show', compact('page'));
})->name('pages.show');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

// Social Authentication Routes
Route::get('/auth/google', [\App\Http\Controllers\Auth\SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\SocialAuthController::class, 'handleGoogleCallback']);

require __DIR__.'/auth.php';

// ==========================================
// RUTE SAKTI KHUSUS CPANEL (Bypass Terminal)
// ==========================================

// 1. Bikin symlink gambar (Jalankan sekali pas baru deploy)
// Kunjungi: domain-lu.com/cpanel-link
Route::get('/cpanel-link', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return 'Storage Link berhasil dibuat! Gambar sekarang harusnya muncul.';
});

// 2. Bersihin Cache (Jalankan kalau ada error view/config nyangkut)
// Kunjungi: domain-lu.com/cpanel-clear
Route::get('/cpanel-clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return 'Semua Cache berhasil dibersihkan dari server!';
});

// 3. Migrate Database Darurat (Kalau males upload SQL manual)
// Kunjungi: domain-lu.com/cpanel-migrate
Route::get('/cpanel-migrate', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'Migrasi Database Sukses Dijalankan!';
});
