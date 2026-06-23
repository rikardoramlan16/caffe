<x-layouts.app title="Cashier Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
        $defaultCashierTab = request('active_tab', $section === 'pembayaran' ? 'antrean' : 'pesanan');
        $cashierTables = collect($tables ?? [])->map(function ($t) {
            return [
                'id' => $t->id,
                'code' => $t->code,
                'capacity' => $t->capacity,
            ];
        })->values();
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>{{ $appName ?? 'CafeFlow' }}</span></a>
                <nav class="side-nav" aria-label="Navigasi Kasir">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.cashier') }}">📊 Dashboard</a>
                    <a class="{{ $section === 'pembayaran' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', ['section' => 'pembayaran', 'active_tab' => 'antrean']) }}">💳 Pembayaran</a>
                    <a class="{{ $section === 'riwayat' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', 'riwayat') }}">🕒 Riwayat Transaksi</a>
                    <a class="{{ $section === 'kelola-barang' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', 'kelola-barang') }}">📦 Kelola Barang</a>
                    <div style="margin: 10px 0; border-top: 1px solid rgba(255,255,255,0.05);"></div>
                    <a href="{{ route('profil') }}">👤 Profil Saya</a>
                    <a href="{{ route('staff.attendance') }}">📅 Absensi Saya</a>
                    <a href="{{ route('staff.payroll') }}">💵 Slip Gaji</a>

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>☕</span>
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
                        <span class="eyebrow">Point of Sale (POS) Workspace</span>
                        <h1>Dashboard Kasir</h1>
                        <p class="muted">Kelola order masuk dari QR pelanggan, lakukan pembayaran kasir, riwayat pembayaran, serta kelola meja.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        <button onclick="openScannerPairingModal()" class="btn btn-gold" style="font-weight: 700; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; padding: 10px 14px; border: none; cursor: pointer; border-radius: 6px; height: 38px;">📲 HP Scanner</button>
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
                        ⚠️ {{ session('error') }}
                    </div>
                @endif

                <!-- Widgets Kasir -->
                <section id="cashier-summary" class="grid grid-4" aria-label="Widget utama">
                    <article class="metric-card">
                        <span class="pill" style="background: #f59e0b; color: var(--bg-app);">Pending POS</span>
                        <strong>{{ $metrics['waiting_payment'] }}</strong>
                        <span>Menunggu Pembayaran</span>
                        <p class="muted">Antrean transaksi masuk</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill">Antrean</span>
                        <strong>{{ $metrics['orders_today'] }}</strong>
                        <span>Order Hari Ini</span>
                        <p class="muted">Akumulasi pesanan masuk</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: #10b981; color: var(--bg-app);">Pendapatan</span>
                        <strong>Rp {{ number_format($metrics['revenue_today'], 0, ',', '.') }}</strong>
                        <span>Pendapatan Hari Ini</span>
                        <p class="muted">Kas masuk sukses hari ini</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill">Transaksi</span>
                        <strong>{{ $metrics['total_transactions'] }}</strong>
                        <span>Total Transaksi</span>
                        <p class="muted">Order lunas sukses</p>
                    </article>
                </section>

                <section class="split" id="order-masuk" style="margin-top: 24px;">
                    <!-- Tabel Order Baru / Waiting Payment -->
                    <!-- Panel Utama Cashier Tabbed -->
                    <div class="panel" style="flex: 2; display: flex; flex-direction: column; gap: 16px;">
                        <!-- Tab Headers -->
                        <div style="display: flex; border-bottom: 1px solid var(--line); margin-bottom: 12px; gap: 10px;">
                            <button type="button" onclick="switchCashierTab('pesanan')" id="tab-btn-pesanan" style="padding: 10px 16px; background: none; border: none; font-size: 14px; font-weight: 700; color: {{ $defaultCashierTab === 'pesanan' ? 'var(--text-gold)' : 'var(--muted)' }}; border-bottom: {{ $defaultCashierTab === 'pesanan' ? '2px solid var(--text-gold)' : 'none' }}; cursor: pointer; transition: all 0.2s;">📋 Buat Pesanan</button>
                            <button type="button" onclick="switchCashierTab('antrean')" id="tab-btn-antrean" style="padding: 10px 16px; background: none; border: none; font-size: 14px; font-weight: 700; color: {{ $defaultCashierTab === 'antrean' ? 'var(--text-gold)' : 'var(--muted)' }}; border-bottom: {{ $defaultCashierTab === 'antrean' ? '2px solid var(--text-gold)' : 'none' }}; cursor: pointer; transition: all 0.2s; position: relative;">
                                📥 Antrean Order
                                @if($newOrders->count() > 0)
                                    <span id="tab-btn-antrean-count" style="background: #ef4444; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; margin-left: 6px; font-weight: 800;">{{ $newOrders->count() }}</span>
                                @endif
                            </button>
                        </div>

                        <!-- Tab 1: Buat Pesanan Baru -->
                        <div id="tab-content-pesanan" style="display: {{ $defaultCashierTab === 'pesanan' ? 'flex' : 'none' }}; flex-direction: column; gap: 16px;">
                            <form id="direct-order-form" action="{{ route('cashier.orders.create-direct') }}" method="POST" style="display: flex; flex-direction: column; gap: 16px; margin: 0;">
                                @csrf
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 6px;">
                                    <div>
                                        <label style="font-size: 12px; font-weight: 600; color: var(--ink); display:block; margin-bottom:4px;">Pilih Meja (Opsional)</label>
                                        <select name="table_id" style="width: 100%; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid var(--line); color: var(--ink); font-size: 13px;">
                                            <option value="">Tanpa Meja (Direct/Takeaway)</option>
                                            @foreach ($tables as $t)
                                                <option value="{{ $t->id }}">{{ $t->code }} (Kap. {{ $t->capacity }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label style="font-size: 12px; font-weight: 600; color: var(--ink); display:block; margin-bottom:4px;">Catatan / Nama Pelanggan</label>
                                        <input type="text" name="customer_note" placeholder="Catatan atau nama..." style="width: 100%; padding: 8px; border-radius: 6px; background: var(--bg-app); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                    </div>
                                </div>

                                <!-- Draft Cart List -->
                                <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--line); padding: 14px; border-radius: 8px; display: flex; flex-direction: column; gap: 10px;">
                                    <h4 style="font-size: 13px; color: var(--text-gold); margin-bottom: 4px; display: flex; justify-content: space-between; align-items: center;">
                                        <span>🛒 Detail Keranjang Kasir</span>
                                        <span id="cart-item-count" style="font-size: 12px; color: var(--muted);">0 Item</span>
                                    </h4>
                                    <div id="cart-list-container" style="display: flex; flex-direction: column; gap: 8px; max-height: 180px; overflow-y: auto;">
                                        <div class="muted" style="text-align: center; font-size: 12px; padding: 10px;">Keranjang kosong. Klik produk di bawah untuk menambahkan.</div>
                                    </div>
                                    <div style="border-top: 1px solid var(--line); padding-top: 8px; display: flex; justify-content: space-between; font-size: 13px; font-weight: 700; color: var(--ink);">
                                        <span>Estimasi Subtotal:</span>
                                        <span id="cart-subtotal">Rp 0</span>
                                    </div>
                                    <button type="submit" class="btn btn-gold" id="btn-submit-cart" disabled style="width:100%; font-size: 13px; padding: 10px; font-weight:700;">➕ Buat & Masukkan Antrean</button>
                                </div>

                                <!-- Product & Menu Selection Grid -->
                                <div>
                                    <h4 style="font-size: 13px; color: var(--ink); margin-bottom: 10px;">📦 Pilih Menu & Produk Ready-Made</h4>
                                    
                                    <!-- Search Input -->
                                    <input type="text" id="pos-search-input" onkeyup="filterPOSItems()" placeholder="Cari menu atau produk ready-made..." style="width: 100%; padding: 8px 12px; border-radius: 6px; background: var(--bg-app); border: 1px solid var(--line); color: var(--ink); font-size: 12px; margin-bottom: 12px; outline: none;">

                                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 10px; max-height: 280px; overflow-y: auto; padding-right: 4px;" id="pos-items-grid">
                                        <!-- Products -->
                                        @foreach($products as $p)
                                            <div class="pos-item-card" data-name="{{ strtolower($p->name) }}" style="background: rgba(255,255,255,0.02); border: 1px solid var(--line); padding: 10px; border-radius: 6px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;">
                                                <div>
                                                    <div style="font-size: 12px; font-weight: 700; color: var(--ink);">{{ $p->name }}</div>
                                                    <div class="muted" style="font-size: 10px; color: var(--text-gold); font-weight:600;">Rp {{ number_format($p->price, 0, ',', '.') }}</div>
                                                    <span style="font-size: 8px; text-transform: uppercase; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 4px; border-radius: 4px; display: inline-block; margin-top: 4px;">Barang</span>
                                                </div>
                                                <button type="button" onclick="addToPOSCart('product', {{ $p->id }}, '{{ addslashes($p->name) }}', {{ $p->price }})" class="btn btn-gold" style="font-size: 10px; padding: 4px 8px; min-height: unset; height: auto; border:none; border-radius:4px; cursor:pointer;">+ Tambah</button>
                                            </div>
                                        @endforeach

                                        <!-- Menus -->
                                        @foreach($menus as $m)
                                            <div class="pos-item-card" data-name="{{ strtolower($m->name) }}" style="background: rgba(255,255,255,0.02); border: 1px solid var(--line); padding: 10px; border-radius: 6px; display: flex; flex-direction: column; justify-content: space-between; gap: 6px;">
                                                <div>
                                                    <div style="font-size: 12px; font-weight: 700; color: var(--ink);">{{ $m->name }}</div>
                                                    <div class="muted" style="font-size: 10px; color: var(--text-gold); font-weight:600;">Rp {{ number_format($m->price, 0, ',', '.') }}</div>
                                                    <span style="font-size: 8px; text-transform: uppercase; background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 2px 4px; border-radius: 4px; display: inline-block; margin-top: 4px;">Minuman/Makanan</span>
                                                </div>
                                                <button type="button" onclick="addToPOSCart('menu', {{ $m->id }}, '{{ addslashes($m->name) }}', {{ $m->price }})" class="btn btn-gold" style="font-size: 10px; padding: 4px 8px; min-height: unset; height: auto; border:none; border-radius:4px; cursor:pointer;">+ Tambah</button>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Tab 2: Antrean Order Baru -->
                        <div id="tab-content-antrean" style="display: {{ $defaultCashierTab === 'antrean' ? 'flex' : 'none' }}; flex-direction: column; gap: 16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="margin: 0; font-size: 16px;">📥 Antrean Order Baru (Menunggu Pembayaran)</h3>
                            </div>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 8px;">Silakan konfirmasi metode pembayaran untuk memasukkan order ke queue barista.</p>
                            
                            <div id="antrean-orders-container" style="display: flex; flex-direction: column; gap: 16px;">
                                @forelse ($newOrders as $order)
                                    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 10px; display: flex; flex-direction: column; gap: 12px;">
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 8px;">
                                            <div>
                                                <span style="font-size: 15px; font-weight: 800; color: var(--text-gold);">{{ $order->invoice_number }}</span>
                                                <span style="font-size: 13px; font-weight: 700; margin-left: 8px; color: var(--text-main);">🪑 {{ $order->table ? 'Meja ' . $order->table->code : 'Tanpa Meja (Direct)' }}</span>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-size: 14px; font-weight: 800; color: #10b981;">Rp {{ number_format($order->total, 0, ',', '.') }}</div>
                                                <span class="pill" style="font-size: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; margin-top: 4px; display: inline-block;">WAITING PAYMENT</span>
                                            </div>
                                        </div>

                                        <!-- Item detail -->
                                        <div style="font-size: 13px;">
                                            <div style="font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Detail Pesanan:</div>
                                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                                @foreach ($order->items as $item)
                                                    <div style="display: flex; justify-content: space-between; color: var(--text-main);">
                                                        <span>• {{ $item->menu ? $item->menu->name : ($item->product ? $item->product->name : 'Item') }} <strong style="color: var(--text-gold);">x{{ $item->quantity }}</strong></span>
                                                        <span class="muted">@Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            @if ($order->customer_note)
                                                <div style="margin-top: 8px; background: rgba(255,255,255,0.02); padding: 6px; border-radius: 4px; font-style: italic; color: var(--text-muted);">
                                                    📝 Catatan: {{ $order->customer_note }}
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Barcode Scanner Input for Ready-made Products -->
                                        <form action="{{ route('orders.add-barcode', $order->id) }}" method="POST" style="margin: 6px 0 0 0; display: flex; align-items: center; gap: 8px;">
                                            @csrf
                                            <div style="position: relative; flex: 1; display: flex; align-items: center;">
                                                <span style="position: absolute; left: 10px; font-size: 14px; pointer-events: none;">🏷️</span>
                                                <input type="text" name="barcode" placeholder="Scan barcode produk jadi..." required autocomplete="off" style="width: 100%; padding: 8px 10px 8px 30px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 12px; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='var(--text-gold)'" onblur="this.style.borderColor='rgba(255,255,255,0.08)'">
                                            </div>
                                            <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 8px 12px; min-height: unset; height: 32px; border: none; cursor: pointer; border-radius: 6px;">+ Tambah</button>
                                        </form>

                                        <!-- Aksi Kasir -->
                                        <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; margin-top: 8px; background: rgba(255,255,255,0.01); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02);">
                                            <!-- Form Pindah Meja -->
                                            <form action="{{ route('orders.move', $order->id) }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                                                @csrf
                                                <select name="table_id" required style="padding: 6px; font-size: 12px; border-radius: 4px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main);">
                                                    <option value="">Pindah Meja</option>
                                                    @foreach ($tables as $t)
                                                        @if ($order->table_id !== $t->id)
                                                            <option value="{{ $t->id }}">{{ $t->code }} (Kap. {{ $t->capacity }})</option>
                                                        @endif
                                                    @endforeach
                                                </select>
                                                <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 6px 10px;">Pindah</button>
                                            </form>

                                            <!-- Form Pembayaran -->
                                            <form action="{{ route('orders.payment', $order->id) }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                                                @csrf
                                                <select name="method" required style="padding: 6px; font-size: 12px; border-radius: 4px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main);">
                                                    <option value="QRIS">QRIS</option>
                                                    <option value="Cash">Cash (Tunai)</option>
                                                    <option value="Debit">Debit Card</option>
                                                    <option value="Transfer">Transfer Bank</option>
                                                </select>
                                                <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px; font-weight: 700; background: #10b981; border: none; color: white; border-radius:4px; cursor:pointer;">💵 Konfirmasi Bayar</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 40px; background: rgba(255,255,255,0.01); border-radius: 8px;">
                                        📭 Belum ada antrean order baru yang masuk.
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Kanan: Scanner & Riwayat -->
                    <div style="flex: 1; display: flex; flex-direction: column; gap: 24px;">
                        @if ($section === 'pembayaran')
                        <!-- Panel: Pairing Scanner HP Inline -->
                        <div class="panel" id="scanner-inline-card" style="display: flex; flex-direction: column; gap: 14px; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow);">
                            <h3 style="color: var(--ink); display: flex; align-items: center; gap: 8px; font-size: 16px;">📲 HP Scanner Nirkabel</h3>
                            <p class="muted" style="font-size: 12px; color: var(--muted); margin-top: -6px;">Sambungkan HP Anda untuk memindai barcode produk langsung ke POS.</p>
                            
                            <div style="background: var(--cream-soft); padding: 12px; border-radius: 8px; border: 1px solid var(--line); text-align: center;">
                                <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: var(--muted); letter-spacing: 0.5px;">Kode Pairing POS</div>
                                <div id="pairing-code-inline" style="font-size: 26px; font-weight: 800; color: var(--text-gold); font-family: monospace; letter-spacing: 2px; margin: 4px 0;">------</div>
                                <p style="font-size: 11px; color: var(--ink);">Buka <a href="{{ route('scanner.index') }}" target="_blank" style="color: var(--text-gold); font-weight: 700; text-decoration: underline;">/scanner</a> di ponsel.</p>
                            </div>
                            
                            <div style="display: flex; flex-direction: column; align-items: center; gap: 6px;">
                                <div id="scanner-qrcode-inline" style="background: white; padding: 6px; border-radius: 6px; border: 1px solid var(--line); display: inline-block; width: 112px; height: 112px;">
                                    <div style="width:100px; height:100px; display:flex; align-items:center; justify-content:center; color: #333; font-size: 10px; font-weight: 600;">Memuat QR...</div>
                                </div>
                                <span style="font-size: 10px; color: var(--muted); text-align: center;">Scan QR ini untuk terhubung otomatis</span>
                            </div>
                            
                            <div id="pairing-status-inline" style="padding: 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-align: center; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                                🔴 Menunggu Koneksi HP...
                            </div>
                            
                            <button onclick="requestNewPairingCode()" class="btn btn-outline" style="font-size: 11px; border: 1px solid var(--line); color: var(--ink); padding: 6px; cursor: pointer; width: 100%;">🔄 Regenerate Code</button>
                        </div>
                        @endif

                        <!-- Riwayat Pembayaran Terbaru -->
                        <div class="panel" id="riwayat" style="margin: 0; width: 100%;">
                            <h3>🕒 Riwayat Pembayaran Terbaru</h3>
                            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Transaksi yang berhasil diproses hari ini.</p>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px; max-height: 520px; overflow-y: auto; padding-right: 4px;">
                                @forelse ($payments as $pay)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.02); border-radius: 6px;">
                                        <div>
                                            <strong>{{ $pay->order ? $pay->order->invoice_number : 'INV-PAY' }}</strong>
                                            <div class="muted" style="font-size: 11px;">{{ $pay->order && $pay->order->table ? 'Meja ' . $pay->order->table->code : 'Tanpa Meja' }} · {{ $pay->paid_at ? $pay->paid_at->format('H:i') : $pay->created_at->format('H:i') }}</div>
                                        </div>
                                        <div style="text-align: right;">
                                            <div style="font-size: 13px; font-weight: 700; color: #10b981;">Rp {{ number_format($pay->amount, 0, ',', '.') }}</div>
                                            <span class="pill" style="font-size: 9px; background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 2px 6px;">{{ strtoupper($pay->method) }}</span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 20px;">Belum ada riwayat transaksi lunas.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>

                @if ($section === 'kelola-barang')
                <section id="kelola-barang-section" style="margin-top: 24px;">
                    <!-- Kelola Barang (Ready-made Products) -->
                    <div class="panel">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h3>📦 Kelola Barang (Produk Jadi)</h3>
                                <p class="muted" style="font-size: 12px; margin-top: 2px;">Kelola stok produk jadi siap jual berserta barcode scanner.</p>
                            </div>
                            <button onclick="openAddProductModal()" class="btn btn-gold" style="font-size: 12px; padding: 8px 14px; border: none; cursor: pointer; border-radius: 6px; font-weight: 700; height: 36px;">+ Tambah Barang Baru</button>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05); color: var(--text-muted);">
                                        <th style="padding: 12px 10px;">Nama Barang</th>
                                        <th style="padding: 12px 10px;">Nomor Barcode</th>
                                        <th style="padding: 12px 10px;">Harga</th>
                                        <th style="padding: 12px 10px;">Status</th>
                                        <th style="padding: 12px 10px; text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($products as $p)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 12px 10px; font-weight: 700;">
                                                {{ $p->name }}
                                                <p class="muted" style="font-size:11px; font-weight:500; margin: 2px 0 0 0;">{{ $p->description ?? 'Produk jadi siap saji/jual.' }}</p>
                                            </td>
                                            <td style="padding: 12px 10px; font-family: monospace; font-size: 12px; color: var(--text-gold); font-weight: 700;">🏷️ {{ $p->barcode ?? '-' }}</td>
                                            <td style="padding: 12px 10px; font-weight: 800; color: #10b981;">Rp {{ number_format($p->price, 0, ',', '.') }}</td>
                                            <td style="padding: 12px 10px;">
                                                <form action="{{ route('admin.products.toggle', $p->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="pill" style="border:none; cursor:pointer; background: {{ $p->is_available ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $p->is_available ? '#10b981' : '#ef4444' }}; font-size: 10px; font-weight:800;">
                                                        {{ $p->is_available ? 'Tersedia' : 'Habis' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td style="padding: 12px 10px; text-align: right;">
                                                <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                                    <button onclick='openEditProductModal(@json($p))' class="btn btn-gold" style="font-size: 11px; padding: 6px 10px; border-radius: 4px; border: none; cursor: pointer; height: 28px; min-height: unset;">Edit</button>
                                                    <form action="{{ route('admin.products.destroy', $p->id) }}" method="POST" style="margin: 0; display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 6px 10px; border-radius: 4px; cursor: pointer; font-size: 11px; height: 28px; min-height: unset;" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">Hapus</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="muted" style="text-align: center; padding: 40px;">Belum ada data barang. Silakan tambah barang baru.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- Modal 1: Add Product -->
                <div id="add-product-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                    <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow);">
                        <h3 style="color: var(--ink);">📝 Tambah Barang Baru</h3>
                        <button onclick="closeAddProductModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--muted); cursor:pointer;">✕</button>
                        
                        <form action="{{ route('admin.products.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label for="product_name" style="font-size: 12px; font-weight: 600; color: var(--ink);">Nama Barang</label>
                                    <input type="text" id="product_name" name="name" required placeholder="misal: Coca Cola" style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label for="product_barcode" style="font-size: 12px; font-weight: 600; color: var(--ink);">Nomor Barcode (Opsional)</label>
                                    <input type="text" id="product_barcode" name="barcode" placeholder="Scan atau ketik barcode..." style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <label for="product_price" style="font-size: 12px; font-weight: 600; color: var(--ink);">Harga (Rupiah)</label>
                                <input type="number" id="product_price" name="price" required placeholder="misal: 10000" style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <label for="product_desc" style="font-size: 12px; font-weight: 600; color: var(--ink);">Deskripsi</label>
                                <textarea id="product_desc" name="description" placeholder="Keterangan barang..." style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                            </div>

                            <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Barang Baru</button>
                        </form>
                    </div>
                </div>

                <!-- Modal 2: Edit Product -->
                <div id="edit-product-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                    <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow);">
                        <h3 style="color: var(--ink);">📝 Edit Barang Produk</h3>
                        <button onclick="closeEditProductModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--muted); cursor:pointer;">✕</button>
                        
                        <form id="edit-product-form" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                            @csrf
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Nama Barang</label>
                                    <input type="text" id="edit_product_name" name="name" required style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Nomor Barcode (Opsional)</label>
                                    <input type="text" id="edit_product_barcode" name="barcode" placeholder="Scan atau ketik barcode..." style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Harga (Rupiah)</label>
                                    <input type="number" id="edit_product_price" name="price" required style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Ketersediaan</label>
                                    <select id="edit_product_available" name="is_available" required style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                        <option value="1">Tersedia</option>
                                        <option value="0">Habis</option>
                                    </select>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Status Rekomendasi</label>
                                    <select id="edit_product_featured" name="is_featured" required style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none;">
                                        <option value="0">Biasa</option>
                                        <option value="1">Best Seller / Unggulan</option>
                                    </select>
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 4px;">
                                    <!-- Filler to keep layout balanced -->
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <label style="font-size: 12px; font-weight: 600; color: var(--ink);">Deskripsi</label>
                                <textarea id="edit_product_desc" name="description" style="padding: 10px 14px; border-radius: 6px; background: var(--cream-soft); border: 1px solid var(--line); color: var(--ink); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                            </div>

                            <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Perubahan Barang</button>
                        </form>
                    </div>
                </div>
                @endif
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = @json($section);
            if (section === 'dashboard') {
                document.getElementById('order-masuk')?.style.setProperty('display', 'none');
                return;
            }

            document.getElementById('cashier-summary')?.style.setProperty('display', 'none');
            if (section === 'riwayat') {
                const orderSection = document.getElementById('order-masuk');
                const historyPanel = document.getElementById('riwayat');
                if (orderSection) orderSection.style.display = 'block';
                if (historyPanel) historyPanel.style.display = '';
                historyPanel?.scrollIntoView({ block: 'start' });
            }
            if (section === 'kelola-barang') {
                document.getElementById('order-masuk')?.style.setProperty('display', 'none');
            }
        });

        @if ($section === 'kelola-barang')
        function openAddProductModal() {
            document.getElementById('add-product-modal').style.display = 'flex';
        }
        function closeAddProductModal() {
            document.getElementById('add-product-modal').style.display = 'none';
        }
        function openEditProductModal(product) {
            document.getElementById('edit_product_name').value = product.name;
            document.getElementById('edit_product_price').value = product.price;
            document.getElementById('edit_product_barcode').value = product.barcode || '';
            document.getElementById('edit_product_available').value = product.is_available ? '1' : '0';
            document.getElementById('edit_product_featured').value = product.is_featured ? '1' : '0';
            document.getElementById('edit_product_desc').value = product.description || '';
            
            const form = document.getElementById('edit-product-form');
            form.action = `/admin/products/${product.id}/update`;
            
            document.getElementById('edit-product-modal').style.display = 'flex';
        }
        function closeEditProductModal() {
            document.getElementById('edit-product-modal').style.display = 'none';
        }
        @endif

        // Scanner Pairing Logic
        let scannerPollInterval = null;
        let activePairingCode = localStorage.getItem('cafeflow_scanner_code') || '';

        function openScannerPairingModal() {
            document.getElementById('scanner-pairing-modal').style.display = 'flex';
            if (!activePairingCode) {
                requestNewPairingCode();
            } else {
                displayActivePairing(activePairingCode);
            }
        }

        function closeScannerPairingModal() {
            document.getElementById('scanner-pairing-modal').style.display = 'none';
        }

        function requestNewPairingCode() {
            const displays = [
                document.getElementById('pairing-code-display'),
                document.getElementById('pairing-code-inline')
            ];
            displays.forEach(el => { if (el) el.innerText = '------'; });

            const statuses = [
                document.getElementById('pairing-status-display'),
                document.getElementById('pairing-status-inline')
            ];
            statuses.forEach(statusBadge => {
                if (statusBadge) {
                    statusBadge.innerHTML = '🟡 Membuat Kode...';
                    statusBadge.style.background = 'rgba(245, 158, 11, 0.1)';
                    statusBadge.style.color = '#f59e0b';
                    statusBadge.style.borderColor = 'rgba(245, 158, 11, 0.2)';
                }
            });

            fetch('{{ route("cashier.scanner.generate") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'ngrok-skip-browser-warning': 'true'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    activePairingCode = data.pairing_code;
                    localStorage.setItem('cafeflow_scanner_code', data.pairing_code);
                    displayActivePairing(data.pairing_code);
                } else {
                    statuses.forEach(statusBadge => {
                        if (statusBadge) statusBadge.innerText = '🔴 Gagal membuat kode.';
                    });
                }
            })
            .catch(err => {
                console.error(err);
                statuses.forEach(statusBadge => {
                    if (statusBadge) statusBadge.innerText = '🔴 Gangguan koneksi.';
                });
            });
        }

        function displayActivePairing(code) {
            const displayEl = document.getElementById('pairing-code-display');
            const inlineEl = document.getElementById('pairing-code-inline');
            if (displayEl) displayEl.innerText = code;
            if (inlineEl) inlineEl.innerText = code;
            
            const pairingUrl = `{{ url('/scanner') }}?pair=${code}`;
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' + encodeURIComponent(pairingUrl);
            
            const qrDisplay = document.getElementById('scanner-qrcode-container');
            const qrInline = document.getElementById('scanner-qrcode-inline');
            
            if (qrDisplay) qrDisplay.innerHTML = `<img src="${qrUrl}" width="130" height="130" alt="QR Code" style="display:block; margin: 0 auto;">`;
            if (qrInline) qrInline.innerHTML = `<img src="${qrUrl}" width="100" height="100" alt="QR Code" style="display:block; margin: 0 auto;">`;
            
            startBackgroundPolling(code);
        }

        function startBackgroundPolling(code) {
            if (scannerPollInterval) clearInterval(scannerPollInterval);
            
            // Perform initial status check
            checkScannerStatus(code);
            
            // Start interval
            scannerPollInterval = setInterval(() => {
                checkScannerStatus(code);
            }, 3000);
        }

        function checkScannerStatus(code) {
            fetch(`/dashboard/cashier/scanner/status?code=${code}`, {
                headers: {
                    'ngrok-skip-browser-warning': 'true'
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const statusBadge = document.getElementById('pairing-status-display');
                        const statusInline = document.getElementById('pairing-status-inline');
                        
                        if (data.paired) {
                            [statusBadge, statusInline].forEach(badge => {
                                if (badge) {
                                    badge.innerHTML = '🟢 HP Scanner Terhubung!';
                                    badge.style.background = 'rgba(16, 185, 129, 0.1)';
                                    badge.style.color = '#10b981';
                                    badge.style.borderColor = 'rgba(16, 185, 129, 0.2)';
                                }
                            });

                            // Process pending scans from the mobile scanner
                            if (data.pending_scans && data.pending_scans.length > 0) {
                                data.pending_scans.forEach(scan => {
                                    addToPOSCart('product', scan.product_id, scan.product_name, scan.product_price);
                                });
                                // Switch to pesanan tab to show the cart
                                switchCashierTab('pesanan');
                                // Play a notification sound
                                playNotificationBeep();
                            }
                        } else {
                            [statusBadge, statusInline].forEach(badge => {
                                if (badge) {
                                    badge.innerHTML = '🔴 Menunggu Koneksi HP...';
                                    badge.style.background = 'rgba(239, 68, 68, 0.1)';
                                    badge.style.color = '#ef4444';
                                    badge.style.borderColor = 'rgba(239, 68, 68, 0.2)';
                                }
                            });
                            localStorage.removeItem('cafeflow_scanner_code');
                            activePairingCode = '';
                        }
                    }
                })
                .catch(err => console.warn('Polling error:', err));
        }

        function playNotificationBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioCtx.createOscillator();
                const gainNode = audioCtx.createGain();
                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gainNode.gain.setValueAtTime(0.15, audioCtx.currentTime);
                oscillator.connect(gainNode);
                gainNode.connect(audioCtx.destination);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.15);
            } catch (e) { /* silent */ }
        }

        // Tab switching logic for Cashier
        function switchCashierTab(tab) {
            const tabBtnPesanan = document.getElementById('tab-btn-pesanan');
            const tabBtnAntrean = document.getElementById('tab-btn-antrean');
            const tabContentPesanan = document.getElementById('tab-content-pesanan');
            const tabContentAntrean = document.getElementById('tab-content-antrean');

            if (!tabBtnPesanan || !tabBtnAntrean) return;

            if (tab === 'pesanan') {
                tabBtnPesanan.style.color = 'var(--text-gold)';
                tabBtnPesanan.style.borderBottom = '2px solid var(--text-gold)';
                tabBtnAntrean.style.color = 'var(--muted)';
                tabBtnAntrean.style.borderBottom = 'none';
                
                if (tabContentPesanan) tabContentPesanan.style.display = 'flex';
                if (tabContentAntrean) tabContentAntrean.style.display = 'none';
            } else {
                tabBtnAntrean.style.color = 'var(--text-gold)';
                tabBtnAntrean.style.borderBottom = '2px solid var(--text-gold)';
                tabBtnPesanan.style.color = 'var(--muted)';
                tabBtnPesanan.style.borderBottom = 'none';
                
                if (tabContentPesanan) tabContentPesanan.style.display = 'none';
                if (tabContentAntrean) tabContentAntrean.style.display = 'flex';
            }
        }

        function renderCashierOrderCard(order) {
            const itemsHtml = (order.items || []).map((item) => `
                <div style="display: flex; justify-content: space-between; color: var(--text-main);">
                    <span>• ${escapeHtml(item.name)} <strong style="color: var(--text-gold);">x${item.quantity}</strong></span>
                    <span class="muted">@Rp ${Number(item.unit_price || 0).toLocaleString('id-ID')}</span>
                </div>
            `).join('');

            const noteHtml = order.customer_note
                ? `<div style="margin-top: 8px; background: rgba(255,255,255,0.02); padding: 6px; border-radius: 4px; font-style: italic; color: var(--text-muted);">📝 Catatan: ${escapeHtml(order.customer_note)}</div>`
                : '';

            return `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 10px; display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 8px;">
                        <div>
                            <span style="font-size: 15px; font-weight: 800; color: var(--text-gold);">${escapeHtml(order.invoice_number)}</span>
                            <span style="font-size: 13px; font-weight: 700; margin-left: 8px; color: var(--text-main);">🪑 ${escapeHtml(order.table_label || 'Tanpa Meja (Direct)')}</span>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-size: 14px; font-weight: 800; color: #10b981;">Rp ${Number(order.total || 0).toLocaleString('id-ID')}</div>
                            <span class="pill" style="font-size: 10px; background: rgba(245, 158, 11, 0.1); color: #f59e0b; margin-top: 4px; display: inline-block;">WAITING PAYMENT</span>
                        </div>
                    </div>

                    <div style="font-size: 13px;">
                        <div style="font-weight: 700; color: var(--text-muted); margin-bottom: 6px;">Detail Pesanan:</div>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            ${itemsHtml || '<div class="muted">Tidak ada item.</div>'}
                        </div>
                        ${noteHtml}
                    </div>

                    <form action="/orders/${order.id}/add-by-barcode" method="POST" style="margin: 6px 0 0 0; display: flex; align-items: center; gap: 8px;">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" autocomplete="off">
                        <div style="position: relative; flex: 1; display: flex; align-items: center;">
                            <span style="position: absolute; left: 10px; font-size: 14px; pointer-events: none;">🏷️</span>
                            <input type="text" name="barcode" placeholder="Scan barcode produk jadi..." required autocomplete="off" style="width: 100%; padding: 8px 10px 8px 30px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 12px; outline: none; transition: border-color 0.2s;">
                        </div>
                        <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 8px 12px; min-height: unset; height: 32px; border: none; cursor: pointer; border-radius: 6px;">+ Tambah</button>
                    </form>

                    <div style="display: flex; gap: 10px; justify-content: space-between; align-items: center; margin-top: 8px; background: rgba(255,255,255,0.01); padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02);">
                        <form action="/orders/${order.id}/move" method="POST" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" autocomplete="off">
                            <select name="table_id" required style="padding: 6px; font-size: 12px; border-radius: 4px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main);">
                                <option value="">Pindah Meja</option>
                                ${cashierTables.map(table => `<option value="${table.id}">${escapeHtml(table.code)} (Kap. ${table.capacity})</option>`).join('')}
                            </select>
                            <button type="submit" class="btn btn-gold" style="font-size: 11px; padding: 6px 10px;">Pindah</button>
                        </form>

                        <form action="/orders/${order.id}/payment" method="POST" style="margin: 0; display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" autocomplete="off">
                            <select name="method" required style="padding: 6px; font-size: 12px; border-radius: 4px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main);">
                                <option value="QRIS">QRIS</option>
                                <option value="Cash">Cash (Tunai)</option>
                                <option value="Debit">Debit Card</option>
                                <option value="Transfer">Transfer Bank</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px; font-weight: 700; background: #10b981; border: none; color: white; border-radius:4px; cursor:pointer;">💵 Konfirmasi Bayar</button>
                        </form>
                    </div>
                </div>
            `;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        let lastOrderCount = {{ $newOrders->count() }};

        function showCashierToast(message) {
            const toast = document.getElementById('cashier-toast');
            const msgEl = document.getElementById('cashier-toast-message');
            if (toast && msgEl) {
                msgEl.textContent = message;
                toast.style.display = 'flex';
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(20px)';
                toast.style.transition = 'all 0.3s ease-out';
                
                // Trigger reflow
                toast.offsetHeight;
                
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
                
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(20px)';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        }

        function refreshCashierWaitingOrders() {
            fetch('{{ route('dashboard.cashier.waiting-orders') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(res => res.json())
                .then(data => {
                    if (!data || !data.success) return;

                    // Play beep and show toast if order count increases
                    if (data.count > lastOrderCount) {
                        playNotificationBeep();
                        showCashierToast(`Ada ${data.count - lastOrderCount} pesanan baru masuk!`);
                    }
                    lastOrderCount = data.count;

                    const badge = document.getElementById('tab-btn-antrean-count');
                    if (badge) {
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }

                    const container = document.getElementById('antrean-orders-container');
                    if (container) {
                        const tabContent = document.getElementById('tab-content-antrean');
                        // Only update container content if the tab is visible
                        if (tabContent && tabContent.style.display !== 'none') {
                            if (!data.orders || data.orders.length === 0) {
                                container.innerHTML = '<div class="muted" style="text-align: center; padding: 40px; background: rgba(255,255,255,0.01); border-radius: 8px;">📭 Belum ada antrean order baru yang masuk.</div>';
                            } else {
                                container.innerHTML = data.orders.map(renderCashierOrderCard).join('');
                            }
                        }
                    }
                })
                .catch(err => console.warn('Gagal refresh antrean cashier:', err));
        }

        // POS Cart logic
        let posCart = [];
        const cashierTables = @json($cashierTables);

        function addToPOSCart(type, id, name, price) {
            const existing = posCart.find(item => item.type === type && item.id === id);
            if (existing) {
                existing.quantity++;
                // Update name/price if provided (in case of first add vs increment)
                if (name) existing.name = name;
                if (price > 0) existing.price = price;
            } else {
                posCart.push({ type, id, name, price, quantity: 1 });
            }
            renderPOSCart();
        }

        function removeFromPOSCart(type, id) {
            const index = posCart.findIndex(item => item.type === type && item.id === id);
            if (index !== -1) {
                posCart[index].quantity--;
                if (posCart[index].quantity <= 0) {
                    posCart.splice(index, 1);
                }
            }
            renderPOSCart();
        }

        function removeAllFromPOSCart(type, id) {
            posCart = posCart.filter(item => !(item.type === type && item.id === id));
            renderPOSCart();
        }

        function renderPOSCart() {
            const container = document.getElementById('cart-list-container');
            const submitBtn = document.getElementById('btn-submit-cart');
            const subtotalEl = document.getElementById('cart-subtotal');
            const countEl = document.getElementById('cart-item-count');
            
            if (!container) return;

            // Remove existing hidden inputs from form
            const form = document.getElementById('direct-order-form');
            const oldInputs = form.querySelectorAll('.cart-hidden-input');
            oldInputs.forEach(el => el.remove());

            if (posCart.length === 0) {
                container.innerHTML = '<div class="muted" style="text-align: center; font-size: 12px; padding: 10px;">Keranjang kosong. Klik produk di bawah untuk menambahkan.</div>';
                subtotalEl.innerText = 'Rp 0';
                countEl.innerText = '0 Item';
                if (submitBtn) submitBtn.disabled = true;
                return;
            }

            container.innerHTML = '';
            let subtotal = 0;
            let totalItems = 0;

            posCart.forEach((item, index) => {
                subtotal += item.price * item.quantity;
                totalItems += item.quantity;

                // Render list item
                const itemDiv = document.createElement('div');
                itemDiv.style.display = 'flex';
                itemDiv.style.justifyContent = 'space-between';
                itemDiv.style.alignItems = 'center';
                itemDiv.style.padding = '6px 8px';
                itemDiv.style.background = 'rgba(255,255,255,0.02)';
                itemDiv.style.borderRadius = '4px';
                itemDiv.style.fontSize = '12px';

                itemDiv.innerHTML = `
                    <div style="flex: 1; padding-right: 10px;">
                        <strong>${item.name}</strong>
                        <div class="muted" style="font-size: 10px;">@Rp ${item.price.toLocaleString('id-ID')}</div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button" onclick="removeFromPOSCart('${item.type}', ${item.id})" class="btn" style="padding: 2px 6px; font-size: 10px; min-height: unset; height: auto; border:none; cursor:pointer;">-</button>
                        <span style="font-weight: 700; color: var(--text-gold);">${item.quantity}</span>
                        <button type="button" onclick="addToPOSCart('${item.type}', ${item.id}, '', 0)" class="btn" style="padding: 2px 6px; font-size: 10px; min-height: unset; height: auto; border:none; cursor:pointer;">+</button>
                        <button type="button" onclick="removeAllFromPOSCart('${item.type}', ${item.id})" class="btn" style="padding: 2px 6px; font-size: 10px; min-height: unset; height: auto; background: rgba(239,68,68,0.1); color: #ef4444; margin-left: 4px; border:none; cursor:pointer;">✕</button>
                    </div>
                `;
                container.appendChild(itemDiv);

                // Add hidden inputs to form
                const idName = item.type === 'product' ? 'product_id' : 'menu_id';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.className = 'cart-hidden-input';
                idInput.name = `items[${index}][${idName}]`;
                idInput.value = item.id;
                form.appendChild(idInput);

                const qtyInput = document.createElement('input');
                qtyInput.type = 'hidden';
                qtyInput.className = 'cart-hidden-input';
                qtyInput.name = `items[${index}][quantity]`;
                qtyInput.value = item.quantity;
                form.appendChild(qtyInput);
            });

            subtotalEl.innerText = 'Rp ' + subtotal.toLocaleString('id-ID');
            countEl.innerText = totalItems + ' Item';
            if (submitBtn) submitBtn.disabled = false;
        }

        function filterPOSItems() {
            const query = document.getElementById('pos-search-input').value.toLowerCase();
            const cards = document.querySelectorAll('.pos-item-card');
            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                if (name.includes(query)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Start background check on load if code exists
        if (activePairingCode) {
            startBackgroundPolling(activePairingCode);
        } else {
            // Auto request code if we are in the pembayaran section and have the inline panel
            const hasInlineEl = document.getElementById('scanner-inline-card');
            if (hasInlineEl) {
                requestNewPairingCode();
            }
        }

        // Check query parameter active_tab on load
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const activeTab = urlParams.get('active_tab') || @json($defaultCashierTab);
            switchCashierTab(activeTab === 'antrean' ? 'antrean' : 'pesanan');
            if (@json($section) === 'pembayaran') {
                refreshCashierWaitingOrders();
                setInterval(refreshCashierWaitingOrders, 5000);
            }
        });
    </script>

    <!-- Modal: Pairing Scanner HP -->
    <div id="scanner-pairing-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(450px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; background: var(--surface); border: 1px solid var(--line); border-radius: 12px; box-shadow: var(--shadow);">
            <h3 style="color: var(--ink);">📲 Hubungkan Scanner HP</h3>
            <button onclick="closeScannerPairingModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--muted); cursor:pointer;">✕</button>
            
            <div style="text-align: center; display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                <p class="muted" style="font-size: 13px; color: var(--muted);">Buka scanner nirkabel di HP Anda untuk melakukan scan barcode ready-made product langsung ke antrean pembayaran POS.</p>
                
                <div style="background: var(--cream-soft); padding: 16px; border-radius: 8px; border: 1px solid var(--line);">
                    <div style="font-size: 11px; text-transform: uppercase; font-weight: 700; color: var(--muted); letter-spacing: 0.5px;">Kode Pairing POS</div>
                    <div id="pairing-code-display" style="font-size: 32px; font-weight: 800; color: var(--text-gold); font-family: monospace; letter-spacing: 4px; margin: 8px 0;">------</div>
                    <p style="font-size: 12px; color: var(--ink);">Masukkan kode di atas pada halaman <a href="{{ route('scanner.index') }}" target="_blank" style="color: var(--text-gold); font-weight: 700; text-decoration: underline;">/scanner</a> di ponsel Anda.</p>
                </div>
                
                <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                    <div id="scanner-qrcode-container" style="background: white; padding: 10px; border-radius: 8px; border: 1px solid var(--line); display: inline-block; width: 150px; height: 150px;">
                        <div id="qrcode-image-placeholder" style="width:130px; height:130px; display:flex; align-items:center; justify-content:center; color: #333; font-size: 11px; font-weight: 600;">Memuat QR...</div>
                    </div>
                    <span style="font-size: 11px; color: var(--muted);">Scan QR di atas dengan kamera ponsel Anda untuk terhubung otomatis</span>
                </div>
                
                <div id="pairing-status-display" style="padding: 10px; border-radius: 6px; font-size: 12px; font-weight: 700; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2);">
                    🔴 Menunggu Koneksi HP...
                </div>
                
                <button onclick="requestNewPairingCode()" class="btn btn-outline" style="font-size: 12px; border: 1px solid var(--line); color: var(--ink); padding: 8px; cursor: pointer;">🔄 Regenerate Code</button>
            </div>
        </div>
    </div>

    <!-- Toast Notification Container -->
    <div id="cashier-toast" style="position: fixed; bottom: 24px; right: 24px; background: var(--bg-surface); border: 2px solid var(--text-gold); border-radius: 10px; padding: 16px 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); display: none; align-items: center; gap: 12px; z-index: 1100; backdrop-filter: blur(10px);">
        <span style="font-size: 20px;">🔔</span>
        <div>
            <strong style="color: var(--text-gold); font-size: 14px; display: block; margin-bottom: 2px;">Pesanan Baru Masuk!</strong>
            <span id="cashier-toast-message" style="color: var(--text-main); font-size: 12px;">Ada pesanan baru lewat scan meja.</span>
        </div>
    </div>
</x-layouts.app>
