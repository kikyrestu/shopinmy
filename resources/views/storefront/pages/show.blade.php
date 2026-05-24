@extends('layouts.storefront')

@section('title', $page->title)

@section('content')
<div class="bg-gray-50 dark:bg-[#121212] py-6 border-b border-gray-100 dark:border-gray-800">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex text-sm text-gray-500 dark:text-gray-500 font-medium">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">{{ __('Home') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-gray-900 dark:text-gray-100">{{ $page->title }}</li>
            </ol>
        </nav>
    </div>
</div>

<main class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-8 sm:p-12">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-8">{{ $page->title }}</h1>
            <div class="prose prose-brand max-w-none text-gray-600 dark:text-gray-400 leading-relaxed prose-headings:text-gray-900 dark:text-gray-100 prose-a:text-brand-600 prose-a:font-semibold">
                {!! $page->content !!}
            </div>
        </div>
    </div>
</main>
@endsection
