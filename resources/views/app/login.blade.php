<x-layouts.app title="Staff Login - CafeFlow">
    <div class="app-shell" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top left, var(--bg-surface) 20%, var(--bg-app) 100%);">
        <div class="panel" style="width: 100%; max-width: 420px; padding: 40px; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); margin: 20px;">
            <div style="text-align: center; margin-bottom: 30px;">
                <a class="brand" href="{{ route('landing') }}" style="display: inline-flex; font-size: 24px; font-weight: 700; text-decoration: none; align-items: center; gap: 8px; margin-bottom: 12px;">
                    <span class="brand-mark" style="background: var(--text-gold); color: var(--bg-app); border-radius: 6px; padding: 2px 8px; font-weight: 800; font-size: 16px;">CF</span>
                    <span style="color: var(--text-main);">CafeFlow</span>
                </a>
                <span class="eyebrow" style="display: block; margin-top: 8px; color: var(--text-gold);">Internal Staff Access</span>
                <h1 style="font-size: 28px; font-weight: 800; margin: 8px 0 4px 0; color: var(--text-main);">Masuk Sistem</h1>
                <p class="muted" style="font-size: 14px;">Silakan masuk menggunakan akun kerja Anda.</p>
            </div>

            @if (session('error'))
                <div class="panel" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #ef4444; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>⚠️</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="panel" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #10b981; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <span>✅</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <form action="{{ url('/staff/login') }}" method="POST" style="display: flex; flex-direction: column; gap: 18px;">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <label for="email" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Email Kerja</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="staff@cafeflow.test" style="width: 100%; padding: 12px 16px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: var(--text-main); font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    @error('email')
                        <span style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label for="password" style="font-size: 13px; font-weight: 600; color: var(--text-main);">Password</label>
                    </div>
                    <input type="password" id="password" name="password" required placeholder="••••••••" style="width: 100%; padding: 12px 16px; border-radius: 8px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); color: var(--text-main); font-size: 14px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.1)'">
                    @error('password')
                        <span style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</span>
                    @enderror
                </div>

                <button class="btn btn-primary" type="submit" style="width: 100%; padding: 14px; border-radius: 8px; font-weight: 700; font-size: 15px; margin-top: 10px; background: var(--text-gold); color: var(--bg-app); border: none; cursor: pointer; transition: transform 0.1s, opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" onmousedown="this.style.transform='scale(0.98)'" onmouseup="this.style.transform='scale(1)'">
                    Masuk ke Dashboard
                </button>
            </form>

            <div style="text-align: center; margin-top: 24px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 20px;">
                <a href="{{ route('landing') }}" style="display: inline-block; font-size: 13px; color: var(--text-gold); text-decoration: none;">← Kembali ke Landing Page</a>
            </div>
        </div>
    </div>
</x-layouts.app>
