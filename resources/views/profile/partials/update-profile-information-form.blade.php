<section>
    <header>
        <h2 class="text-base font-bold text-slate-900">
            Informasi Profil Toko
        </h2>
        <p class="mt-1 text-xs text-slate-500">
            Perbarui nama pengguna, username untuk login, dan alamat email Anda.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Nama Lengkap
            </label>
            <input id="name" name="name" type="text" 
                   class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" 
                   value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Username (Untuk Login)
            </label>
            <input id="username" name="username" type="text" 
                   class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" 
                   value="{{ old('username', $user->username) }}" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('username')" />
        </div>

        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                Alamat Email
            </label>
            <input id="email" name="email" type="email" 
                   class="block w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition" 
                   value="{{ old('email', $user->email) }}" required autocomplete="username" />
            <x-input-error class="mt-1.5" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-2 text-xs text-amber-700 bg-amber-50 p-3 rounded-lg border border-amber-200">
                    Alamat email belum diverifikasi.
                    <button form="send-verification" class="underline font-bold text-amber-900 ml-1">
                        Kirim ulang email verifikasi.
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 font-semibold text-emerald-600">
                            Tautan verifikasi baru telah dikirim ke alamat email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl shadow-md shadow-emerald-600/20 transition">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-xs font-semibold text-emerald-600">
                    Tersimpan.
                </p>
            @endif
        </div>
    </form>
</section>
