@extends('layouts.frontend')

@section('title', 'Payment Cancelled')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-red-50 via-white to-blue-50">
    <div class="max-w-md w-full space-y-8 text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-red-100 mb-4">
                <svg class="h-10 w-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Payment Cancelled</h2>
            <p class="text-gray-600 mb-6">
                Your payment was cancelled. No charges have been made to your account.
            </p>
            <div class="space-y-4">
                <a href="{{ route('pricing') }}" class="block w-full bg-primary-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    Try Again
                </a>
                <a href="{{ route('home') }}" class="block w-full bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

