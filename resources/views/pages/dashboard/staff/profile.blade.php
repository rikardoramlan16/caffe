<x-layouts.app title="Profil Saya - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Staf">
                    @if ($authUser['role'] === 'kasir')
                        <a href="{{ route('dashboard.cashier') }}">🏠 Dashboard</a>
                    @else
                        <a href="{{ route('dashboard.barista') }}">🏠 Dashboard</a>
                    @endif
                    <a class="active" href="{{ route('staff.profile') }}">👤 Profil Saya</a>
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

                @if ($employee)
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
</x-layouts.app>
