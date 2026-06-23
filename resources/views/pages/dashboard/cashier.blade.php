<x-layouts.app title="Cashier Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>CafeFlow</span></a>
                <nav class="side-nav" aria-label="Navigasi Kasir">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.cashier') }}">📊 Dashboard</a>
                    <a class="{{ $section === 'order-masuk' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', 'order-masuk') }}">📥 Order Masuk</a>
                    <a class="{{ $section === 'pembayaran' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', 'pembayaran') }}">💳 Pembayaran</a>
                    <a class="{{ $section === 'riwayat' ? 'active' : '' }}" href="{{ route('dashboard.cashier.section', 'riwayat') }}">🕒 Riwayat Transaksi</a>
                    <div style="margin: 10px 0; border-top: 1px solid rgba(255,255,255,0.05);"></div>
                    <a href="{{ route('staff.profile') }}">👤 Profil Saya</a>
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
                        <a class="btn" href="{{ route('landing') }}">Landing Page</a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; color: #10b981; font-size: 14px;">
                        🎉 {{ session('success') }}
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
                    <div class="panel" style="flex: 2;">
                        <h3>📥 Antrean Order Baru (Menunggu Pembayaran)</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Silakan konfirmasi metode pembayaran untuk memasukkan order ke queue barista.</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            @forelse ($newOrders as $order)
                                <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 16px; border-radius: 10px; display: flex; flex-direction: column; gap: 12px;">
                                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 8px;">
                                        <div>
                                            <span style="font-size: 15px; font-weight: 800; color: var(--text-gold);">{{ $order->invoice_number }}</span>
                                            <span style="font-size: 13px; font-weight: 700; margin-left: 8px; color: var(--text-main);">🪑 Meja {{ $order->table ? $order->table->code : 'N/A' }}</span>
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
                                                    <span>• {{ $item->menu ? $item->menu->name : 'Item' }} <strong style="color: var(--text-gold);">x{{ $item->quantity }}</strong></span>
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
                                            <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 12px; font-weight: 700; background: #10b981; border: none; color: white;">💵 Konfirmasi Bayar</button>
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

                    <!-- Riwayat Pembayaran Terbaru -->
                    <div class="panel" id="riwayat">
                        <h3>🕒 Riwayat Pembayaran Terbaru</h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Transaksi yang berhasil diproses hari ini.</p>
                        
                        <div style="display: flex; flex-direction: column; gap: 10px; max-height: 520px; overflow-y: auto; padding-right: 4px;">
                            @forelse ($payments as $pay)
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.02); border-radius: 6px;">
                                    <div>
                                        <strong>{{ $pay->order ? $pay->order->invoice_number : 'INV-PAY' }}</strong>
                                        <div class="muted" style="font-size: 11px;">Meja {{ $pay->order && $pay->order->table ? $pay->order->table->code : 'N/A' }} · {{ $pay->paid_at ? $pay->paid_at->format('H:i') : $pay->created_at->format('H:i') }}</div>
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
                </section>
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
        });
    </script>
</x-layouts.app>
