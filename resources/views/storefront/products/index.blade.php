@extends('layouts.storefront')

@section('title', request('search') ? __('Search Results for') . ' "' . request('search') . '"' : __('All Products'))

@section('content')
<main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @livewire('storefront.product-list')
</main>
@endsection
