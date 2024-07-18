@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8">
        <div class="space-y-8">
            @if (isset($tag))
                <h2 class="text-xl font-bold mb-6">Videos tagged with "{{ $tag }}"</h2>
            @endif
            @if (isset($searchTerm))
                <h2 class="text-xl font-bold mb-6">Videos searched by "{{ $searchTerm }}"</h2>
            @endif
            <div>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    @foreach ($videos as $video)
                        @include('partials.video-card', ['video' => $video])
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-6 flex justify-center">
            @if ($videos->hasPages())
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center space-x-4">
                    {{-- Previous Page Link --}}
                    @if ($videos->onFirstPage())
                        <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 rounded-md cursor-default">
                            <i class="fas fa-chevron-left"></i>
                        </span>
                    @else
                        <a href="{{ route('videos.page', ['page' => $videos->currentPage() - 1]) }}" rel="prev"
                            class="px-3 py-1 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-700">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    @endif

                    {{-- Page Selector --}}
                    <div class="flex items-center space-x-2">
                        <select onchange="location = this.value;" class="bg-gray-800 text-white rounded-md">
                            @for ($i = 1; $i <= $videos->lastPage(); $i++)
                                <option value="{{ route('videos.page', ['page' => $i]) }}"
                                    {{ $i == $videos->currentPage() ? 'selected' : '' }}>
                                    Page {{ $i }}
                                </option>
                            @endfor
                        </select>
                        <span class="text-sm font-medium text-gray-400">
                            of {{ $videos->lastPage() }}
                        </span>
                    </div>

                    {{-- Next Page Link --}}
                    @if ($videos->hasMorePages())
                        <a href="{{ route('videos.page', ['page' => $videos->currentPage() + 1]) }}" rel="next"
                            class="px-3 py-1 text-sm font-medium text-white bg-gray-800 rounded-md hover:bg-gray-700">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="px-3 py-1 text-sm font-medium text-gray-500 bg-gray-700 rounded-md cursor-default">
                            <i class="fas fa-chevron-right"></i>
                        </span>
                    @endif
                </nav>
            @endif
        </div>
    </div>
@endsection

<script>
    let oscillatingIntervals = {};

    function startOscillating(videoId) {
        const img = document.getElementById('thumbnail-' + videoId);
        const thumbs = JSON.parse(img.dataset.thumbs.replace(/&quot;/g, '"'));
        let index = 0;

        oscillatingIntervals[videoId] = setInterval(() => {
            img.src = thumbs[index].src;
            index = (index + 1) % thumbs.length;
        }, 500);
    }

    function stopOscillating(videoId) {
        clearInterval(oscillatingIntervals[videoId]);
        const img = document.getElementById('thumbnail-' + videoId);
        img.src = img.dataset.defaultThumb;
    }
</script>
