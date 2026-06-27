<div class="bg-surface-container-lowest rounded-xl p-4 sm:p-5 border border-gray-200 shadow-premium flex flex-col justify-between card-hover">
    <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant">
            <span class="material-symbols-outlined text-xl sm:text-2xl">group</span>
        </div>
        <span class="text-tertiary-container font-label-md text-xs sm:text-sm flex items-center gap-1">
            <span class="material-symbols-outlined text-sm">trending_up</span> +12%
        </span>
    </div>
    <div>
        <h3 class="font-label-md text-xs sm:text-sm text-on-surface-variant mb-1">Total Users</h3>
        <p class="font-headline-lg text-2xl sm:text-3xl text-on-surface">{{ number_format($total) }}</p>
    </div>
</div>
