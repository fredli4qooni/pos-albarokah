@if (session('success') || session('error') || session('warning') || session('status'))
<div x-data="{ show: true }"
     x-show="show"
     x-init="setTimeout(() => show = false, 4500)"
     x-transition:enter="transition ease-out duration-300 transform"
     x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
     x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="fixed bottom-5 right-5 z-50 max-w-sm w-full">
    
    @if (session('success'))
        <div class="bg-emerald-600 text-white p-4 rounded-xl shadow-xl flex items-start space-x-3 border border-emerald-500">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="flex-1 text-sm font-medium">
                {{ session('success') }}
            </div>
            <button @click="show = false" class="text-emerald-200 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @elseif (session('error'))
        <div class="bg-rose-600 text-white p-4 rounded-xl shadow-xl flex items-start space-x-3 border border-rose-500">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-rose-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1 text-sm font-medium">
                {{ session('error') }}
            </div>
            <button @click="show = false" class="text-rose-200 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @elseif (session('warning'))
        <div class="bg-amber-600 text-white p-4 rounded-xl shadow-xl flex items-start space-x-3 border border-amber-500">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 text-sm font-medium">
                {{ session('warning') }}
            </div>
            <button @click="show = false" class="text-amber-200 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @elseif (session('status'))
        <div class="bg-blue-600 text-white p-4 rounded-xl shadow-xl flex items-start space-x-3 border border-blue-500">
            <div class="flex-shrink-0 mt-0.5">
                <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1 text-sm font-medium">
                {{ session('status') }}
            </div>
            <button @click="show = false" class="text-blue-200 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

</div>
@endif
