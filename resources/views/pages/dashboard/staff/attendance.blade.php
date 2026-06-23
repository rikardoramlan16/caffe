<x-layouts.app title="Absensi Saya - CafeFlow">
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
                    <a href="{{ route('staff.profile') }}">👤 Profil Saya</a>
                    <a class="active" href="{{ route('staff.attendance') }}">📅 Absensi Saya</a>
                    <a href="{{ route('staff.payroll') }}">💵 Slip Gaji</a>
                    <div style="margin-top:auto;padding-top:20px;border-top:1px solid rgba(255,255,255,0.05);">
                        <div style="padding:10px;font-size:13px;color:var(--text-gold);display:flex;align-items:center;gap:8px;">
                            <span>{{ $authUser['role'] === 'kasir' ? '💳' : '☕' }}</span>
                            <span>{{ $authUser['name'] }}</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">@csrf<button type="submit" class="btn" style="width:100%;text-align:left;background:rgba(239,68,68,0.1);color:#ef4444;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">🚪 Keluar</button></form>
                    </div>
                </nav>
            </aside>

            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">Portal Staf</span>
                        <h1>Rekap Absensi Saya</h1>
                        <p class="muted">Lihat histori kehadiran kamu. Data ini digunakan dalam perhitungan payroll bulanan.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        <button onclick="document.getElementById('clock-in-modal').style.display='flex'" class="btn btn-gold" style="font-size:13px;padding:8px 16px;">⏱ Clock In / Out</button>
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#10b981;font-size:14px;">🎉 {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="panel" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#ef4444;font-size:14px;">⚠️ {{ session('error') }}</div>
                @endif

                <!-- Summary for this month -->
                @php
                    $hadir = $myAttendances->whereIn('status',['Hadir','Terlambat'])->count();
                    $alpha = $myAttendances->where('status','Alpha')->count();
                    $terlambat = $myAttendances->where('status','Terlambat')->count();
                    $cuti = $myAttendances->whereIn('status',['Cuti','Sakit','Izin'])->count();
                    $potonganAlpha = $alpha * 50000;
                    $potonganTerlambat = $terlambat * 10000;
                @endphp
                <section class="grid grid-4" style="margin-bottom:24px;">
                    <article class="metric-card"><span class="pill" style="background:rgba(16,185,129,0.1);color:#10b981;">Hadir</span><strong>{{ $hadir }}</strong><span>Hari Bulan Ini</span></article>
                    <article class="metric-card"><span class="pill" style="background:rgba(239,68,68,0.1);color:#ef4444;">Alpha</span><strong>{{ $alpha }}</strong><span>× Rp 50.000 Potongan</span></article>
                    <article class="metric-card"><span class="pill" style="background:rgba(245,158,11,0.1);color:#f59e0b;">Terlambat</span><strong>{{ $terlambat }}</strong><span>× Rp 10.000 Potongan</span></article>
                    <article class="metric-card"><span class="pill" style="background:rgba(239,68,68,0.15);color:#ef4444;">Est. Potongan</span><strong style="font-size:17px;">Rp {{ number_format($potonganAlpha + $potonganTerlambat,0,',','.') }}</strong><span>Estimasi Potongan Bulan Ini</span></article>
                </section>

                <!-- Attendance History -->
                <div class="panel">
                    <h3>📅 Histori Kehadiran Saya</h3>
                    <div style="overflow-x:auto;margin-top:14px;">
                        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;color:var(--ink);">
                            <thead>
                                <tr style="border-bottom:2px solid var(--line);">
                                    <th style="padding:12px 10px;">Tanggal</th>
                                    <th style="padding:12px 10px;">Jam Masuk</th>
                                    <th style="padding:12px 10px;">Jam Keluar</th>
                                    <th style="padding:12px 10px;">Durasi</th>
                                    <th style="padding:12px 10px;">Status</th>
                                    <th style="padding:12px 10px;">Est. Potongan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($myAttendances as $att)
                                    @php
                                        $sc = ['Hadir'=>['rgba(16,185,129,0.1)','#10b981'],'Terlambat'=>['rgba(245,158,11,0.1)','#f59e0b'],'Alpha'=>['rgba(239,68,68,0.1)','#ef4444'],'Izin'=>['rgba(59,130,246,0.1)','#3b82f6'],'Sakit'=>['rgba(139,92,246,0.1)','#8b5cf6'],'Cuti'=>['rgba(100,116,139,0.1)','#64748b']][$att->status] ?? ['rgba(255,255,255,0.05)','var(--muted)'];
                                        $dur = '';
                                        if ($att->clock_in && $att->clock_out) {
                                            $in = \Carbon\Carbon::createFromFormat('H:i:s', $att->clock_in);
                                            $out = \Carbon\Carbon::createFromFormat('H:i:s', $att->clock_out);
                                            $diff = $in->diff($out);
                                            $dur = $diff->h . 'j ' . $diff->i . 'm';
                                        }
                                        $potong = 0;
                                        if ($att->status === 'Alpha') $potong = 50000;
                                        elseif ($att->status === 'Terlambat') $potong = 10000;
                                    @endphp
                                    <tr style="border-bottom:1px solid var(--line);">
                                        <td style="padding:10px;font-weight:600;">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('d F Y') }}</td>
                                        <td style="padding:10px;color:#10b981;font-weight:600;">{{ $att->clock_in ? substr($att->clock_in,0,5) : '—' }}</td>
                                        <td style="padding:10px;color:#ef4444;">{{ $att->clock_out ? substr($att->clock_out,0,5) : '—' }}</td>
                                        <td style="padding:10px;color:var(--muted);">{{ $dur ?: '—' }}</td>
                                        <td style="padding:10px;"><span class="pill" style="background:{{ $sc[0] }};color:{{ $sc[1] }};font-size:10px;font-weight:800;">{{ $att->status }}</span></td>
                                        <td style="padding:10px;font-weight:700;color:#ef4444;">{{ $potong > 0 ? '− Rp ' . number_format($potong,0,',','.') : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="muted" style="padding:40px;text-align:center;">Belum ada data absensi untuk bulan ini.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Clock In/Out Modal -->
    <div id="clock-in-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(380px,94%);padding:28px;display:flex;flex-direction:column;gap:16px;text-align:center;position:relative;">
            <h3>⏱ Clock In / Clock Out</h3>
            <button onclick="document.getElementById('clock-in-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <div id="clock-display" style="font-size:48px;font-weight:800;color:var(--coffee);letter-spacing:2px;font-feature-settings:'tnum';padding:10px 0;"></div>
            <div class="muted" style="font-size:13px;" id="clock-date"></div>
            <p class="muted" style="font-size:12px;">Tekan tombol Clock In saat tiba, dan Clock Out saat selesai bekerja.</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <form action="{{ route('staff.clock') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="in">
                    <button type="submit" class="btn btn-gold" style="width:100%;padding:14px;font-size:14px;font-weight:800;border:none;cursor:pointer;border-radius:8px;">▶ Clock In</button>
                </form>
                <form action="{{ route('staff.clock') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="out">
                    <button type="submit" class="btn" style="width:100%;padding:14px;font-size:14px;font-weight:800;border:none;cursor:pointer;border-radius:8px;background:rgba(239,68,68,0.15);color:#ef4444;">■ Clock Out</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2,'0');
            const m = String(now.getMinutes()).padStart(2,'0');
            const s = String(now.getSeconds()).padStart(2,'0');
            const d = now.toLocaleDateString('id-ID', {weekday:'long',year:'numeric',month:'long',day:'numeric'});
            const el = document.getElementById('clock-display');
            const dl = document.getElementById('clock-date');
            if (el) el.textContent = `${h}:${m}:${s}`;
            if (dl) dl.textContent = d;
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</x-layouts.app>
