@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-8">
        <h1 class="text-2xl font-bold mb-6">Tags</h1>

        <!-- Formulário de pesquisa -->
        <div class="mb-6">
            <input id="search" type="text" placeholder="Search tags..."
                class="w-full p-2 rounded bg-gray-700 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <!-- Box fixo com as letras -->
        <div id="letter-box"
            class="hidden sm:flex flex-wrap justify-center bg-gray-900 rounded-lg shadow-lg p-4 mb-6 lg:fixed lg:top-16 lg:left-1/2 lg:transform lg:-translate-x-1/2 lg:bg-gray-900 lg:rounded-lg lg:shadow-lg z-50">
            @foreach ($letters as $letter)
                <a href="#{{ $letter === '#' ? 'special-characters' : $letter }}"
                    class="block text-white text-xl hover:text-blue-400 transition-colors duration-300 mx-2">
                    {{ $letter }}
                </a>
            @endforeach
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
                        @if ($tag['thumb_src'])
                            <img src="{{ $tag['thumb_src'] }}" alt="{{ $tag['tag_title'] }}"
                                class="w-full h-32 object-cover rounded-lg">
                        @endif
                        <a href="{{ url('/tag/' . $tag['tag']) }}"
                            class="absolute inset-0 flex items-center justify-center text-white bg-black bg-opacity-50 hover:bg-opacity-0 transition-all duration-300">
                            <i class="fas fa-tag mr-2"></i>{{ $tag['tag_title'] }}
                        </a>
                    </div>
                @endforeach
            @endforeach
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const anchors = document.querySelectorAll('#letter-box a');
            const searchInput = document.getElementById('search');
            const tagItems = document.querySelectorAll('.tag-item');
            const letterSections = document.querySelectorAll('.letter-section');
            const letterBox = document.getElementById('letter-box');

            anchors.forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    let targetId = this.getAttribute('href').substring(1);
                    let target = document.getElementById(targetId);
                    if (target) {
                        anchors.forEach(a => a.classList.remove('text-blue-400', 'font-bold'));
                        this.classList.add('text-blue-400', 'font-bold');
                        target.scrollIntoView({
                            behavior: 'smooth'
                        });
                    }
                });
            });

            searchInput.addEventListener('keyup', function() {
                let filter = searchInput.value.toLowerCase();
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
                    let hasVisibleTags = Array.from(section.nextElementSibling.querySelectorAll(
                            '.tag-item'))
                        .some(item => item.style.display !== 'none');
                    section.style.display = hasVisibleTags ? '' : 'none';
                });
            });

            window.addEventListener('scroll', function() {
                const topOffset = letterBox.getBoundingClientRect().top;
                if (topOffset <= 80) {
                    letterBox.classList.add('is-sticky');
                } else {
                    letterBox.classList.remove('is-sticky');
                }
            });
        });
    </script>

    <style>
        #letter-box.is-sticky {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 50;
            background: #1a202c;
            padding: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .tag-item a {
            transition: background-color 0.3s, color 0.3s;
        }

        .tag-item a:hover {
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
        }
    </style>
@endsection
