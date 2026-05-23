@extends('layouts.storefront')

@section('title', __('Order Successful'))

@section('content')
<div class="bg-gray-50 py-20 min-h-[60vh] flex items-center justify-center">
    <div class="max-w-4xl w-full mx-auto px-4 sm:px-6">
        <div class="bg-white rounded-3xl p-8 sm:p-12 text-center shadow-sm border border-gray-100">
            <div class="w-24 h-24 bg-emerald-100 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-8 shadow-inner">
                <i class="ph-bold ph-check text-5xl"></i>
            </div>
            
            <h1 class="text-3xl font-extrabold text-gray-900 mb-4">{{ __('Thank you for your order!') }}</h1>
            <p class="text-gray-500 mb-8">{{ __('Your order has been placed successfully and is now being processed. We have sent an email confirmation with the details.') }}</p>
            
            <div class="bg-gray-50 rounded-2xl p-6 mb-8 text-left">
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <div class="text-gray-500 font-medium mb-1">{{ __('Order Number') }}</div>
                        <div class="font-bold text-gray-900">{{ $order->order_number }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 font-medium mb-1">{{ __('Order Date') }}</div>
                        <div class="font-bold text-gray-900">{{ $order->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 font-medium mb-1">{{ __('Payment Method') }}</div>
                        <div class="font-bold text-gray-900 uppercase">{{ $order->payment->method ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-gray-500 font-medium mb-1">{{ __('Total Amount') }}</div>
                        <div class="font-bold text-brand-600">RM {{ number_format($order->total, 2) }}</div>
                    </div>
                </div>
            </div>
            
            @if($order->payment && $order->payment->method === 'manual_transfer' && in_array($order->payment->status, ['pending', 'failed']))
                <livewire:storefront.upload-receipt :order="$order" />
            @endif

            <div class="flex flex-col sm:flex-row gap-4 justify-center mt-12">
                <a href="{{ route('home') }}" class="px-8 py-3.5 bg-brand-600 hover:bg-brand-700 text-white font-bold rounded-full transition-all shadow-lg shadow-brand-500/30 transform hover:-translate-y-0.5">
                    {{ __('Continue Shopping') }}
                </a>
                @auth
                <a href="{{ route('dashboard') }}" class="px-8 py-3.5 bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-bold rounded-full transition-all">
                    {{ __('View Order Status') }}
                </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
