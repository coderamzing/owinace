@extends('layouts.frontend')

@section('title', 'Thank You')

@section('content')
<div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-primary-50 via-white to-blue-50">
    <div class="max-w-md w-full space-y-8 text-center">
        <div class="bg-white rounded-2xl shadow-xl p-8">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 mb-4">
                <svg class="h-10 w-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-2">Thank You!</h2>
            <p class="text-gray-600 mb-6">
                We appreciate your business. Your subscription has been activated and you can now access all the features of your plan.
            </p>
            <div class="space-y-4">
                <a href="{{ route('login') }}" class="block w-full bg-primary-600 text-white text-center py-3 rounded-lg font-semibold hover:bg-primary-700 transition-colors">
                    Go to Dashboard
                </a>
                <a href="{{ route('home') }}" class="block w-full bg-gray-200 text-gray-800 text-center py-3 rounded-lg font-semibold hover:bg-gray-300 transition-colors">
                    Back to Home
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

