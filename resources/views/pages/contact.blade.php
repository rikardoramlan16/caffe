<x-layouts.app title="Hubungi Kami - CafeFlow">
    <div class="app-shell">
        <header class="topbar">
            <div class="container nav">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>CafeFlow</span></a>
                <nav class="nav-links" aria-label="Navigasi utama">
                    <a href="{{ route('landing') }}">Home</a>
                    <a href="{{ route('menu.preview') }}">Menu</a>
                    <a href="{{ route('landing') }}#cara-kerja">Cara Kerja</a>
                    <a href="{{ route('about') }}">Tentang</a>
                    <a class="active" href="{{ route('contact') }}">Kontak</a>
                </nav>
                <div class="actions">
                    <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                    <a class="btn" href="{{ route('login') }}">Masuk Staf</a>
                    <a class="btn btn-gold" href="{{ route('menu.preview') }}">Pesan Sekarang</a>
                </div>
            </div>
        </header>

        <main class="container section" style="max-width: 900px; padding: 60px 20px;">
            <span class="eyebrow" style="margin-bottom: 14px; display: inline-flex;">Get in touch</span>
            <h1 style="font-size: clamp(34px, 5vw, 54px); margin-bottom: 24px;">Hubungi Kopi Senja</h1>
            
            <div class="split" style="grid-template-columns: 1.1fr 0.9fr; gap: 30px; margin-top: 20px;">
                <!-- Contact Form -->
                <div class="panel" style="padding: 24px;">
                    <h3>Kirim Pesan</h3>
                    <p class="muted" style="font-size: 13px; margin-top: -6px; margin-bottom: 20px;">Tulis kritik, saran, atau pertanyaan kerja sama kemitraan di bawah ini.</p>
                    
                    <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 14px;" onsubmit="event.preventDefault(); alert('Pesan berhasil terkirim! Terima kasih.'); this.reset();">
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label for="name" style="font-size: 12px; font-weight: 600;">Nama Anda</label>
                            <input type="text" id="name" required placeholder="mis. John Doe" style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label for="email" style="font-size: 12px; font-weight: 600;">Alamat Email</label>
                            <input type="email" id="email" required placeholder="name@domain.com" style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label for="msg" style="font-size: 12px; font-weight: 600;">Pesan</label>
                            <textarea id="msg" required placeholder="Tulis masukan Anda..." style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 100px; outline: none; resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer;">Kirim Pesan</button>
                    </form>
                </div>

                <!-- Info Cards -->
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div class="panel" style="padding: 20px;">
                        <h3>🏢 Head Office & Outlet Kemang</h3>
                        <p class="muted" style="font-size: 14px; line-height: 1.6;">Jl. Kemang Raya No. 45C, Mampang Prapatan, Jakarta Selatan, 12730.</p>
                        <div style="font-size: 13px; margin-top: 10px; font-weight: 700; color: var(--text-gold);">📞 +62 812-3456-7890</div>
                    </div>
                    <div class="panel" style="padding: 20px;">
                        <h3>⏰ Jam Operasional</h3>
                        <div style="display: flex; justify-content: space-between; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.02); padding-bottom: 6px; margin-bottom: 6px;">
                            <span>Senin - Jumat</span>
                            <strong>07:00 - 22:00 WIB</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 13px;">
                            <span>Sabtu - Minggu</span>
                            <strong>08:00 - 23:00 WIB</strong>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="footer" style="margin-top: 80px;">
            <div class="container nav">
                <span class="brand"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>CafeFlow</span></span>
                <span>© 2026 Kopi Senja. All rights reserved.</span>
            </div>
        </footer>
    </div>
</x-layouts.app>
