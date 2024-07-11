<div class="block bg-gray-800 rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300">
    <a href="{{ $video['url'] }}" onmouseover="startOscillating('{{ $video['id'] }}')"
        onmouseout="stopOscillating('{{ $video['id'] }}')">
        <div class="relative group">
            <img id="thumbnail-{{ $video['id'] }}" src="{{ $video['default_thumb'] }}"
                data-default-thumb="{{ $video['default_thumb'] }}" data-thumbs="{{ json_encode($video['thumbs']) }}"
                onerror="this.onerror=null;this.src='path/to/default-thumbnail.jpg';" alt="Video Thumbnail"
                class="w-full h-32 sm:h-48 object-cover">
            <div
                class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <i class="fas fa-play-circle text-white text-4xl"></i>
            </div>
            <div class="absolute bottom-0 left-0 bg-black bg-opacity-75 text-white text-xs px-2 py-1">
                <i class="fas fa-clock mr-1"></i>{{ $video['length_min'] }}
            </div>
        </div>
    </a>
    <div class="p-4">
        <h2 class="text-lg font-semibold">{{ $video['title'] }}</h2>
        <p class="text-gray-400 mt-2">
            <i class="fas fa-calendar-alt mr-1"></i>{{ \Carbon\Carbon::parse($video['added'])->format('m/d/Y h:i A') }}
        </p>
        <p class="text-gray-400 mt-2">
            <i class="fas fa-eye mr-1"></i>{{ number_format($video['views']) }} views
        </p>
        <div class="flex items-center mt-2">
            @for ($i = 1; $i <= 5; $i++)
                <i
                    class="fas fa-star {{ $video['rate'] >= $i ? 'text-yellow-500' : ($video['rate'] >= $i - 0.5 ? 'fa-star-half-alt text-yellow-500' : 'text-gray-600') }}"></i>
            @endfor
            <span class="ml-2 text-gray-400">{{ $video['rate'] }} rating</span>
        </div>
        <p class="text-gray-400 mt-2">
            <i class="fas fa-tags mr-1"></i>

            @foreach ($video['tags'] as $tag)
                <a href="/tags/{{ $tag }}" class="mr-2 mb-2 hover:underline">{{ $tag }}</a>
            @endforeach
        </p>
    </div>
</div>
