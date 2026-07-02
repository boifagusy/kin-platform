<header class="fixed top-0 left-0 w-full z-50 flex justify-between items-center px-4 sm:px-6 h-16 bg-white shadow-md border-b border-gray-100">
    <div class="flex items-center gap-2 sm:gap-3">
        <button id="menuButton" class="md:hidden p-2 rounded-full hover:bg-gray-100 transition-colors">
            <span class="material-symbols-outlined text-primary text-2xl">menu</span>
        </button>
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined fill text-primary text-2xl">shield</span>
            <span class="font-bold text-xl sm:text-2xl text-primary">Kin Admin</span>
        </div>
    </div>
    <div class="flex items-center gap-2 sm:gap-4">
        <div class="relative hidden sm:block">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
            <input class="pl-10 pr-4 py-2 rounded-full border border-gray-200 bg-white text-gray-700 focus:outline-none focus:ring-2 focus:ring-primary text-sm w-48 md:w-64" placeholder="Search..." type="text"/>
        </div>
        <button class="sm:hidden p-2 rounded-full hover:bg-gray-100">
            <span class="material-symbols-outlined text-gray-500">search</span>
        </button>
        <button class="p-2 rounded-full hover:bg-gray-100 relative">
            <span class="material-symbols-outlined text-gray-500">notifications</span>
            <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>
        <div class="w-8 h-8 rounded-full bg-green-100 text-primary flex items-center justify-center font-bold text-sm shadow-sm">
            {{ strtoupper(substr(Auth::guard('admin')->user()->name ?? 'A', 0, 2)) }}
        </div>
        <form method="POST" action="{{ route('admin.logout') }}" class="hidden sm:block">
            @csrf
            <button type="submit" class="text-sm text-red-600 hover:text-red-700">Logout</button>
        </form>
    </div>
</header>
