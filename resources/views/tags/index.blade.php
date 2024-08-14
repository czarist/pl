@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8">
        <h1 class="text-2xl font-bold mb-6">Tags</h1>

        <!-- Formulário de pesquisa -->
        <div class="mb-6 relative">
            <input id="search" type="text" placeholder="Search tags..."
                class="w-full p-2 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">

            <!-- Ícone de loading -->
            <div id="loading-spinner" class="hidden absolute right-2 top-1/2 transform -translate-y-1/2">
                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                    </circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291l2.414-2.414a1 1 0 011.414 0l5.293 5.293-1.414 1.414-4.879-4.879-2.414 2.414a1 1 0 01-1.414 0L6 17.291z">
                    </path>
                </svg>
            </div>
        </div>

        <!-- Seletor de letras fixo para mobile e desktop -->
        <div class="mb-6">
            <select id="letter-select"
                class="w-full p-2 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select a letter...</option>
                @foreach ($letters as $letter)
                    <option value="{{ $letter === '#' ? 'special-characters' : $letter }}">
                        {{ $letter }}
                    </option>
                @endforeach
            </select>
        </div>

        <div id="tag-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($groupedTags as $letter => $tags)
                <div class="col-span-full letter-section">
                    <h2 id="{{ $letter === '#' ? 'special-characters' : $letter }}"
                        class="text-2xl font-bold mb-4 border-b-2 border-gray-600 pb-2">
                        {{ $letter }}
                    </h2>
                </div>
                @foreach ($tags as $tag)
                    <div class="bg-gray-800 p-4 rounded-lg shadow-lg tag-item relative overflow-hidden">
                        <a href="{{ url('/tag/' . $tag['tag']) }}"
                            class="flex items-center justify-center text-white hover:text-blue-400 transition-all duration-300">
                            <i class="fas fa-tag mr-2"></i>{{ $tag['tag_title'] }}
                        </a>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search');
            const tagItems = document.querySelectorAll('.tag-item');
            const letterSections = document.querySelectorAll('.letter-section');
            const letterSelect = document.getElementById('letter-select');
            const loadingSpinner = document.getElementById('loading-spinner');

            // Função debounce para melhorar a performance do search
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    const context = this;
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(context, args), wait);
                };
            }

            // Função de pesquisa otimizada
            const searchTags = debounce(function() {
                loadingSpinner.classList.remove('hidden');
                let filter = searchInput.value.toLowerCase();

                setTimeout(() => {
                    if (filter === '') {
                        letterSections.forEach(section => {
                            section.style.display = '';
                        });
                    }

                    tagItems.forEach(item => {
                        let text = item.innerText.toLowerCase();
                        if (text.includes(filter)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    letterSections.forEach(section => {
                        let hasVisibleTags = Array.from(section.nextElementSibling
                                .querySelectorAll('.tag-item'))
                            .some(item => item.style.display !== 'none');
                        section.style.display = hasVisibleTags ? '' : 'none';
                    });

                    loadingSpinner.classList.add('hidden');
                }, 500);
            }, 300); // Espera de 300ms para debounce

            // Evento de digitação com debounce
            searchInput.addEventListener('input', searchTags);

            // Navegação por letras
            letterSelect.addEventListener('change', function() {
                let targetId = this.value;
                if (targetId) {
                    let target = document.getElementById(targetId);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                }
            });
        });
    </script>

    <style>
        .tag-item a {
            transition: background-color 0.3s, color 0.3s;
            display: block;
            padding: 1rem;
            text-align: center;
        }

        .tag-item a:hover {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
        }
    </style>
@endsection
