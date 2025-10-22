<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14 items-center">
            <div class="flex space-x-4 items-center">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-blue-700 hover:text-blue-900">
                    🌿 CFL Estimator
                </a>
            </div>

            <div>
                @auth
                    <span class="text-gray-700 mr-4">Hello, {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button class="text-red-600 hover:text-red-800 font-medium">Logout</button>
                    </form>
                @endauth
            </div>
        </div>
    </div>
</nav>
