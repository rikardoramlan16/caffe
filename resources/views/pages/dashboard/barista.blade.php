<x-layouts.app title="Barista Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>CafeFlow</span></a>
                <nav class="side-nav" aria-label="Navigasi Barista">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.barista') }}">📊 Dashboard</a>
                    <a class="{{ $section === 'queue-paid' ? 'active' : '' }}" href="{{ route('dashboard.barista.section', 'queue-paid') }}">📥 Queue Pesanan PAID</a>
                    <a class="{{ $section === 'queue-making' ? 'active' : '' }}" href="{{ route('dashboard.barista.section', 'queue-making') }}">☕ Sedang Dibuat MAKING</a>
                    <a class="{{ $section === 'riwayat' ? 'active' : '' }}" href="{{ route('dashboard.barista.section', 'riwayat') }}">🕒 Riwayat Produksi</a>
                    <a href="{{ route('admin.inventory.index') }}">📦 Ketersediaan Stok</a>
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
                        <span class="eyebrow">Realtime Production Queue</span>
                        <h1>Barista Kitchen Station</h1>
                        <p class="muted">Monitor pesanan masuk yang lunas, kelola proses pembuatan minuman, dan tandai minuman siap diantar.</p>
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

                <!-- Widgets Barista -->
                <section id="barista-summary" class="grid grid-4" aria-label="Widget utama">
                    <article class="metric-card">
                        <span class="pill" style="background: #3b82f6; color: white;">Paid Queue</span>
                        <strong>{{ $metrics['queue_active'] }}</strong>
                        <span>Queue Aktif</span>
                        <p class="muted">Menunggu dibuat</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: var(--text-gold); color: var(--bg-app);">Brewing</span>
                        <strong>{{ $metrics['making'] }}</strong>
                        <span>Sedang Dibuat</span>
                        <p class="muted">Proses pengerjaan barista</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill" style="background: #10b981; color: white;">Siap Antar</span>
                        <strong>{{ $metrics['ready'] }}</strong>
                        <span>Siap Diambil</span>
                        <p class="muted">Menunggu waiter antar ke meja</p>
                    </article>
                    <article class="metric-card">
                        <span class="pill">Produktivitas</span>
                        <strong>{{ $metrics['drinks_today'] }} cup</strong>
                        <span>Total Minuman Hari Ini</span>
                        <p class="muted">Di luar kategori pastry</p>
                    </article>
                </section>

                <section id="barista-queues" class="split" style="margin-top: 24px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <!-- Daftar Pesanan PAID -->
                    <div class="panel" id="queue-paid">
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: #3b82f6; width: 10px; height: 10px; border-radius: 50%; display: inline-block;"></span>
                            Daftar Pesanan PAID (Antrean Masuk)
                        </h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Pesanan yang telah lunas dan siap diproduksi.</p>

                        <div style="display: flex; flex-direction: column; gap: 14px; max-height: 550px; overflow-y: auto;">
                            @forelse ($paidOrders as $order)
                                <div style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); padding: 14px; border-radius: 8px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 6px; margin-bottom: 8px;">
                                        <strong>{{ $order->invoice_number }} · Meja {{ $order->table ? $order->table->code : 'N/A' }}</strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">{{ $order->created_at->format('H:i') }}</span>
                                    </div>
                                    <div style="font-size: 13px; display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px;">
                                        @foreach ($order->items as $item)
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>• {{ $item->menu ? $item->menu->name : 'Item' }} <strong style="color: var(--text-gold);">x{{ $item->quantity }}</strong></span>
                                                @if ($item->note)
                                                    <span class="muted" style="font-size: 11px; font-style: italic;">({{ $item->note }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($order->customer_note)
                                            <div style="font-size: 12px; background: rgba(255,255,255,0.02); padding: 4px; border-radius: 4px; font-style: italic; color: var(--text-muted); margin-top: 4px;">
                                                Catatan: {{ $order->customer_note }}
                                            </div>
                                        @endif
                                    </div>
                                    <form action="{{ route('orders.status', $order->id) }}" method="POST" style="margin: 0; text-align: right;">
                                        @csrf
                                        <input type="hidden" name="status" value="MAKING">
                                        <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 14px; font-weight: 700; width: 100%;">
                                            ⚡ START MAKING
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="muted" style="text-align: center; padding: 40px; background: rgba(255,255,255,0.01); border-radius: 8px;">
                                    😴 Belum ada antrean pesanan baru.
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Daftar Pesanan MAKING -->
                    <div class="panel" id="queue-making">
                        <h3 style="display: flex; align-items: center; gap: 8px;">
                            <span style="background: var(--text-gold); width: 10px; height: 10px; border-radius: 50%; display: inline-block;"></span>
                            Daftar Pesanan MAKING (Sedang Dibuat)
                        </h3>
                        <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Minuman yang sedang diseduh/dibuat oleh barista.</p>

                        <div style="display: flex; flex-direction: column; gap: 14px; max-height: 550px; overflow-y: auto;">
                            @forelse ($makingOrders as $order)
                                <div style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); padding: 14px; border-radius: 8px; border-left: 3px solid var(--text-gold);">
                                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255,255,255,0.03); padding-bottom: 6px; margin-bottom: 8px;">
                                        <strong>{{ $order->invoice_number }} · Meja {{ $order->table ? $order->table->code : 'N/A' }}</strong>
                                        <span style="font-size: 11px; color: var(--text-muted);">Mulai: {{ $order->updated_at->format('H:i') }}</span>
                                    </div>
                                    <div style="font-size: 13px; display: flex; flex-direction: column; gap: 4px; margin-bottom: 12px;">
                                        @foreach ($order->items as $item)
                                            <div style="display: flex; justify-content: space-between;">
                                                <span>• {{ $item->menu ? $item->menu->name : 'Item' }} <strong style="color: var(--text-gold);">x{{ $item->quantity }}</strong></span>
                                                @if ($item->note)
                                                    <span class="muted" style="font-size: 11px; font-style: italic;">({{ $item->note }})</span>
                                                @endif
                                            </div>
                                        @endforeach
                                        @if ($order->customer_note)
                                            <div style="font-size: 12px; background: rgba(255,255,255,0.02); padding: 4px; border-radius: 4px; font-style: italic; color: var(--text-muted); margin-top: 4px;">
                                                Catatan: {{ $order->customer_note }}
                                            </div>
                                        @endif
                                    </div>
                                    <form action="{{ route('orders.status', $order->id) }}" method="POST" style="margin: 0; text-align: right;">
                                        @csrf
                                        <input type="hidden" name="status" value="READY">
                                        <button type="submit" class="btn btn-primary" style="font-size: 12px; padding: 6px 14px; font-weight: 700; width: 100%; background: #10b981; border: none; color: white;">
                                            ✅ READY TO SERVE
                                        </button>
                                    </form>
                                </div>
                            @empty
                                <div class="muted" style="text-align: center; padding: 40px; background: rgba(255,255,255,0.01); border-radius: 8px;">
                                    ☕ Stasiun bersih. Silakan bersantai sejenak atau bersiap untuk order berikutnya.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <!-- Riwayat Produksi (Pesanan DONE Hari Ini) -->
                <section class="panel" id="riwayat" style="margin-top: 24px;">
                    <h3>🕒 Riwayat Produksi Selesai Hari Ini</h3>
                    <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 16px;">Daftar pesanan yang telah sukses diselesaikan oleh barista hari ini.</p>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                    <th style="padding: 10px;">Nomor Invoice</th>
                                    <th style="padding: 10px;">Meja</th>
                                    <th style="padding: 10px;">Detail Minuman</th>
                                    <th style="padding: 10px;">Selesai Pada</th>
                                    <th style="padding: 10px; text-align: right;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($doneOrders as $done)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <td style="padding: 10px; font-weight: 700; color: var(--text-gold);">{{ $done->invoice_number }}</td>
                                        <td style="padding: 10px;">Meja {{ $done->table ? $done->table->code : 'N/A' }}</td>
                                        <td style="padding: 10px;">
                                            @foreach ($done->items as $item)
                                                <span>{{ $item->menu ? $item->menu->name : 'Item' }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}</span>
                                            @endforeach
                                        </td>
                                        <td style="padding: 10px; color: var(--text-muted);">{{ $done->updated_at->format('H:i') }}</td>
                                        <td style="padding: 10px; text-align: right;">
                                            <span class="pill" style="background: rgba(16, 185, 129, 0.1); color: #10b981; font-size: 10px; font-weight: 700;">DONE</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="muted" style="padding: 20px; text-align: center;">Belum ada pesanan berstatus DONE yang diselesaikan hari ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = @json($section);
            if (section === 'dashboard') {
                document.getElementById('barista-queues')?.style.setProperty('display', 'none');
                document.getElementById('riwayat')?.style.setProperty('display', 'none');
                return;
            }

            document.getElementById('barista-summary')?.style.setProperty('display', 'none');
            if (section === 'riwayat') {
                document.getElementById('barista-queues')?.style.setProperty('display', 'none');
            } else {
                document.getElementById('riwayat')?.style.setProperty('display', 'none');
            }
        });
    </script>
</x-layouts.app>
