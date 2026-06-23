<x-layouts.app title="Slip Gaji Saya - CafeFlow">
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
                    <a href="{{ route('profil') }}">👤 Profil Saya</a>
                    <a href="{{ route('staff.attendance') }}">📅 Absensi Saya</a>
                    <a class="active" href="{{ route('staff.payroll') }}">💵 Slip Gaji</a>
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
                        <h1>Slip Gaji Saya</h1>
                        <p class="muted">Lihat histori pembayaran gaji dan unduh slip gaji bulanan Anda.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                    </div>
                </div>

                <!-- Payroll History -->
                <div class="panel">
                    <h3>💵 Histori Payroll</h3>
                    <p class="muted" style="font-size:12px;margin-top:-6px;margin-bottom:16px;">Hanya slip gaji yang sudah disetujui (Approved) yang akan tampil di sini.</p>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:13px;color:var(--ink);">
                            <thead>
                                <tr style="border-bottom:2px solid var(--line);">
                                    <th style="padding:12px 10px;">Periode</th>
                                    <th style="padding:12px 10px;">Gaji Pokok</th>
                                    <th style="padding:12px 10px;">+ Bonus</th>
                                    <th style="padding:12px 10px;">− Potongan</th>
                                    <th style="padding:12px 10px;">Take Home Pay</th>
                                    <th style="padding:12px 10px;text-align:right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($payrolls as $pay)
                                    <tr style="border-bottom:1px solid var(--line);">
                                        <td style="padding:10px;font-weight:700;">{{ $pay->month }}</td>
                                        <td style="padding:10px;">Rp {{ number_format($pay->basic_salary,0,',','.') }}</td>
                                        <td style="padding:10px;color:#10b981;">+ Rp {{ number_format($pay->bonus + $pay->allowance,0,',','.') }}</td>
                                        <td style="padding:10px;color:#ef4444;">− Rp {{ number_format($pay->deduction,0,',','.') }}</td>
                                        <td style="padding:10px;font-weight:800;color:var(--coffee);font-size:14px;">Rp {{ number_format($pay->total_salary,0,',','.') }}</td>
                                        <td style="padding:10px;text-align:right;">
                                            <a href="{{ route('staff.slip', $pay->id) }}" target="_blank" class="btn btn-gold" style="font-size:11px;padding:6px 12px;border-radius:6px;display:inline-flex;align-items:center;gap:6px;">
                                                📄 Unduh Slip
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="muted" style="padding:40px;text-align:center;">Belum ada slip gaji yang disetujui.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-layouts.app>
