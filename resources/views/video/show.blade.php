@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8 flex">
        <!-- Sidebar Ads -->
        <aside class="w-1/4 hidden lg:block pr-4">
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg mb-4">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-white">Advertisement</h3>
                    <div class="bg-gray-700 h-64 flex items-center justify-center">
                        <!-- Placeholder for Ad -->
                        <span class="text-gray-400">Ad Space</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg mb-4">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-white">Advertisement</h3>
                    <div class="bg-gray-700 h-64 flex items-center justify-center">
                        <!-- Placeholder for Ad -->
                        <span class="text-gray-400">Ad Space</span>
                    </div>
                </div>
            </div>
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg mb-4">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-white">Advertisement</h3>
                    <div class="bg-gray-700 h-64 flex items-center justify-center">
                        <!-- Placeholder for Ad -->
                        <span class="text-gray-400">Ad Space</span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="w-full lg:w-3/4">
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg">
                <div class="relative group">
                    <iframe src="{{ $video->embed_url }}" frameborder="0" class="w-full h-64 sm:h-96"
                        allowfullscreen></iframe>
                </div>
                <div class="p-4">
                    <h2 class="text-lg font-semibold">{{ $video->title }}</h2>
                    <p class="text-gray-400 mt-2">
                        <i
                            class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($video->added)->format('m/d/Y h:i A') }}
                    </p>
                    <p class="text-gray-400 mt-2">
                        <i class="fas fa-eye mr-1"></i>{{ number_format($video->views) }} views
                    </p>
                    <div class="flex items-center mt-2">
                        @for ($i = 1; $i <= 5; $i++)
                            <i
                                class="fas fa-star {{ $video->rate >= $i ? 'text-yellow-500' : ($video->rate >= $i - 0.5 ? 'fa-star-half-alt text-yellow-500' : 'text-gray-600') }}"></i>
                        @endfor
                        <span class="ml-2 text-gray-400">{{ $video->rate }} rating</span>
                    </div>
                    <p class="text-gray-400 mt-2">
                        <i class="fas fa-tags mr-1"></i>
                        @foreach ($video->tags as $tag)
                            <a href="/tags/{{ $tag->tag_name }}" class="mr-2 mb-2 hover:underline">{{ $tag->tag_name }}</a>
                        @endforeach
                    </p>
                </div>
            </div>

            <!-- Bottom Ads -->
            <div class="bg-gray-800 rounded-lg overflow-hidden shadow-lg mt-8">
                <div class="p-4">
                    <h3 class="text-lg font-semibold text-white">Advertisement</h3>
                    <div class="bg-gray-700 h-64 flex items-center justify-center">
                        <!-- Placeholder for Ad -->
                        <span class="text-gray-400">Ad Space</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
