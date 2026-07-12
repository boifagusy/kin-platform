<nav class="fixed left-0 top-16 h-[calc(100vh-64px)] w-64 z-40 overflow-y-auto bg-white border-r border-gray-200 hidden md:block shadow-sm">
    <div class="p-5 pb-3 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-primary">
                <span class="material-symbols-outlined fill">shield</span>
            </div>
            <div>
                <h2 class="font-bold text-lg text-primary">Admin Console</h2>
                <p class="text-gray-500 text-xs">Safety Management</p>
            </div>
        </div>
    </div>

    <div class="py-4 px-3">
        @include('partials.admin.navigation')
    </div>
</nav>
