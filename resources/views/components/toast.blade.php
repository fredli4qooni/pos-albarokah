<div x-data="{
        toasts: [
            @if (session('success'))
                { id: 1, type: 'success', message: '{{ addslashes(session('success')) }}' },
            @elseif (session('error'))
                { id: 2, type: 'error', message: '{{ addslashes(session('error')) }}' },
            @elseif (session('warning'))
                { id: 3, type: 'warning', message: '{{ addslashes(session('warning')) }}' },
            @elseif (session('status'))
                { id: 4, type: 'info', message: '{{ addslashes(session('status')) }}' },
            @endif
        ],
        init() {
            this.toasts.forEach(t => {
                setTimeout(() => this.remove(t.id), 4500);
            });
        },
        add(detail) {
            const id = Date.now() + Math.random();
            const type = detail.type || 'info';
            const message = detail.message || '';
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 4500);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
     }"
     @show-toast.window="add($event.detail)"
     class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm w-full pointer-events-none">
    
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0 scale-100"
             x-transition:leave="transition ease-in duration-200 transform"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="pointer-events-auto p-4 rounded-xl shadow-2xl flex items-start space-x-3 border text-white"
             :class="{
                 'bg-emerald-600 border-emerald-500': toast.type === 'success',
                 'bg-rose-600 border-rose-500': toast.type === 'error',
                 'bg-amber-600 border-amber-500': toast.type === 'warning',
                 'bg-blue-600 border-blue-500': toast.type === 'info'
             }">
            
            <!-- Icon -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <svg class="w-5 h-5 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="w-5 h-5 text-rose-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </template>
                <template x-if="toast.type === 'warning'">
                    <svg class="w-5 h-5 text-amber-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
                <template x-if="toast.type === 'info'">
                    <svg class="w-5 h-5 text-blue-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </div>

            <!-- Message -->
            <div class="flex-1 text-xs font-semibold leading-relaxed" x-text="toast.message"></div>

            <!-- Close Button -->
            <button @click="remove(toast.id)" type="button" class="text-white/70 hover:text-white transition flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>

</div>
