<x-layouts.app title="Verifikasi Wajah - CafeFlow">
    <div class="app-shell" style="min-height: 100vh; display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at top left, var(--bg-surface) 20%, var(--bg-app) 100%);">
        <div class="panel" style="width: 100%; max-width: 440px; padding: 40px; border-radius: 16px; box-shadow: 0 8px 32px rgba(0,0,0,0.12); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.05); margin: 20px;">
            
            <div style="text-align: center; margin-bottom: 24px;">
                <span class="eyebrow" style="color: var(--text-gold);">Security Verification</span>
                <h1 style="font-size: 26px; font-weight: 800; margin: 8px 0 4px 0; color: var(--text-main);">Verifikasi Wajah</h1>
                <p class="muted" style="font-size: 13px;">Halo, <strong style="color: var(--text-main);">{{ $user->name }}</strong>. Posisikan wajah Anda pada kamera untuk masuk.</p>
            </div>

            <!-- Webcam scan area -->
            <div style="position:relative; width:100%; aspect-ratio:4/3; background:#050505; border-radius:10px; overflow:hidden; border:1px solid var(--line); margin-bottom:20px; display:flex; align-items:center; justify-content:center;">
                <video id="webcam" autoplay muted playsinline style="width:100%; height:100%; object-fit:cover; transform:scaleX(-1);"></video>
                <canvas id="face-canvas" style="position:absolute; top:0; left:0; width:100%; height:100%; transform:scaleX(-1); pointer-events:none;"></canvas>
                
                <!-- HUD scan circles -->
                <div id="scan-hud" style="position:absolute; width:220px; height:220px; border:2px dashed var(--text-gold); border-radius:50%; opacity:0.25; pointer-events:none; box-sizing:border-box; animation: pulseHUD 2.5s infinite ease-in-out;"></div>
                
                <!-- Loading Models overlay -->
                <div id="models-loading" style="position:absolute; display:flex; flex-direction:column; align-items:center; gap:12px; background:rgba(0,0,0,0.9); width:100%; height:100%; justify-content:center; z-index:3;">
                    <div class="spinner"></div>
                    <span style="font-size:13px;color:var(--text-gold);font-weight:600;">Memuat Sistem Deteksi...</span>
                </div>
            </div>

            <!-- Status HUD -->
            <div style="display:flex; flex-direction:column; gap:8px; margin-bottom:20px; background:rgba(0,0,0,0.1); padding:12px; border-radius:8px; border:1px solid rgba(255,255,255,0.02);">
                <div style="display:flex; justify-content:space-between; font-size:12px;">
                    <span class="muted">Status Kamera:</span>
                    <span id="cam-status" style="font-weight:700; color:#ef4444;">Memuat...</span>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:12px;">
                    <span class="muted">Pemindaian:</span>
                    <span id="detect-status" style="font-weight:700; color:var(--text-muted);">Menunggu kamera...</span>
                </div>
            </div>

            <!-- Back to login/cancel -->
            <div style="text-align: center; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 16px;">
                <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color:var(--text-muted); font-size:13px; font-weight:600; cursor:pointer; text-decoration:underline;">Batal & Kembali ke Login</button>
                </form>
            </div>

        </div>
    </div>

    <style>
        @keyframes pulseHUD {
            0% { opacity: 0.3; transform: scale(0.98); }
            50% { opacity: 0.6; transform: scale(1.02); }
            100% { opacity: 0.3; transform: scale(0.98); }
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .spinner {
            border: 3px solid rgba(199, 154, 75, 0.2);
            border-top: 3px solid var(--text-gold);
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s infinite linear;
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        let localStream = null;
        let modelsLoaded = false;
        let detectInterval = null;
        let isVerifying = false;

        window.onload = async function() {
            await startWebcam();
        };

        async function startWebcam() {
            const video = document.getElementById('webcam');
            const loadingOverlay = document.getElementById('models-loading');
            const camStatus = document.getElementById('cam-status');
            const detectStatus = document.getElementById('detect-status');

            if (!modelsLoaded) {
                try {
                    const MODEL_URL = '/models';
                    await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                    await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                    modelsLoaded = true;
                } catch (err) {
                    alert('Gagal memuat algoritma wajah: ' + err.message);
                    loadingOverlay.style.display = 'none';
                    return;
                }
                loadingOverlay.style.display = 'none';
            }

            try {
                localStream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480 } 
                });
                video.srcObject = localStream;
                camStatus.textContent = 'Aktif';
                camStatus.style.color = '#10b981';
                detectStatus.textContent = 'Mencari wajah Anda...';
                detectStatus.style.color = 'var(--text-gold)';
                
                startFaceVerification(video);
            } catch (err) {
                alert('Gagal membuka kamera. Pastikan izin kamera telah diberikan.');
                camStatus.textContent = 'Gagal';
                camStatus.style.color = '#ef4444';
            }
        }

        function startFaceVerification(video) {
            const canvas = document.getElementById('face-canvas');
            const detectStatus = document.getElementById('detect-status');
            const hud = document.getElementById('scan-hud');
            
            detectInterval = setInterval(async () => {
                if (!localStream || isVerifying) return;
                
                const detections = await faceapi.detectSingleFace(
                    video, 
                    new faceapi.TinyFaceDetectorOptions({ inputSize: 224, scoreThreshold: 0.5 })
                ).withFaceLandmarks().withFaceDescriptor();
                
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                if (detections) {
                    const dims = faceapi.matchDimensions(canvas, video, true);
                    const resizedDetections = faceapi.resizeResults(detections, dims);
                    const box = resizedDetections.detection.box;
                    
                    // Draw tracker
                    ctx.strokeStyle = '#c79a4b';
                    ctx.lineWidth = 3;
                    ctx.shadowBlur = 12;
                    ctx.shadowColor = '#c79a4b';
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    
                    // Corner highlights
                    ctx.fillStyle = '#c79a4b';
                    // Top Left
                    ctx.fillRect(box.x - 2, box.y - 2, 14, 3);
                    ctx.fillRect(box.x - 2, box.y - 2, 3, 14);
                    // Top Right
                    ctx.fillRect(box.x + box.width - 12, box.y - 2, 14, 3);
                    ctx.fillRect(box.x + box.width - 1, box.y - 2, 3, 14);
                    // Bottom Left
                    ctx.fillRect(box.x - 2, box.y + box.height - 1, 14, 3);
                    ctx.fillRect(box.x - 2, box.y + box.height - 12, 3, 14);
                    // Bottom Right
                    ctx.fillRect(box.x + box.width - 12, box.y + box.height - 1, 14, 3);
                    ctx.fillRect(box.x + box.width - 1, box.y + box.height - 12, 3, 14);

                    detectStatus.textContent = 'Mencocokkan wajah...';
                    detectStatus.style.color = '#10b981';
                    hud.style.borderColor = '#10b981';
                    hud.style.opacity = '0.6';
                    
                    // Trigger match verification automatically
                    await verifyFace(detections.descriptor);
                } else {
                    detectStatus.textContent = 'Sejajarkan wajah Anda pada kamera...';
                    detectStatus.style.color = 'var(--text-muted)';
                    hud.style.borderColor = 'var(--text-gold)';
                    hud.style.opacity = '0.25';
                }
            }, 400);
        }

        async function verifyFace(descriptor) {
            isVerifying = true;
            const detectStatus = document.getElementById('detect-status');
            const descriptorArray = Array.from(descriptor);
            
            try {
                const response = await fetch("{{ route('login.verify-face') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ descriptor: JSON.stringify(descriptorArray) })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    detectStatus.textContent = result.message;
                    detectStatus.style.color = '#10b981';
                    
                    stopCamera();
                    
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 800);
                } else {
                    detectStatus.textContent = result.message || 'Wajah tidak cocok.';
                    detectStatus.style.color = '#ef4444';
                    
                    setTimeout(() => {
                        isVerifying = false;
                    }, 1500);
                }
            } catch (e) {
                detectStatus.textContent = 'Kesalahan jaringan: ' + e.message;
                detectStatus.style.color = '#ef4444';
                setTimeout(() => {
                    isVerifying = false;
                }, 2000);
            }
        }

        function stopCamera() {
            if (detectInterval) {
                clearInterval(detectInterval);
                detectInterval = null;
            }
            if (localStream) {
                localStream.getTracks().forEach(track => track.stop());
                localStream = null;
            }
            const video = document.getElementById('webcam');
            if (video) video.srcObject = null;
        }

        window.onbeforeunload = function() {
            stopCamera();
        };
    </script>
</x-layouts.app>
