<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Hosanna Enterprise')</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400&family=Cinzel+Decorative:wght@400;700;900&family=Montserrat:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* Typography */
        body { font-family: 'Outfit', sans-serif; }
        h1 { font-family: 'Cinzel Decorative', serif; }
        h2, h3, h4, .font-serif { font-family: 'Playfair Display', serif; }
        
        /* Scroll Reveal Animations */
        .reveal { opacity: 0; transform: translateY(40px); transition: all 0.9s cubic-bezier(0.5, 0, 0, 1); }
        .reveal-left { opacity: 0; transform: translateX(-40px); transition: all 0.9s cubic-bezier(0.5, 0, 0, 1); }
        .reveal-right { opacity: 0; transform: translateX(40px); transition: all 0.9s cubic-bezier(0.5, 0, 0, 1); }
        .zoom-in { opacity: 0; transform: scale(0.95); transition: all 0.9s cubic-bezier(0.5, 0, 0, 1); }
        
        .reveal.active, .reveal-left.active, .reveal-right.active, .zoom-in.active {
            opacity: 1; transform: translate(0) scale(1);
        }
        
        /* Stagger Delays */
        .delay-100 { transition-delay: 100ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-400 { transition-delay: 400ms; }
        
        /* Custom UI Enhancements */
        .text-glow { text-shadow: 0 0 30px rgba(255,255,255,0.4); }
        .glass-nav { 
            background: rgba(15, 23, 42, 0.85); 
            backdrop-filter: blur(16px); 
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.05); 
        }
        
        /* Inner Page Header */
        .inner-header {
            background-image: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url('https://images.unsplash.com/photo-1438283173091-5dbf5c5a3206?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased selection:bg-indigo-500 selection:text-white flex flex-col min-h-screen">

    <!-- Navigation -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-300 py-6 {{ Route::is('home') ? 'bg-transparent' : 'glass-nav py-4' }}">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-white tracking-wider uppercase font-serif z-50 flex items-center gap-2">
                    <svg class="w-8 h-8 text-fuchsia-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    CAC Hosanna Chapel
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-sm font-medium transition-colors {{ Route::is('home') ? 'text-white' : 'text-white/80 hover:text-white' }}">Home</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium transition-colors {{ Route::is('about') ? 'text-white' : 'text-white/80 hover:text-white' }}">About Us</a>
                    <a href="{{ route('events') }}" class="text-sm font-medium transition-colors {{ Route::is('events') ? 'text-white' : 'text-white/80 hover:text-white' }}">Events</a>
                    <a href="{{ route('ministers') }}" class="text-sm font-medium transition-colors {{ Route::is('ministers') ? 'text-white' : 'text-white/80 hover:text-white' }}">Our Ministers</a>
                    <a href="{{ route('ministries') }}" class="text-sm font-medium transition-colors {{ Route::is('ministries') ? 'text-white' : 'text-white/80 hover:text-white' }}">Ministries</a>
                </div>
                
                <div class="hidden md:flex items-center">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="px-6 py-2.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-semibold hover:bg-white hover:text-indigo-900 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-semibold hover:bg-white hover:text-indigo-900 hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition-all duration-300">
                            Portal Login
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="text-white hover:text-indigo-200 focus:outline-none z-50 relative">
                        <!-- Hamburger Icon -->
                        <svg id="hamburger-icon" class="h-6 w-6 block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        <!-- Close (X) Icon -->
                        <svg id="close-icon" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Drawer (Hidden by default) -->
        <div id="mobile-menu" class="hidden md:hidden absolute top-full left-0 w-full glass-nav shadow-lg transition-all duration-300 overflow-hidden">
            <div class="px-4 pt-2 pb-6 space-y-2 border-t border-white/5">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-white hover:bg-white/10 transition-colors {{ Route::is('home') ? 'bg-white/10' : '' }}">Home</a>
                <a href="{{ route('about') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-white hover:bg-white/10 transition-colors {{ Route::is('about') ? 'bg-white/10' : '' }}">About Us</a>
                <a href="{{ route('events') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-white hover:bg-white/10 transition-colors {{ Route::is('events') ? 'bg-white/10' : '' }}">Events</a>
                <a href="{{ route('ministers') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-white hover:bg-white/10 transition-colors {{ Route::is('ministers') ? 'bg-white/10' : '' }}">Our Ministers</a>
                <a href="{{ route('ministries') }}" class="block px-3 py-2.5 rounded-lg text-base font-medium text-white hover:bg-white/10 transition-colors {{ Route::is('ministries') ? 'bg-white/10' : '' }}">Ministries</a>
                
                <div class="pt-4 border-t border-white/10">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="block w-full text-center px-4 py-3 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-md transition-all duration-300">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-3 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold shadow-md transition-all duration-300">
                            Portal Login
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Page Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-16 border-t border-slate-800/50 relative overflow-hidden mt-auto">
        <div class="absolute top-0 inset-x-0 h-px bg-gradient-to-r from-transparent via-indigo-500 to-transparent opacity-20"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-12 gap-12 mb-12">
            <!-- Brand -->
            <div class="md:col-span-4">
                <a href="{{ route('home') }}" class="text-3xl font-bold text-white tracking-widest uppercase font-serif mb-6 flex items-center gap-2">
                    <svg class="w-8 h-8 text-fuchsia-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    CAC Hosanna Chapel
                </a>
                <p class="mb-8 font-light leading-relaxed pr-4">A church dedicated to transforming lives through the undeniable power of the Gospel and the strength of our community.</p>
                <div class="flex space-x-4">
                    <a href="#" class="w-12 h-12 rounded-full bg-slate-900 border border-slate-800 flex items-center justify-center hover:bg-indigo-600 hover:border-indigo-600 hover:text-white transition-all hover:-translate-y-1"><svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg></a>
                </div>
            </div>
            
            <!-- Quick Links -->
            <div class="md:col-span-4 md:pl-10">
                <h4 class="text-white font-semibold mb-6 uppercase tracking-wider text-sm">Explore</h4>
                <ul class="space-y-4 font-light">
                    <li><a href="{{ route('about') }}" class="hover:text-fuchsia-400 transition-colors flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-slate-700 mr-2"></span>About Us</a></li>
                    <li><a href="{{ route('events') }}" class="hover:text-fuchsia-400 transition-colors flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-slate-700 mr-2"></span>Upcoming Events</a></li>
                    <li><a href="{{ route('ministers') }}" class="hover:text-fuchsia-400 transition-colors flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-slate-700 mr-2"></span>Our Ministers</a></li>
                    <li><a href="{{ route('ministries') }}" class="hover:text-fuchsia-400 transition-colors flex items-center"><span class="w-1.5 h-1.5 rounded-full bg-slate-700 mr-2"></span>Ministries</a></li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="md:col-span-4">
                <h4 class="text-white font-semibold mb-6 uppercase tracking-wider text-sm">Visit Us</h4>
                <address class="not-italic text-slate-400 font-light leading-loose">
                    123 Worship Avenue,<br>
                    Faith City, FC 12345<br>
                    <br>
                    <a href="mailto:info@hosanna.com" class="hover:text-fuchsia-400 transition-colors flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        info@hosanna.com
                    </a>
                    <a href="tel:+1234567890" class="hover:text-fuchsia-400 transition-colors flex items-center gap-2 mt-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        +1 (234) 567-890
                    </a>
                </address>
            </div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm border-t border-slate-800/50 pt-8 font-light">
            &copy; {{ date('Y') }} Hosanna Enterprise. All rights reserved.
        </div>
    </footer>

    <!-- JavaScript for Animations and Navbar -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Intersection Observer for Reveal Animations
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.15
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('active');
                    }
                });
            }, observerOptions);

            const animElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .zoom-in');
            animElements.forEach(el => observer.observe(el));

            // Navbar Glassmorphism on Scroll (only on home page where it starts transparent)
            const navbar = document.getElementById('navbar');
            const isHome = {{ Route::is('home') ? 'true' : 'false' }};
            
            if (isHome) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        navbar.classList.add('glass-nav');
                        navbar.classList.remove('bg-transparent', 'py-6');
                        navbar.classList.add('py-4');
                    } else {
                        // Only remove glass-nav if mobile menu is closed
                        const mobileMenu = document.getElementById('mobile-menu');
                        if (!mobileMenu || mobileMenu.classList.contains('hidden')) {
                            navbar.classList.remove('glass-nav', 'py-4');
                            navbar.classList.add('bg-transparent', 'py-6');
                        } else {
                            navbar.classList.remove('py-6');
                            navbar.classList.add('py-4');
                        }
                    }
                });
            }

            // Mobile Menu Toggle
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');

            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', () => {
                    const isCollapsed = mobileMenu.classList.contains('hidden');
                    if (isCollapsed) {
                        // Open Mobile Menu Drawer
                        mobileMenu.classList.remove('hidden');
                        hamburgerIcon.classList.add('hidden');
                        closeIcon.classList.remove('hidden');
                        
                        // Force background glass-nav if at top of transparent home page
                        if (isHome && window.scrollY <= 50) {
                            navbar.classList.add('glass-nav');
                            navbar.classList.remove('bg-transparent', 'py-6');
                            navbar.classList.add('py-4');
                        }
                    } else {
                        // Close Mobile Menu Drawer
                        mobileMenu.classList.add('hidden');
                        hamburgerIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                        
                        // Restore transparency if at top of home page
                        if (isHome && window.scrollY <= 50) {
                            navbar.classList.remove('glass-nav', 'py-4');
                            navbar.classList.add('bg-transparent', 'py-6');
                        }
                    }
                });

                // Clean up when expanding to desktop viewport
                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 768) { // 768px is the 'md' Tailwind breakpoint
                        mobileMenu.classList.add('hidden');
                        hamburgerIcon.classList.remove('hidden');
                        closeIcon.classList.add('hidden');
                        
                        if (isHome && window.scrollY <= 50) {
                            navbar.classList.remove('glass-nav', 'py-4');
                            navbar.classList.add('bg-transparent', 'py-6');
                        }
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>