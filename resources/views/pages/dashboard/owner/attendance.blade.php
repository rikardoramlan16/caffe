<x-layouts.app title="Absensi Karyawan - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Sidebar">
                    @if ($authUser['role'] === 'owner')
                        <a href="{{ route('dashboard.owner') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a class="active" href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                        <a href="{{ route('dashboard.owner.section', 'penjualan') }}">📊 Laporan</a>
                        <a href="{{ route('admin.inventory.index') }}">📦 Inventory</a>
                        <a href="{{ route('dashboard.owner.section', 'approval') }}">🛡️ Approval</a>
                    @elseif ($authUser['role'] === 'super_admin')
                        <a href="{{ route('dashboard.super-admin') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Kelola Karyawan</a>
                        <a class="active" href="{{ route('owner.attendance') }}">📅 Kelola Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Kelola Payroll</a>
                    @else
                        <a href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a class="active" href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                    @endif

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>👑</span>
                            <span>{{ $authUser['name'] }} ({{ ucfirst($authUser['role']) }})</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; text-align: left; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600;">
                                🚪 Keluar Staf
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">{{ ucfirst($authUser['role']) }} Workspace</span>
                        <h1>Manajemen Absensi Karyawan</h1>
                        <p class="muted">Pantau kehadiran karyawan secara lengkap — jam masuk, jam keluar, dan status absensi harian.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        @if (in_array($authUser['role'], ['owner','super_admin']))
                            <button onclick="openAddAttendanceModal()" class="btn btn-gold" style="font-size: 13px; padding: 8px 16px;">+ Tambah Absensi Manual</button>
                        @endif
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #10b981; font-size: 14px;">
                        🎉 {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="panel" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; color: #ef4444; font-size: 14px;">
                        ⚠️ {{ session('error') }}
                    </div>
                @endif

                <!-- Summary Stats -->
                @php
                    $totalHadir = $attendances->where('status','Hadir')->count() + $attendances->where('status','Terlambat')->count();
                    $totalAlpha = $attendances->where('status','Alpha')->count();
                    $totalSakit = $attendances->where('status','Sakit')->count();
                    $totalIzin = $attendances->where('status','Izin')->count();
                    $totalCuti = $attendances->where('status','Cuti')->count();
                @endphp
                <section class="grid grid-4" style="margin-bottom: 24px;">
                    <article class="metric-card">
                        <span class="pill" style="background: rgba(16,185,129,0.1); color: #10b981;">Hadir</span>
                        <strong>{{ $totalHadir }}</strong>
                        <span>Hadir / Terlambat</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: rgba(239,68,68,0.1); color: #ef4444;">Alpha</span>
                        <strong>{{ $totalAlpha }}</strong>
                        <span>Absen Tanpa Izin</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: rgba(59,130,246,0.1); color: #3b82f6;">Sakit</span>
                        <strong>{{ $totalSakit }}</strong>
                        <span>Sakit & Izin</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: rgba(245,158,11,0.1); color: #f59e0b;">Cuti</span>
                        <strong>{{ $totalCuti }}</strong>
                        <span>Sedang Cuti</span>
                    </article>
                </section>

                <!-- Filter Panel -->
                <div class="panel" style="margin-bottom: 20px;">
                    <form action="{{ route('owner.attendance') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
                        <div style="flex: 1.5; min-width: 180px; display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--muted);">Cari Karyawan</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama karyawan..." style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline:none; width:100%;">
                        </div>
                        <div style="flex:1; min-width:130px; display:flex; flex-direction:column; gap:4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--muted);">Tanggal</label>
                            <input type="date" name="date" value="{{ request('date') }}" style="padding: 10px 14px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline:none; width:100%;">
                        </div>
                        @if ($authUser['role'] !== 'admin')
                        <div style="flex:1; min-width:140px; display:flex; flex-direction:column; gap:4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--muted);">Cabang</label>
                            <select name="branch_id" style="padding: 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline:none; width:100%;">
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $br)
                                    <option value="{{ $br->id }}" {{ request('branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div style="flex:1; min-width:130px; display:flex; flex-direction:column; gap:4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--muted);">Status</label>
                            <select name="status" style="padding: 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline:none; width:100%;">
                                <option value="">Semua Status</option>
                                @foreach ($statuses as $s)
                                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="display:flex; gap:8px;">
                            <button type="submit" class="btn btn-primary" style="font-size:13px; padding:10px 16px;">Cari</button>
                            <a href="{{ route('owner.attendance') }}" class="btn" style="font-size:13px; padding:10px 14px;">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Attendance Table -->
                <div class="panel">
                    <h3>📅 Rekap Absensi Karyawan</h3>
                    <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Rekap harian seluruh data absensi karyawan. Alpha & Terlambat otomatis mempengaruhi kalkulasi potongan payroll.</p>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--ink);">
                            <thead>
                                <tr style="border-bottom: 2px solid var(--line);">
                                    <th style="padding: 12px 10px;">Karyawan</th>
                                    <th style="padding: 12px 10px;">Cabang</th>
                                    <th style="padding: 12px 10px;">Tanggal</th>
                                    <th style="padding: 12px 10px;">Jam Masuk</th>
                                    <th style="padding: 12px 10px;">Jam Keluar</th>
                                    <th style="padding: 12px 10px;">Durasi Kerja</th>
                                    <th style="padding: 12px 10px;">Status</th>
                                    @if (in_array($authUser['role'], ['owner','super_admin']))
                                        <th style="padding: 12px 10px; text-align:right;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($attendances as $att)
                                    @php
                                        $statusColors = [
                                            'Hadir' => ['rgba(16,185,129,0.1)', '#10b981'],
                                            'Terlambat' => ['rgba(245,158,11,0.1)', '#f59e0b'],
                                            'Izin' => ['rgba(59,130,246,0.1)', '#3b82f6'],
                                            'Sakit' => ['rgba(139,92,246,0.1)', '#8b5cf6'],
                                            'Alpha' => ['rgba(239,68,68,0.1)', '#ef4444'],
                                            'Cuti' => ['rgba(100,116,139,0.1)', '#64748b']
                                        ];
                                        $sc = $statusColors[$att->status] ?? ['rgba(255,255,255,0.05)', 'var(--muted)'];
                                        
                                        // Calculate work duration
                                        $duration = '';
                                        if ($att->clock_in && $att->clock_out) {
                                            $in = \Carbon\Carbon::createFromFormat('H:i:s', $att->clock_in);
                                            $out = \Carbon\Carbon::createFromFormat('H:i:s', $att->clock_out);
                                            $diff = $in->diff($out);
                                            $duration = $diff->h . 'j ' . $diff->i . 'm';
                                        }
                                    @endphp
                                    <tr style="border-bottom: 1px solid var(--line);">
                                        <td style="padding: 10px; font-weight: 700;">
                                            👤 {{ $att->employee ? $att->employee->name : 'N/A' }}
                                            <div class="muted" style="font-size: 11px; font-weight: 500;">{{ $att->employee ? ($roles[$att->employee->role] ?? ucfirst($att->employee->role)) : '' }}</div>
                                        </td>
                                        <td style="padding: 10px;">🏢 {{ $att->employee && $att->employee->branch ? $att->employee->branch->name : '-' }}</td>
                                        <td style="padding: 10px; font-weight: 600;">{{ \Carbon\Carbon::parse($att->date)->translatedFormat('d F Y') }}</td>
                                        <td style="padding: 10px; font-weight: 600; color: #10b981;">{{ $att->clock_in ? substr($att->clock_in, 0, 5) : '—' }}</td>
                                        <td style="padding: 10px; color: #ef4444;">{{ $att->clock_out ? substr($att->clock_out, 0, 5) : '—' }}</td>
                                        <td style="padding: 10px; color: var(--muted);">{{ $duration ?: '—' }}</td>
                                        <td style="padding: 10px;">
                                            <span class="pill" style="background: {{ $sc[0] }}; color: {{ $sc[1] }}; font-size: 10px; font-weight: 800;">
                                                {{ $att->status }}
                                            </span>
                                        </td>
                                        @if (in_array($authUser['role'], ['owner','super_admin']))
                                        <td style="padding: 10px; text-align: right;">
                                            <button onclick='openEditAttendanceModal({{ $att->id }}, "{{ $att->clock_in }}", "{{ $att->clock_out }}", "{{ $att->status }}")' class="btn" style="font-size: 11px; padding: 4px 8px; border-radius: 4px;">Edit</button>
                                        </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="muted" style="padding: 40px; text-align: center;">Tidak ada data absensi untuk filter yang dipilih.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Add Attendance -->
    @if (in_array($authUser['role'], ['owner','super_admin']))
    <div id="add-attendance-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(480px,94%);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;max-height:90%;overflow-y:auto;">
            <h3>📅 Tambah Absensi Manual</h3>
            <button onclick="document.getElementById('add-attendance-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <form action="{{ route('owner.attendance.store') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Karyawan</label>
                    <select name="employee_id" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                        <option value="">Pilih Karyawan</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} — {{ $roles[$emp->role] ?? ucfirst($emp->role) }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Tanggal</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Status</label>
                        <select name="status" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                            @foreach ($statuses as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jam Masuk</label>
                        <input type="time" name="clock_in" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jam Keluar</label>
                        <input type="time" name="clock_out" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                </div>
                <button type="submit" class="btn btn-gold" style="padding:12px;font-weight:800;border:none;cursor:pointer;border-radius:6px;margin-top:10px;">Simpan Absensi</button>
            </form>
        </div>
    </div>

    <!-- Modal Edit Attendance -->
    <div id="edit-attendance-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(440px,94%);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;">
            <h3>✏️ Edit Absensi</h3>
            <button onclick="document.getElementById('edit-attendance-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <form id="edit-attendance-form" method="POST" style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jam Masuk</label>
                        <input type="time" id="edit_att_in" name="clock_in" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jam Keluar</label>
                        <input type="time" id="edit_att_out" name="clock_out" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Status Absensi</label>
                    <select id="edit_att_status" name="status" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                        @foreach ($statuses as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-gold" style="padding:12px;font-weight:800;border:none;cursor:pointer;border-radius:6px;margin-top:10px;">Simpan Perubahan</button>
            </form>
        </div>
    </div>
    @endif

    <script>
        function openAddAttendanceModal() {
            document.getElementById('add-attendance-modal').style.display = 'flex';
        }
        function openEditAttendanceModal(id, clockIn, clockOut, status) {
            const form = document.getElementById('edit-attendance-form');
            form.action = `/dashboard/owner/attendance/${id}/update`;
            document.getElementById('edit_att_in').value = clockIn ? clockIn.substring(0,5) : '';
            document.getElementById('edit_att_out').value = clockOut ? clockOut.substring(0,5) : '';
            document.getElementById('edit_att_status').value = status;
            document.getElementById('edit-attendance-modal').style.display = 'flex';
        }
    </script>
</x-layouts.app>

