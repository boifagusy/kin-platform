<div class="bg-surface-container-lowest rounded-xl p-4 sm:p-5 border border-gray-200 shadow-premium flex flex-col justify-between card-hover">
    <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-primary-container/20 text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-xl sm:text-2xl">devices</span>
        </div>
        <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
    </div>
    <div>
        <h3 class="font-label-md text-xs sm:text-sm text-on-surface-variant mb-1">Tracked Devices</h3>
        <p class="font-headline-lg text-2xl sm:text-3xl text-primary">{{ $devices }}</p>
    </div>
</div>
