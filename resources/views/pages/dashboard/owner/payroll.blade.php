<x-layouts.app title="Payroll & Penggajian - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Sidebar">
                    @if ($authUser['role'] === 'owner')
                        <a href="{{ route('dashboard.owner') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a class="active" href="{{ route('owner.payroll') }}">💵 Payroll</a>
                        <a href="{{ route('dashboard.owner.section', 'penjualan') }}">📊 Laporan</a>
                        <a href="{{ route('admin.inventory.index') }}">📦 Inventory</a>
                        <a href="{{ route('dashboard.owner.section', 'approval') }}">🛡️ Approval</a>
                    @elseif ($authUser['role'] === 'super_admin')
                        <a href="{{ route('dashboard.super-admin') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Kelola Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Kelola Absensi</a>
                        <a class="active" href="{{ route('owner.payroll') }}">💵 Kelola Payroll</a>
                    @else
                        <a href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a class="active" href="{{ route('owner.payroll') }}">💵 Payroll</a>
                    @endif
                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>👑</span><span>{{ $authUser['name'] }} ({{ ucfirst($authUser['role']) }})</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="btn" style="width:100%;text-align:left;background:rgba(239,68,68,0.1);color:#ef4444;border:none;padding:10px 14px;border-radius:6px;cursor:pointer;font-size:13px;font-weight:600;">🚪 Keluar Staf</button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">{{ ucfirst($authUser['role']) }} Workspace</span>
                        <h1>Payroll & Penggajian Karyawan</h1>
                        <p class="muted">Kelola siklus penggajian bulanan: generate payroll otomatis, kelola bonus, potongan, cuti, dan cetak slip gaji.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        @if (in_array($authUser['role'], ['owner','super_admin']))
                            <button onclick="document.getElementById('generate-modal').style.display='flex'" class="btn btn-gold" style="font-size:13px;padding:8px 16px;">⚙️ Generate Payroll</button>
                        @endif
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#10b981;font-size:14px;">🎉 {{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="panel" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);padding:12px 16px;border-radius:8px;margin-bottom:20px;color:#ef4444;font-size:14px;">⚠️ {{ session('error') }}</div>
                @endif

                <!-- Summary Stats -->
                @php
                    $totalPending = $payrolls->where('status','PENDING')->count();
                    $totalApproved = $payrolls->where('status','APPROVED')->count();
                    $totalPayroll = $payrolls->where('status','APPROVED')->sum('total_salary');
                    $pendingBonuses = $bonuses->where('status','PENDING')->count();
                @endphp
                <section class="grid grid-4" style="margin-bottom:24px;">
                    <article class="metric-card">
                        <span class="pill" style="background:rgba(245,158,11,0.1);color:#f59e0b;">Pending</span>
                        <strong>{{ $totalPending }}</strong>
                        <span>Payroll Menunggu Persetujuan</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background:rgba(16,185,129,0.1);color:#10b981;">Approved</span>
                        <strong>{{ $totalApproved }}</strong>
                        <span>Payroll Disetujui</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background:rgba(199,154,75,0.2);color:var(--coffee);">Total Biaya</span>
                        <strong style="font-size:18px;">Rp {{ number_format($totalPayroll, 0, ',', '.') }}</strong>
                        <span>Total Gaji Dibayarkan</span>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background:rgba(59,130,246,0.1);color:#3b82f6;">Bonus</span>
                        <strong>{{ $pendingBonuses }}</strong>
                        <span>Pengajuan Bonus Pending</span>
                    </article>
                </section>

                <!-- Filter Panel -->
                <div class="panel" style="margin-bottom:20px;">
                    <form action="{{ route('owner.payroll') }}" method="GET" style="display:flex;flex-wrap:wrap;gap:14px;align-items:flex-end;">
                        @if ($authUser['role'] !== 'admin')
                        <div style="flex:1;min-width:140px;display:flex;flex-direction:column;gap:4px;">
                            <label style="font-size:12px;font-weight:700;color:var(--muted);">Cabang</label>
                            <select name="branch_id" style="padding:10px;border-radius:6px;background:var(--surface);border:1px solid var(--line);color:var(--ink);font-size:13px;outline:none;width:100%;">
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $br)
                                    <option value="{{ $br->id }}" {{ request('branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div style="flex:1;min-width:130px;display:flex;flex-direction:column;gap:4px;">
                            <label style="font-size:12px;font-weight:700;color:var(--muted);">Periode</label>
                            <select name="month" style="padding:10px;border-radius:6px;background:var(--surface);border:1px solid var(--line);color:var(--ink);font-size:13px;outline:none;width:100%;">
                                <option value="">Semua Periode</option>
                                @foreach ($payrollMonths as $m)
                                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div style="flex:1;min-width:120px;display:flex;flex-direction:column;gap:4px;">
                            <label style="font-size:12px;font-weight:700;color:var(--muted);">Status</label>
                            <select name="status" style="padding:10px;border-radius:6px;background:var(--surface);border:1px solid var(--line);color:var(--ink);font-size:13px;outline:none;width:100%;">
                                <option value="">Semua Status</option>
                                <option value="PENDING" {{ request('status')=='PENDING'?'selected':'' }}>Pending</option>
                                <option value="APPROVED" {{ request('status')=='APPROVED'?'selected':'' }}>Approved</option>
                                <option value="REJECTED" {{ request('status')=='REJECTED'?'selected':'' }}>Rejected</option>
                            </select>
                        </div>
                        <div style="display:flex;gap:8px;">
                            <button type="submit" class="btn btn-primary" style="font-size:13px;padding:10px 16px;">Filter</button>
                            <a href="{{ route('owner.payroll') }}" class="btn" style="font-size:13px;padding:10px 14px;">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Payroll Table -->
                <div class="panel" style="margin-bottom:24px;">
                    <h3>💵 Daftar Rekap Payroll</h3>
                    <p class="muted" style="font-size:12px;margin-top:-6px;margin-bottom:16px;">Histori payroll seluruh karyawan. Klik "Slip" untuk cetak slip gaji resmi yang sudah disetujui.</p>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;color:var(--ink);">
                            <thead>
                                <tr style="border-bottom:2px solid var(--line);">
                                    <th style="padding:12px 10px;">Karyawan</th>
                                    <th style="padding:12px 10px;">Cabang</th>
                                    <th style="padding:12px 10px;">Periode</th>
                                    <th style="padding:12px 10px;">Gaji Pokok</th>
                                    <th style="padding:12px 10px;">+ Bonus</th>
                                    <th style="padding:12px 10px;">− Potongan</th>
                                    <th style="padding:12px 10px;">Total Gaji</th>
                                    <th style="padding:12px 10px;">Status</th>
                                    <th style="padding:12px 10px;text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrolls as $pay)
                                    @php
                                        $colors = $pay->status === 'APPROVED' ? ['rgba(16,185,129,0.1)','#10b981'] : ($pay->status === 'REJECTED' ? ['rgba(239,68,68,0.1)','#ef4444'] : ['rgba(245,158,11,0.1)','#f59e0b']);
                                    @endphp
                                    <tr style="border-bottom:1px solid var(--line);">
                                        <td style="padding:10px;font-weight:700;">
                                            👤 {{ $pay->employee ? $pay->employee->name : ($pay->user ? $pay->user->name : 'N/A') }}
                                            <div class="muted" style="font-size:11px;font-weight:500;">{{ $pay->employee ? ($roles[$pay->employee->role] ?? ucfirst($pay->employee->role)) : '' }}</div>
                                        </td>
                                        <td style="padding:10px;">🏢 {{ $pay->employee && $pay->employee->branch ? $pay->employee->branch->name : '—' }}</td>
                                        <td style="padding:10px;font-weight:600;">{{ $pay->month }}</td>
                                        <td style="padding:10px;">Rp {{ number_format($pay->basic_salary,0,',','.') }}</td>
                                        <td style="padding:10px;color:#10b981;font-weight:600;">+ Rp {{ number_format($pay->bonus + $pay->allowance,0,',','.') }}</td>
                                        <td style="padding:10px;color:#ef4444;font-weight:600;">− Rp {{ number_format($pay->deduction,0,',','.') }}</td>
                                        <td style="padding:10px;font-weight:800;color:var(--coffee);">Rp {{ number_format($pay->total_salary,0,',','.') }}</td>
                                        <td style="padding:10px;">
                                            <span class="pill" style="background:{{ $colors[0] }};color:{{ $colors[1] }};font-size:10px;font-weight:800;">{{ $pay->status }}</span>
                                        </td>
                                        <td style="padding:10px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
                                            @if ($pay->status === 'APPROVED')
                                                <a href="{{ route('owner.payroll.slip', $pay->id) }}" target="_blank" class="btn" style="font-size:11px;padding:4px 8px;border-radius:4px;border:1px solid var(--coffee);color:var(--coffee);">📄 Slip</a>
                                            @endif
                                            @if ($pay->status === 'PENDING' && $authUser['role'] === 'owner')
                                                <form action="{{ route('owner.payroll.status', $pay->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="APPROVED">
                                                    <button type="submit" class="btn btn-gold" style="font-size:11px;padding:4px 8px;border:none;">✓ Setujui</button>
                                                </form>
                                                <form action="{{ route('owner.payroll.status', $pay->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="REJECTED">
                                                    <button type="submit" class="btn" style="font-size:11px;padding:4px 8px;background:rgba(239,68,68,0.1);color:#ef4444;border:none;">✕ Tolak</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="muted" style="padding:40px;text-align:center;">Belum ada data payroll. Silakan generate payroll terlebih dahulu.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Bonus & Deduction Proposals (2-column) -->
                <div class="split" style="grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px;">
                    <!-- Bonus Proposals -->
                    <div class="panel">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3>🏆 Pengajuan Bonus</h3>
                            <button onclick="document.getElementById('add-bonus-modal').style.display='flex'" class="btn btn-gold" style="font-size:11px;padding:4px 10px;">+ Ajukan Bonus</button>
                        </div>
                        @forelse ($bonuses as $bon)
                            <div style="padding:12px;background:rgba(255,255,255,0.01);border:1px solid var(--line);border-radius:6px;margin-bottom:8px;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                    <div>
                                        <div style="font-weight:700;font-size:13px;">{{ $bon->employee ? $bon->employee->name : 'N/A' }}</div>
                                        <div class="muted" style="font-size:11px;">{{ $bon->bonus_type }} · {{ $bon->reason }}</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:800;color:#10b981;font-size:13px;">+ Rp {{ number_format($bon->amount,0,',','.') }}</div>
                                        @php $bc = $bon->status === 'APPROVED' ? ['rgba(16,185,129,0.1)','#10b981'] : ($bon->status === 'REJECTED' ? ['rgba(239,68,68,0.1)','#ef4444'] : ['rgba(245,158,11,0.1)','#f59e0b']); @endphp
                                        <span class="pill" style="background:{{ $bc[0] }};color:{{ $bc[1] }};font-size:9px;font-weight:800;">{{ $bon->status }}</span>
                                    </div>
                                </div>
                                @if ($bon->status === 'PENDING' && $authUser['role'] === 'owner')
                                    <div style="display:flex;gap:6px;margin-top:8px;">
                                        <form action="{{ route('owner.bonus.approve', $bon->id) }}" method="POST" style="margin:0;"><@csrf <input type="hidden" name="status" value="APPROVED"><button type="submit" class="btn btn-gold" style="font-size:10px;padding:3px 8px;border:none;">Setujui</button></form>
                                        <form action="{{ route('owner.bonus.approve', $bon->id) }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="status" value="REJECTED"><button type="submit" class="btn" style="font-size:10px;padding:3px 8px;background:rgba(239,68,68,0.1);color:#ef4444;border:none;">Tolak</button></form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="muted" style="text-align:center;padding:20px;font-size:13px;">Belum ada pengajuan bonus.</div>
                        @endforelse
                    </div>

                    <!-- Deduction Proposals -->
                    <div class="panel">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                            <h3>✂️ Pengajuan Potongan</h3>
                            <button onclick="document.getElementById('add-deduction-modal').style.display='flex'" class="btn" style="font-size:11px;padding:4px 10px;background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.2);">+ Ajukan Potongan</button>
                        </div>
                        @forelse ($deductions as $ded)
                            <div style="padding:12px;background:rgba(255,255,255,0.01);border:1px solid var(--line);border-radius:6px;margin-bottom:8px;">
                                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                                    <div>
                                        <div style="font-weight:700;font-size:13px;">{{ $ded->employee ? $ded->employee->name : 'N/A' }}</div>
                                        <div class="muted" style="font-size:11px;">{{ $ded->deduction_type }} · {{ $ded->reason }}</div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div style="font-weight:800;color:#ef4444;font-size:13px;">− Rp {{ number_format($ded->amount,0,',','.') }}</div>
                                        @php $dc = $ded->status === 'APPROVED' ? ['rgba(16,185,129,0.1)','#10b981'] : ($ded->status === 'REJECTED' ? ['rgba(239,68,68,0.1)','#ef4444'] : ['rgba(245,158,11,0.1)','#f59e0b']); @endphp
                                        <span class="pill" style="background:{{ $dc[0] }};color:{{ $dc[1] }};font-size:9px;font-weight:800;">{{ $ded->status }}</span>
                                    </div>
                                </div>
                                @if ($ded->status === 'PENDING' && $authUser['role'] === 'owner')
                                    <div style="display:flex;gap:6px;margin-top:8px;">
                                        <form action="{{ route('owner.deduction.approve', $ded->id) }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="status" value="APPROVED"><button type="submit" class="btn btn-gold" style="font-size:10px;padding:3px 8px;border:none;">Setujui</button></form>
                                        <form action="{{ route('owner.deduction.approve', $ded->id) }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="status" value="REJECTED"><button type="submit" class="btn" style="font-size:10px;padding:3px 8px;background:rgba(239,68,68,0.1);color:#ef4444;border:none;">Tolak</button></form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="muted" style="text-align:center;padding:20px;font-size:13px;">Belum ada pengajuan potongan.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Leave Requests Panel -->
                <div class="panel">
                    <h3>🏖️ Pengajuan Cuti Karyawan</h3>
                    <div style="overflow-x:auto;margin-top:14px;">
                        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;color:var(--ink);">
                            <thead>
                                <tr style="border-bottom:2px solid var(--line);">
                                    <th style="padding:10px;">Karyawan</th>
                                    <th style="padding:10px;">Jenis Cuti</th>
                                    <th style="padding:10px;">Tanggal Mulai</th>
                                    <th style="padding:10px;">Tanggal Selesai</th>
                                    <th style="padding:10px;">Alasan</th>
                                    <th style="padding:10px;">Status</th>
                                    @if ($authUser['role'] === 'owner')
                                        <th style="padding:10px;text-align:right;">Aksi</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leaves as $lv)
                                    @php $lc = $lv->status === 'APPROVED' ? ['rgba(16,185,129,0.1)','#10b981'] : ($lv->status === 'REJECTED' ? ['rgba(239,68,68,0.1)','#ef4444'] : ['rgba(245,158,11,0.1)','#f59e0b']); @endphp
                                    <tr style="border-bottom:1px solid var(--line);">
                                        <td style="padding:10px;font-weight:700;">{{ $lv->employee ? $lv->employee->name : 'N/A' }}</td>
                                        <td style="padding:10px;color:var(--coffee);">{{ $lv->type }}</td>
                                        <td style="padding:10px;">{{ \Carbon\Carbon::parse($lv->start_date)->translatedFormat('d M Y') }}</td>
                                        <td style="padding:10px;">{{ \Carbon\Carbon::parse($lv->end_date)->translatedFormat('d M Y') }}</td>
                                        <td style="padding:10px;">{{ Str::limit($lv->reason, 40) }}</td>
                                        <td style="padding:10px;"><span class="pill" style="background:{{ $lc[0] }};color:{{ $lc[1] }};font-size:10px;font-weight:800;">{{ $lv->status }}</span></td>
                                        @if ($authUser['role'] === 'owner')
                                            <td style="padding:10px;text-align:right;display:flex;gap:6px;justify-content:flex-end;">
                                                @if ($lv->status === 'PENDING')
                                                    <form action="{{ route('owner.leave.approve', $lv->id) }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="status" value="APPROVED"><button type="submit" class="btn btn-gold" style="font-size:10px;padding:3px 8px;border:none;">Setujui</button></form>
                                                    <form action="{{ route('owner.leave.approve', $lv->id) }}" method="POST" style="margin:0;">@csrf<input type="hidden" name="status" value="REJECTED"><button type="submit" class="btn" style="font-size:10px;padding:3px 8px;background:rgba(239,68,68,0.1);color:#ef4444;border:none;">Tolak</button></form>
                                                @else
                                                    <span class="muted" style="font-size:11px;">Diproses</span>
                                                @endif
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="muted" style="padding:20px;text-align:center;">Belum ada pengajuan cuti.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal: Generate Payroll -->
    @if (in_array($authUser['role'], ['owner','super_admin']))
    <div id="generate-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(440px,94%);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;">
            <h3>⚙️ Generate Payroll Otomatis</h3>
            <button onclick="document.getElementById('generate-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <p class="muted" style="font-size:12px;margin-top:-6px;">Sistem akan menghitung gaji seluruh karyawan aktif berdasarkan: gaji pokok + tunjangan + bonus (disetujui) − potongan absensi − potongan lainnya.</p>
            <form action="{{ route('owner.payroll.generate') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Pilih Periode Penggajian</label>
                    <select name="month" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                        @php
                            $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            $currentMonth = now()->month - 1;
                            $currentYear = now()->year;
                        @endphp
                        @foreach ($months as $i => $m)
                            <option value="{{ $m }} {{ $currentYear }}" {{ $i === $currentMonth ? 'selected' : '' }}>{{ $m }} {{ $currentYear }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);padding:12px;border-radius:6px;font-size:12px;color:#f59e0b;">
                    ⚠️ <strong>Perhatian:</strong> Payroll yang sudah berstatus APPROVED tidak akan diproses ulang. Hanya payroll PENDING atau baru yang akan dibuat.
                </div>
                <button type="submit" class="btn btn-gold" style="padding:12px;font-weight:800;border:none;cursor:pointer;border-radius:6px;margin-top:6px;">⚙️ Proses Generate Payroll</button>
            </form>
        </div>
    </div>
    @endif

    <!-- Modal: Add Bonus -->
    <div id="add-bonus-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(440px,94%);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;">
            <h3>🏆 Ajukan Bonus Karyawan</h3>
            <button onclick="document.getElementById('add-bonus-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <form action="{{ route('owner.payroll.bonus') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Karyawan</label>
                    <select name="employee_id" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                        <option value="">Pilih Karyawan</option>
                        @foreach ($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jenis Bonus</label>
                        <select name="bonus_type" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                            <option>Bonus Penjualan</option><option>Bonus Kehadiran</option><option>Bonus Kinerja</option><option>Bonus Khusus</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jumlah (Rp)</label>
                        <input type="number" name="amount" required min="0" placeholder="misal: 200000" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Alasan Bonus</label>
                    <input type="text" name="reason" required placeholder="misal: Pencapaian target penjualan bulan ini" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                </div>
                <button type="submit" class="btn btn-gold" style="padding:12px;font-weight:800;border:none;cursor:pointer;border-radius:6px;margin-top:6px;">Simpan Pengajuan Bonus</button>
            </form>
        </div>
    </div>

    <!-- Modal: Add Deduction -->
    <div id="add-deduction-modal" style="position:fixed;top:0;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);display:none;z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
        <div class="panel" style="width:min(440px,94%);padding:24px;display:flex;flex-direction:column;gap:14px;position:relative;">
            <h3>✂️ Ajukan Potongan Gaji</h3>
            <button onclick="document.getElementById('add-deduction-modal').style.display='none'" style="position:absolute;right:20px;top:20px;background:none;border:none;font-size:18px;color:var(--muted);cursor:pointer;">✕</button>
            <form action="{{ route('owner.payroll.deduction') }}" method="POST" style="display:flex;flex-direction:column;gap:14px;margin-top:10px;">
                @csrf
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Karyawan</label>
                    <select name="employee_id" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                        <option value="">Pilih Karyawan</option>
                        @foreach ($employees as $emp)<option value="{{ $emp->id }}">{{ $emp->name }}</option>@endforeach
                    </select>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jenis Potongan</label>
                        <select name="deduction_type" required style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                            <option>Alpha</option><option>Terlambat</option><option>Denda</option><option>Kasbon</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:4px;">
                        <label style="font-size:12px;font-weight:600;">Jumlah (Rp)</label>
                        <input type="number" name="amount" required min="0" placeholder="misal: 50000" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    <label style="font-size:12px;font-weight:600;">Alasan Potongan</label>
                    <input type="text" name="reason" required placeholder="misal: Denda selisih kas kecil shift malam" style="padding:10px 14px;border-radius:6px;background:var(--bg-app);border:1px solid rgba(255,255,255,0.08);color:var(--text-main);font-size:13px;">
                </div>
                <button type="submit" class="btn" style="padding:12px;font-weight:800;border:none;cursor:pointer;border-radius:6px;background:rgba(239,68,68,0.15);color:#ef4444;margin-top:6px;">Simpan Pengajuan Potongan</button>
            </form>
        </div>
    </div>
</x-layouts.app>

