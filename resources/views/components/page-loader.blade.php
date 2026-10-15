{{-- Global Page Transition & Smooth Loading Screen --}}
<div id="page-loader"
     class="fixed inset-0 z-[99999] flex flex-col items-center justify-center bg-white/92 backdrop-blur-[2px] opacity-0 pointer-events-none transition-opacity duration-150 ease-out select-none"
     aria-hidden="true"
     role="status"
     aria-live="polite">
    <div class="flex flex-col items-center justify-center p-6 text-center max-w-xs mx-auto animate-loader-appear">
        {{-- Circular Loading Spinner dengan Nuansa Khas MPKSDI --}}
        <div class="relative w-16 h-16 sm:w-20 sm:h-20 flex items-center justify-center">
            {{-- Outer Rotating Ring: Cerulean MPKSDI (#005c8a) --}}
            <div class="absolute inset-0 rounded-full border-[3px] border-sky-100 border-t-[#005c8a] border-r-[#005c8a] animate-spin"></div>

            {{-- Inner Counter-Rotating Ring: Saffron Gold (#f8bb00) --}}
            <div class="absolute inset-2 sm:inset-2.5 rounded-full border-[2.5px] border-amber-100 border-b-[#f8bb00] border-l-[#f8bb00] animate-spin" style="animation-direction: reverse; animation-duration: 0.9s;"></div>

            {{-- Center Badge Emblem MPKSDI --}}
            <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-white shadow-2xs border border-slate-100 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('aset/logo-mpksdi.webp') }}" 
                     alt="MPKSDI" 
                     class="w-5 h-5 sm:w-6 sm:h-6 object-contain"
                     onerror="this.src='{{ asset('favicon.png') }}'">
            </div>
        </div>

        {{-- Typography Hierarchy --}}
        <div class="mt-4 space-y-1">
            <p class="text-xs sm:text-sm font-bold text-[#063A60] tracking-wide">
                Menyiapkan Halaman
            </p>
            <p class="text-[11px] text-slate-500 font-medium">
                Memuat data dan aset sistem...
            </p>
        </div>
    </div>
</div>
