<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Books Marketplace') - Books Marketplace</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        .container {
            max-width: 1200px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <a href="{{ route('books.browse') }}" class="flex items-center space-x-2">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                        <span class="text-xl font-bold text-gray-900">Books Marketplace</span>
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('books.browse') }}" 
                       class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                        Browse Books
                    </a>
                    @if(auth()->check())
                        <a href="{{ route('books.dashboard') }}" 
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Dashboard
                        </a>
                        <a href="{{ route('books.create') }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                            <i class="fas fa-plus mr-2"></i>Add Book
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-gray-700 hover:text-blue-600 px-3 py-2 rounded-md text-sm font-medium transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
                            Register
                        </a>
                    @endif
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button type="button" class="text-gray-700 hover:text-blue-600 p-2" onclick="toggleMobileMenu()">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Navigation -->
            <div id="mobileMenu" class="hidden md:hidden border-t border-gray-200">
                <div class="px-2 pt-2 pb-3 space-y-1">
                    <a href="{{ route('books.browse') }}" 
                       class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                        Browse Books
                    </a>
                    @if(auth()->check())
                        <a href="{{ route('books.dashboard') }}" 
                           class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Dashboard
                        </a>
                        <a href="{{ route('books.create') }}" 
                           class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Add Book
                        </a>
                        <a href="{{ route('logout') }}" 
                           class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Logout
                        </a>
                    @else
                        <a href="{{ route('login') }}" 
                           class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Login
                        </a>
                        <a href="{{ route('register') }}" 
                           class="text-gray-700 hover:text-blue-600 block px-3 py-2 rounded-md text-base font-medium">
                            Register
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-12">
        <div class="container mx-auto px-4 py-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Books Marketplace</h3>
                    <p class="text-gray-400">Discover, promote, and sell your books to a global audience.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ route('books.browse') }}" class="hover:text-white">Browse Books</a></li>
                        @if(auth()->check())
                            <li><a href="{{ route('books.create') }}" class="hover:text-white">Add Book</a></li>
                            <li><a href="{{ route('books.dashboard') }}" class="hover:text-white">Dashboard</a></li>
                        @endif
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Support</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Help Center</a></li>
                        <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                        <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Connect</h3>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} Books Marketplace. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }
    </script>
</body>
</html>
