<header id="header" class="bg-gray-800 p-4">
    <div class="container mx-auto flex justify-between items-center">
        <div class="text-lg font-bold">
            <a href="{{ url('/') }}" class="text-white hover:text-blue-400 transition-colors duration-300">
                <img id="icon-header" class="w90" src="{{ asset('icon.png') }}" alt="Video Gallery">
            </a>
        </div>
        <div class="flex items-center space-x-4">
            <form action="{{ route('videos.search') }}" method="GET" class="flex items-center space-x-4">
                <input name="search" type="text" placeholder="Search Video"
                    class="bg-gray-700 text-white px-4 py-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 placeholder-gray-400">
            </form>
            <nav class="flex space-x-4">
                <a href="{{ url('/') }}" class="text-gray-300 hover:text-white transition-colors duration-300"><i
                        class="fas fa-home mr-1"></i>Home</a>
                <a href="{{ route('tags.index') }}"
                    class="text-gray-300 hover:text-white transition-colors duration-300"><i
                        class="fas fa-tags mr-1"></i>Tags</a>
                <a href="{{ url('/random') }}" class="text-gray-300 hover:text-white transition-colors duration-300"><i
                        class="fas fa-random mr-1"></i>Random</a>
                <a href="#" class="text-gray-300 hover:text-white transition-colors duration-300"><i
                        class="fas fa-video mr-1"></i>Live</a>
            </nav>
        </div>
    </div>
</header>
<div class="mt-20"></div> <!-- Add this for testing scroll -->

<style>
    body {
        padding-top: 80px;
        /* Make space for fixed header */
    }

    .w50 {
        width: 50px;
        transition: 0.5s;
    }

    .w90 {
        width: 90px;
        transition: 0.5s;
    }

    #header {
        transition: top 0.3s;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1000;
    }

    #header.sticky {
        position: fixed;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.getElementById('header');
        const iconHeader = document.getElementById('icon-header');

        window.addEventListener('scroll', function() {
            if (window.scrollY > 0) {
                header.classList.add('sticky');
                iconHeader.classList.add('w50');
                iconHeader.classList.remove('w90');

            } else {
                header.classList.remove('sticky');
                iconHeader.classList.remove('w50');
                iconHeader.classList.add('w90');
            }
        });
    });
</script>
