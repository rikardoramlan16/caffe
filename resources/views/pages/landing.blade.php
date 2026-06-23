<x-layouts.app title="CafeFlow - Premium Cafe & Smart QR Ordering">
    <!-- Custom styling for landing page to enhance warm beige-coffee premium design system -->
    <style>
        :root {
            --c-beige-light: #fdfbf7;
            --c-beige-medium: #f5efe6;
            --c-beige-dark: #e8ded2;
            --c-coffee-light: #8c6a5c;
            --c-coffee-medium: #5c3d2e;
            --c-coffee-dark: #38220f;
            --c-gold-light: #f3e5ab;
            --c-gold-medium: #d4af37;
            --c-gold-dark: #aa820a;
            --font-display: 'Outfit', 'Instrument Sans', sans-serif;
        }

        [data-theme="dark"] {
            --c-beige-light: #16110d;
            --c-beige-medium: #1f1814;
            --c-beige-dark: #2c211b;
            --c-coffee-light: #bca094;
            --c-coffee-medium: #a67c69;
            --c-coffee-dark: #fbf7ef;
            --c-gold-light: #524225;
            --c-gold-medium: #e5c158;
            --c-gold-dark: #f5d680;
        }

        body {
            background-color: var(--c-beige-light);
            color: var(--ink);
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
        }

        .font-display {
            font-family: var(--font-display);
        }

        /* Hero and general section tweaks */
        .landing-shell {
            background: 
                linear-gradient(180deg, var(--c-beige-medium) 0%, var(--c-beige-light) 40%, var(--c-beige-light) 100%),
                radial-gradient(circle at 10% 20%, rgba(212, 175, 55, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 70%, rgba(92, 61, 46, 0.06) 0%, transparent 45%);
        }

        .premium-nav-link {
            position: relative;
            font-weight: 600;
            transition: color 0.25s ease;
        }
        .premium-nav-link::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--c-gold-medium);
            transition: width 0.25s ease;
        }
        .premium-nav-link:hover {
            color: var(--c-coffee-light);
        }
        .premium-nav-link:hover::after {
            width: 100%;
        }

        .badge-premium {
            background: rgba(212, 175, 55, 0.12);
            color: var(--c-coffee-medium);
            border: 1px solid rgba(212, 175, 55, 0.3);
            border-radius: 99px;
            padding: 6px 16px;
            font-weight: 700;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            width: fit-content;
        }
        
        [data-theme="dark"] .badge-premium {
            background: rgba(229, 193, 88, 0.08);
            color: var(--c-gold-medium);
            border-color: rgba(229, 193, 88, 0.2);
        }

        /* Glassmorphism elements */
        .glass-panel {
            background: rgba(255, 255, 255, 0.65);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(61, 36, 22, 0.08);
            box-shadow: 0 8px 32px rgba(61, 36, 22, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        [data-theme="dark"] .glass-panel {
            background: rgba(31, 24, 20, 0.7);
            border-color: rgba(244, 234, 216, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        .glass-panel:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(61, 36, 22, 0.1);
        }

        /* Premium coffee buttons */
        .btn-gold-outline {
            border: 1px solid var(--c-gold-medium);
            color: var(--c-coffee-medium);
            background: transparent;
        }
        .btn-gold-outline:hover {
            background: var(--c-gold-medium);
            color: var(--c-beige-light);
        }

        /* Mockup Mobile Styling */
        .mock-phone {
            border: 12px solid var(--c-coffee-dark);
            border-radius: 36px;
            background: var(--c-beige-light);
            box-shadow: 0 32px 80px rgba(54, 33, 21, 0.15);
            overflow: hidden;
            width: 280px;
            margin: 0 auto;
            position: relative;
        }
        .mock-phone-screen {
            padding: 16px;
            min-height: 440px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        /* Grid features custom styling */
        .step-card {
            border-radius: 16px;
            padding: 24px;
            background: var(--surface);
            border: 1px solid var(--line);
            text-align: center;
            position: relative;
            transition: all 0.3s ease;
        }
        .step-card:hover {
            transform: translateY(-4px);
            border-color: var(--c-gold-medium);
        }
        .step-number {
            width: 48px;
            height: 48px;
            background: var(--c-coffee-medium);
            color: #fff;
            font-size: 18px;
            font-weight: 800;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            box-shadow: 0 4px 10px rgba(92, 61, 46, 0.2);
        }

        /* Menu card style */
        .menu-card {
            border-radius: 16px;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--line);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .menu-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(61, 36, 22, 0.08);
            border-color: var(--c-gold-medium);
        }
        .menu-card-img {
            height: 180px;
            background: linear-gradient(135deg, var(--c-beige-medium), var(--c-beige-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 64px;
            position: relative;
        }
        .menu-card-tag {
            position: absolute;
            top: 12px;
            right: 12px;
            background: var(--c-gold-medium);
            color: var(--c-coffee-dark);
            font-weight: 700;
            font-size: 11px;
            padding: 4px 10px;
            border-radius: 99px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .menu-card-info {
            padding: 20px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
            justify-content: space-between;
        }

        /* Promo card style */
        .promo-card {
            border-radius: 16px;
            background: linear-gradient(135deg, var(--c-coffee-medium), var(--c-coffee-dark));
            color: #fff;
            padding: 30px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--line);
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
        }
        .promo-card:hover {
            transform: scale(1.02);
        }
        .promo-card::before {
            content: '';
            position: absolute;
            width: 140px;
            height: 140px;
            background: rgba(255,255,255,0.03);
            border-radius: 50%;
            top: -40px;
            right: -40px;
        }
        .promo-card-tag {
            background: var(--c-gold-medium);
            color: var(--c-coffee-dark);
            font-size: 12px;
            font-weight: 800;
            padding: 4px 12px;
            border-radius: 99px;
            width: fit-content;
            margin-bottom: 16px;
        }

        /* FAQ details styling */
        .faq-item-premium {
            border: 1px solid var(--line);
            background: var(--surface);
            border-radius: 12px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: all 0.25s ease;
        }
        .faq-item-premium[open] {
            border-color: var(--c-gold-medium);
            box-shadow: 0 4px 18px rgba(61, 36, 22, 0.04);
        }
        .faq-item-premium summary {
            padding: 18px 24px;
            font-weight: 700;
            font-size: 16px;
            cursor: pointer;
            list-style: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
            outline: none;
        }
        .faq-item-premium summary::-webkit-details-marker {
            display: none;
        }
        .faq-item-premium summary::after {
            content: '+';
            font-size: 20px;
            font-weight: 400;
            color: var(--c-gold-medium);
            transition: transform 0.25s ease;
        }
        .faq-item-premium[open] summary::after {
            transform: rotate(45deg);
        }
        .faq-item-premium p {
            padding: 0 24px 20px 24px;
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        /* Modal styling */
        .cf-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.55);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 20px;
            animation: cf-fadeIn 0.25s ease;
        }
        .cf-modal-content {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 20px;
            width: 100%;
            max-width: 440px;
            box-shadow: var(--shadow);
            overflow: hidden;
            animation: cf-scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }
        @keyframes cf-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes cf-scaleUp {
            from { transform: scale(0.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* Scanner animation */
        .scanner-view {
            width: 100%;
            height: 180px;
            background: #111;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed rgba(255,255,255,0.15);
        }
        .scanner-box {
            width: 110px;
            height: 110px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%2220%22 height=%2220%22 fill=%22%23ffffff%22 x=%220%22 y=%220%22/><rect width=%2220%22 height=%2220%22 fill=%22%23ffffff%22 x=%2280%22 y=%220%22/><rect width=%2220%22 height=%2220%22 fill=%22%23ffffff%22 x=%220%22 y=%2280%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2240%22 y=%2240%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2250%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2210%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2250%22 y=%2210%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2280%22 y=%2260%22/><rect width=%2210%22 height=%2210%22 fill=%22%23ffffff%22 x=%2220%22 y=%2220%22/></svg>');
            background-size: cover;
            opacity: 0.8;
        }
        .scanner-line {
            position: absolute;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--c-gold-medium);
            box-shadow: 0 0 12px var(--c-gold-medium);
            animation: scanLine 2s infinite linear;
        }
        @keyframes scanLine {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
    </style>

    <div class="app-shell landing-shell">
        <!-- HEADER / NAVBAR -->
        <header class="topbar">
            <div class="container nav">
                <a class="brand font-display" href="#">
                    <span class="brand-mark">☕</span>
                    <span style="font-size: 20px; font-weight: 800; tracking: -0.5px;">CafeFlow</span>
                </a>
                
                <nav class="nav-links font-display" aria-label="Navigasi utama">
                    <a class="premium-nav-link" href="#">Home</a>
                    <a class="premium-nav-link" href="#menu-favorit">Menu</a>
                    <a class="premium-nav-link" href="#promo">Promo</a>
                    <a class="premium-nav-link" href="#cara-kerja">Cara Kerja</a>
                    <a class="premium-nav-link" href="#faq">FAQ</a>
                </nav>

                <div class="actions">
                    <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema" style="border-radius: 50%;">◐</button>
                    <a class="btn btn-gold-outline" href="{{ route('login') }}" style="font-weight: 700; border-radius: 20px;">🔒 Portal Staff</a>
                    <button class="btn btn-gold" onclick="openScannerModal()" style="font-weight: 700; border-radius: 20px; padding: 10px 22px;">🚀 Pesan Sekarang</button>
                </div>
            </div>
        </header>

        <main>
            <!-- HERO SECTION -->
            <section class="container hero">
                <div style="display: flex; flex-direction: column; gap: 18px;">
                    <div class="badge-premium font-display">
                        <span>☕</span> Smart QR Ordering Cafe
                    </div>
                    <h1 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark); margin: 0; line-height: 1.1;">
                        Pesan Minuman Langsung Dari Meja Anda
                    </h1>
                    <p style="font-size: 16px; margin: 0; color: var(--muted); line-height: 1.6; max-width: 540px;">
                        Scan QR meja, pilih minuman favorit, dan pantau status pesanan secara realtime tanpa perlu antre di kasir. Pengalaman nongkrong yang lebih santai.
                    </p>
                    <div class="actions" style="margin-top: 14px;">
                        <button class="btn btn-gold" onclick="openScannerModal()" style="padding: 12px 28px; border-radius: 24px;">📷 Scan QR Meja</button>
                        <a class="btn btn-gold-outline" href="#menu-favorit" style="padding: 12px 28px; border-radius: 24px;">📖 Lihat Menu</a>
                    </div>
                </div>

                <div class="hero-art" style="display: flex; justify-content: center; align-items: center; position: relative;">
                    <!-- Simulated Customer Menu Mobile Frame mockup -->
                    <div class="mock-phone">
                        <div class="mock-phone-screen">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.05); padding-bottom: 10px; margin-bottom: 12px;">
                                    <span style="font-size: 13px; font-weight: 800; color: var(--c-coffee-medium); display: inline-flex; align-items: center; gap: 4px;">
                                        📍 Meja 5
                                    </span>
                                    <span style="font-size: 10px; color: var(--muted); font-weight: bold;">CafeFlow App</span>
                                </div>
                                
                                <!-- Category list -->
                                <div style="display: flex; gap: 8px; margin-bottom: 14px;">
                                    <span style="font-size: 10px; background: var(--c-coffee-medium); color: white; padding: 3px 8px; border-radius: 12px; font-weight: bold;">Coffee</span>
                                    <span style="font-size: 10px; background: rgba(0,0,0,0.03); color: var(--muted); padding: 3px 8px; border-radius: 12px; font-weight: bold;">Non Coffee</span>
                                    <span style="font-size: 10px; background: rgba(0,0,0,0.03); color: var(--muted); padding: 3px 8px; border-radius: 12px; font-weight: bold;">Tea</span>
                                </div>

                                <!-- Item List -->
                                <div style="display: flex; flex-direction: column; gap: 8px;">
                                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.04); border-radius: 8px; padding: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 18px;">☕</span>
                                            <div>
                                                <div style="font-size: 11px; font-weight: 800;">Es Kopi Susu</div>
                                                <div style="font-size: 9px; color: var(--c-gold-dark); font-weight: bold;">Rp 18.000</div>
                                            </div>
                                        </div>
                                        <span style="font-size: 10px; background: var(--c-gold-medium); color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: bold;">+</span>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.04); border-radius: 8px; padding: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 18px;">🍵</span>
                                            <div>
                                                <div style="font-size: 11px; font-weight: 800;">Matcha Latte</div>
                                                <div style="font-size: 9px; color: var(--c-gold-dark); font-weight: bold;">Rp 22.000</div>
                                            </div>
                                        </div>
                                        <span style="font-size: 10px; background: var(--c-gold-medium); color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: bold;">+</span>
                                    </div>

                                    <div style="display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.04); border-radius: 8px; padding: 8px;">
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="font-size: 18px;">🍫</span>
                                            <div>
                                                <div style="font-size: 11px; font-weight: 800;">Chocolate</div>
                                                <div style="font-size: 9px; color: var(--c-gold-dark); font-weight: bold;">Rp 20.000</div>
                                            </div>
                                        </div>
                                        <span style="font-size: 10px; background: var(--c-gold-medium); color: #fff; padding: 2px 6px; border-radius: 4px; font-weight: bold;">+</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Cart bar -->
                            <div style="background: var(--c-coffee-medium); border-radius: 8px; padding: 10px; color: white; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                <span style="font-size: 11px; font-weight: 800;">🛒 Keranjang (2)</span>
                                <span style="font-size: 11px; font-weight: 800;">Lihat</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAVORITE MENU SECTION -->
            <section id="menu-favorit" class="container section">
                <div class="section-head" style="align-items: center;">
                    <div>
                        <span class="badge-premium font-display" style="margin-bottom: 8px;">✨ Best Sellers</span>
                        <h2 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark);">Menu Favorit</h2>
                    </div>
                    <p class="muted" style="margin: 0; font-size: 15px;">Minuman paling banyak dipesan pelanggan.</p>
                </div>

                <div class="grid grid-4" style="margin-top: 24px;">
                    <!-- Card 1 -->
                    <div class="menu-card">
                        <div class="menu-card-img">
                            <span>☕</span>
                            <span class="menu-card-tag">Terlaris</span>
                        </div>
                        <div class="menu-card-info">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 6px 0; color: var(--c-coffee-dark);">Es Kopi Susu</h3>
                                <p class="muted" style="font-size: 13px; line-height: 1.4; margin: 0 0 16px 0;">Espresso house blend, susu segar berkualitas, gula aren premium.</p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--c-gold-dark); font-size: 16px;">Rp 18.000</strong>
                                <button class="btn btn-gold" onclick="openScannerModal()" style="font-size: 12px; padding: 6px 14px; border-radius: 12px;">Pesan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2 -->
                    <div class="menu-card">
                        <div class="menu-card-img">
                            <span>🍵</span>
                            <span class="menu-card-tag">Premium</span>
                        </div>
                        <div class="menu-card-info">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 6px 0; color: var(--c-coffee-dark);">Matcha Latte</h3>
                                <p class="muted" style="font-size: 13px; line-height: 1.4; margin: 0 0 16px 0;">Bubuk matcha khas Jepang, steamed milk lembut, sedikit sirup.</p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--c-gold-dark); font-size: 16px;">Rp 22.000</strong>
                                <button class="btn btn-gold" onclick="openScannerModal()" style="font-size: 12px; padding: 6px 14px; border-radius: 12px;">Pesan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3 -->
                    <div class="menu-card">
                        <div class="menu-card-img">
                            <span>🍫</span>
                        </div>
                        <div class="menu-card-info">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 6px 0; color: var(--c-coffee-dark);">Chocolate</h3>
                                <p class="muted" style="font-size: 13px; line-height: 1.4; margin: 0 0 16px 0;">Cokelat premium pekat dipadukan susu kental manis dan creamy.</p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--c-gold-dark); font-size: 16px;">Rp 20.000</strong>
                                <button class="btn btn-gold" onclick="openScannerModal()" style="font-size: 12px; padding: 6px 14px; border-radius: 12px;">Pesan</button>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4 -->
                    <div class="menu-card">
                        <div class="menu-card-img">
                            <span>🧋</span>
                        </div>
                        <div class="menu-card-info">
                            <div>
                                <h3 style="font-size: 18px; font-weight: 800; margin: 0 0 6px 0; color: var(--c-coffee-dark);">Thai Tea</h3>
                                <p class="muted" style="font-size: 13px; line-height: 1.4; margin: 0 0 16px 0;">Teh Thailand autentik diseduh segar, dicampur susu manis.</p>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <strong style="color: var(--c-gold-dark); font-size: 16px;">Rp 18.000</strong>
                                <button class="btn btn-gold" onclick="openScannerModal()" style="font-size: 12px; padding: 6px 14px; border-radius: 12px;">Pesan</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 36px;">
                    <button class="btn btn-gold-outline" onclick="openScannerModal()" style="padding: 12px 32px; border-radius: 24px; font-weight: 700;">📖 Lihat Semua Menu</button>
                </div>
            </section>

            <!-- PROMO SECTION -->
            <section id="promo" class="container section">
                <div class="section-head" style="align-items: center; justify-content: center; text-align: center; flex-direction: column;">
                    <span class="badge-premium font-display" style="margin-bottom: 8px;">🔥 Special Offers</span>
                    <h2 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark); margin: 0 0 6px 0;">Promo Hari Ini</h2>
                    <p class="muted" style="margin: 0; font-size: 15px;">Dapatkan harga terbaik untuk pesanan favorit Anda.</p>
                </div>

                <div class="grid grid-3" style="margin-top: 24px;">
                    <div class="promo-card">
                        <div class="promo-card-tag">NEW MEMBER</div>
                        <h3 class="font-display" style="font-size: 22px; font-weight: 900; margin: 0 0 10px 0; line-height: 1.2;">Diskon 20% First Order</h3>
                        <p style="font-size: 13px; opacity: 0.85; margin: 0 0 20px 0; line-height: 1.5;">Khusus pemesanan pertama menggunakan QR Code di meja cafe kami.</p>
                        <button class="btn btn-gold" onclick="openScannerModal()" style="border-radius: 20px; font-size: 12px; padding: 8px 18px;">Klaim Promo</button>
                    </div>

                    <div class="promo-card" style="background: linear-gradient(135deg, var(--c-gold-dark), var(--c-coffee-medium));">
                        <div class="promo-card-tag" style="background: #fff; color: var(--c-coffee-dark);">BUNDLING</div>
                        <h3 class="font-display" style="font-size: 22px; font-weight: 900; margin: 0 0 10px 0; line-height: 1.2;">Bundling Kopi + Pastry</h3>
                        <p style="font-size: 13px; opacity: 0.85; margin: 0 0 20px 0; line-height: 1.5;">Hemat Rp10.000! Nikmati Croissant hangat & Aren Signature Latte pilihan.</p>
                        <button class="btn btn-primary" onclick="openScannerModal()" style="border-radius: 20px; font-size: 12px; padding: 8px 18px; background: #fff; color: var(--c-coffee-medium);">Pesan Bundling</button>
                    </div>

                    <div class="promo-card">
                        <div class="promo-card-tag">UPGRADE</div>
                        <h3 class="font-display" style="font-size: 22px; font-weight: 900; margin: 0 0 10px 0; line-height: 1.2;">Gratis Upgrade Oatmilk</h3>
                        <p style="font-size: 13px; opacity: 0.85; margin: 0 0 20px 0; line-height: 1.5;">Ganti susu biasa ke Oatmilk tanpa biaya tambahan untuk menu Matcha Latte.</p>
                        <button class="btn btn-gold" onclick="openScannerModal()" style="border-radius: 20px; font-size: 12px; padding: 8px 18px;">Ganti Susu</button>
                    </div>
                </div>
            </section>

            <!-- HOW IT WORKS SECTION -->
            <section id="cara-kerja" class="container section">
                <div class="section-head" style="align-items: center; justify-content: center; text-align: center; flex-direction: column;">
                    <span class="badge-premium font-display" style="margin-bottom: 8px;">💡 Mudah & Cepat</span>
                    <h2 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark); margin: 0 0 6px 0;">Cara Pemesanan</h2>
                    <p class="muted" style="margin: 0; font-size: 15px;">Ikuti langkah praktis berikut langsung dari meja Anda.</p>
                </div>

                <div class="grid grid-4" style="margin-top: 32px;">
                    <div class="step-card">
                        <div class="step-number">1</div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 8px 0; color: var(--c-coffee-dark);">📷 Scan QR</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Scan QR Code yang tersedia di atas meja Anda menggunakan kamera HP.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">2</div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 8px 0; color: var(--c-coffee-dark);">📖 Pilih Menu</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Jelajahi menu digital, sesuaikan topping, es batu, dan tingkat manis.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">3</div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 8px 0; color: var(--c-coffee-dark);">💳 Checkout</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Lakukan konfirmasi pesanan dan pilih opsi bayar e-wallet / QRIS.</p>
                    </div>
                    <div class="step-card">
                        <div class="step-number">4</div>
                        <h3 style="font-size: 16px; font-weight: 800; margin: 0 0 8px 0; color: var(--c-coffee-dark);">☕ Pesanan Diproses</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Pesanan terkirim realtime ke kasir dan barista untuk disajikan.</p>
                    </div>
                </div>
            </section>

            <!-- WHY CHOOSE CAFEFLOW SECTION -->
            <section class="container section">
                <div class="section-head" style="align-items: center; justify-content: center; text-align: center; flex-direction: column;">
                    <span class="badge-premium font-display" style="margin-bottom: 8px;">🌟 Layanan Unggul</span>
                    <h2 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark); margin: 0 0 6px 0;">Kenapa Memilih CafeFlow</h2>
                    <p class="muted" style="margin: 0; font-size: 15px;">Alasan kenapa pengalaman ngopi di sini jauh lebih memuaskan.</p>
                </div>

                <div class="grid grid-3" style="margin-top: 28px;">
                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">📱</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">QR Ordering</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Pesan langsung tanpa perlu beranjak dari tempat duduk atau mencari pelayan.</p>
                    </div>

                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">⚡</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">Realtime Order</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Pesanan instan masuk ke dashboard POS kasir begitu Anda menyelesaikan pesanan.</p>
                    </div>

                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">☕</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">Barista Queue</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Antrean realtime termonitor di ruang racik barista demi efisiensi waktu pembuatan.</p>
                    </div>

                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">📦</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">Inventory Control</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Pengawasan ketersediaan bahan baku agar minuman Anda selalu tersedia segar.</p>
                    </div>

                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">📊</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">Dashboard Owner</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Laporan penjualan harian dan analitik data performa penjualan terintegrasi.</p>
                    </div>

                    <div class="glass-panel" style="border-radius: 16px; padding: 24px;">
                        <div class="icon-box" style="font-size: 20px;">🔔</div>
                        <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 12px 0 6px 0; color: var(--c-coffee-dark);">Status Pesanan</h3>
                        <p class="muted" style="font-size: 13px; line-height: 1.5; margin: 0;">Pantau alur pembuatan pesanan mulai dari diterima, dibuat, hingga siap saji.</p>
                    </div>
                </div>
            </section>

            <!-- FAQ SECTION -->
            <section id="faq" class="container section">
                <div class="section-head" style="align-items: center; justify-content: center; text-align: center; flex-direction: column;">
                    <span class="badge-premium font-display" style="margin-bottom: 8px;">❓ FAQ</span>
                    <h2 class="font-display" style="font-weight: 900; color: var(--c-coffee-dark); margin: 0 0 6px 0;">Pertanyaan Umum</h2>
                    <p class="muted" style="margin: 0; font-size: 15px;">Hal yang paling sering ditanyakan pelanggan kami.</p>
                </div>

                <div style="max-width: 720px; margin: 24px auto 0 auto;">
                    <details class="faq-item-premium">
                        <summary>Bagaimana cara memesan?</summary>
                        <p>Cukup arahkan kamera smartphone Anda ke QR Code yang tertempel di meja. Klik link yang muncul, dan Anda akan langsung diarahkan ke menu digital meja Anda tanpa perlu mengunduh aplikasi.</p>
                    </details>

                    <details class="faq-item-premium">
                        <summary>Apakah harus membuat akun atau login?</summary>
                        <p>Tidak perlu. Anda bisa memesan sebagai tamu langsung. Sistem menyimpan sesi pemesanan Anda sementara waktu pada browser Anda untuk keamanan pelacakan status.</p>
                    </details>

                    <details class="faq-item-premium">
                        <summary>Bagaimana jika QR Code di meja rusak?</summary>
                        <p>Apabila QR Code sulit dipindai, Anda dapat meminta bantuan langsung kepada pelayan di area cafe, atau menekan tombol 'Pesan Sekarang' di website ini dan mengetikkan kode meja Anda secara manual.</p>
                    </details>

                    <details class="faq-item-premium">
                        <summary>Bagaimana cara membayar?</summary>
                        <p>Pembayaran dapat diselesaikan langsung secara digital di halaman checkout menggunakan QRIS (Gopay, OVO, ShopeePay, Dana, dll.) atau Anda dapat memilih pembayaran tunai di kasir utama.</p>
                    </details>
                </div>
            </section>
        </main>

        <!-- FOOTER -->
        <footer class="footer" style="background: var(--c-beige-medium); margin-top: 60px; padding: 48px 0 32px 0; border-top: 1px solid var(--line);">
            <div class="container" style="display: grid; grid-template-columns: 1.5fr 1fr 1fr 1.2fr; gap: 40px;">
                <div>
                    <a class="brand font-display" href="#" style="margin-bottom: 16px;">
                        <span class="brand-mark">☕</span>
                        <span style="font-size: 18px; font-weight: 800; color: var(--c-coffee-dark);">CafeFlow</span>
                    </a>
                    <p class="muted" style="font-size: 13px; line-height: 1.6; margin-top: 12px; max-width: 280px;">
                        Sistem pemesanan QR digital cerdas terpadu untuk efisiensi cafe modern Anda.
                    </p>
                </div>

                <div>
                    <h4 class="font-display" style="font-size: 14px; font-weight: 800; margin: 0 0 16px 0; color: var(--c-coffee-dark);">Menu Cepat</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <a href="#" class="muted hover:text-brown">Home</a>
                        <a href="#menu-favorit" class="muted hover:text-brown">Menu Favorit</a>
                        <a href="#promo" class="muted hover:text-brown">Promo Terkini</a>
                        <a href="#cara-kerja" class="muted hover:text-brown">Cara Kerja</a>
                        <a href="#faq" class="muted hover:text-brown">FAQ</a>
                    </div>
                </div>

                <div>
                    <h4 class="font-display" style="font-size: 14px; font-weight: 800; margin: 0 0 16px 0; color: var(--c-coffee-dark);">Sosial Media</h4>
                    <div style="display: flex; flex-direction: column; gap: 8px; font-size: 13px;">
                        <a href="#" class="muted hover:text-brown">Instagram</a>
                        <a href="#" class="muted hover:text-brown">Tiktok</a>
                        <a href="#" class="muted hover:text-brown">Facebook</a>
                        <a href="#" class="muted hover:text-brown">Youtube</a>
                    </div>
                </div>

                <div>
                    <h4 class="font-display" style="font-size: 14px; font-weight: 800; margin: 0 0 16px 0; color: var(--c-coffee-dark);">Kontak & Alamat</h4>
                    <p class="muted" style="font-size: 13px; line-height: 1.6; margin: 0 0 12px 0;">
                        📍 Jl. Kopi Senja Kemang No. 22, Mampang Prapatan, Jakarta Selatan.
                    </p>
                    <p class="muted" style="font-size: 13px; margin: 0;">
                        📞 +62 812-3456-7890
                    </p>
                    <p class="muted" style="font-size: 13px; margin: 4px 0 0 0;">
                        ✉️ hello@cafeflow.test
                    </p>
                </div>
            </div>

            <div class="container" style="border-top: 1px solid var(--line); margin-top: 40px; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; font-size: 12px;">
                <span class="muted">© 2026 CafeFlow. All rights reserved.</span>
                <span class="muted">Made with ❤️ for Coffee Lovers.</span>
            </div>
        </footer>
    </div>

    <!-- POPUP SCAN MEJA MODAL (Simulated QR Scanner Popup) -->
    <div id="scan-qr-modal" class="cf-modal" onclick="closeScannerModal()">
        <div class="cf-modal-content" onclick="event.stopPropagation()">
            <div style="padding: 24px; border-bottom: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center;">
                <h3 class="font-display" style="font-size: 18px; font-weight: 800; margin: 0; color: var(--c-coffee-dark);">📷 Pesan Minuman</h3>
                <button onclick="closeScannerModal()" style="background: none; border: none; font-size: 20px; color: var(--muted); cursor: pointer; padding: 0 4px;">✕</button>
            </div>
            
            <div style="padding: 24px;">
                <p class="muted" style="font-size: 13px; margin: 0 0 16px 0; line-height: 1.5;">
                    Silakan scan QR yang tersedia di meja untuk mulai memesan secara otomatis. Anda juga dapat memilih nomor meja demo di bawah ini.
                </p>

                <!-- Scanner simulation box -->
                <div class="scanner-view">
                    <div class="scanner-line"></div>
                    <div class="scanner-box"></div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <label for="modal-table-select" style="font-size: 12px; font-weight: 700; color: var(--c-coffee-medium); display: block; margin-bottom: 6px;">Pilih Meja Demo (Cepat)</label>
                        <select id="modal-table-select" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); color: var(--ink); font-size: 13px; outline: none;" onchange="syncTableInput()">
                            <option value="TBL005">Meja 5 (TBL005)</option>
                            <option value="TBL001">Meja 1 (TBL001)</option>
                            <option value="TBL002">Meja 2 (TBL002)</option>
                            <option value="TBL003">Meja 3 (TBL003)</option>
                            <option value="TBL004">Meja 4 (TBL004)</option>
                            <option value="TBL006">Meja 6 (TBL006)</option>
                            <option value="TBL007">Meja 7 (TBL007)</option>
                            <option value="TBL008">Meja 8 (TBL008)</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal-table-input" style="font-size: 12px; font-weight: 700; color: var(--c-coffee-medium); display: block; margin-bottom: 6px;">Atau Tulis Kode Meja Manual</label>
                        <input type="text" id="modal-table-input" value="TBL005" placeholder="Contoh: TBL005" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--line); background: var(--surface); color: var(--ink); font-size: 13px; outline: none; font-weight: bold;">
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 24px;">
                    <button class="btn btn-gold-outline" onclick="closeScannerModal()" style="flex: 1; border-radius: 12px; font-size: 13px;">Batal</button>
                    <button class="btn btn-gold" onclick="simulateScanRedirect()" style="flex: 1.5; border-radius: 12px; font-size: 13px;">📷 Scan QR Meja</button>
                </div>

                <div style="text-align: center; margin-top: 16px;">
                    <a href="{{ route('menu.preview') }}" style="font-size: 12px; color: var(--c-gold-dark); font-weight: 700; text-decoration: underline;">📖 Cukup Lihat Menu Publik</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side script for Modal Scanner simulation -->
    <script>
        function openScannerModal() {
            document.getElementById('scan-qr-modal').style.display = 'flex';
        }

        function closeScannerModal() {
            document.getElementById('scan-qr-modal').style.display = 'none';
        }

        function syncTableInput() {
            const selectEl = document.getElementById('modal-table-select');
            const inputEl = document.getElementById('modal-table-input');
            inputEl.value = selectEl.value;
        }

        function simulateScanRedirect() {
            const tableCode = document.getElementById('modal-table-input').value.trim();
            if (!tableCode) {
                alert('Silakan pilih atau masukkan kode meja terlebih dahulu.');
                return;
            }
            // Redirect to customer table entry
            window.location.href = '/qr/' + tableCode;
        }
    </script>
</x-layouts.app>
