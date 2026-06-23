<x-layouts.app title="Owner Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
        $monthlyChartData = $monthlyChartData ?? [];
        $monthlyChartValues = is_array($monthlyChartData)
            ? array_values($monthlyChartData)
            : (is_object($monthlyChartData) && method_exists($monthlyChartData, 'toArray') ? array_values($monthlyChartData->toArray()) : []);
        $purchaseChartData = $purchaseChartData ?? [];
        $purchaseChartValues = is_array($purchaseChartData)
            ? array_values($purchaseChartData)
            : (is_object($purchaseChartData) && method_exists($purchaseChartData, 'toArray') ? array_values($purchaseChartData->toArray()) : []);
        $topProducts = $topProducts ?? collect();
        $stockUsage = $stockUsage ?? collect();
        $maxVal = count($monthlyChartValues) ? max(1, max($monthlyChartValues)) : 1;
        $maxPO = count($purchaseChartValues) ? max(1, max($purchaseChartValues)) : 1;
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Owner">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.owner') }}">Dashboard</a>
                    <a class="{{ $section === 'penjualan' ? 'active' : '' }}" href="{{ route('dashboard.owner.section', 'penjualan') }}">Penjualan</a>
                    <a class="{{ $section === 'keuangan' ? 'active' : '' }}" href="{{ route('dashboard.owner.section', 'keuangan') }}">Keuangan</a>
                    <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                    <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                    <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                    <a class="{{ $section === 'analitik' ? 'active' : '' }}" href="{{ route('dashboard.owner.section', 'analitik') }}">Analitik</a>
                    <a href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    <a class="{{ $section === 'approval' ? 'active' : '' }}" href="{{ route('dashboard.owner.section', 'approval') }}">Approval
                        @php
                            $pendingApprovalsCount = $payrolls->where('status', 'PENDING')->count() +
                                                     $expenses->where('status', 'PENDING')->count() +
                                                     $bonuses->where('status', 'PENDING')->count() +
                                                     $deletions->where('status', 'PENDING')->count();
                        @endphp
                        @if ($pendingApprovalsCount > 0)
                            <span style="background: #ef4444; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: 800; margin-left: 4px;">{{ $pendingApprovalsCount }}</span>
                        @endif
                    </a>
                    
                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>👑</span>
                            <span>{{ $user['name'] }} (Owner)</span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; text-align: left; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 10px 14px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; transition: background 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.2)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.1)'">
                                🚪 Keluar Staf
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <!-- Main Content Container -->
            <main class="content">
                <!-- Page Head -->
                <div class="page-head">
                    <div>
                        <span class="eyebrow">Owner Workspace</span>
                        <h1 id="page-title">Dashboard Utama</h1>
                        <p class="muted" id="page-subtitle">Monitoring performa bisnis, pengeluaran keuangan, inventory, dan persetujuan kebijakan.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        <a class="btn" href="{{ route('landing') }}">Landing Page</a>
                    </div>
                </div>

                <!-- Success Message -->
                @if (session('success'))
                    <div class="panel" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; color: #10b981; font-size: 14px;">
                        🎉 {{ session('success') }}
                    </div>
                @endif

                <!-- Low Stock Alert Banner -->
                @if($lowStockWarnings->count() > 0)
                    <div class="panel" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.2); padding: 14px 18px; border-radius: 8px; margin-bottom: 24px;">
                        <strong style="color: #f59e0b; display: flex; align-items: center; gap: 8px; font-size: 14px;">
                            <span>⚠</span> PERINGATAN INVENTORY: Terdapat {{ $lowStockWarnings->count() }} barang dengan stok menipis/habis!
                        </strong>
                        <div style="display: flex; flex-direction: column; gap: 4px; margin-top: 8px; font-size: 12px; color: var(--text-main);">
                            @foreach($lowStockWarnings as $warn)
                                <div>• <strong>{{ $warn->name }}</strong> tersisa <span style="color: #ef4444; font-weight: 700;">{{ $warn->current_stock }} {{ $warn->unit }}</span> (Batas Minimum: {{ $warn->min_stock }} {{ $warn->unit }}). Segera lakukan pembelian ulang ke supplier.</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- ============================================== -->
                <!-- TAB 1: DASHBOARD OVERVIEW -->
                <!-- ============================================== -->
                <div id="tab-dashboard" class="tab-content" style="{{ $section !== 'dashboard' ? 'display: none;' : '' }}">
                    <!-- Metrics Section -->
                    <section class="grid grid-4" style="margin-bottom: 24px;" aria-label="Widget utama">
                        <article class="metric-card">
                            <span class="pill" style="background: #10b981; color: var(--bg-app);">Keuangan</span>
                            <strong>Rp {{ number_format($metrics['revenue_today'], 0, ',', '.') }}</strong>
                            <span>Omzet Hari Ini</span>
                            <p class="muted">Seluruh outlet kafe</p>
                        </article>
                        
                        <article class="metric-card">
                            <span class="pill" style="background: #10b981; color: var(--bg-app);">Keuangan</span>
                            <strong>Rp {{ number_format($metrics['revenue_month'], 0, ',', '.') }}</strong>
                            <span>Omzet Bulan Ini</span>
                            <p class="muted">Akumulasi berjalan</p>
                        </article>

                        <article class="metric-card">
                            <span class="pill" style="background: var(--text-gold); color: var(--bg-app);">Keuangan</span>
                            <strong>Rp {{ number_format($metrics['net_profit'], 0, ',', '.') }}</strong>
                            <span>Laba Bersih</span>
                            <p class="muted">Setelah gaji & operasional</p>
                        </article>

                        <article class="metric-card">
                            <span class="pill">Penjualan</span>
                            <strong>{{ $metrics['total_orders'] }}</strong>
                            <span>Total Pesanan</span>
                            <p class="muted">Transaksi sukses</p>
                        </article>

                        <article class="metric-card">
                            <span class="pill">Staf</span>
                            <strong>{{ $metrics['total_employees'] }}</strong>
                            <span>Karyawan</span>
                            <p class="muted">Staf terdaftar</p>
                        </article>

                        <article class="metric-card">
                            <span class="pill" style="background: var(--text-gold); color: var(--bg-app);">Menu</span>
                            <strong style="font-size: 15px; word-break: break-all;">{{ $metrics['best_selling'] }}</strong>
                            <span>Produk Terlaris</span>
                            <p class="muted">Menu paling digemari</p>
                        </article>

                        <article class="metric-card">
                            <span class="pill" style="background: #ef4444; color: white;">Inventory</span>
                            <strong>{{ $metrics['low_stock'] }} / {{ $metrics['out_of_stock'] }}</strong>
                            <span>Stok Menipis / Habis</span>
                            <p class="muted">Perlu restock segera</p>
                        </article>
                    </section>

                    <!-- Split Section Charts -->
                    <section class="split">
                        <!-- Revenue Chart -->
                        <div class="panel">
                            <h3>Grafik Penjualan Bulanan (2026)</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 20px;">Akumulasi pendapatan sukses per bulan.</p>
                            @php
                                $maxVal = count($monthlyChartValues) ? max(1, max($monthlyChartValues)) : 1;
                            @endphp
                            <div class="chart" style="display: flex; align-items: flex-end; justify-content: space-between; height: 200px; gap: 8px; padding-top: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                @foreach ($monthlyChartData as $month => $revenue)
                                    @php
                                        $pct = $maxVal > 0 ? ($revenue / $maxVal) * 85 + 5 : 0;
                                    @endphp
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end;">
                                        <span class="bar" style="height: {{ $pct }}%; width: 100%; min-width: 14px; border-radius: 4px 4px 0 0; background: var(--text-gold); cursor: pointer;" title="Rp {{ number_format($revenue, 0, ',', '.') }}"></span>
                                        <span style="font-size: 10px; color: var(--text-muted); font-weight: 600;">{{ $month }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Top Selling Products list -->
                        <div class="panel">
                            <h3>Breakdown Produk Terlaris</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Top menu berdasarkan total porsi terjual.</p>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @forelse ($topProducts as $prod)
                                    <div>
                                        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 4px;">
                                            <span>☕ {{ $prod->name }}</span>
                                            <span>{{ $prod->total_qty }} porsi</span>
                                        </div>
                                        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                                            @php
                                                $topProductQty = max(1, (int) ($topProducts->first()->total_qty ?? 0));
                                            @endphp
                                            <div style="height: 100%; background: var(--text-gold); border-radius: 3px; width: {{ $topProductQty > 0 ? min(100, ($prod->total_qty / $topProductQty) * 100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada data penjualan produk.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ============================================== -->
                <!-- TAB 2: PENJUALAN REPORT -->
                <!-- ============================================== -->
                <div id="tab-penjualan" class="tab-content" style="{{ $section !== 'penjualan' ? 'display: none;' : '' }}">
                    <div class="panel">
                        <h3>Daftar Riwayat Transaksi Penjualan (Read-Only)</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Monitoring realtime seluruh pesanan pelanggan masuk.</p>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Invoice</th>
                                        <th style="padding: 10px;">Meja</th>
                                        <th style="padding: 10px;">Subtotal</th>
                                        <th style="padding: 10px;">Total Bayar</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px;">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($orders as $ord)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700; color: var(--text-gold);">{{ $ord->invoice_number }}</td>
                                            <td style="padding: 10px;">📍 {{ $ord->table ? $ord->table->code : 'Meja' }}</td>
                                            <td style="padding: 10px;">Rp {{ number_format($ord->subtotal, 0, ',', '.') }}</td>
                                            <td style="padding: 10px; font-weight: 700;">Rp {{ number_format($ord->total, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;">
                                                <span class="pill" style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                                                    {{ $ord->status }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px;">{{ $ord->created_at->format('d M H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="muted" style="padding: 20px; text-align: center;">Belum ada pesanan masuk.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 3: KEUANGAN REPORT -->
                <!-- ============================================== -->
                <div id="tab-keuangan" class="tab-content" style="{{ $section !== 'keuangan' ? 'display: none;' : '' }}">
                    <section class="grid grid-3" style="margin-bottom: 24px;">
                        <article class="metric-card">
                            <span class="pill" style="background: #10b981; color: var(--bg-app);">Bulan Ini</span>
                            <strong>Rp {{ number_format($metrics['revenue_month'], 0, ',', '.') }}</strong>
                            <span>Total Omset Bulanan</span>
                        </article>
                        <article class="metric-card">
                            <span class="pill" style="background: #ef4444; color: white;">Bulan Ini</span>
                            <strong>Rp {{ number_format($approvedExpensesThisMonth, 0, ',', '.') }}</strong>
                            <span>Total Biaya Operasional</span>
                        </article>
                        <article class="metric-card">
                            <span class="pill" style="background: var(--text-gold); color: var(--bg-app);">Laba</span>
                            <strong>Rp {{ number_format($metrics['net_profit'], 0, ',', '.') }}</strong>
                            <span>Laba Bersih Bersih</span>
                        </article>
                    </section>

                    <div class="panel">
                        <h3>Laporan Biaya Pengeluaran Cabang Disetujui</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Catatan keuangan biaya pengeluaran operasional cabang.</p>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Cabang</th>
                                        <th style="padding: 10px;">Judul Pengeluaran</th>
                                        <th style="padding: 10px;">Kategori</th>
                                        <th style="padding: 10px;">Jumlah Biaya</th>
                                        <th style="padding: 10px;">Tanggal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($expenses->where('status', 'APPROVED') as $exp)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">🏢 {{ $exp->branch ? $exp->branch->name : 'Global' }}</td>
                                            <td style="padding: 10px;">{{ $exp->title }} <p class="muted" style="font-size: 11px; margin: 2px 0 0 0;">{{ $exp->description }}</p></td>
                                            <td style="padding: 10px; color: var(--text-gold);">{{ $exp->category }}</td>
                                            <td style="padding: 10px; font-weight:700;">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;">{{ $exp->created_at->format('d M Y') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Belum ada pengeluaran disetujui.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 4: PENGGAJIAN & SLIP -->
                <!-- ============================================== -->
                <div id="tab-penggajian" class="tab-content" style="{{ $section !== 'penggajian' ? 'display: none;' : '' }}">
                    <div class="panel">
                        <h3>Daftar Payroll Karyawan</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Siklus penggajian bulanan staf cabang outlet.</p>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Karyawan</th>
                                        <th style="padding: 10px;">Bulan</th>
                                        <th style="padding: 10px;">Gaji Pokok</th>
                                        <th style="padding: 10px;">Tunjangan / Bonus</th>
                                        <th style="padding: 10px;">Potongan</th>
                                        <th style="padding: 10px;">Total Bersih</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px; text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payrolls as $pay)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">
                                                👤 {{ $pay->user ? $pay->user->name : 'N/A' }}
                                                <div class="muted" style="font-size: 11px;">Role: {{ $pay->user ? str($pay->user->role)->title() : 'Staff' }}</div>
                                            </td>
                                            <td style="padding: 10px;">{{ $pay->month }}</td>
                                            <td style="padding: 10px;">Rp {{ number_format($pay->basic_salary, 0, ',', '.') }}</td>
                                            <td style="padding: 10px; color: #10b981;">+ Rp {{ number_format($pay->allowance + $pay->bonus, 0, ',', '.') }}</td>
                                            <td style="padding: 10px; color: #ef4444;">- Rp {{ number_format($pay->deduction, 0, ',', '.') }}</td>
                                            <td style="padding: 10px; font-weight: 700; color: var(--text-gold);">Rp {{ number_format($pay->total_salary, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;">
                                                <span class="pill" style="font-size: 10px; background: {{ $pay->status === 'APPROVED' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $pay->status === 'APPROVED' ? '#10b981' : '#ef4444' }};">
                                                    {{ $pay->status }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px; text-align: right;">
                                                <a href="{{ route('owner.payroll.slip', $pay->id) }}" target="_blank" class="btn" style="font-size: 11px; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--text-gold); color: var(--text-gold);">
                                                    📄 Cetak Slip
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="muted" style="padding: 20px; text-align: center;">Belum ada data payroll.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- TAB 6: ANALITIK CHARTS -->
                <!-- ============================================== -->
                <div id="tab-analitik" class="tab-content" style="{{ $section !== 'analitik' ? 'display: none;' : '' }}">
                    <section class="grid grid-2" style="margin-bottom: 24px;">
                        <div class="panel">
                            <h3>Grafik Pemakaian Stok</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Top barang baku terpakai produksi.</p>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                @forelse($stockUsage as $usage)
                                    <div>
                                        <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
                                            <span>{{ $usage->name }}</span>
                                            <span>{{ number_format($usage->total_usage, 0, ',', '.') }} unit</span>
                                        </div>
                                        <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.05); border-radius: 3px; overflow: hidden;">
                                            @php
                                                $topStockUsage = max(1, (float) ($stockUsage->first()->total_usage ?? 0));
                                            @endphp
                                            <div style="height: 100%; background: #3b82f6; border-radius: 3px; width: {{ $topStockUsage > 0 ? min(100, ($usage->total_usage / $topStockUsage) * 100) : 0 }}%;"></div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada pemakaian stok.</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="panel">
                            <h3>Grafik Pengeluaran PO (2026)</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Total biaya pembelian PO sukses ke supplier.</p>
                            @php
                                $maxPO = count($purchaseChartValues) ? max(1, max($purchaseChartValues)) : 1;
                            @endphp
                            <div class="chart" style="display: flex; align-items: flex-end; justify-content: space-between; height: 130px; gap: 6px; padding-top: 10px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                                @foreach ($purchaseChartData as $month => $amount)
                                    @php
                                        $pct = $maxPO > 0 ? ($amount / $maxPO) * 80 + 5 : 0;
                                    @endphp
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 4px; height: 100%; justify-content: flex-end;">
                                        <span class="bar" style="height: {{ $pct }}%; width: 100%; background: #ef4444; border-radius: 2px 2px 0 0;" title="Rp {{ number_format($amount, 0, ',', '.') }}"></span>
                                        <span style="font-size: 9px; color: var(--text-muted);">{{ substr($month, 0, 3) }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
                <!-- ============================================== -->
                <!-- TAB 8: CONSOLIDATED APPROVALS -->
                <!-- ============================================== -->
                <div id="tab-approval" class="tab-content" style="{{ $section !== 'approval' ? 'display: none;' : '' }}">
                    
                    <!-- 1. Payroll Approval Section -->
                    <div class="panel" style="margin-bottom: 24px;">
                        <h3 style="display: flex; align-items: center; justify-content: space-between;">
                            <span>💵 Persetujuan Payroll Karyawan</span>
                            <span class="pill" style="font-size: 11px;">{{ $payrolls->where('status', 'PENDING')->count() }} Butuh Tindakan</span>
                        </h3>
                        <div style="overflow-x: auto; margin-top: 14px;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Staf</th>
                                        <th style="padding: 10px;">Bulan</th>
                                        <th style="padding: 10px;">Gaji Bersih</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px; text-align: right;">Persetujuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payrolls->where('status', 'PENDING') as $pay)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">👤 {{ $pay->user ? $pay->user->name : 'Staff' }} <span class="muted" style="font-size: 11px; font-weight:500;">({{ $pay->user ? str($pay->user->role)->title() : 'Staff' }})</span></td>
                                            <td style="padding: 10px;">{{ $pay->month }}</td>
                                            <td style="padding: 10px; font-weight: 700; color: var(--text-gold);">Rp {{ number_format($pay->total_salary, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"><span class="pill" style="font-size: 9px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">{{ $pay->status }}</span></td>
                                            <td style="padding: 10px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                                <form action="{{ route('owner.payroll.status', $pay->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="APPROVED">
                                                    <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; min-height:30px; border:none;">Approve</button>
                                                </form>
                                                <form action="{{ route('owner.payroll.status', $pay->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="REJECTED">
                                                    <button type="submit" class="btn" style="font-size: 11px; padding: 4px 10px; min-height:30px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border:none;">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Tidak ada pengajuan payroll baru.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. Expense Approval Section -->
                    <div class="panel" style="margin-bottom: 24px;">
                        <h3 style="display: flex; align-items: center; justify-content: space-between;">
                            <span>📈 Persetujuan Pengeluaran Operasional</span>
                            <span class="pill" style="font-size: 11px;">{{ $expenses->where('status', 'PENDING')->count() }} Butuh Tindakan</span>
                        </h3>
                        <div style="overflow-x: auto; margin-top: 14px;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Cabang</th>
                                        <th style="padding: 10px;">Judul</th>
                                        <th style="padding: 10px;">Jumlah</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px; text-align: right;">Persetujuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($expenses->where('status', 'PENDING') as $exp)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">🏢 {{ $exp->branch ? $exp->branch->name : 'Global' }}</td>
                                            <td style="padding: 10px;"><strong>{{ $exp->title }}</strong> <p class="muted" style="font-size: 11px; margin: 2px 0 0 0;">{{ $exp->description }}</p></td>
                                            <td style="padding: 10px; font-weight: 700; color: var(--text-gold);">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"><span class="pill" style="font-size: 9px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">{{ $exp->status }}</span></td>
                                            <td style="padding: 10px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                                <form action="{{ route('owner.expense.status', $exp->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="APPROVED">
                                                    <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; min-height:30px; border:none;">Approve</button>
                                                </form>
                                                <form action="{{ route('owner.expense.status', $exp->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="REJECTED">
                                                    <button type="submit" class="btn" style="font-size: 11px; padding: 4px 10px; min-height:30px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border:none;">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Tidak ada pengajuan biaya operasional baru.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. Bonus Approval Section -->
                    <div class="panel" style="margin-bottom: 24px;">
                        <h3 style="display: flex; align-items: center; justify-content: space-between;">
                            <span>🏆 Persetujuan Bonus Karyawan</span>
                            <span class="pill" style="font-size: 11px;">{{ $bonuses->where('status', 'PENDING')->count() }} Butuh Tindakan</span>
                        </h3>
                        <div style="overflow-x: auto; margin-top: 14px;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Karyawan</th>
                                        <th style="padding: 10px;">Alasan</th>
                                        <th style="padding: 10px;">Jumlah Bonus</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px; text-align: right;">Persetujuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bonuses->where('status', 'PENDING') as $bon)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">👤 {{ $bon->user ? $bon->user->name : 'Staff' }} <span class="muted" style="font-size: 11px; font-weight:500;">({{ $bon->user ? str($bon->user->role)->title() : 'Staff' }})</span></td>
                                            <td style="padding: 10px;">{{ $bon->reason }}</td>
                                            <td style="padding: 10px; font-weight: 700; color: #10b981;">+ Rp {{ number_format($bon->amount, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;"><span class="pill" style="font-size: 9px; background: rgba(245, 158, 11, 0.1); color: #f59e0b;">{{ $bon->status }}</span></td>
                                            <td style="padding: 10px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                                <form action="{{ route('owner.bonus.approve', $bon->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="APPROVED">
                                                    <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; min-height:30px; border:none;">Approve</button>
                                                </form>
                                                <form action="{{ route('owner.bonus.approve', $bon->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="REJECTED">
                                                    <button type="submit" class="btn" style="font-size: 11px; padding: 4px 10px; min-height:30px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border:none;">Reject</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Tidak ada pengajuan bonus karyawan baru.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 4. Deletion Approval Section -->
                    <div class="panel">
                        <h3 style="display: flex; align-items: center; justify-content: space-between;">
                            <span>🚨 Persetujuan Penghapusan Data Penting</span>
                            <span class="pill" style="font-size: 11px; background: rgba(239, 68, 68, 0.1); color: #ef4444;">{{ $deletions->where('status', 'PENDING')->count() }} Butuh Tindakan</span>
                        </h3>
                        <div style="overflow-x: auto; margin-top: 14px;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Tabel Data</th>
                                        <th style="padding: 10px;">ID Record</th>
                                        <th style="padding: 10px;">Ringkasan Data</th>
                                        <th style="padding: 10px;">Pemohon</th>
                                        <th style="padding: 10px;">Alasan</th>
                                        <th style="padding: 10px; text-align: right;">Persetujuan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($deletions->where('status', 'PENDING') as $del)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700; color: #ef4444;">{{ strtoupper($del->table_name) }}</td>
                                            <td style="padding: 10px;">#{{ $del->record_id }}</td>
                                            <td style="padding: 10px; font-family: monospace; font-size: 11px;">{{ $del->data_summary }}</td>
                                            <td style="padding: 10px; font-weight: 600;">👤 {{ $del->requester ? $del->requester->name : 'Staff' }}</td>
                                            <td style="padding: 10px;">{{ $del->reason }}</td>
                                            <td style="padding: 10px; text-align: right; display: flex; gap: 8px; justify-content: flex-end;">
                                                <form action="{{ route('owner.deletion.status', $del->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="APPROVED">
                                                    <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; min-height:30px; border:none; background: #ef4444; color: white;">Approve Hapus</button>
                                                </form>
                                                <form action="{{ route('owner.deletion.status', $del->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <input type="hidden" name="status" value="REJECTED">
                                                    <button type="submit" class="btn" style="font-size: 11px; padding: 4px 10px; min-height:30px; border: 1px solid var(--line);">Tolak</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="muted" style="padding: 20px; text-align: center;">Tidak ada pengajuan penghapusan data penting.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Client-side Tab Switcher -->
    <script>
        function switchTab(tabId, element) {
            // Hide all tab content
            const contents = document.getElementsByClassName('tab-content');
            for(let content of contents) {
                content.style.display = 'none';
            }

            // Remove active class from nav links
            const navLinks = document.querySelector('.side-nav').getElementsByTagName('a');
            for(let link of navLinks) {
                link.classList.remove('active');
            }

            // Show target tab content
            document.getElementById(`tab-${tabId}`).style.display = 'block';

            // Add active class to clicked link
            element.classList.add('active');

            // Update page headers dynamically
            const title = document.getElementById('page-title');
            const subtitle = document.getElementById('page-subtitle');
            
            if (tabId === 'dashboard') {
                title.innerText = 'Dashboard Utama';
                subtitle.innerText = 'Monitoring performa bisnis, pengeluaran keuangan, inventory, dan persetujuan kebijakan.';
            } else if (tabId === 'penjualan') {
                title.innerText = 'Pemantauan Penjualan';
                subtitle.innerText = 'Daftar riwayat transaksi pesanan masuk kafe secara realtime.';
            } else if (tabId === 'keuangan') {
                title.innerText = 'Pemantauan Keuangan';
                subtitle.innerText = 'Analisis kas masuk, kas keluar operasional, laba bersih kafe.';
            } else if (tabId === 'penggajian') {
                title.innerText = 'Gaji Karyawan';
                subtitle.innerText = 'Daftar riwayat payroll gaji bulanan staf dan cetak slip gaji.';} else if (tabId === 'analitik') {
                title.innerText = 'Analitik Stok & PO';
                subtitle.innerText = 'Grafik analitik penggunaan bahan baku dan pengeluaran pembelian.';
            } else if (tabId === 'stok') {
                title.innerText = 'Stok Bahan Baku Gudang';
                subtitle.innerText = 'Pemantauan persediaan stok bahan baku dan status warning restock.';
            } else if (tabId === 'approval') {
                title.innerText = 'Pusat Persetujuan Kebijakan';
                subtitle.innerText = 'Pemberian otorisasi/approval atas payroll, pengeluaran operasional, bonus staf, dan penghapusan data.';
            }
        }
    </script>
</x-layouts.app>



