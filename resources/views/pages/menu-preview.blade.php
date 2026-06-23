<x-layouts.app title="Daftar Menu - Kopi Senja">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top">
                    <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>Kopi Senja Menu</span></a>
                    <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                </div>

                <main class="mobile-body" style="padding-bottom: 30px; position: relative; overflow-y: auto; height: calc(100% - 130px);">
                    <!-- Heading -->
                    <div style="padding: 10px 0;">
                        <span class="eyebrow" style="color: var(--text-gold);">Menu Preview</span>
                        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 6px; color: var(--text-main);">Daftar Menu Kafe</h1>
                        <p class="muted" style="font-size: 13px; margin-bottom: 16px;">Silakan lihat pilihan kopi premium, teh ceremonial, dan cemilan hangat kami.</p>
                        
                        <!-- CTA Banner -->
                        <div style="background: linear-gradient(135deg, var(--coffee-deep), rgba(199, 154, 75, 0.15)); border: 1px solid var(--text-gold); border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: center;">
                            <strong style="color: var(--text-gold); font-size: 14px; display: block; margin-bottom: 6px;">Ingin memesan langsung dari meja?</strong>
                            <p style="color: #fffaf0; font-size: 12px; margin: 0 0 12px 0; line-height: 1.5;">Scan QR code fisik yang tertempel di meja Anda untuk memesan secara digital tanpa antre.</p>
                            <button class="btn btn-gold" style="width: 100%; font-size: 12px; padding: 8px; font-weight: 800; border: none;" onclick="openPreviewScanner()">
                                📷 Scan QR Meja Sekarang
                            </button>
                        </div>

                        <!-- Menu List -->
                        <div style="display: flex; flex-direction: column; gap: 14px;">
                            @foreach ($menu as $item)
                                <div class="menu-item" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); padding: 14px; border-radius: 10px; cursor: pointer; transition: transform 0.1s;" onclick="triggerTableWarning()">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items: flex-start;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 4px;">
                                                <strong style="font-size: 14px; color: var(--text-main);">{{ $item->name }}</strong>
                                                @if($item->is_featured)
                                                    <span class="pill" style="font-size: 8px; padding: 2px 6px; background: #e0b766; color: #120d0a;">Best Seller</span>
                                                @endif
                                            </div>
                                            <p class="muted" style="font-size: 11px; margin: 4px 0 6px 0;">{{ $item->description ?? 'Komposisi premium racikan barista.' }}</p>
                                            <strong style="color: var(--text-gold); font-size: 13px;">Rp {{ number_format($item->price, 0, ',', '.') }}</strong>
                                        </div>
                                        <div style="text-align: right; display:flex; flex-direction:column; align-items:flex-end; gap: 8px;">
                                            <span class="pill" style="font-size: 9px; background: rgba(255,255,255,0.05); color: var(--text-gold);">{{ $item->category ? $item->category->name : 'Menu' }}</span>
                                            <button class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; font-weight: 700; border-radius: 4px;" onclick="event.stopPropagation(); triggerTableWarning();">+ Tambah</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </main>

                <!-- Simple Bottom Navigation for preview -->
                <nav class="bottom-nav" aria-label="Navigasi preview">
                    <a href="{{ route('landing') }}">Home</a>
                    <a class="active" href="{{ route('menu.preview') }}">Menu</a>
                    <a href="#" onclick="triggerTableWarning(); return false;">Keranjang</a>
                    <a href="#" onclick="openPreviewScanner(); return false;">Scan QR</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Popup Warning: Belum Memilih Meja -->
    @if (!session('customer_table_code'))
        <div id="table-warning-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); display: flex; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(6px);">
            <div style="background: var(--bg-surface); width: min(340px, 90%); border-radius: 16px; padding: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 16px;">
                <div style="font-size: 40px; color: var(--text-gold);">📍</div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">Anda belum memilih meja.</h2>
                    <p class="muted" style="font-size: 12px; margin: 0; line-height: 1.5;">Untuk melakukan pemesanan, silakan scan QR code yang berada di meja Anda atau pilih nomor meja secara manual sebagai cadangan.</p>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 8px;">
                    <button class="btn btn-gold" style="width: 100%; padding: 12px; font-weight: 800; font-size: 13px; border: none;" onclick="openScannerFromWarning()">
                        📷 Scan QR Meja
                    </button>
                    <button class="btn" style="width: 100%; padding: 12px; font-weight: 700; font-size: 13px; border: 1px solid var(--line); background: transparent;" onclick="showManualTableSelect()">
                        ⌨️ Pilih Meja Manual
                    </button>
                    <button class="btn" style="width: 100%; padding: 8px; font-weight: 500; font-size: 11px; color: var(--text-muted); border: none; background: transparent;" onclick="closeTableWarning()">
                        Hanya Lihat Menu
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Dropdown Pilih Meja Manual Modal -->
    <div id="manual-table-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); display: none; z-index: 1100; align-items: center; justify-content: center; backdrop-filter: blur(6px);">
        <div style="background: var(--bg-surface); width: min(340px, 90%); border-radius: 16px; padding: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 16px;">
            <div style="font-size: 32px; color: var(--text-gold);">⌨️</div>
            <div>
                <h2 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">Pilih Meja Manual</h2>
                <p class="muted" style="font-size: 12px; margin: 0;">Silakan pilih meja tempat Anda duduk saat ini untuk melanjutkan pemesanan.</p>
            </div>
            
            <form action="#" onsubmit="event.preventDefault(); goToManualTable();" style="display: flex; flex-direction: column; gap: 14px;">
                <select id="manual-table-select" required style="width: 100%; padding: 12px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 14px; outline: none;">
                    <option value="">Pilih Nomor Meja...</option>
                    @foreach(range(1, 6) as $num)
                        <option value="TBL00{{ $num }}">Meja {{ $num }}</option>
                    @endforeach
                </select>
                
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button type="button" class="btn" style="flex: 1; padding: 10px;" onclick="hideManualTableSelect()">Kembali</button>
                    <button type="submit" class="btn btn-gold" style="flex: 1; padding: 10px; border: none; font-weight: 800;">Lanjut Pesan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- QR Code Scan Simulation Modal (Camera Mock) -->
    <div id="scanner-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.85); display: none; z-index: 1200; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
        <div style="background: var(--bg-surface); width: min(360px, 92%); border-radius: 16px; padding: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 18px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-weight: 800; color: var(--text-main);">Kamera QR Scanner</h3>
                <button style="background: none; border: none; font-size: 18px; color: var(--text-muted); cursor: pointer;" onclick="closeScannerModal()">✕</button>
            </div>
            
            <p class="muted" style="font-size: 12px; margin: 0;">Membuka kamera belakang perangkat... Posisikan QR code meja di dalam bingkai.</p>
            
            <!-- Scanning Frame Animation Mock -->
            <div style="width: 200px; height: 200px; background: rgba(255,255,255,0.02); border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px; margin: 10px auto; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                <div style="width: 130px; height: 130px; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%2220%22 height=%2220%22 x=%220%22 y=%220%22/><rect width=%2220%22 height=%2220%22 x=%2280%22 y=%220%22/><rect width=%2220%22 height=%2220%22 x=%220%22 y=%2280%22/><rect width=%2210%22 height=%2210%22 x=%2240%22 y=%2240%22/><rect width=%2210%22 height=%2210%22 x=%2250%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 x=%2210%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 x=%2250%22 y=%2210%22/><rect width=%2210%22 height=%2210%22 x=%2280%22 y=%2260%22/><rect width=%2210%22 height=%2210%22 x=%2220%22 y=%2220%22/></svg>'); background-size: cover; opacity: 0.65;"></div>
                <div style="position: absolute; width: 100%; height: 2px; background: var(--text-gold); top: 0; left: 0; box-shadow: 0 0 8px var(--text-gold); animation: qrScan 2s infinite linear;"></div>
            </div>
            
            <div style="margin-top: 6px;">
                <span class="muted" style="font-size: 11px; display: block; margin-bottom: 6px;">Simulasi scan otomatis meja (Pilih salah satu):</span>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px;">
                    @foreach(range(1, 6) as $num)
                        <button class="btn" style="padding: 6px; font-size: 11px;" onclick="simulateQrScan('TBL00{{ $num }}')">Meja {{ $num }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function triggerTableWarning() {
            const modal = document.getElementById('table-warning-modal');
            if (modal) {
                modal.style.display = 'flex';
            } else {
                // If they clicked to order but warning isn't rendered, redirect them to home or menu preview
                openPreviewScanner();
            }
        }
        
        function closeTableWarning() {
            const modal = document.getElementById('table-warning-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        function showManualTableSelect() {
            closeTableWarning();
            document.getElementById('manual-table-modal').style.display = 'flex';
        }

        function hideManualTableSelect() {
            document.getElementById('manual-table-modal').style.display = 'none';
            triggerTableWarning();
        }

        function goToManualTable() {
            const select = document.getElementById('manual-table-select');
            const val = select.value;
            if (val) {
                window.location.href = `/qr/${val}`;
            }
        }

        function openPreviewScanner() {
            closeTableWarning();
            document.getElementById('scanner-modal').style.display = 'flex';
        }

        function openScannerFromWarning() {
            closeTableWarning();
            document.getElementById('scanner-modal').style.display = 'flex';
        }

        function closeScannerModal() {
            document.getElementById('scanner-modal').style.display = 'none';
            triggerTableWarning();
        }

        function simulateQrScan(code) {
            document.getElementById('scanner-modal').style.display = 'none';
            // Visual simulated loading
            const toast = document.createElement('div');
            toast.style = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); background:var(--text-gold); color:var(--bg-app); padding:12px 24px; border-radius:8px; font-weight:800; font-size:14px; box-shadow:0 8px 24px rgba(0,0,0,0.3); z-index:2000; transition:opacity 0.2s;';
            toast.innerText = `🔍 QR Dibaca: Meja ${code.replace('TBL00', '')}. Redirecting...`;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                window.location.href = `/qr/${code}`;
            }, 1000);
        }
    </script>
</x-layouts.app>
