@extends('layouts.app')

@section('content')
    <div class="flex items-center justify-center h-screen bg-gray-900">
        <div class="text-center">
            <i class="fas fa-exclamation-triangle text-yellow-500 text-6xl mb-4"></i>
            <h1 class="text-4xl text-white font-bold mb-4">404 - Page Not Found</h1>
            <p class="text-gray-400 mb-8">The page you are looking for does not exist.</p>
            <a href="{{ url('/') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 transition">Go to
                Homepage</a>
        </div>
    </div>
@endsection
