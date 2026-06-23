<x-layouts.app title="Super Admin Dashboard - CafeFlow">
    @php
        $section = $section ?? 'dashboard';
        $monthlyChartData = $monthlyChartData ?? [];
        $monthlyChartValues = is_array($monthlyChartData)
            ? array_values($monthlyChartData)
            : (is_object($monthlyChartData) && method_exists($monthlyChartData, 'toArray') ? array_values($monthlyChartData->toArray()) : []);
        $maxVal = count($monthlyChartValues) ? max(1, max($monthlyChartValues)) : 1;
        $settingValue = fn (string $key, ?string $fallback = '') => optional($settings->get($key))->value ?? $fallback;
    @endphp

    <div class="app-shell">
        <div class="app-layout">
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>CafeFlow</span></a>
                <nav class="side-nav" aria-label="Navigasi Super Admin">
                    <a class="{{ $section === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard.super-admin') }}">
                        <strong>Dashboard</strong>
                    </a>
                    <a class="{{ $section === 'user' ? 'active' : '' }}" href="{{ route('dashboard.super-admin.section', 'user') }}">
                        <strong>User</strong>
                    </a>
                    <a class="{{ $section === 'roles' ? 'active' : '' }}" href="{{ route('dashboard.super-admin.section', 'roles') }}">
                        <strong>Role & Permission</strong>
                    </a>
                    <a class="{{ $section === 'monitoring' ? 'active' : '' }}" href="{{ route('dashboard.super-admin.section', 'monitoring') }}">
                        <strong>Monitoring</strong>
                    </a>
                    <a class="{{ $section === 'pengaturan' ? 'active' : '' }}" href="{{ route('dashboard.super-admin.section', 'pengaturan') }}">
                        <strong>Pengaturan Sistem</strong>
                    </a>
                    <a class="{{ $section === 'logs' ? 'active' : '' }}" href="{{ route('dashboard.super-admin.section', 'logs') }}">
                        <strong>System Logs</strong>
                    </a>
                    <a href="{{ route('admin.inventory.index') }}">
                        <strong>Gudang Stok</strong>
                    </a>

                    <div style="margin: 15px 12px 5px 12px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 10px; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); font-weight: 700;">Dashboard Lain</div>
                    <a href="{{ route('dashboard.admin') }}">
                        <strong>💼 Admin Toko</strong>
                    </a>
                    <a href="{{ route('dashboard.cashier') }}">
                        <strong>💰 Kasir</strong>
                    </a>
                    <a href="{{ route('dashboard.barista') }}">
                        <strong>☕ Barista</strong>
                    </a>
                    <a href="{{ route('dashboard.owner') }}">
                        <strong>👑 Owner</strong>
                    </a>

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
                        <div style="padding: 10px 12px; font-size: 13px; color: var(--gold); font-weight: 800;">
                            {{ $user['name'] }}
                        </div>
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" class="btn" style="width: 100%; justify-content: flex-start; background: rgba(239,68,68,0.12); color: #ef4444; border: 0;">
                                Keluar
                            </button>
                        </form>
                    </div>
                </nav>
            </aside>

            <main class="content">
                <div class="page-head">
                    <div>
                        <span class="eyebrow">Super Admin Workspace</span>
                        <h1>{{ [
                            'dashboard' => 'Dashboard',
                            'user' => 'User',
                            'roles' => 'Role & Permission',
                            'monitoring' => 'Monitoring',
                            'pengaturan' => 'Pengaturan Sistem',
                            'logs' => 'System Logs',
                        ][$section] ?? 'Dashboard' }}</h1>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">T</button>
                        <a class="btn" href="{{ route('landing') }}">Landing Page</a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="panel" style="border-color: rgba(16,185,129,0.28); color: #10b981; margin-bottom: 18px;">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="panel" style="border-color: rgba(239,68,68,0.28); color: #ef4444; margin-bottom: 18px;">
                        {{ session('error') }}
                    </div>
                @endif

                <section id="dashboard-section" data-module="dashboard">
                    <div class="grid grid-4" aria-label="KPI">
                        <article class="metric-card">
                            <span class="pill">User</span>
                            <strong>{{ $metrics['users'] }}</strong>
                            <span>Total User</span>
                        </article>
                        <article class="metric-card">
                            <span class="pill">Order</span>
                            <strong>{{ number_format($metrics['transactions'], 0, ',', '.') }}</strong>
                            <span>Total Transaksi</span>
                        </article>
                        <article class="metric-card">
                            <span class="pill">Pendapatan</span>
                            <strong>Rp {{ number_format($metrics['revenue'], 0, ',', '.') }}</strong>
                            <span>Total Pendapatan</span>
                        </article>
                        <article class="metric-card">
                            <span class="pill">Stok</span>
                            <strong>{{ $inventoryStats['items'] }}</strong>
                            <span>{{ $inventoryStats['low_stock'] }} rendah, {{ $inventoryStats['out_of_stock'] }} kosong</span>
                        </article>
                    </div>

                    <div class="split">
                        <div class="panel">
                            <h3>Grafik Penjualan Bulanan</h3>
                            <div class="chart" style="height: 220px;">
                                @foreach ($monthlyChartData as $month => $revenue)
                                    @php
                                        $pct = $maxVal > 0 ? ($revenue / $maxVal) * 85 + 5 : 0;
                                    @endphp
                                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 8px; height: 100%; justify-content: flex-end;">
                                        <span class="bar" style="height: {{ $pct }}%; width: 100%;" title="Rp {{ number_format($revenue, 0, ',', '.') }}"></span>
                                        <span style="font-size: 11px; font-weight: 700;">{{ $month }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="panel">
                            <h3>Status Order</h3>
                            <div style="display: grid; gap: 10px;">
                                @forelse ($orderStatusCounts as $status => $total)
                                    <div class="order-row">
                                        <strong>{{ $status }}</strong>
                                        <span class="pill">{{ $total }}</span>
                                    </div>
                                @empty
                                    <div class="order-row"><strong>Belum ada order</strong><span class="pill">0</span></div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="split">
                        <div class="panel">
                            <h3>Aktivitas User</h3>
                            <div style="display: grid; gap: 10px;">
                                @foreach ($activeUsers->take(8) as $usr)
                                    <div class="order-row">
                                        <div>
                                            <strong>{{ $usr->name }}</strong>
                                            <div class="muted" style="font-size: 12px;">{{ str($usr->role)->replace('_', ' ')->title() }}</div>
                                        </div>
                                        <span class="pill">{{ $usr->activity_logs_count }} aksi</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="panel">
                            <h3>Ringkasan Role</h3>
                            <div style="display: grid; gap: 10px;">
                                @foreach ($roles as $role)
                                    <div class="order-row">
                                        <strong>{{ $role->label ?? str($role->name)->replace('_', ' ')->title() }}</strong>
                                        <span class="pill">{{ $role->users_count }} user</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>

                <section id="user-section" data-module="user">
                    <div class="panel">
                        <h3>Tambah User</h3>
                        <form action="{{ route('super-admin.users.store') }}" method="POST" style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)) auto; gap: 10px; align-items: end;">
                            @csrf
                            <label>Nama<input name="name" required style="width: 100%; margin-top: 6px;"></label>
                            <label>Email<input name="email" type="email" required style="width: 100%; margin-top: 6px;"></label>
                            <label>Role
                                <select name="role_id" required style="width: 100%; margin-top: 6px;">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}">{{ $role->label ?? $role->name }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label>Password<input name="password" type="password" required style="width: 100%; margin-top: 6px;"></label>
                            <button class="btn btn-primary" type="submit">Tambah</button>
                        </form>
                    </div>

                    <div class="panel" style="margin-top: 16px;">
                        <h3>Tabel User</h3>
                        <div style="display: grid; gap: 10px;">
                            @foreach ($users as $item)
                                <div class="order-row" style="align-items: end;">
                                    <form action="{{ route('super-admin.users.update', $item) }}" method="POST" style="display: grid; grid-template-columns: 1.1fr 1.2fr .9fr 1fr auto; gap: 10px; align-items: end; flex: 1;">
                                        @csrf
                                        <label>Nama<input name="name" value="{{ $item->name }}" required style="width: 100%; margin-top: 6px;"></label>
                                        <label>Email<input name="email" type="email" value="{{ $item->email }}" required style="width: 100%; margin-top: 6px;"></label>
                                        <label>Role
                                            <select name="role_id" required style="width: 100%; margin-top: 6px;">
                                                @foreach ($roles as $role)
                                                    <option value="{{ $role->id }}" @selected($item->role_id === $role->id)>{{ $role->label ?? $role->name }}</option>
                                                @endforeach
                                            </select>
                                        </label>
                                        <label>Password Baru<input name="password" type="password" style="width: 100%; margin-top: 6px;"></label>
                                        <button class="btn" type="submit">Simpan</button>
                                    </form>
                                    <form action="{{ route('super-admin.users.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn" type="submit" style="color: #ef4444;">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="roles-section" data-module="roles">
                    <div class="split">
                        <div class="panel">
                            <h3>Tambah Role</h3>
                            <form action="{{ route('super-admin.roles.store') }}" method="POST" style="display: grid; gap: 12px;">
                                @csrf
                                <label>Nama Role<input name="name" required placeholder="contoh: supervisor" style="width: 100%; margin-top: 6px;"></label>
                                <label>Label<input name="label" required placeholder="Supervisor" style="width: 100%; margin-top: 6px;"></label>
                                <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;">
                                    @foreach ($permissions as $permission)
                                        <label class="pill" style="justify-content: flex-start; color: inherit;">
                                            <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}">
                                            {{ $permission->label ?? $permission->name }}
                                        </label>
                                    @endforeach
                                </div>
                                <button class="btn btn-primary" type="submit">Tambah Role</button>
                            </form>
                        </div>

                        <div class="panel">
                            <h3>Tambah Permission</h3>
                            <form action="{{ route('super-admin.permissions.store') }}" method="POST" style="display: grid; gap: 12px;">
                                @csrf
                                <label>Nama Permission<input name="name" required placeholder="contoh: export_report" style="width: 100%; margin-top: 6px;"></label>
                                <label>Label<input name="label" required placeholder="Export Report" style="width: 100%; margin-top: 6px;"></label>
                                <button class="btn btn-primary" type="submit">Tambah Permission</button>
                            </form>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px;">
                                @foreach ($permissions as $permission)
                                    <form action="{{ route('super-admin.permissions.destroy', $permission) }}" method="POST" onsubmit="return confirm('Hapus permission ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="pill" type="submit" style="border: 0; cursor: pointer;">{{ $permission->label ?? $permission->name }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="panel" style="margin-top: 16px;">
                        <h3>Matriks Role & Permission</h3>
                        <div style="display: grid; gap: 12px;">
                            @foreach ($roles as $role)
                                <div class="order-row" style="align-items: start;">
                                    <form action="{{ route('super-admin.roles.permissions', $role) }}" method="POST" style="flex: 1;">
                                        @csrf
                                        <div style="display: flex; justify-content: space-between; gap: 12px; align-items: center; margin-bottom: 10px;">
                                            <strong>{{ $role->label ?? $role->name }}</strong>
                                            <span class="pill">{{ $role->users_count }} user</span>
                                        </div>
                                        <div style="display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px;">
                                            @foreach ($permissions as $permission)
                                                <label class="pill" style="justify-content: flex-start; color: inherit;">
                                                    <input type="checkbox" name="permission_ids[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                                                    {{ $permission->label ?? $permission->name }}
                                                </label>
                                            @endforeach
                                        </div>
                                        <button class="btn" type="submit" style="margin-top: 12px;">Simpan Permission</button>
                                    </form>
                                    <form action="{{ route('super-admin.roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Hapus role ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn" type="submit" style="color: #ef4444;">Hapus</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>

                <section id="monitoring-section" data-module="monitoring">
                    <div class="grid grid-4">
                        <article class="metric-card"><span class="pill">Aktivitas</span><strong>{{ $activities->count() }}</strong><span>Log terbaru</span></article>
                        <article class="metric-card"><span class="pill">User Aktif</span><strong>{{ $activeUsers->where('activity_logs_count', '>', 0)->count() }}</strong><span>User dengan aksi</span></article>
                        <article class="metric-card"><span class="pill">Stok Rendah</span><strong>{{ $inventoryStats['low_stock'] }}</strong><span>Item</span></article>
                        <article class="metric-card"><span class="pill">Stok Kosong</span><strong>{{ $inventoryStats['out_of_stock'] }}</strong><span>Item</span></article>
                    </div>
                    <div class="panel" style="margin-top: 16px;">
                        <h3>Aktivitas Sistem</h3>
                        <div style="display: grid; gap: 10px;">
                            @forelse ($activities as $activity)
                                <div class="order-row">
                                    <div>
                                        <strong>{{ $activity->description }}</strong>
                                        <div class="muted" style="font-size: 12px;">{{ $activity->created_at->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    <span class="pill">{{ $activity->user ? $activity->user->name : 'System' }}</span>
                                </div>
                            @empty
                                <div class="order-row"><strong>Belum ada aktivitas</strong><span class="pill">0</span></div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <section id="pengaturan-section" data-module="pengaturan">
                    <div class="panel">
                        <h3>Konfigurasi Aplikasi</h3>
                        <form action="{{ route('super-admin.settings.update') }}" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px;">
                            @csrf
                            <label>Nama Aplikasi<input name="settings[app_name]" value="{{ $settingValue('app_name', config('app.name')) }}" style="width: 100%; margin-top: 6px;"></label>
                            <label>Service Fee<input name="settings[service_fee]" value="{{ $settingValue('service_fee', '0') }}" style="width: 100%; margin-top: 6px;"></label>
                            <label>Telepon<input name="settings[company_phone]" value="{{ $settingValue('company_phone') }}" style="width: 100%; margin-top: 6px;"></label>
                            <div style="display: flex; flex-direction: column; gap: 6px;">
                                <label style="margin-bottom: 0;">Logo Aplikasi</label>
                                <div style="display: flex; align-items: center; gap: 12px; margin-top: 6px;">
                                    @if ($settingValue('app_logo'))
                                        <img id="app-logo-preview" src="{{ asset($settingValue('app_logo')) }}" alt="Logo Aplikasi" style="height: 38px; width: 38px; border-radius: 6px; object-fit: cover; border: 1px solid rgba(255,255,255,0.08); background: var(--bg-app);">
                                    @else
                                        <div id="app-logo-preview-placeholder" style="height: 38px; width: 38px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.03); border: 1px dashed rgba(255,255,255,0.1); font-size: 11px; color: var(--text-muted);">No Logo</div>
                                    @endif
                                    <input type="file" name="app_logo" accept="image/*" style="flex: 1; font-size: 13px; color: var(--text-muted);">
                                </div>
                            </div>
                            <label style="grid-column: 1 / -1;">Alamat<textarea name="settings[company_address]" rows="3" style="width: 100%; margin-top: 6px;">{{ $settingValue('company_address') }}</textarea></label>
                            <button class="btn btn-primary" type="submit" style="width: max-content;">Simpan Pengaturan</button>
                        </form>
                    </div>
                </section>

                <section id="logs-section" data-module="logs">
                    <div class="panel">
                        <h3>Tabel Log Aktivitas</h3>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                <thead>
                                    <tr style="text-align: left; border-bottom: 1px solid var(--line);">
                                        <th style="padding: 10px;">Waktu</th>
                                        <th style="padding: 10px;">Level</th>
                                        <th style="padding: 10px;">Source</th>
                                        <th style="padding: 10px;">Pesan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($systemLogs as $log)
                                        <tr style="border-bottom: 1px solid var(--line);">
                                            <td style="padding: 10px;">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                            <td style="padding: 10px;"><span class="pill">{{ strtoupper($log->level) }}</span></td>
                                            <td style="padding: 10px;">{{ $log->source }}</td>
                                            <td style="padding: 10px;">{{ $log->message }}</td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" style="padding: 16px;">Belum ada log sistem.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const section = @json($section);
            document.querySelectorAll('[data-module]').forEach((el) => {
                el.style.display = el.dataset.module === section ? '' : 'none';
            });
        });
    </script>
</x-layouts.app>
