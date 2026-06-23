<x-layouts.app title="Tentang Kami - CafeFlow">
    <div class="app-shell">
        <header class="topbar">
            <div class="container nav">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>CafeFlow</span></a>
                <nav class="nav-links" aria-label="Navigasi utama">
                    <a href="{{ route('landing') }}">Home</a>
                    <a href="{{ route('menu.preview') }}">Menu</a>
                    <a href="{{ route('landing') }}#cara-kerja">Cara Kerja</a>
                    <a class="active" href="{{ route('about') }}">Tentang</a>
                    <a href="{{ route('contact') }}">Kontak</a>
                </nav>
                <div class="actions">
                    <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                    <a class="btn" href="{{ route('login') }}">Masuk Staf</a>
                    <a class="btn btn-gold" href="{{ route('menu.preview') }}">Pesan Sekarang</a>
                </div>
            </div>
        </header>

        <main class="container section" style="max-width: 800px; padding: 60px 20px;">
            <span class="eyebrow" style="margin-bottom: 14px; display: inline-flex;">Our Story</span>
            <h1 style="font-size: clamp(34px, 5vw, 54px); margin-bottom: 24px;">Tentang Kopi Senja</h1>
            
            <div class="panel" style="margin-bottom: 40px; padding: 30px; line-height: 1.8; font-size: 16px; display: flex; flex-direction: column; gap: 20px;">
                <p><strong>Kopi Senja</strong> didirikan pada tahun 2022 dengan visi menyajikan kopi berkualitas tinggi, segar, dan bersumber secara etis dari petani lokal Indonesia. Kami percaya bahwa setiap cangkir kopi memiliki cerita, dan kami ingin menjadi bagian dari cerita pagi hari Anda yang produktif maupun sore hari Anda yang menenangkan.</p>
                
                <p>Dengan memadukan cita rasa tradisional biji kopi nusantara pilihan dan inovasi teknologi digital seperti **CafeFlow**, kami berkomitmen untuk memberikan pengalaman pemesanan yang cepat, modern, dan tanpa hambatan bagi seluruh pelanggan setia kami.</p>
                
                <p>Setiap cangkir kopi yang disajikan oleh Barista tersertifikasi kami diracik dengan presisi, menjaga kesegaran susu full cream, dan dipadukan secara harmonis dengan sirup artisan orisinal racikan kami sendiri.</p>
            </div>

            <div class="grid grid-3" style="margin-top: 40px;">
                <div class="metric-card" style="text-align: center;">
                    <span class="pill" style="margin: 0 auto 10px auto;">Espresso</span>
                    <strong>100%</strong>
                    <span>Biji Arabika Lokal</span>
                </div>
                <div class="metric-card" style="text-align: center;">
                    <span class="pill" style="margin: 0 auto 10px auto;">Layanan</span>
                    <strong>QR Order</strong>
                    <span>Pemesanan cepat dan terukur</span>
                </div>
                <div class="metric-card" style="text-align: center;">
                    <span class="pill" style="margin: 0 auto 10px auto;">Pelayanan</span>
                    <strong>Fast QR</strong>
                    <span>Tanpa Antre Lama</span>
                </div>
            </div>
        </main>

        <footer class="footer" style="margin-top: 80px;">
            <div class="container nav">
                <span class="brand"><span class="brand-mark">CF</span><span>CafeFlow</span></span>
                <span>© 2026 Kopi Senja. All rights reserved.</span>
            </div>
        </footer>
    </div>
</x-layouts.app>
