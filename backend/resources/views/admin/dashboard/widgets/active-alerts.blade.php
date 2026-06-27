<div class="bg-danger-light rounded-xl p-4 sm:p-5 border border-danger/20 shadow-premium flex flex-col justify-between relative overflow-hidden card-hover">
    <div class="absolute top-0 right-0 w-20 h-20 sm:w-24 sm:h-24 bg-danger/5 rounded-bl-full -z-10"></div>
    <div class="flex justify-between items-start mb-3 sm:mb-4">
        <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-danger text-on-error flex items-center justify-center pulse-dot">
            <span class="material-symbols-outlined fill text-xl sm:text-2xl">emergency_home</span>
        </div>
        <span class="bg-danger text-on-error px-2 py-0.5 rounded text-[10px] sm:text-[11px] font-bold uppercase tracking-wider">Critical</span>
    </div>
    <div>
        <h3 class="font-label-md text-xs sm:text-sm text-danger mb-1 font-bold">Active Alerts</h3>
        <p class="font-headline-lg text-2xl sm:text-3xl text-danger">{{ $active }}</p>
    </div>
</div>
