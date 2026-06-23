<x-layouts.app title="Data Karyawan - CafeFlow">
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">CF</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Sidebar">
                    @if ($authUser['role'] === 'owner')
                        <a href="{{ route('dashboard.owner') }}">📊 Dashboard</a>
                        <a class="active" href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                        <a href="{{ route('dashboard.owner.section', 'penjualan') }}">📊 Laporan</a>
                        <a href="{{ route('admin.inventory.index') }}">📦 Stok Bahan</a>
                        <a href="{{ route('dashboard.owner.section', 'approval') }}">🛡️ Approval</a>
                    @elseif ($authUser['role'] === 'super_admin')
                        <a href="{{ route('dashboard.super-admin') }}">📊 Dashboard</a>
                        <a class="active" href="{{ route('owner.employees') }}">👤 Kelola Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Kelola Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Kelola Payroll</a>
                    @else
                        <a href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                        <a class="active" href="{{ route('owner.employees') }}">👤 Karyawan</a>
                        <a href="{{ route('owner.attendance') }}">📅 Absensi</a>
                        <a href="{{ route('owner.payroll') }}">💵 Payroll</a>
                    @endif
                    
                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>👑</span>
                            <span>{{ $authUser['name'] }} ({{ ucfirst($authUser['role']) }})</span>
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

            <!-- Main Content -->
            <main class="content">
                <!-- Page Head -->
                <div class="page-head">
                    <div>
                        <span class="eyebrow">{{ ucfirst($authUser['role']) }} Workspace</span>
                        <h1>Data & Manajemen Karyawan</h1>
                        <p class="muted">Kelola data lengkap karyawan, jabatan, penugasan cabang, status, dan setting gaji pokok.</p>
                    </div>
                    <div class="actions">
                        <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                        <a class="btn" href="{{ route('landing') }}">Landing Page</a>
                    </div>
                </div>

                <!-- Session Flash Messages -->
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

                <!-- Filters and Search Panel -->
                <div class="panel" style="margin-bottom: 24px;">
                    <form action="{{ route('owner.employees') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end;">
                        <div style="flex: 1.5; min-width: 200px; display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Cari Karyawan</label>
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, hp..." style="padding: 10px 14px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none; width: 100%;">
                        </div>
                        
                        @if ($authUser['role'] !== 'admin')
                            <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; gap: 4px;">
                                <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Cabang</label>
                                <select name="branch_id" style="padding: 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none; width:100%;">
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $br)
                                        <option value="{{ $br->id }}" {{ request('branch_id') == $br->id ? 'selected' : '' }}>{{ $br->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Role</label>
                            <select name="role" style="padding: 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none; width:100%;">
                                <option value="">Semua Role</option>
                                @foreach ($roles as $val => $lbl)
                                    <option value="{{ $val }}" {{ request('role') == $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div style="flex: 1; min-width: 140px; display: flex; flex-direction: column; gap: 4px;">
                            <label style="font-size: 12px; font-weight: 700; color: var(--text-muted);">Status</label>
                            <select name="status" style="padding: 10px; border-radius: 6px; background: var(--surface); border: 1px solid var(--line); color: var(--ink); font-size: 13px; outline: none; width:100%;">
                                <option value="">Semua Status</option>
                                <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="Cuti" {{ request('status') == 'Cuti' ? 'selected' : '' }}>Cuti</option>
                                <option value="Resign" {{ request('status') == 'Resign' ? 'selected' : '' }}>Resign</option>
                            </select>
                        </div>

                        <div style="display: flex; gap: 10px;">
                            <button type="submit" class="btn btn-primary" style="font-size: 13px; padding: 10px 16px;">Cari</button>
                            <a href="{{ route('owner.employees') }}" class="btn" style="font-size: 13px; padding: 10px 14px;">Reset</a>
                        </div>
                    </form>
                </div>

                <!-- Table View -->
                <div class="panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h3>👤 Daftar Staf & Karyawan Kafe</h3>
                        @if ($authUser['role'] !== 'admin')
                            <button onclick="openAddEmployeeModal()" class="btn btn-gold" style="font-size: 13px; padding: 8px 16px;">+ Tambah Karyawan</button>
                        @endif
                    </div>

                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                            <thead>
                                <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                    <th style="padding: 12px 10px;">Foto</th>
                                    <th style="padding: 12px 10px;">Karyawan</th>
                                    <th style="padding: 12px 10px;">Kontak & HP</th>
                                    <th style="padding: 12px 10px;">Role</th>
                                    <th style="padding: 12px 10px;">Cabang</th>
                                    <th style="padding: 12px 10px;">Gaji Pokok</th>
                                    <th style="padding: 12px 10px;">Gabung</th>
                                    <th style="padding: 12px 10px;">Status</th>
                                    <th style="padding: 12px 10px; text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($employees as $emp)
                                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                        <td style="padding: 10px;">
                                            <div style="width: 40px; height: 40px; border-radius: 50%; background: var(--line); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid rgba(199,154,75,0.2);">
                                                @if ($emp->photo_path)
                                                    <img src="{{ $emp->photo_path }}" alt="Photo" style="width: 100%; height: 100%; object-fit: cover;">
                                                @else
                                                    <span style="font-size: 18px; font-weight:700; color:var(--text-gold);">{{ substr($emp->name, 0, 1) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td style="padding: 10px; font-weight: 700;">
                                            {{ $emp->name }}
                                            <div class="muted" style="font-size: 11px; font-weight: 500;">{{ $emp->email }}</div>
                                        </td>
                                        <td style="padding: 10px;">
                                            {{ $emp->phone ?? '-' }}
                                            <div class="muted" style="font-size: 11px;">{{ Str::limit($emp->address, 20) }}</div>
                                        </td>
                                        <td style="padding: 10px; color: var(--text-gold); font-weight: 600;">
                                            {{ $roles[$emp->role] ?? ucfirst($emp->role) }}
                                        </td>
                                        <td style="padding: 10px; font-weight: 600;">
                                            🏢 {{ $emp->branch ? $emp->branch->name : 'Global' }}
                                        </td>
                                        <td style="padding: 10px; font-weight: 700;">
                                            @if ($authUser['role'] === 'admin')
                                                <span class="muted" style="font-style: italic; font-weight:500;">Dibatasi</span>
                                            @else
                                                Rp {{ number_format($emp->basic_salary, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td style="padding: 10px;">{{ \Carbon\Carbon::parse($emp->joined_at)->translatedFormat('d M Y') }}</td>
                                        <td style="padding: 10px;">
                                            @php
                                                $colorMap = [
                                                    'Aktif' => ['rgba(16, 185, 129, 0.1)', '#10b981'],
                                                    'Nonaktif' => ['rgba(239, 68, 68, 0.1)', '#ef4444'],
                                                    'Cuti' => ['rgba(245, 158, 11, 0.1)', '#f59e0b'],
                                                    'Resign' => ['rgba(100, 116, 139, 0.1)', '#64748b']
                                                ];
                                                $colors = $colorMap[$emp->status] ?? ['rgba(255,255,255,0.05)', 'var(--text-muted)'];
                                            @endphp
                                            <span class="pill" style="font-size: 10px; background: {{ $colors[0] }}; color: {{ $colors[1] }}; border: none; font-weight:800; padding: 4px 8px;">
                                                {{ $emp->status }}
                                            </span>
                                        </td>
                                        <td style="padding: 10px; text-align: right;">
                                            @if ($authUser['role'] === 'admin')
                                                <!-- Admin cannot edit / delete -->
                                                <button class="btn" disabled style="font-size: 11px; padding: 4px 8px; opacity:0.5; cursor: not-allowed;">Dibatasi</button>
                                            @else
                                                <button onclick='openEditEmployeeModal({!! json_encode($emp) !!})' class="btn" style="font-size: 11px; padding: 4px 8px; border-radius: 4px;">Edit</button>
                                                <form action="{{ route('owner.employees.destroy', $emp->id) }}" method="POST" style="margin: 0; display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;" onclick="return confirm('Apakah Anda yakin ingin menghapus data karyawan ini beserta akun loginnya?')">Hapus</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="muted" style="padding: 40px; text-align: center;">Tidak ada karyawan terdaftar sesuai filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal 1: Add Employee -->
    <div id="add-employee-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(600px, 94%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; max-height:90%; overflow-y:auto;">
            <h3>👤 Tambah Karyawan Baru</h3>
            <button onclick="closeAddEmployeeModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            <p class="muted" style="font-size:12px; margin-top:-8px;">Sistem akan otomatis membuatkan akun login staf untuk karyawan baru ini dengan password bawaan <code>password</code>.</p>
            
            <form action="{{ route('owner.employees.store') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nama Lengkap</label>
                        <input type="text" name="name" required placeholder="misal: Andi Wijaya" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Email (Unik)</label>
                        <input type="email" name="email" required placeholder="misal: andi@cafeflow.test" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nomor HP</label>
                        <input type="text" name="phone" placeholder="misal: 0812345678" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Tanggal Bergabung</label>
                        <input type="date" name="joined_at" required value="{{ date('Y-m-d') }}" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Alamat Lengkap</label>
                    <input type="text" name="address" placeholder="misal: Jl. Mawar Indah No. 12" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Role / Jabatan</label>
                        <select name="role" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="kasir">Kasir</option>
                            <option value="barista">Barista</option>
                            <option value="admin">Admin</option>
                            <option value="pelayan">Pelayan</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Cabang Penempatan</label>
                        <select name="branch_id" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            @foreach ($branches as $br)
                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Gaji Pokok (Rupiah)</label>
                        <input type="number" name="basic_salary" required placeholder="misal: 3500000" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Status</label>
                        <select name="status" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Resign">Resign</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Foto Profil (Opsional)</label>
                    <input type="file" name="photo" accept="image/*" style="font-size:12px; color:var(--text-muted);">
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 800; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Karyawan Baru</button>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Employee -->
    <div id="edit-employee-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(600px, 94%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; max-height:90%; overflow-y:auto;">
            <h3>👤 Edit Data Karyawan</h3>
            <button onclick="closeEditEmployeeModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            
            <form id="edit-employee-form" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nama Lengkap</label>
                        <input type="text" id="edit_emp_name" name="name" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Email</label>
                        <input type="email" id="edit_emp_email" name="email" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nomor HP</label>
                        <input type="text" id="edit_emp_phone" name="phone" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Tanggal Bergabung</label>
                        <input type="date" id="edit_emp_joined" name="joined_at" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Alamat Lengkap</label>
                    <input type="text" id="edit_emp_address" name="address" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Role / Jabatan</label>
                        <select id="edit_emp_role" name="role" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="kasir">Kasir</option>
                            <option value="barista">Barista</option>
                            <option value="admin">Admin</option>
                            <option value="pelayan">Pelayan</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Cabang Penempatan</label>
                        <select id="edit_emp_branch" name="branch_id" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            @foreach ($branches as $br)
                                <option value="{{ $br->id }}">{{ $br->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Gaji Pokok (Rupiah)</label>
                        <input type="number" id="edit_emp_salary" name="basic_salary" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Status</label>
                        <select id="edit_emp_status" name="status" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="Aktif">Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                            <option value="Cuti">Cuti</option>
                            <option value="Resign">Resign</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Ganti Foto Profil (Opsional)</label>
                    <input type="file" name="photo" accept="image/*" style="font-size:12px; color:var(--text-muted);">
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 800; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Script for modals -->
    <script>
        function openAddEmployeeModal() {
            document.getElementById('add-employee-modal').style.display = 'flex';
        }
        function closeAddEmployeeModal() {
            document.getElementById('add-employee-modal').style.display = 'none';
        }

        function openEditEmployeeModal(employee) {
            document.getElementById('edit_emp_name').value = employee.name;
            document.getElementById('edit_emp_email').value = employee.email;
            document.getElementById('edit_emp_phone').value = employee.phone || '';
            document.getElementById('edit_emp_address').value = employee.address || '';
            document.getElementById('edit_emp_joined').value = employee.joined_at;
            document.getElementById('edit_emp_role').value = employee.role;
            document.getElementById('edit_emp_branch').value = employee.branch_id;
            document.getElementById('edit_emp_salary').value = employee.basic_salary;
            document.getElementById('edit_emp_status').value = employee.status;
            
            const form = document.getElementById('edit-employee-form');
            form.action = `/dashboard/owner/employees/${employee.id}/update`;
            
            document.getElementById('edit-employee-modal').style.display = 'flex';
        }
        function closeEditEmployeeModal() {
            document.getElementById('edit-employee-modal').style.display = 'none';
        }
    </script>
</x-layouts.app>

