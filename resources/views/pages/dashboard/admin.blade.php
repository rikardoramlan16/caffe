<x-layouts.app title="Admin Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
        $dailyChartData = $dailyChartData ?? [];
        $dailyChartValues = is_array($dailyChartData)
            ? array_values($dailyChartData)
            : (is_object($dailyChartData) && method_exists($dailyChartData, 'toArray') ? array_values($dailyChartData->toArray()) : []);
        $topProducts = $topProducts ?? collect();
        $maxVal = count($dailyChartValues) ? max(1, max($dailyChartValues)) : 1;
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>CafeFlow</span></a>
                <nav class="side-nav" aria-label="Navigasi Admin">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                    <a href="{{ route('admin.menu.index') }}">🍵 Menu Minuman</a>
                    <a href="{{ route('admin.menu.index') }}?section=barang">📦 Kelola Barang</a>
                    <a href="{{ route('admin.menu.index') }}?section=kategori">📁 Kategori</a>
                    <a href="{{ route('admin.menu.index') }}?section=topping">🍬 Topping</a>
                    <a class="{{ $section === 'meja' ? 'active' : '' }}" href="{{ route('dashboard.admin.section', 'meja') }}">🪑 Meja Cafe</a>
                    <a class="{{ $section === 'qr-meja' ? 'active' : '' }}" href="{{ route('dashboard.admin.section', 'qr-meja') }}">📱 QR Meja</a>
                    <a class="{{ $section === 'staff' ? 'active' : '' }}" href="{{ route('dashboard.admin.section', 'staff') }}">👥 Staff Outlet</a>
                    <a class="{{ $section === 'laporan' ? 'active' : '' }}" href="{{ route('dashboard.admin.section', 'laporan') }}">📈 Laporan Penjualan</a>

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>🏢</span>
                            <span>{{ $user['name'] }}</span>
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

            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">Branch Admin Workspace</span>
                        <h1>Dashboard Admin</h1>
                        <p class="muted">Operasional harian, manajemen menu & kategori, monitoring pesanan aktif, dan analisis penjualan.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        <a class="btn" href="{{ route('landing') }}">Landing Page</a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; color: #10b981; font-size: 14px;">
                        🎉 {{ session('success') }}
                    </div>
                @endif

                <!-- Widgets Utama -->
                <section id="dashboard-summary" class="grid grid-4" aria-label="Widget utama">
                    <article class="metric-card">
                        <span class="pill">Hari Ini</span>
                        <strong>{{ $metrics['orders_today'] }}</strong>
                        <span>Total Order Hari Ini</span>
                        <p class="muted">Pesanan masuk cabang</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: #10b981; color: var(--bg-app);">Pendapatan</span>
                        <strong>Rp {{ number_format($metrics['revenue_today'], 0, ',', '.') }}</strong>
                        <span>Pendapatan Hari Ini</span>
                        <p class="muted">Omset bersih cabang</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: var(--text-gold); color: var(--bg-app);">Favorit</span>
                        <strong style="font-size: 18px; word-break: break-all;">{{ $metrics['best_selling'] }}</strong>
                        <span>Menu Terlaris</span>
                        <p class="muted">Paling sering dipesan</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill">Operasional</span>
                        <strong>{{ $metrics['active_orders'] }}</strong>
                        <span>Order Aktif</span>
                        <p class="muted">Antrean dalam proses</p>
                    </article>
                </section>

                <section class="split" id="laporan">
                    <!-- Grafik Penjualan Harian -->
                    <div class="panel">
                        <h3>Grafik Penjualan Harian (Seminggu Terakhir)</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 20px;">Pendapatan harian sukses di cabang ini.</p>
                        @php
                            $maxVal = count($dailyChartValues) ? max(1, max($dailyChartValues)) : 1;
                        @endphp
                        <div class="chart" style="display: flex; align-items: flex-end; justify-content: space-between; height: 180px; gap: 12px; padding-top: 20px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            @foreach ($dailyChartData as $day => $revenue)
                                @php
                                    $pct = $maxVal > 0 ? ($revenue / $maxVal) * 80 + 5 : 0;
                                @endphp
                                <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 6px; height: 100%; justify-content: flex-end;">
                                    <span class="bar" style="height: {{ $pct }}%; width: 100%; min-width: 14px; border-radius: 4px 4px 0 0; background: var(--text-gold); cursor: pointer;" title="Rp {{ number_format($revenue, 0, ',', '.') }}"></span>
                                    <span style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ substr($day, 0, 3) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Produk Terlaris -->
                    <div class="panel">
                        <h3>Breakdown Produk Terlaris</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Top menu berdasarkan total porsi terjual.</p>
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @forelse ($topProducts as $idx => $prod)
                                <div>
                                    <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; margin-bottom: 4px;">
                                        <span>{{ $prod->name }}</span>
                                        <span>{{ $prod->total_qty }} porsi <span class="muted">(Rp {{ number_format($prod->total_sales, 0, ',', '.') }})</span></span>
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

                <!-- Panel Manajemen Menu & Kategori -->
                <section class="split" id="menu" style="margin-top: 24px;">
                    <!-- Menu List & Form Tambah Menu -->
                    <div class="panel" style="flex: 2;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3>🍵 Manajemen Menu Outlet</h3>
                            <a href="#tambah-menu-modal" class="btn btn-gold" style="font-size: 12px; padding: 6px 12px;">+ Tambah Menu</a>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Nama</th>
                                        <th style="padding: 10px;">Kategori</th>
                                        <th style="padding: 10px;">Harga</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px; text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($menus as $m)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">{{ $m->name }}</td>
                                            <td style="padding: 10px; color: var(--text-gold);">{{ $m->category ? $m->category->name : 'N/A' }}</td>
                                            <td style="padding: 10px;">Rp {{ number_format($m->price, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;">
                                                <span class="pill" style="background: {{ $m->is_available ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $m->is_available ? '#10b981' : '#ef4444' }}; font-size: 10px;">
                                                    {{ $m->is_available ? 'Tersedia' : 'Habis' }}
                                                </span>
                                            </td>
                                            <td style="padding: 10px; text-align: right;">
                                                <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" style="margin: 0; display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Belum ada menu minuman.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kategori List & Tambah Kategori Form -->
                    <div class="panel" id="kategori">
                        <h3>📁 Kategori Menu</h3>
                        <form action="{{ route('admin.category.store') }}" method="POST" style="margin-top: 14px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">
                            @csrf
                            <label for="cat_name" style="font-size: 12px; font-weight: 600;">Tambah Kategori Baru</label>
                            <div style="display: flex; gap: 8px;">
                                <input type="text" id="cat_name" name="name" required placeholder="Nama Kategori (mis. Tea, Coffee)" style="flex: 1; padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                <button type="submit" class="btn btn-gold" style="font-size: 12px; padding: 8px 14px;">Simpan</button>
                            </div>
                        </form>

                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach ($categories as $cat)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(255,255,255,0.02); border-radius: 6px; font-size: 13px;">
                                    <strong>📁 {{ $cat->name }}</strong>
                                    <span class="muted" style="font-size: 11px;">{{ $cat->menus_count ?? 0 }} item</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <!-- Panel Tambah Menu Modal Preview -->
                <section class="panel" id="tambah-menu-modal" style="margin-top: 24px; max-width: 600px;">
                    <h3>📝 Tambah Menu Baru</h3>
                    <form action="{{ route('admin.menu.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label for="menu_name" style="font-size: 12px; font-weight: 600;">Nama Menu</label>
                            <input type="text" id="menu_name" name="name" required placeholder="mis. Iced Taro Latte" style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <label for="menu_category" style="font-size: 12px; font-weight: 600;">Kategori</label>
                                <select id="menu_category" name="category_id" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                    <option value="">Pilih Kategori</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <label for="menu_price" style="font-size: 12px; font-weight: 600;">Harga (Rupiah)</label>
                                <input type="number" id="menu_price" name="price" required placeholder="mis. 32000" style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            </div>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <label for="menu_desc" style="font-size: 12px; font-weight: 600;">Deskripsi</label>
                            <textarea id="menu_desc" name="description" placeholder="Deskripsi komposisi minuman..." style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 80px; outline: none; resize: none;"></textarea>
                        </div>

                        <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px;">Simpan Menu Baru</button>
                    </form>
                </section>

                <section class="split" id="meja" style="margin-top: 24px;">
                    @if ($section === 'qr-meja')
                        <div class="panel" id="qr-panel">
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; margin-bottom: 14px;">
                                <div>
                                    <h3>QR Code Meja</h3>
                                    <p class="muted" style="font-size: 12px; margin-top: -6px;">Pelanggan memindai QR Code ini untuk membuka menu dan sistem otomatis mengenali nomor meja.</p>
                                </div>
                                <button class="btn btn-gold no-print" type="button" onclick="window.print()" style="border: none; padding: 10px 14px; font-weight: 800; border-radius: 6px;">Cetak</button>
                            </div>
                            <div class="qr-sheet" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(230px, 1fr)); gap: 12px;">
                                @foreach ($tables as $t)
                                    <article class="qr-card" style="background: #fff; color: #111; border: 1px solid #e5e7eb; border-radius: 8px; padding: 14px; text-align: center; break-inside: avoid;">
                                        <div style="font-size: 11px; font-weight: 800; color: #6b7280; text-transform: uppercase;">CafeFlow Order</div>
                                        <div style="font-size: 24px; font-weight: 900; margin: 4px 0 10px;">Meja {{ $t->code }}</div>
                                        <img src="{{ route('admin.tables.qr-code', $t) }}" alt="QR Code meja {{ $t->code }}" style="width: 100%; max-width: 220px; height: 220px; object-fit: contain; display: block; margin: 0 auto;">
                                        <div style="font-size: 11px; margin-top: 8px; color: #374151; word-break: break-all;">{{ route('qr.login', $t->code) }}</div>
                                        <div style="font-size: 11px; margin-top: 6px; color: #6b7280;">Kapasitas {{ $t->capacity }} pax</div>
                                    </article>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Meja & QR Meja -->
                    <div class="panel" @if ($section === 'qr-meja') style="display: none;" @endif>
                        <h3>🪑 Manajemen Meja Cafe</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 12px;">Daftar meja aktif di cabang ini.</p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 10px;">
                            @foreach ($tables as $t)
                                <div style="padding: 10px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.04); border-radius: 8px; text-align: center;">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 700;">MEJA</div>
                                    <div style="font-size: 20px; font-weight: 800; color: var(--text-gold); margin: 2px 0;">{{ $t->code }}</div>
                                    <div style="font-size: 10px; color: var(--text-main); background: rgba(255,255,255,0.05); padding: 2px; border-radius: 4px; display: inline-block;">Kap. {{ $t->capacity }} pax</div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Staff List -->
                    <div class="panel" id="staff" @if ($section === 'qr-meja') style="display: none;" @endif>
                        <h3>👥 Staff & Karyawan Outlet</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 12px;">Anggota tim kerja cabang.</p>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @foreach ($staff as $st)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.02); border-radius: 6px;">
                                    <div>
                                        <strong>{{ $st->name }}</strong>
                                        <div class="muted" style="font-size: 11px;">{{ $st->email }}</div>
                                    </div>
                                    <span class="pill" style="font-size: 11px; background: var(--text-gold); color: var(--bg-app); font-weight: 700;">{{ str($st->role)->replace('_', ' ')->title() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <style>
        @media print {
            body { background: #fff !important; }
            .sidebar,
            .page-head,
            .no-print,
            main.content > section:not(#meja),
            #meja > .panel:not(#qr-panel) { display: none !important; }
            .app-shell,
            .app-layout,
            main.content,
            #meja,
            #qr-panel { display: block !important; width: 100% !important; padding: 0 !important; margin: 0 !important; background: #fff !important; border: 0 !important; box-shadow: none !important; }
            .qr-sheet { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 10mm !important; }
            .qr-card { border: 1px solid #111 !important; page-break-inside: avoid; }
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = @json($section);
            const sections = {
                dashboard: ['dashboard-summary', 'laporan'],
                laporan: ['laporan'],
                meja: ['meja'],
                'qr-meja': ['meja'],
                staff: ['meja'],
            };

            if (section === 'dashboard') {
                document.querySelectorAll('#menu, #tambah-menu-modal, #meja').forEach((el) => el.style.display = 'none');
                return;
            }

            document.querySelectorAll('main.content > section').forEach((el) => el.style.display = 'none');
            (sections[section] || ['dashboard-summary', 'laporan']).forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.style.display = '';
            });
        });
    </script>
</x-layouts.app>
