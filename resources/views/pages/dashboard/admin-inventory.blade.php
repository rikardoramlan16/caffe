<x-layouts.app title="Inventory & Supplier - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Sidebar">
                    @if ($user['role'] === 'owner')
                        <a href="{{ route('dashboard.owner') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                        <a class="active" href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    @elseif ($user['role'] === 'super_admin')
                        <a href="{{ route('dashboard.super-admin') }}">📊 Dashboard</a>
                        <a href="{{ route('owner.employees') }}">👤 Kelola Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Kelola Absensi</a>
                        <a class="active" href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    @elseif ($user['role'] === 'barista')
                        <a href="{{ route('dashboard.barista') }}">📊 Dashboard</a>
                        <a href="{{ route('dashboard.barista.section', 'queue-paid') }}">📥 Queue Pesanan</a>
                        <a class="active" href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    @else
                        <a href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                        <a href="{{ route('admin.menu.index') }}">🍵 Menu Minuman</a>
                        <a href="{{ route('admin.menu.index') }}?section=barang">📦 Kelola Barang</a>
                        <a class="active" href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    @endif
                    
                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>🏢</span>
                            <span>{{ $user['name'] }} ({{ ucfirst($user['role']) }})</span>
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
                        <span class="eyebrow">{{ ucfirst($user['role']) }} Workspace</span>
                        <h1 id="page-title">Gudang & Supplier</h1>
                        <p class="muted" id="page-subtitle">Kontrol persediaan bahan baku gudang, purchase order supplier, stock opname fisik, log histori mutasi barang.</p>
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

                @if (session('error'))
                    <div class="panel" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; color: #ef4444; font-size: 14px;">
                        ❌ {{ session('error') }}
                    </div>
                @endif

                <!-- Navigation Tabs Submenu -->
                <div class="preview-tabs" style="margin-bottom: 24px;">
                    <button class="tab sub-tab active" onclick="switchSubTab('stok', this)">📦 Data Barang</button>
                    @if ($user['role'] !== 'barista')
                        <button class="tab sub-tab" onclick="switchSubTab('transaksi', this)">🔄 Masuk & Keluar</button>
                    @endif
                    @if (in_array($user['role'], ['admin', 'super_admin']))
                        <button class="tab sub-tab" onclick="switchSubTab('opname', this)">⚖️ Stock Opname</button>
                        <button class="tab sub-tab" onclick="switchSubTab('supplier', this)">🏢 Supplier</button>
                        <button class="tab sub-tab" onclick="switchSubTab('po', this)">📋 Purchase Order</button>
                    @endif
                </div>

                <!-- ============================================== -->
                <!-- SUBTAB 1: DATA BARANG -->
                <!-- ============================================== -->
                <div id="subtab-stok" class="subtab-content">
                    <div class="panel">
                        <h3>Persediaan Bahan Baku Kafe</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Tabel stok bahan baku realtime.</p>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Bahan Baku</th>
                                        <th style="padding: 10px;">Kategori</th>
                                        <th style="padding: 10px;">Stok Saat Ini</th>
                                        <th style="padding: 10px;">Batas Minimum</th>
                                        <th style="padding: 10px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($inventories as $inv)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">📦 {{ $inv->name }}</td>
                                            <td style="padding: 10px; color: var(--text-gold);">{{ $inv->category ? $inv->category->name : 'N/A' }}</td>
                                            <td style="padding: 10px; font-weight:700;">{{ $inv->current_stock }} {{ $inv->unit }}</td>
                                            <td style="padding: 10px;">{{ $inv->min_stock }} {{ $inv->unit }}</td>
                                            <td style="padding: 10px;">
                                                @if ($inv->current_stock <= 0)
                                                    <span class="pill" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; font-size: 10px;">Habis</span>
                                                @elseif ($inv->current_stock <= $inv->min_stock)
                                                    <span class="pill" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; font-size: 10px;">Menipis</span>
                                                @else
                                                    <span class="pill" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 10px;">Aman</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Belum ada bahan baku terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ============================================== -->
                <!-- SUBTAB 2: BARANG MASUK & KELUAR -->
                <!-- ============================================== -->
                @if ($user['role'] !== 'barista')
                <div id="subtab-transaksi" class="subtab-content" style="display: none;">
                    <section class="split" style="grid-template-columns: {{ in_array($user['role'], ['admin','super_admin']) ? '0.9fr 1.1fr' : '1fr' }};">
                        
                        @if (in_array($user['role'], ['admin', 'super_admin']))
                        <!-- Input forms -->
                        <div style="display: flex; flex-direction: column; gap: 20px;">
                            <!-- Barang Masuk -->
                            <div class="panel">
                                <h3>➕ Catat Barang Masuk (IN)</h3>
                                <form action="{{ route('admin.inventory.store') }}" method="POST" style="margin-top: 12px; display: flex; flex-direction: column; gap: 12px;">
                                    @csrf
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Pilih Barang</label>
                                        <select name="inventory_id" required style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                            @foreach($inventories as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <label style="font-size: 12px; font-weight: 600;">Jumlah Kuantitas</label>
                                            <input type="number" step="0.01" name="quantity" required placeholder="mis. 50" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                        </div>
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <label style="font-size: 12px; font-weight: 600;">Harga Beli (Opsional)</label>
                                            <input type="number" name="price" placeholder="mis. 15000" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                        </div>
                                    </div>
                                    <div style="display: grid; grid-template-columns: 1fr; gap: 12px;">
                                        <div style="display: flex; flex-direction: column; gap: 4px;">
                                            <label style="font-size: 12px; font-weight: 600;">Supplier / Toko</label>
                                            <input type="text" name="supplier_name" placeholder="Nama Supplier / Lokal Market" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                        </div>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Catatan</label>
                                        <input type="text" name="note" placeholder="Pembelian reguler biji kopi gayo..." style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                    </div>
                                    <button type="submit" class="btn btn-gold" style="padding: 10px; border: none; font-weight: 800; font-size: 13px; margin-top: 6px;">Simpan Barang Masuk</button>
                                </form>
                            </div>

                            <!-- Barang Keluar Manual -->
                            <div class="panel">
                                <h3>➖ Catat Barang Keluar Manual (OUT)</h3>
                                <form action="{{ route('admin.inventory.manual-out') }}" method="POST" style="margin-top: 12px; display: flex; flex-direction: column; gap: 12px;">
                                    @csrf
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Pilih Barang</label>
                                        <select name="inventory_id" required style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                            @foreach($inventories as $inv)
                                                <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Jumlah Kuantitas</label>
                                        <input type="number" step="0.01" name="quantity" required placeholder="mis. 10" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Catatan / Alasan</label>
                                        <input type="text" name="note" required placeholder="Susu Full cream basi / bocor..." style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                    </div>
                                    <button type="submit" class="btn" style="padding: 10px; border: 1px solid var(--line); font-weight: 800; font-size: 13px; color:#ef4444; background: rgba(239, 68, 68, 0.05); margin-top: 6px;">Simpan Barang Keluar</button>
                                </form>
                            </div>
                        </div>
                        @endif

                        <!-- Transactions History Log -->
                        <div class="panel">
                            <h3>📜 Histori Mutasi & Audit Trail Stok</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Catatan log masuk/keluar sistem terintegrasi.</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 550px; overflow-y: auto; padding-right: 6px;">
                                @forelse($transactions as $tx)
                                    <div style="padding: 10px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-size:12px;">
                                        <div>
                                            <strong>{{ $tx->inventory ? $tx->inventory->name : 'Bahan' }}</strong>
                                            <div class="muted" style="font-size:11px; margin-top:2px;">Reff: {{ $tx->reference }} · {{ $tx->note }}</div>
                                            <div style="font-size: 10px; color: var(--text-muted); margin-top: 2px;">{{ $tx->created_at->format('d M H:i') }}</div>
                                        </div>
                                        <span class="pill" style="font-size: 11px; font-weight:800; background: {{ $tx->type === 'IN' ? 'rgba(16, 185, 129, 0.1)' : ($tx->type === 'OUT' ? 'rgba(239, 68, 68, 0.1)' : 'rgba(59, 130, 246, 0.1)') }}; color: {{ $tx->type === 'IN' ? '#10b981' : ($tx->type === 'OUT' ? '#ef4444' : '#3b82f6') }};">
                                            {{ $tx->type }} ({{ $tx->quantity > 0 ? '+' : '' }}{{ $tx->quantity }})
                                        </span>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada log transaksi mutasi.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
                @endif

                @if (in_array($user['role'], ['admin', 'super_admin']))
                <!-- ============================================== -->
                <!-- SUBTAB 3: STOCK OPNAME -->
                <!-- ============================================== -->
                <div id="subtab-opname" class="subtab-content" style="display: none;">
                    <section class="split" style="grid-template-columns: 1.25fr 0.75fr;">
                        <!-- Stock Opname Sheet Form -->
                        <div class="panel">
                            <h3>⚖️ Catat Stock Opname Fisik Kafe</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 20px;">Sesuaikan stok sistem dengan melakukan pencocokan stok fisik riil di gudang.</p>
                            
                            <form action="{{ route('admin.opname.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                                @csrf
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px;">
                                        <thead>
                                            <tr style="border-bottom: 2px solid rgba(255,255,255,0.05); color: var(--text-gold);">
                                                <th style="padding: 10px;">Bahan Baku</th>
                                                <th style="padding: 10px;">Stok Sistem</th>
                                                <th style="padding: 10px; width: 140px;">Stok Fisik Gudang</th>
                                                <th style="padding: 10px;">Selisih</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($inventories as $index => $inv)
                                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                                    <td style="padding: 10px; font-weight:700;">📦 {{ $inv->name }}</td>
                                                    <td style="padding: 10px;">
                                                        <span id="sys-val-{{ $index }}">{{ $inv->current_stock }}</span> {{ $inv->unit }}
                                                        <input type="hidden" name="items[{{ $index }}][inventory_id]" value="{{ $inv->id }}">
                                                    </td>
                                                    <td style="padding: 10px;">
                                                        <div style="display:flex; align-items:center; gap: 6px;">
                                                            <input type="number" step="0.01" min="0" required name="items[{{ $index }}][physical_stock]" id="phys-input-{{ $index }}" oninput="calcDifference({{ $index }}, {{ $inv->current_stock }})" style="width: 80px; padding: 6px; border-radius: 4px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                                            <span>{{ $inv->unit }}</span>
                                                        </div>
                                                    </td>
                                                    <td style="padding: 10px; font-weight:700;">
                                                        <span id="diff-label-{{ $index }}" style="color:var(--text-muted);">0</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <button type="submit" class="btn btn-gold" style="width:100%; padding: 12px; font-weight:800; border:none; margin-top: 10px;">💾 Simpan & Eksekusi Penyesuaian Stok</button>
                            </form>
                        </div>

                        <!-- Opname History logs -->
                        <div class="panel">
                            <h3>📝 Riwayat Laporan Stock Opname</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 12px;">Daftar audit penyesuaian stok fisik.</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 450px; overflow-y: auto;">
                                @forelse($stockOpnames as $so)
                                    <div style="padding: 12px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px; font-size:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                                            <strong style="color: var(--text-gold);">{{ $so->opname_number }}</strong>
                                            <span class="pill" style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981;">{{ $so->status }}</span>
                                        </div>
                                        <div class="muted" style="font-size:10px; margin-bottom: 6px;">{{ $so->created_at->format('d M Y H:i') }}</div>
                                        
                                        <div style="display:flex; flex-direction:column; gap:4px; font-size:11px; border-top:1px solid rgba(255,255,255,0.03); padding-top:6px;">
                                            @foreach($so->items as $item)
                                                <div style="display:flex; justify-content:space-between;">
                                                    <span>• {{ $item->inventory ? $item->inventory->name : 'Bahan' }}</span>
                                                    <span>{{ $item->physical_stock }} vs {{ $item->system_stock }} (<strong style="color: {{ $item->difference < 0 ? '#ef4444' : ($item->difference > 0 ? '#10b981' : 'var(--text-muted)') }};">{{ $item->difference > 0 ? '+' : '' }}{{ $item->difference }}</strong>)</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada riwayat stock opname.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ============================================== -->
                <!-- SUBTAB 4: SUPPLIER MANAGEMENT -->
                <!-- ============================================== -->
                <div id="subtab-supplier" class="subtab-content" style="display: none;">
                    <section class="split" style="grid-template-columns: 0.85fr 1.15fr;">
                        <!-- Add Supplier Form -->
                        <div class="panel">
                            <h3>🏢 Tambah Supplier Baru</h3>
                            <form action="{{ route('admin.supplier.store') }}" method="POST" style="margin-top: 14px; display: flex; flex-direction: column; gap: 12px;">
                                @csrf
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600;">Nama Supplier</label>
                                    <input type="text" name="name" required placeholder="mis. PT Kopi Nusantara" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Kontak WA / Telepon</label>
                                        <input type="text" name="contact" placeholder="mis. 08123456789" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                    </div>
                                    <div style="display: flex; flex-direction: column; gap: 4px;">
                                        <label style="font-size: 12px; font-weight: 600;">Email Supplier</label>
                                        <input type="email" name="email" placeholder="order@supplier.com" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                    </div>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600;">Alamat</label>
                                    <input type="text" name="address" placeholder="Alamat kantor / gudang supplier..." style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600;">Bahan Baku Yang Disuplai</label>
                                    <input type="text" name="supplied_products" placeholder="Biji Kopi, Sirup, Cup Kertas..." style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                </div>
                                <button type="submit" class="btn btn-gold" style="padding: 10px; border: none; font-weight: 800; font-size: 13px; margin-top: 6px;">Simpan Supplier</button>
                            </form>
                        </div>

                        <!-- Supplier list -->
                        <div class="panel">
                            <h3>🏢 Mitra Supplier Aktif</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Daftar penyuplai bahan baku kafe resmi.</p>
                            
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                    <thead>
                                        <tr style="border-bottom: 2px solid rgba(255,255,255,0.05); color: var(--text-gold);">
                                            <th style="padding: 10px;">Supplier</th>
                                            <th style="padding: 10px;">Hubungi</th>
                                            <th style="padding: 10px;">Suplai Produk</th>
                                            <th style="padding: 10px; text-align: right;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($suppliers as $sup)
                                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                                <td style="padding: 10px; font-weight: 700;">
                                                    🏢 {{ $sup->name }}
                                                    <p class="muted" style="font-size: 11px; margin: 2px 0 0 0;">{{ $sup->address }}</p>
                                                </td>
                                                <td style="padding: 10px;">
                                                    📞 {{ $sup->contact }}
                                                    <div class="muted" style="font-size: 11px;">{{ $sup->email }}</div>
                                                </td>
                                                <td style="padding: 10px;">{{ $sup->supplied_products ?? 'Umum' }}</td>
                                                <td style="padding: 10px; text-align: right;">
                                                    <form action="{{ route('admin.supplier.destroy', $sup->id) }}" method="POST" style="margin:0;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border:none; padding: 4px 8px; font-size:11px;" onclick="return confirm('Hapus supplier ini?')">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="muted" style="padding: 20px; text-align: center;">Belum ada supplier terdaftar.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ============================================== -->
                <!-- SUBTAB 5: PURCHASE ORDER (PO) -->
                <!-- ============================================== -->
                <div id="subtab-po" class="subtab-content" style="display: none;">
                    <section class="split" style="grid-template-columns: 0.9fr 1.1fr;">
                        <!-- Create PO Form -->
                        <div class="panel">
                            <h3>📋 Buat Purchase Order (PO)</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 12px;">Buat pengajuan pemesanan bahan baku resmi ke supplier.</p>
                            
                            <form action="{{ route('admin.po.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                                @csrf
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--text-gold);">1. PILIH SUPPLIER</label>
                                    <select name="supplier_id" required style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                        <option value="">Pilih Supplier...</option>
                                        @foreach($suppliers as $sup)
                                            <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 6px;">
                                    <label style="font-size: 12px; font-weight: 700; color: var(--text-gold);">2. BARANG & KUANTITAS</label>
                                    <div id="po-items" style="display: flex; flex-direction: column; gap: 8px;">
                                        <!-- Row 1 -->
                                        <div style="display: flex; gap: 8px; align-items: center;">
                                            <select name="items[0][inventory_id]" required style="flex: 2; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                                <option value="">Pilih Barang...</option>
                                                @foreach($inventories as $inv)
                                                    <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
                                                @endforeach
                                            </select>
                                            <input type="number" step="0.01" name="items[0][quantity]" required placeholder="Qty" style="flex: 1; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                            <input type="number" name="items[0][price]" required placeholder="Harga" style="flex: 1; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                                        </div>
                                    </div>
                                    
                                    <button type="button" class="btn" style="border: 1px dashed var(--text-gold); color: var(--text-gold); font-size:11px; padding: 4px; font-weight: 700; margin-top: 4px;" onclick="addPoRow()">
                                        + Tambah Item PO
                                    </button>
                                </div>

                                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 800; border: none; margin-top: 10px;">⚡ Buat & Kirim PO</button>
                            </form>
                        </div>

                        <!-- PO History with confirmation actions -->
                        <div class="panel">
                            <h3>📋 Laporan & Status Purchase Order</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Konfirmasi penerimaan barang untuk PO terkirim.</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 12px; max-height: 500px; overflow-y: auto;">
                                @forelse($purchaseOrders as $po)
                                    <div style="padding: 14px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.04); border-radius: 8px; font-size:12px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                                            <div>
                                                <strong>PO Number: <span style="color: var(--text-gold);">{{ $po->po_number }}</span></strong>
                                                <div class="muted" style="font-size:11px; margin-top: 2px;">Mitra: {{ $po->supplier ? $po->supplier->name : 'N/A' }}</div>
                                            </div>
                                            <span class="pill" style="font-size:10px; background: {{ $po->status === 'COMPLETED' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(245, 158, 11, 0.1)' }}; color: {{ $po->status === 'COMPLETED' ? '#10b981' : '#f59e0b' }};">
                                                {{ $po->status }}
                                            </span>
                                        </div>

                                        <!-- Item breakdown -->
                                        <div style="font-size:11px; background:rgba(255,255,255,0.01); padding: 8px; border-radius: 4px; display:flex; flex-direction:column; gap:4px; margin: 8px 0;">
                                            @foreach($po->items as $item)
                                                <div style="display:flex; justify-content:space-between;">
                                                    <span>• {{ $item->inventory ? $item->inventory->name : 'Bahan' }}</span>
                                                    <span>{{ $item->quantity }} {{ $item->inventory ? $item->inventory->unit : '' }} @ Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                                </div>
                                            @endforeach
                                            <div style="display:flex; justify-content:space-between; border-top:1px solid rgba(255,255,255,0.03); padding-top:4px; font-weight:700;">
                                                <span>Total Biaya</span>
                                                <span style="color: var(--text-gold);">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</span>
                                            </div>
                                        </div>

                                        @if($po->status === 'SENT')
                                            <!-- Action to confirm receipt -->
                                            <form action="{{ route('admin.po.receive', $po->id) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" class="btn btn-gold" style="width:100%; min-height:30px; font-size:11px; padding: 4px; border:none; font-weight:800;">
                                                    ✓ Konfirmasi Barang Datang (Selesai)
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada transaksi purchase order.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>
                @endif
            </main>
        </div>
    </div>

    <!-- Client-side logic for tabs and sub-items -->
    <script>
        function switchSubTab(tabId, element) {
            // Hide all subtab contents
            const contents = document.getElementsByClassName('subtab-content');
            for(let content of contents) {
                content.style.display = 'none';
            }

            // Remove active style from tabs
            const tabs = document.getElementsByClassName('sub-tab');
            for(let tab of tabs) {
                tab.classList.remove('active');
            }

            // Show target subtab
            document.getElementById(`subtab-${tabId}`).style.display = 'block';

            // Add active class to current tab
            element.classList.add('active');

            // Update page headings
            const title = document.getElementById('page-title');
            const subtitle = document.getElementById('page-subtitle');
            
            if (tabId === 'stok') {
                title.innerText = 'Gudang & Supplier';
                subtitle.innerText = 'Kontrol persediaan bahan baku gudang, purchase order supplier, stock opname fisik, log histori mutasi barang.';
            } else if (tabId === 'transaksi') {
                title.innerText = 'Mutasi Stok Manual';
                subtitle.innerText = 'Catat barang masuk (IN) dan barang keluar manual (OUT) dengan audit trail lengkap.';
            } else if (tabId === 'opname') {
                title.innerText = 'Pencocokan Stock Opname';
                subtitle.innerText = 'Sesuaikan stok fisik gudang dengan stok sistem untuk menghindari selisih.';
            } else if (tabId === 'supplier') {
                title.innerText = 'Mitra Supplier Kafe';
                subtitle.innerText = 'Kelola kontak, alamat, dan produk penyuplai bahan baku utama.';
            } else if (tabId === 'po') {
                title.innerText = 'Kelola Purchase Order (PO)';
                subtitle.innerText = 'Buat purchase order barang ke supplier dan konfirmasi kedatangan stok.';
            }
        }

        // Live Opname Difference Calculation
        function calcDifference(idx, systemStock) {
            const physInput = document.getElementById(`phys-input-${idx}`);
            const diffLabel = document.getElementById(`diff-label-${idx}`);
            
            if (physInput.value === '') {
                diffLabel.innerText = '0';
                diffLabel.style.color = 'var(--text-muted)';
                return;
            }

            const physVal = parseFloat(physInput.value);
            const diff = physVal - systemStock;
            
            diffLabel.innerText = (diff > 0 ? '+' : '') + diff;
            if (diff > 0) {
                diffLabel.style.color = '#10b981'; // Green
            } else if (diff < 0) {
                diffLabel.style.color = '#ef4444'; // Red
            } else {
                diffLabel.style.color = 'var(--text-muted)';
            }
        }

        // Add dynamic item rows to PO Form
        let poItemOptionsHtml = `
            <option value="">Pilih Barang...</option>
            @foreach($inventories as $inv)
                <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
            @endforeach
        `;

        function addPoRow() {
            const container = document.getElementById('po-items');
            const rowCount = container.children.length;

            const row = document.createElement('div');
            row.style = 'display: flex; gap: 8px; align-items: center; margin-top: 4px;';
            row.innerHTML = `
                <select name="items[${rowCount}][inventory_id]" required style="flex: 2; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    ${poItemOptionsHtml}
                </select>
                <input type="number" step="0.01" name="items[${rowCount}][quantity]" required placeholder="Qty" style="flex: 1; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                <input type="number" name="items[${rowCount}][price]" required placeholder="Harga" style="flex: 1; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                <button type="button" style="background:none; border:none; color:#ef4444; font-size:16px; cursor:pointer;" onclick="this.parentElement.remove()">✕</button>
            `;

            container.appendChild(row);
        }
    </script>
</x-layouts.app>
