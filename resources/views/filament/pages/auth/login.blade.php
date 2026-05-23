<style>
    /* ── Login Page Custom Styles ── */
    .cb-login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;position:relative;overflow:hidden;transition:background .5s}
    .cb-login-wrap.is-dark{background:#030712}
    .cb-login-wrap.is-light{background:#f9fafb}
    .cb-bg-blob{position:absolute;border-radius:9999px;pointer-events:none}
    .cb-bg-blob.b1{top:-20%;left:-10%;width:50%;height:50%;filter:blur(120px)}
    .cb-bg-blob.b2{bottom:0;right:0;width:40%;height:40%;filter:blur(100px)}
    .is-dark .cb-bg-blob.b1{background:rgba(245,158,11,.1)}
    .is-light .cb-bg-blob.b1{background:rgba(245,158,11,.05)}
    .is-dark .cb-bg-blob.b2{background:rgba(31,41,55,.5)}
    .is-light .cb-bg-blob.b2{background:rgba(229,231,235,.5)}
    .cb-toggle{position:fixed;top:1.5rem;right:1.5rem;z-index:50;width:2.5rem;height:2.5rem;border-radius:.75rem;display:flex;align-items:center;justify-content:center;transition:all .3s;box-shadow:0 4px 6px -1px rgba(0,0,0,.1);cursor:pointer;border:1px solid}
    .is-dark .cb-toggle{background:#1f2937;color:#fbbf24;border-color:#374151}
    .is-light .cb-toggle{background:#fff;color:#6b7280;border-color:#e5e7eb}
    .cb-toggle:hover{transform:scale(1.1)}
    .cb-toggle svg{width:1.25rem;height:1.25rem}
    .cb-container{width:100%;max-width:420px;position:relative;z-index:10}
    .cb-brand{display:flex;flex-direction:column;align-items:center;text-align:center;margin-bottom:2rem}
    .cb-logo{width:4rem;height:4rem;border-radius:1rem;display:flex;align-items:center;justify-content:center;margin-bottom:1.25rem;position:relative;transition:all .3s;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .is-dark .cb-logo{background:#1f2937;border:1px solid #374151;color:#fbbf24}
    .is-light .cb-logo{background:#fff;border:1px solid #e5e7eb;color:#f59e0b}
    .cb-logo svg{width:2rem;height:2rem}
    .cb-dot{position:absolute;top:-.25rem;right:-.25rem;width:.875rem;height:.875rem;background:#22c55e;border-radius:9999px}
    .is-dark .cb-dot{border:2px solid #030712}
    .is-light .cb-dot{border:2px solid #fff}
    .cb-title{font-size:1.5rem;font-weight:700;letter-spacing:-.025em;transition:color .3s}
    .is-dark .cb-title{color:#fff}
    .is-light .cb-title{color:#111827}
    .cb-subtitle{font-size:.875rem;margin-top:.25rem;font-weight:500;transition:color .3s}
    .is-dark .cb-subtitle{color:#9ca3af}
    .is-light .cb-subtitle{color:#6b7280}
    .cb-card{border-radius:1rem;overflow:hidden;transition:all .3s;box-shadow:0 10px 15px -3px rgba(0,0,0,.1)}
    .is-dark .cb-card{background:#111827;border:1px solid #1f2937;box-shadow:0 10px 15px rgba(0,0,0,.3)}
    .is-light .cb-card{background:#fff;border:1px solid rgba(229,231,235,.8)}
    .cb-card-body{padding:1.5rem 2rem}
    .cb-section-hdr{display:flex;align-items:center;gap:.5rem;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:1px solid}
    .is-dark .cb-section-hdr{border-color:#1f2937}
    .is-light .cb-section-hdr{border-color:#f3f4f6}
    .cb-section-hdr svg{width:1rem;height:1rem}
    .is-dark .cb-section-hdr svg{color:#6b7280}
    .is-light .cb-section-hdr svg{color:#9ca3af}
    .cb-section-hdr span{font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em}
    .is-dark .cb-section-hdr span{color:#9ca3af}
    .is-light .cb-section-hdr span{color:#374151}
    .cb-form{display:flex;flex-direction:column;gap:1.25rem}
    .cb-label{display:block;font-size:.875rem;font-weight:500;margin-bottom:.375rem;transition:color .3s}
    .is-dark .cb-label{color:#d1d5db}
    .is-light .cb-label{color:#374151}
    .cb-input{display:block;width:100%;padding:.625rem .75rem;border-radius:.5rem;font-size:.875rem;outline:none;transition:all .2s;border:1px solid;box-shadow:0 1px 2px rgba(0,0,0,.05)}
    .is-dark .cb-input{background:#1f2937;border-color:#374151;color:#fff}
    .is-dark .cb-input::placeholder{color:#6b7280}
    .is-light .cb-input{background:#f9fafb;border-color:#d1d5db;color:#111827}
    .is-light .cb-input::placeholder{color:#9ca3af}
    .cb-input:focus{border-color:#f59e0b;box-shadow:0 0 0 3px rgba(245,158,11,.15)}
    .cb-pw-wrap{position:relative}
    .cb-pw-wrap .cb-input{padding-right:2.5rem}
    .cb-pw-toggle{position:absolute;top:0;right:0;bottom:0;display:flex;align-items:center;padding-right:.75rem;cursor:pointer;background:none;border:none}
    .cb-pw-toggle svg{width:1.25rem;height:1.25rem}
    .is-dark .cb-pw-toggle{color:#6b7280}
    .is-light .cb-pw-toggle{color:#9ca3af}
    .cb-remember{display:flex;align-items:center;gap:.5rem;padding-top:.25rem}
    .cb-remember input{width:1rem;height:1rem;accent-color:#f59e0b;cursor:pointer;border-radius:.25rem}
    .cb-remember label{font-size:.875rem;cursor:pointer;transition:color .3s}
    .is-dark .cb-remember label{color:#9ca3af}
    .is-light .cb-remember label{color:#4b5563}
    .cb-submit{width:100%;display:flex;justify-content:center;align-items:center;gap:.5rem;margin-top:1rem;padding:.625rem 1rem;border-radius:.5rem;font-size:.875rem;font-weight:600;color:#fff;background:#f59e0b;border:none;cursor:pointer;transition:all .2s;box-shadow:0 1px 3px rgba(245,158,11,.3)}
    .cb-submit:hover{background:#d97706}
    .cb-submit:active{transform:scale(.98)}
    .cb-submit:focus{outline:none;box-shadow:0 0 0 3px rgba(245,158,11,.4)}
    .cb-submit svg{width:1rem;height:1rem}
    .cb-submit.is-loading{opacity:.7;cursor:wait}
    .cb-spinner{animation:cbs .75s linear infinite}
    @keyframes cbs{to{transform:rotate(360deg)}}
    .cb-footer{padding:.875rem;border-top:1px solid;text-align:center;transition:all .3s}
    .is-dark .cb-footer{background:rgba(31,41,55,.5);border-color:#1f2937}
    .is-light .cb-footer{background:#f9fafb;border-color:#f3f4f6}
    .cb-footer p{font-size:.6875rem;display:flex;align-items:center;justify-content:center;gap:.375rem;font-weight:500}
    .is-dark .cb-footer p{color:#6b7280}
    .is-light .cb-footer p{color:#6b7280}
    .cb-footer svg{width:.75rem;height:.75rem}
    .cb-links{margin-top:2rem;display:flex;justify-content:center;align-items:center;gap:1rem;font-size:.875rem;font-weight:500}
    .is-dark .cb-links{color:#6b7280}
    .is-light .cb-links{color:#6b7280}
    .cb-links a{display:flex;align-items:center;gap:.375rem;transition:color .2s;text-decoration:none}
    .is-dark .cb-links a:hover{color:#fff}
    .is-light .cb-links a:hover{color:#111827}
    .cb-links svg{width:1rem;height:1rem}
    .cb-links .sep{transition:color .3s}
    .is-dark .cb-links .sep{color:#374151}
    .is-light .cb-links .sep{color:#d1d5db}
    .cb-error{margin-top:.25rem;font-size:.75rem;color:#ef4444}
</style>

<div class="cb-login-wrap"
     x-data="{ darkMode: localStorage.getItem('theme') === 'dark', showPw: false }"
     x-init="$watch('darkMode', val => { localStorage.setItem('theme', val ? 'dark' : 'light') })"
     :class="darkMode ? 'is-dark' : 'is-light'">

    {{-- Background --}}
    <div class="cb-bg-blob b1"></div>
    <div class="cb-bg-blob b2"></div>

    {{-- Dark Mode Toggle --}}
    <button @click="darkMode = !darkMode" class="cb-toggle" title="Toggle Dark Mode">
        <svg x-show="!darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
        </svg>
        <svg x-show="darkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
    </button>

    {{-- Container --}}
    <div class="cb-container">

        {{-- Brand --}}
        <div class="cb-brand">
            <div class="cb-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.516 2.17a.75.75 0 00-1.032 0 11.209 11.209 0 01-7.877 3.08.75.75 0 00-.722.515A12.74 12.74 0 002.25 9.75c0 5.942 4.064 10.933 9.563 12.348a.749.749 0 00.374 0c5.499-1.415 9.563-6.406 9.563-12.348 0-1.39-.223-2.73-.635-3.985a.75.75 0 00-.722-.516l-.143.001c-2.996 0-5.717-1.17-7.734-3.08zm3.094 8.016a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
                </svg>
                <div class="cb-dot"></div>
            </div>
            <h1 class="cb-title">Admin Portal</h1>
            <p class="cb-subtitle">{{ config('app.name', 'CommBuildy') }} System</p>
        </div>

        {{-- Card --}}
        <div class="cb-card">
            <div class="cb-card-body">
                <div class="cb-section-hdr">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>Otorisasi Sistem</span>
                </div>

                <form wire:submit="authenticate" class="cb-form">
                    {{-- Email --}}
                    <div>
                        <label for="email" class="cb-label">ID Pengguna / Email</label>
                        <input type="email" id="email" wire:model="data.email" required autofocus
                               placeholder="admin@commbuildy.com" class="cb-input">
                        @error('data.email')
                            <p class="cb-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="cb-label">Kata Sandi</label>
                        <div class="cb-pw-wrap">
                            <input :type="showPw ? 'text' : 'password'" id="password" wire:model="data.password" required
                                   placeholder="••••••••" class="cb-input">
                            <button type="button" @click="showPw = !showPw" class="cb-pw-toggle">
                                <svg x-show="!showPw" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <svg x-show="showPw" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('data.password')
                            <p class="cb-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Remember --}}
                    <div class="cb-remember">
                        <input id="remember" type="checkbox" wire:model="data.remember">
                        <label for="remember">Sesi tetap aktif</label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit" class="cb-submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="is-loading">
                        <span wire:loading.remove>Autentikasi Masuk</span>
                        <span wire:loading style="display:flex;align-items:center;gap:.5rem">
                            <svg class="cb-spinner" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:.25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path style="opacity:.75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memverifikasi...
                        </span>
                        <svg wire:loading.remove xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3 10a.75.75 0 01.75-.75h10.638L10.23 5.29a.75.75 0 111.04-1.08l5.5 5.25a.75.75 0 010 1.08l-5.5 5.25a.75.75 0 11-1.04-1.08l4.158-3.96H3.75A.75.75 0 013 10z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </form>
            </div>

            {{-- Footer --}}
            <div class="cb-footer">
                <p>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M12 1.5a5.25 5.25 0 00-5.25 5.25v3a3 3 0 00-3 3v6.75a3 3 0 003 3h10.5a3 3 0 003-3v-6.75a3 3 0 00-3-3v-3c0-2.9-2.35-5.25-5.25-5.25zm3.75 8.25v-3a3.75 3.75 0 10-7.5 0v3h7.5z" clip-rule="evenodd" />
                    </svg>
                    Koneksi Internal Terenkripsi
                </p>
            </div>
        </div>

        {{-- Links --}}
        <div class="cb-links">
            <a href="/">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                </svg>
                Lihat Toko
            </a>
            <span class="sep">|</span>
            <a href="#">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.712 4.33a9.027 9.027 0 011.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 00-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 010 9.424m-4.138-5.976a3.736 3.736 0 00-.88-1.388 3.737 3.737 0 00-1.388-.88m2.268 2.268a3.765 3.765 0 010 2.528m-2.268-4.796l-3.448 4.138m3.448-4.138a9.027 9.027 0 00-1.306-1.652m1.306 1.652l-4.138 3.448M7.288 19.67l3.448-4.138m-3.448 4.138a9.014 9.014 0 01-1.652-1.306 9.027 9.027 0 01-1.306-1.652m0 0l4.138-3.448M4.33 16.712a9.014 9.014 0 010-9.424m4.138 5.976a3.765 3.765 0 010-2.528m0 0a3.736 3.736 0 01.88-1.388 3.737 3.737 0 011.388-.88m-2.268 2.268L4.33 7.288m6.406 1.18L7.288 4.33m0 0a9.024 9.024 0 00-1.652 1.306A9.025 9.025 0 004.33 7.288" />
                </svg>
                Dukungan IT
            </a>
        </div>
    </div>
</div>
