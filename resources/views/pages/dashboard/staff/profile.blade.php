<x-layouts.app title="Profil Saya - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Staf">
                    @if ($authUser['role'] === 'kasir')
                        <a href="{{ route('dashboard.cashier') }}">🏠 Dashboard</a>
                    @else
                        <a href="{{ route('dashboard.barista') }}">🏠 Dashboard</a>
                    @endif
                    <a class="active" href="{{ route('profil') }}">👤 Profil Saya</a>
                    <a href="{{ route('staff.attendance') }}">📅 Absensi Saya</a>
                    <a href="{{ route('staff.payroll') }}">💵 Slip Gaji</a>
                    <div style="margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.05);">
                        <div style="padding:10px;font-size:13px;color:var(--text-gold);display:flex;align-items:center;gap:8px;">
                            <span>{{ $authUser['role'] === 'kasir' ? '💳' : '☕' }}</span>
                            <span>{{ $authUser['name'] }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn" style="width:100%;text-align:left;background:rgba(239,68,68,0.1);color:#ef4444;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">🚪 Keluar</button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">Portal Staf</span>
                        <h1>Profil Karyawan Saya</h1>
                        <p class="muted">Lihat dan perbarui informasi data diri kamu sebagai karyawan CafeFlow.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#10b981;font-size:14px;">🎉 {{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="panel" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#ef4444;font-size:14px;">⚠️ {{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="panel" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#ef4444;font-size:14px;">
                        <ul style="margin:0;padding-left:20px;list-style-type:disc;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if ($employee)
                    <style>
                        @keyframes pulseHUD {
                            0% { opacity: 0.3; transform: scale(0.98); }
                            50% { opacity: 0.6; transform: scale(1.02); }
                            100% { opacity: 0.3; transform: scale(0.98); }
                        }
                        @keyframes spin {
                            to { transform: rotate(360deg); }
                        }
                        .tab-btn:hover {
                            color: var(--text-gold) !important;
                            opacity: 0.8;
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

                    <!-- Tabs Navigation -->
                    <div class="tabs-nav" style="display:flex;gap:12px;margin-bottom:24px;border-bottom:1px solid var(--line);padding-bottom:10px;">
                        <button class="tab-btn active" onclick="switchTab(event, 'tab-profile')" style="background:none;border:none;color:var(--text-gold);font-weight:600;font-size:14px;cursor:pointer;padding:8px 16px;border-bottom:2px solid var(--text-gold);transition: all 0.2s;outline:none;">👤 Profil & Cuti</button>
                        <button class="tab-btn" onclick="switchTab(event, 'tab-security')" style="background:none;border:none;color:var(--text-muted);font-weight:600;font-size:14px;cursor:pointer;padding:8px 16px;transition: all 0.2s;outline:none;">🔒 Keamanan & Verifikasi Wajah</button>
                    </div>

                    <!-- Tab Content: Profile & Leaves -->
                    <div id="tab-profile" class="tab-content">
                        <!-- Employee Info Card -->
                        <section class="split" style="grid-template-columns:1fr 1.8fr;gap:20px;margin-bottom:24px;align-items:start;">
                            <div class="panel" style="text-align:center;padding:32px 24px;display:flex;flex-direction:column;align-items:center;gap:16px;">
                                <div style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,var(--coffee),var(--gold));display:flex;align-items:center;justify-content:center;font-size:32px;border:3px solid var(--coffee);">
                                    {{ $authUser['role'] === 'kasir' ? '💳' : '☕' }}
                                </div>
                                <div>
                                    <h2 style="margin:0 0 4px 0;font-size:20px;">{{ $employee->name }}</h2>
                                    <span class="pill" style="background:rgba(199,154,75,0.2);color:var(--coffee);font-size:11px;">{{ $roles[$employee->role] ?? ucfirst($employee->role) }}</span>
                                </div>
                                <div style="width:100%;background:rgba(255,255,255,0.02);border:1px solid var(--line);border-radius:8px;padding:16px;display:flex;flex-direction:column;gap:8px;text-align:left;">
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">Cabang</span>
                                        <span style="font-weight:700;">{{ $employee->branch ? $employee->branch->name : '—' }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">NIK Karyawan</span>
                                        <span style="font-weight:700;">{{ $employee->employee_number ?? 'EMP-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">Status</span>
                                        <span class="pill" style="background:rgba(16,185,129,0.1);color:#10b981;font-size:10px;font-weight:800;">{{ $employee->status }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">Gaji Pokok</span>
                                        <span style="font-weight:700;color:var(--coffee);">Rp {{ number_format($employee->basic_salary ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">Tunjangan</span>
                                        <span style="font-weight:700;color:#10b981;">+ Rp {{ number_format($employee->allowance ?? 0, 0, ',', '.') }}</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:13px;">
                                        <span class="muted">Bergabung</span>
                                        <span style="font-weight:700;">{{ $employee->hired_date ? \Carbon\Carbon::parse($employee->hired_date)->translatedFormat('d M Y') : '—' }}</span>
                                    </div>
                                </div>
                            </div>

                            <div style="display:flex;flex-direction:column;gap:20px;">
                                <!-- Summary Stats -->
                                <section class="grid grid-4">
                                    <article class="metric-card" style="min-height:auto;padding:16px;">
                                        <span class="pill" style="background:rgba(16,185,129,0.1);color:#10b981;font-size:10px;">Hadir</span>
                                        <strong>{{ $attendanceSummary['hadir'] ?? 0 }}</strong>
                                        <span>Hari Ini Bulan Ini</span>
                                    </article>
                                    <article class="metric-card" style="min-height:auto;padding:16px;">
                                        <span class="pill" style="background:rgba(245,158,11,0.1);color:#f59e0b;font-size:10px;">Terlambat</span>
                                        <strong>{{ $attendanceSummary['terlambat'] ?? 0 }}</strong>
                                        <span>Kali Terlambat</span>
                                    </article>
                                    <article class="metric-card" style="min-height:auto;padding:16px;">
                                        <span class="pill" style="background:rgba(59,130,246,0.1);color:#3b82f6;font-size:10px;">Cuti</span>
                                        <strong>{{ $attendanceSummary['cuti'] ?? 0 }}</strong>
                                        <span>Sisa Jatah Cuti</span>
                                    </article>
                                    <article class="metric-card" style="min-height:auto;padding:16px;">
                                        <span class="pill" style="background:rgba(199,154,75,0.2);color:var(--coffee);font-size:10px;">Payroll</span>
                                        <strong>{{ $totalPayrolls ?? 0 }}</strong>
                                        <span>Total Slip Gaji</span>
                                    </article>
                                </section>

                                <!-- Leave Request Form -->
                                <div class="panel">
                                    <h3>🏖️ Ajukan Cuti</h3>
                                    <p class="muted" style="font-size:12px;margin-top:-6px;margin-bottom:16px;">Ajukan permohonan cuti untuk disetujui oleh Owner / Super Admin.</p>
                                    <form action="{{ route('staff.leave.apply') }}" method="POST" style="display:flex;flex-direction:column;gap:12px;">
                                        @csrf
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                            <div style="display:flex;flex-direction:column;gap:4px;">
                                                <label style="font-size:12px;font-weight:600;">Jenis Cuti</label>
                                                <select name="type" required style="padding:10px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                                                    <option>Cuti Tahunan</option><option>Sakit</option><option>Izin Keluarga</option><option>Cuti Darurat</option>
                                                </select>
                                            </div>
                                            <div style="display:flex;flex-direction:column;gap:4px;">
                                                <label style="font-size:12px;font-weight:600;">Tanggal Mulai</label>
                                                <input type="date" name="start_date" required style="padding:10px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                                            </div>
                                        </div>
                                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                                            <div style="display:flex;flex-direction:column;gap:4px;">
                                                <label style="font-size:12px;font-weight:600;">Tanggal Selesai</label>
                                                <input type="date" name="end_date" required style="padding:10px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                                            </div>
                                            <div style="display:flex;flex-direction:column;gap:4px;">
                                                <label style="font-size:12px;font-weight:600;">Alasan</label>
                                                <input type="text" name="reason" required placeholder="Alasan cuti..." style="padding:10px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="padding:10px;font-size:13px;font-weight:700;border:none;cursor:pointer;border-radius:6px;">Kirim Pengajuan Cuti</button>
                                    </form>
                                </div>

                                <!-- Pending Leave Requests -->
                                @if ($myLeaves && $myLeaves->count())
                                    <div class="panel">
                                        <h3>📋 Riwayat Pengajuan Cuti</h3>
                                        @foreach ($myLeaves->take(5) as $lv)
                                            @php $lc = $lv->status === 'APPROVED' ? ['rgba(16,185,129,0.1)','#10b981'] : ($lv->status === 'REJECTED' ? ['rgba(239,68,68,0.1)','#ef4444'] : ['rgba(245,158,11,0.1)','#f59e0b']); @endphp
                                            <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;border-bottom:1px solid var(--line);font-size:13px;">
                                                <div>
                                                    <span style="font-weight:600;">{{ $lv->type }}</span>
                                                    <span class="muted"> · {{ \Carbon\Carbon::parse($lv->start_date)->format('d/m') }} – {{ \Carbon\Carbon::parse($lv->end_date)->format('d/m/Y') }}</span>
                                                </div>
                                                <span class="pill" style="background:{{ $lc[0] }};color:{{ $lc[1] }};font-size:9px;font-weight:800;">{{ $lv->status }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>

                    <!-- Tab Content: Security & Face Verification -->
                    @php
                        $userAccount = \App\Models\User::find($authUser['id']);
                        $hasFace = !empty($userAccount->face_descriptor);
                    @endphp
                    <div id="tab-security" class="tab-content" style="display:none;">
                        <section class="split" style="grid-template-columns:1fr 1.2fr;gap:20px;align-items:start;">
                            <!-- Change Password Panel -->
                            <div class="panel">
                                <h3>🔑 Ganti Password</h3>
                                <p class="muted" style="font-size:12px;margin-top:-6px;margin-bottom:16px;">Amankan akun kerja Anda dengan mengganti password secara berkala.</p>
                                <form action="{{ route('staff.change-password') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;">
                                    @csrf
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <label style="font-size:12px;font-weight:600;">Password Saat Ini</label>
                                        <input type="password" name="current_password" required placeholder="••••••••" style="padding:10px 14px;border-radius:8px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;outline:none;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                                    </div>
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <label style="font-size:12px;font-weight:600;">Password Baru</label>
                                        <input type="password" name="new_password" required placeholder="Minimal 8 karakter..." style="padding:10px 14px;border-radius:8px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;outline:none;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                                    </div>
                                    <div style="display:flex;flex-direction:column;gap:6px;">
                                        <label style="font-size:12px;font-weight:600;">Konfirmasi Password Baru</label>
                                        <input type="password" name="new_password_confirmation" required placeholder="Masukkan kembali password baru..." style="padding:10px 14px;border-radius:8px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;outline:none;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                                    </div>
                                    <button type="submit" class="btn btn-primary" style="padding:12px;font-size:13px;font-weight:700;border:none;cursor:pointer;border-radius:8px;margin-top:8px;">Perbarui Password</button>
                                </form>
                            </div>

                            <!-- Face Recognition Panel -->
                            <div class="panel" style="position:relative;">
                                <h3>👤 Pendaftaran Verifikasi Wajah</h3>
                                <p class="muted" style="font-size:12px;margin-top:-6px;margin-bottom:16px;">Tambahkan lapisan keamanan. Login akan meminta pemindaian wajah jika wajah Anda terdaftar.</p>

                                <!-- Status Info -->
                                <div id="face-status-badge" style="background:{{ $hasFace ? 'rgba(16,185,129,0.05)' : 'rgba(255,255,255,0.02)' }};border:1px solid {{ $hasFace ? 'rgba(16,185,129,0.15)' : 'var(--line)' }};padding:14px;border-radius:8px;display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                                    <div id="status-icon" style="font-size:24px;color:{{ $hasFace ? '#10b981' : '#f59e0b' }};">{{ $hasFace ? '✅' : '⚠️' }}</div>
                                    <div>
                                        <h4 id="status-title" style="margin:0;font-size:13px;color:{{ $hasFace ? '#10b981' : '#f59e0b' }};">{{ $hasFace ? 'Wajah Anda Sudah Terdaftar' : 'Belum Ada Wajah Terdaftar' }}</h4>
                                        <p id="status-desc" class="muted" style="margin:2px 0 0 0;font-size:11px;">{{ $hasFace ? 'Login akan meminta scan wajah menggunakan kamera webcam.' : 'Login Anda saat ini hanya menggunakan email dan password.' }}</p>
                                    </div>
                                </div>

                                <!-- Webcam container -->
                                <div id="webcam-container" style="position:relative;width:100%;aspect-ratio:4/3;background:#050505;border-radius:10px;overflow:hidden;border:1px solid var(--line);margin-bottom:16px;display:flex;align-items:center;justify-content:center;">
                                    <video id="webcam" autoplay muted playsinline style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);"></video>
                                    <canvas id="face-canvas" style="position:absolute;top:0;left:0;width:100%;height:100%;transform:scaleX(-1);pointer-events:none;"></canvas>
                                    
                                    <!-- High-tech HUD circle scanning animation overlay -->
                                    <div id="scan-hud" style="position:absolute;width:240px;height:240px;border:2px dashed var(--text-gold);border-radius:50%;opacity:0.25;pointer-events:none;box-sizing:border-box;animation: pulseHUD 2.5s infinite ease-in-out;"></div>
                                    
                                    <!-- Camera Overlay / Start Button -->
                                    <div id="camera-placeholder" style="position:absolute;display:flex;flex-direction:column;align-items:center;gap:12px;z-index:2;text-align:center;">
                                        <span style="font-size:36px;opacity:0.8;">📷</span>
                                        <button onclick="startWebcam()" class="btn btn-primary" style="padding:10px 20px;font-size:13px;font-weight:700;">{{ $hasFace ? 'Daftarkan Ulang Wajah' : 'Aktifkan Kamera Scan' }}</button>
                                    </div>
                                    
                                    <!-- Loading models overlay -->
                                    <div id="models-loading" style="position:absolute;display:none;flex-direction:column;align-items:center;gap:12px;background:rgba(0,0,0,0.9);width:100%;height:100%;justify-content:center;z-index:3;">
                                        <div class="spinner"></div>
                                        <span style="font-size:13px;color:var(--text-gold);font-weight:600;">Memuat Algoritma AI...</span>
                                    </div>
                                </div>

                                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:20px;background:rgba(0,0,0,0.1);padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.02);">
                                    <div style="display:flex;justify-content:space-between;font-size:12px;">
                                        <span class="muted">Status Kamera:</span>
                                        <span id="cam-status" style="font-weight:700;color:#ef4444;">Nonaktif</span>
                                    </div>
                                    <div style="display:flex;justify-content:space-between;font-size:12px;">
                                        <span class="muted">Deteksi Wajah:</span>
                                        <span id="detect-status" style="font-weight:700;color:var(--text-muted);">Menunggu Kamera...</span>
                                    </div>
                                </div>

                                <div style="display:flex;gap:10px;">
                                    <button id="btn-scan" disabled onclick="captureAndRegister()" class="btn btn-primary" style="flex:1;padding:12px;font-size:13px;font-weight:700;border:none;cursor:pointer;border-radius:8px;opacity:0.5;transition:all 0.2s;">
                                        Pindai & Daftarkan Wajah
                                    </button>
                                    @if ($hasFace)
                                        <button id="btn-delete-face" onclick="deleteFaceRegistration()" class="btn" style="padding:12px;background:rgba(239,68,68,0.08);color:#ef4444;border:1px solid rgba(239,68,68,0.15);border-radius:8px;cursor:pointer;font-size:13px;font-weight:600;">
                                            Hapus Wajah
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </section>
                    </div>
                @else
                    <div class="panel" style="padding:40px;text-align:center;">
                        <div style="font-size:48px;margin-bottom:16px;">👤</div>
                        <h3>Data Karyawan Belum Terdaftar</h3>
                        <p class="muted">Hubungi Super Admin atau Owner untuk mendaftarkan data karyawan Anda.</p>
                    </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Scripts for Security Tab & face-api.js -->
    @if ($employee)
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
        <script>
            function switchTab(evt, tabId) {
                // Hide all tab content
                document.querySelectorAll('.tab-content').forEach(el => {
                    el.style.display = 'none';
                });
                // Remove active class and reset styling from all buttons
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.classList.remove('active');
                    btn.style.color = 'var(--text-muted)';
                    btn.style.borderBottom = 'none';
                });
                
                // Show selected tab content
                document.getElementById(tabId).style.display = 'block';
                // Style selected button
                evt.currentTarget.classList.add('active');
                evt.currentTarget.style.color = 'var(--text-gold)';
                evt.currentTarget.style.borderBottom = '2px solid var(--text-gold)';

                // If switching away from security, stop webcam
                if (tabId !== 'tab-security') {
                    stopCamera();
                }
            }

            let localStream = null;
            let modelsLoaded = false;
            let detectInterval = null;

            async function startWebcam() {
                const video = document.getElementById('webcam');
                const placeholder = document.getElementById('camera-placeholder');
                const loadingOverlay = document.getElementById('models-loading');
                const camStatus = document.getElementById('cam-status');
                const detectStatus = document.getElementById('detect-status');

                placeholder.style.display = 'none';
                
                if (!modelsLoaded) {
                    loadingOverlay.style.display = 'flex';
                    try {
                        // Models path is /models, which links to public/models/
                        const MODEL_URL = '/models';
                        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                        await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                        await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                        modelsLoaded = true;
                    } catch (err) {
                        alert('Gagal memuat algoritma wajah: ' + err.message);
                        loadingOverlay.style.display = 'none';
                        placeholder.style.display = 'flex';
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
                    detectStatus.textContent = 'Menyelaraskan kamera...';
                    detectStatus.style.color = 'var(--text-gold)';
                    
                    startFaceDetection(video);
                } catch (err) {
                    alert('Gagal membuka kamera: ' + err.message);
                    placeholder.style.display = 'flex';
                }
            }

            function startFaceDetection(video) {
                const canvas = document.getElementById('face-canvas');
                const detectStatus = document.getElementById('detect-status');
                const btnScan = document.getElementById('btn-scan');
                const hud = document.getElementById('scan-hud');
                
                detectInterval = setInterval(async () => {
                    if (!localStream) return;
                    
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
                        
                        // Draw glowing tracking box
                        ctx.strokeStyle = '#c79a4b';
                        ctx.lineWidth = 3;
                        ctx.shadowBlur = 12;
                        ctx.shadowColor = '#c79a4b';
                        ctx.strokeRect(box.x, box.y, box.width, box.height);
                        
                        // Brackets corner highlights
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

                        detectStatus.textContent = 'Wajah Terdeteksi (Siap Pindai)';
                        detectStatus.style.color = '#10b981';
                        btnScan.disabled = false;
                        btnScan.style.opacity = '1';
                        hud.style.borderColor = '#10b981';
                        hud.style.opacity = '0.6';
                        
                        window.latestFaceDescriptor = detections.descriptor;
                    } else {
                        detectStatus.textContent = 'Posisikan wajah Anda tepat di depan kamera...';
                        detectStatus.style.color = 'var(--text-muted)';
                        btnScan.disabled = true;
                        btnScan.style.opacity = '0.5';
                        hud.style.borderColor = 'var(--text-gold)';
                        hud.style.opacity = '0.25';
                        window.latestFaceDescriptor = null;
                    }
                }, 300);
            }

            async function captureAndRegister() {
                if (!window.latestFaceDescriptor) {
                    alert('Wajah tidak terdeteksi.');
                    return;
                }
                
                const descriptorArray = Array.from(window.latestFaceDescriptor);
                stopCamera();
                
                try {
                    const response = await fetch("{{ route('staff.register-face') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ descriptor: JSON.stringify(descriptorArray) })
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert(result.message || 'Gagal menyimpan deskriptor wajah.');
                        startWebcam();
                    }
                } catch (e) {
                    alert('Kesalahan jaringan: ' + e.message);
                    startWebcam();
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
                
                const camStatus = document.getElementById('cam-status');
                const detectStatus = document.getElementById('detect-status');
                const btnScan = document.getElementById('btn-scan');
                const placeholder = document.getElementById('camera-placeholder');
                
                if (camStatus) {
                    camStatus.textContent = 'Nonaktif';
                    camStatus.style.color = '#ef4444';
                }
                if (detectStatus) {
                    detectStatus.textContent = 'Menunggu Kamera...';
                    detectStatus.style.color = 'var(--text-muted)';
                }
                if (btnScan) {
                    btnScan.disabled = true;
                    btnScan.style.opacity = '0.5';
                }
                if (placeholder) {
                    placeholder.style.display = 'flex';
                }
            }

            async function deleteFaceRegistration() {
                if (!confirm('Apakah Anda yakin ingin menghapus verifikasi wajah? Setelah dihapus, login hanya akan menggunakan email & password.')) {
                    return;
                }
                
                try {
                    const response = await fetch("{{ route('staff.delete-face') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    
                    const result = await response.json();
                    if (result.success) {
                        alert(result.message);
                        window.location.reload();
                    } else {
                        alert(result.message || 'Gagal menghapus pendaftaran wajah.');
                    }
                } catch (e) {
                    alert('Kesalahan jaringan: ' + e.message);
                }
            }
        </script>
    @endif
</x-layouts.app>
