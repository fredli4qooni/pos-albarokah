<x-app-layout>
    @section('page_title', 'Tambah Pelanggan Baru')

    <div class="max-w-2xl mx-auto space-y-6">
        
        <!-- Header -->
        <div>
            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-emerald-600 transition mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Kembali ke Data Pelanggan</span>
            </a>
            <h2 class="text-xl font-black text-slate-800 tracking-tight">Tambah Pelanggan / Petani Baru</h2>
            <p class="text-xs text-slate-500 mt-0.5">Daftarkan data petani untuk mempermudah pencatatan transaksi kredit & piutang</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-sm">
            <form method="POST" action="{{ route('customers.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nama Lengkap Petani / Pelanggan <span class="text-rose-500">*</span>
                    </label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus 
                           placeholder="Contoh: Pak Supardi, Haji Mansyur" 
                           class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Nomor HP / WhatsApp
                    </label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" 
                           placeholder="Contoh: 081234567890" 
                           class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition" />
                    <p class="text-[11px] text-slate-400 mt-1">Dapat digunakan untuk konfirmasi piutang via WhatsApp.</p>
                    <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                </div>

                <div>
                    <label for="address" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                        Alamat / Dusun / Kelompok Tani
                    </label>
                    <textarea id="address" name="address" rows="3" 
                              placeholder="Contoh: RT 02 / RW 01 Dusun Krajan, Kelompok Tani Sumber Makmur" 
                              class="block w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">{{ old('address') }}</textarea>
                    <x-input-error :messages="$errors->get('address')" class="mt-1.5" />
                </div>

                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                    <a href="{{ route('customers.index') }}" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-lg shadow-emerald-600/20 transition duration-150 transform hover:-translate-y-0.5">
                        Simpan Pelanggan
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
