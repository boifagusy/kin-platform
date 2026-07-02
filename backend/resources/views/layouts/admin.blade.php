<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0, viewport-fit=cover" name="viewport"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Kin Admin') | System Overview Dashboard</title>
    
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: "#1a5632",
                        secondary: "#d4a017",
                        danger: "#DC2626",
                        "danger-light": "#FEE2E2",
                        "on-surface": "#181d18",
                        "on-surface-variant": "#4a5b4a",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-low": "#f0f5ed",
                        background: "#f0f7f2",
                        "gold-accent": "#D4A017"
                    },
                    fontFamily: {
                        "body-md": ["Manrope", "sans-serif"],
                        "headline-lg": ["Manrope", "sans-serif"]
                    }
                }
            }
        }
    </script>
    
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.fill {
            font-variation-settings: 'FILL' 1, 'wght' 500;
        }
        body { font-family: 'Manrope', 'Inter', sans-serif; background: #f0f7f2; }
        .card-hover { transition: all 0.2s ease; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
        .activity-row { transition: background-color 0.15s ease; }
        @media (max-width: 640px) {
            button, a, [role="button"] { min-height: 44px; }
        }
    </style>
    @stack('styles')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    </head>
<body class="antialiased min-h-screen">

    @include('partials.admin.header')
    @include('partials.admin.sidebar')
    @include('partials.admin.mobile-sidebar')

    <main class="md:ml-64 mt-16 p-4 sm:p-5 md:p-6 w-full max-w-7xl mx-auto">
        @yield('content')
    </main>

    <div id="mobileOverlay" class="fixed inset-0 bg-black/30 z-40 hidden md:hidden"></div>

    <script>
        const menuButton = document.getElementById('menuButton');
        const mobileSidebar = document.getElementById('mobileSidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');
        
        if (menuButton && mobileSidebar && mobileOverlay) {
            function openSidebar() {
                mobileSidebar.classList.remove('-translate-x-full');
                mobileOverlay.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
            
            function closeSidebar() {
                mobileSidebar.classList.add('-translate-x-full');
                mobileOverlay.classList.add('hidden');
                document.body.style.overflow = '';
            }
            
            menuButton.addEventListener('click', openSidebar);
            mobileOverlay.addEventListener('click', closeSidebar);
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !mobileSidebar.classList.contains('-translate-x-full')) {
                    closeSidebar();
                }
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html>
