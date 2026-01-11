<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MaluStore | Admin</title>
    <!-- Favicon -->
    <link rel="icon" href="https://i.ytimg.com/vi/x_9SdeVjfe4/hq720.jpg?sqp=-oaymwEhCK4FEIIDSFryq4qpAxMIARUAAAAAGAElAADIQj0AgKJD&rs=AOn4CLAHJnuMKW_ny8rT3ZCRZPM8jsPCRQ" type="image/x-icon">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
</head>

<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 font-sans">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div id="sidebar" class="sidebar-expanded bg-sidebar text-sidebar-foreground relative shadow-lg">
            <!-- Sidebar Rail for resizing -->
            <div class="sidebar-rail" id="sidebar-rail"></div>
            
            <!-- Logo and Title -->
            <div class="flex items-center h-16 px-4 border-b border-sidebar-border">
                <div class="flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-sidebar-accent" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    <h1 class="ml-3 text-xl font-bold sidebar-logo-text">MaluStore</h1>
                </div>
                <button id="sidebar-toggle" class="ml-auto text-sidebar-foreground hover:text-sidebar-accent transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
            
            <!-- Navigation -->
            <div class="py-4 flex flex-col h-[calc(100%-4rem)]">
                <!-- Main Navigation -->
<div class="px-3 space-y-1">
    <div class="sidebar-group-label text-xs font-medium text-sidebar-foreground/70 px-3 mb-2">Principal</div>
    
    <!-- Dashboard -->
    <a href="/dashboard" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('dashboard') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors" aria-label="Panel">
        <i class="fas fa-tachometer-alt w-5 h-5"></i>
        <span class="ml-3 sidebar-text">Panel</span>
    </a>
    
    <!-- Slide -->
    <a href="/slide-heroes" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('slide') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors" aria-label="Diapositivas">
        <i class="fas fa-slideshare w-5 h-5"></i>
        <span class="ml-3 sidebar-text">Diapositivas</span>
    </a>
    
    <!-- Products -->
    <a href="/products" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('products') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors" aria-label="Productos">
        <i class="fas fa-box w-5 h-5"></i>
        <span class="ml-3 sidebar-text">Productos</span>
    </a>
    
    <!-- Categories -->
    <a href="/categories" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('categories') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors" aria-label="Categorías">
        <i class="fas fa-tags w-5 h-5"></i>
        <span class="ml-3 sidebar-text">Categorías</span>
    </a>
    
    <!-- Orders -->
    <a href="/orders" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('orders*') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors" aria-label="Pedidos">
        <i class="fas fa-shopping-cart w-5 h-5"></i>
        <span class="ml-3 sidebar-text">Pedidos</span>
    </a>
</div>
                
               
                
                <!-- Settings Section -->
                <div class="px-3 mt-6 space-y-1">
                    <div class="sidebar-group-label text-xs font-medium text-sidebar-foreground/70 px-3 mb-2">Ajustes</div>
                    <a href="/settings" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('settings') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors">
                        <i class="fas fa-cog w-5 h-5"></i>
                        <span class="ml-3 sidebar-text">Configuración</span>
                    </a>
                    <a href="/profile" class="sidebar-menu-item flex items-center h-10 px-3 rounded-md text-sm font-medium {{ request()->is('profile') ? 'bg-sidebar-accent text-sidebar-accent-foreground' : 'hover:bg-sidebar-border/50' }} transition-colors">
                        <i class="fas fa-user-circle w-5 h-5"></i>
                        <span class="ml-3 sidebar-text">Perfil</span>
                    </a>
                </div>
                
                <!-- User Profile and Logout -->
                <div class="mt-auto border-t border-sidebar-border pt-4 px-3">
                    <div class="sidebar-profile flex items-center p-3 mb-2">
                        <div class="h-9 w-9 rounded-full bg-sidebar-accent/20 flex items-center justify-center text-sidebar-accent font-bold">
                            {{ auth()->user()->name[0] ?? 'U' }}
                        </div>
                        <div class="ml-3 sidebar-profile-name">
                            <div class="text-sm font-medium">{{ auth()->user()->name ?? 'User' }}</div>
                            <div class="text-xs text-sidebar-foreground/70">{{ auth()->user()->email ?? 'user@example.com' }}</div>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="sidebar-menu-item flex items-center h-10 w-full px-3 rounded-md text-sm font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                            <i class="fas fa-sign-out-alt w-5 h-5"></i>
                            <span class="ml-3 sidebar-text">Cerrar Sesión</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Top Navigation Bar -->
            <header class="h-16 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-800 flex items-center px-6">
                <button id="mobile-sidebar-toggle" class="mr-4 md:hidden text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                
                <div class="flex-1"></div>
                
                <!-- Right side navigation items -->
                <div class="flex items-center space-x-4">
                    <!-- Notifications -->
                    <button class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                    </button>
                    
                    <!-- Theme Toggle -->
                    <button id="theme-toggle" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">
                        <i class="fas fa-moon text-xl dark:hidden"></i>
                        <i class="fas fa-sun hidden dark:block text-xl"></i>
                    </button>
                </div>
            </header>
            
            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>
            
            <!-- Footer -->
            <footer class="border-t border-gray-200 dark:border-gray-800 py-4 px-6 text-center text-sm text-gray-500 dark:text-gray-400">
                <p>
                    Developed by <a href="https://web.facebook.com/urfavhak" class="font-medium text-primary hover:underline" target="_blank" rel="noopener noreferrer">XXXXXX</a>
                </p>
                <p class="mt-1">
                    &copy; 2025 MaluStore. Todos los derechos reservados.
                </p>
            </footer>
        </div>
    </div>

    <script>
        // Sidebar Toggle
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const mobileSidebarToggle = document.getElementById('mobile-sidebar-toggle');
        
        function toggleSidebar() {
            if (sidebar.classList.contains('sidebar-expanded')) {
                sidebar.classList.remove('sidebar-expanded');
                sidebar.classList.add('sidebar-collapsed');
                sidebarToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                `;
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                sidebarToggle.innerHTML = `
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                `;
            }
        }
        
        sidebarToggle.addEventListener('click', toggleSidebar);
        if (mobileSidebarToggle) {
            mobileSidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('hidden');
                sidebar.classList.toggle('md:block');
            });
        }
        
        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        
        // Check for saved theme preference or use the system preference
        const savedTheme = localStorage.getItem('theme') || 
            (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        
        // Apply the saved theme
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Toggle theme
        themeToggle.addEventListener('click', () => {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        });
        
        // Sidebar Rail Resize (Optional advanced feature)
        const sidebarRail = document.getElementById('sidebar-rail');
        let isResizing = false;
        
        sidebarRail.addEventListener('mousedown', (e) => {
            isResizing = true;
            document.addEventListener('mousemove', handleMouseMove);
            document.addEventListener('mouseup', () => {
                isResizing = false;
                document.removeEventListener('mousemove', handleMouseMove);
            }, { once: true });
        });
        
        function handleMouseMove(e) {
            if (!isResizing) return;
            
            const newWidth = e.clientX;
            if (newWidth >= 200 && newWidth <= 400) {
                sidebar.style.width = `${newWidth}px`;
                document.documentElement.style.setProperty('--sidebar-width', `${newWidth}px`);
            }
        }
    </script>
</body>
</html>