<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Wireless Barcode Scanner - CafeFlow</title>
    
    <!-- Google Fonts Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@700&display=swap" rel="stylesheet">
    
    <!-- HTML5-QRCODE Scanner library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        :root {
            --bg-dark: #0f0a07;
            --coffee-deep: #1f140e;
            --coffee-medium: #2d1e15;
            --coffee-soft: #3d2a1f;
            --coffee-light: #594031;
            --text-gold: #e2b07e;
            --text-main: #f3eae1;
            --text-muted: #bda697;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --font-main: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            -webkit-tap-highlight-color: transparent;
        }

        body {
            font-family: var(--font-main);
            background-color: var(--bg-dark);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        header {
            width: 100%;
            background-color: var(--coffee-deep);
            padding: 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(226, 176, 126, 0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .brand-logo {
            background: var(--text-gold);
            color: var(--bg-dark);
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 14px;
        }

        .header-title h1 {
            font-size: 16px;
            font-weight: 700;
        }

        .main-container {
            width: 100%;
            max-width: 480px;
            padding: 20px 16px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            flex: 1;
        }

        .panel {
            background-color: var(--coffee-deep);
            border: 1px solid rgba(226, 176, 126, 0.08);
            border-radius: 14px;
            padding: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
            gap: 14px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-gold);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        p.muted {
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px;
            font-size: 14px;
            font-weight: 700;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-gold {
            background-color: var(--text-gold);
            color: var(--bg-dark);
        }

        .btn-gold:active {
            background-color: #d19f6f;
            transform: scale(0.98);
        }

        .btn-outline {
            background-color: transparent;
            border: 1px solid rgba(226, 176, 126, 0.3);
            color: var(--text-gold);
        }

        .btn-outline:active {
            background-color: rgba(226, 176, 126, 0.05);
        }

        .btn-danger-outline {
            background-color: transparent;
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: var(--danger-color);
        }

        .btn-danger-outline:active {
            background-color: rgba(239, 68, 68, 0.05);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-muted);
        }

        .input-text {
            background-color: var(--coffee-medium);
            border: 1px solid rgba(226, 176, 126, 0.15);
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            width: 100%;
            transition: border-color 0.2s;
        }

        .input-text:focus {
            border-color: var(--text-gold);
        }

        .select-input {
            background-color: var(--coffee-medium);
            border: 1px solid rgba(226, 176, 126, 0.15);
            border-radius: 8px;
            padding: 12px;
            font-size: 14px;
            color: var(--text-main);
            outline: none;
            width: 100%;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23e2b07e' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 14px;
            padding-right: 40px;
        }

        .select-input:focus {
            border-color: var(--text-gold);
        }

        /* Scanner container */
        #scanner-reader-container {
            width: 100%;
            border-radius: 12px;
            overflow: hidden;
            background-color: #000;
            border: 1px solid rgba(226, 176, 126, 0.15);
            position: relative;
        }

        #scanner-reader {
            width: 100%;
        }

        .scanner-placeholder {
            padding: 50px 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .scanner-placeholder svg {
            color: var(--text-gold);
            animation: pulse 1.5s infinite alternate;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        /* Pairing Status Header */
        .status-badge {
            display: flex;
            align-items: center;
            gap: 8px;
            background-color: var(--coffee-medium);
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 12px;
            border-left: 3px solid var(--success-color);
        }

        .status-badge.unpaired {
            border-left-color: var(--danger-color);
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: var(--success-color);
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulseDot 1.4s infinite;
        }

        .pulse-dot.danger {
            background-color: var(--danger-color);
            box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            animation: pulseDotDanger 1.4s infinite;
        }

        @keyframes pulseDot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        @keyframes pulseDotDanger {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* Toast message */
        .toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            width: 90%;
            max-width: 400px;
            padding: 14px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            z-index: 1000;
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .toast.success {
            background-color: var(--success-color);
            color: #fff;
        }

        .toast.error {
            background-color: var(--danger-color);
            color: #fff;
        }

        .order-refresh-btn {
            background: transparent;
            border: none;
            color: var(--text-gold);
            cursor: pointer;
            padding: 4px;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .order-refresh-btn:active {
            transform: rotate(180deg);
            transition: transform 0.2s ease;
        }
    </style>
</head>
<body>

    <header>
        <div class="header-title">
            <div class="brand-logo">CF</div>
            <h1>CafeFlow Scanner</h1>
        </div>
        <div>
            <!-- Header actions if any -->
        </div>
    </header>

    <div class="main-container">
        
        <!-- Alerts if any from session -->
        @if (session('success'))
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px; border-radius: 8px; font-size: 13px; color: var(--success-color);">
                🎉 {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px; border-radius: 8px; font-size: 13px; color: var(--danger-color);">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        @if (!$pairing)
            <!-- SECTION 1: PAIRING CODE INPUT -->
            <div class="panel">
                <h2>📲 Sambungkan ke POS Kasir</h2>
                <p class="muted">Gunakan kamera ponsel Anda untuk memindai QR Code di layar kasir, atau masukkan 6-digit kode pairing di bawah ini.</p>
                
                <form action="{{ route('scanner.pair') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                    @csrf
                    <div class="form-group">
                        <label for="pairing_code">Kode Pairing POS</label>
                        <input type="text" id="pairing_code" name="pairing_code" required maxlength="6" placeholder="Ketik 6 digit kode..." autocomplete="off" style="text-align: center; font-size: 22px; font-weight: 800; letter-spacing: 4px; font-family: 'JetBrains Mono', monospace;" class="input-text">
                    </div>
                    
                    <button type="submit" class="btn btn-gold">Hubungkan Scanner</button>
                </form>
            </div>

            <div class="status-badge unpaired">
                <div class="pulse-dot danger"></div>
                <span style="color: var(--danger-color); font-weight: 700;">Status: Tidak Terhubung</span>
            </div>
        @else
            <!-- SECTION 2: PAIRED SCANNER -->
            <div class="status-badge">
                <div class="pulse-dot"></div>
                <span style="color: var(--success-color); font-weight: 700;">Scanner Terhubung (POS: {{ $pairing->pairing_code }})</span>
            </div>

            <div class="panel">
                <h2>📡 Mode: Kirim ke POS Kasir</h2>
                <p class="muted">Setiap barcode yang di-scan akan langsung masuk ke keranjang kasir POS secara otomatis. Tidak perlu memilih order.</p>
                <div style="display: flex; align-items: center; gap: 8px; background: rgba(16,185,129,0.08); padding: 10px; border-radius: 8px; margin-top: 4px;">
                    <div class="pulse-dot"></div>
                    <span style="font-size: 12px; font-weight: 600; color: var(--success-color);">Siap scan → langsung masuk ke keranjang kasir</span>
                </div>
            </div>

            <div class="panel" style="gap: 12px;">
                <h2>📷 Scan Barcode Produk</h2>
                
                <div id="scanner-reader-container">
                    <div id="scanner-reader" style="display: none;"></div>
                    <div id="scanner-placeholder" class="scanner-placeholder">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"></path>
                            <circle cx="12" cy="13" r="4"></circle>
                        </svg>
                        <p class="muted" style="font-weight: 600; font-size:13px;">Kamera Siap Digunakan</p>
                        <button type="button" class="btn btn-gold" style="font-size:12px; padding: 6px 12px; width: auto;" onclick="startCameraScanner()">Aktifkan Kamera</button>
                    </div>
                </div>
                
                <!-- Manual input fallback -->
                <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 6px; border-top: 1px solid rgba(226,176,126,0.06); padding-top: 12px;">
                    <label style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Ketik Barcode Manual (Untuk Uji Coba)</label>
                    <div style="display: flex; gap: 8px;">
                        <input type="text" id="manual_barcode" placeholder="Ketik barcode..." class="input-text" style="padding: 8px 12px; font-size: 13px;" onkeypress="if(event.key === 'Enter') submitManualBarcode()">
                        <button type="button" onclick="submitManualBarcode()" class="btn btn-gold" style="width: auto; padding: 0 14px; font-size: 12px; height: 38px;">Kirim</button>
                    </div>
                </div>
            </div>

            <!-- Unpair connection panel -->
            <div class="panel" style="padding: 12px;">
                <form action="{{ route('scanner.unpair') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="btn btn-danger-outline" style="padding: 8px; font-size: 12px;">✕ Putuskan Scanner HP</button>
                </form>
            </div>
        @endif

    </div>

    <!-- Toast Notification Banner -->
    <div id="toast" class="toast">
        <span id="toast-icon">📢</span>
        <span id="toast-text">Notifikasi</span>
    </div>

    <script>
        let html5QrcodeScanner = null;

        document.addEventListener('DOMContentLoaded', () => {
            const hasPairing = @json($pairing !== null);
            // Scanner ready - scans auto-buffer to POS cart
        });

        // Toast Helper
        function showToast(text, type = 'success') {
            const toast = document.getElementById('toast');
            const toastText = document.getElementById('toast-text');
            const toastIcon = document.getElementById('toast-icon');

            toastText.innerText = text;
            
            if (type === 'success') {
                toast.className = 'toast show success';
                toastIcon.innerText = '✅';
            } else {
                toast.className = 'toast show error';
                toastIcon.innerText = '⚠️';
            }

            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Load active orders list from cashier branch via AJAX
        function loadActiveOrders() {
            // No longer needed - scans go directly to POS cart buffer
        }

        // Camera barcode scanner toggler
        function startCameraScanner() {
            document.getElementById('scanner-placeholder').style.display = 'none';
            document.getElementById('scanner-reader').style.display = 'block';

            html5QrcodeScanner = new Html5Qrcode("scanner-reader");

            const config = { 
                fps: 15, 
                qrbox: { width: 250, height: 180 },
                aspectRatio: 1.333
            };

            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Camera access failed", err);
                showToast("Gagal mengakses kamera. Pastikan izin kamera aktif.", "error");
                document.getElementById('scanner-placeholder').style.display = 'flex';
                document.getElementById('scanner-reader').style.display = 'none';
            });
        }

        function onScanSuccess(decodedText, decodedResult) {
            console.log(`Scan result: ${decodedText}`, decodedResult);
            
            // Beep sound alternative using Web Audio API
            playBeep();
            
            // Submit the barcode
            submitBarcode(decodedText);
            
            // Pause scanner for 2 seconds to avoid multiple duplicate scans
            if (html5QrcodeScanner) {
                html5QrcodeScanner.pause();
                setTimeout(() => {
                    if (html5QrcodeScanner) html5QrcodeScanner.resume();
                }, 2000);
            }
        }

        function onScanFailure(error) {
            // Silence silent warnings
        }

        // Play scanner feedback sound
        function playBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(1200, audioCtx.currentTime); // 1.2kHz beep
                gainNode.gain.setValueAtTime(0.1, audioCtx.currentTime);
                
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.1); // 100ms
            } catch (e) {
                console.warn("Audio play prevented", e);
            }
        }

        // Submit barcode manually via input field
        function submitManualBarcode() {
            const input = document.getElementById('manual_barcode');
            const code = input.value.trim();
            
            if (!code) {
                showToast('Masukkan barcode terlebih dahulu!', 'error');
                return;
            }

            submitBarcode(code);
            input.value = '';
        }

        // Common API barcode submit logic
        function submitBarcode(barcode) {
            fetch('/scanner/scan-buffer', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'ngrok-skip-browser-warning': 'true'
                },
                body: JSON.stringify({
                    barcode: barcode
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                } else {
                    showToast(data.message || 'Gagal menambahkan produk.', 'error');
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Koneksi terputus. Gagal mengirim barcode.', 'error');
            });
        }
    </script>
</body>
</html>
