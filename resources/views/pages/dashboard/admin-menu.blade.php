<x-layouts.app title="Menu Management - CafeFlow">
    @php
        $section = request('section', 'menu');
    @endphp
    <div class="app-shell">
        <div class="app-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span>Kopi Senja</span></a>
                <nav class="side-nav" aria-label="Navigasi Admin">
                    <a href="{{ route('dashboard.admin') }}">📊 Dashboard</a>
                    <a class="{{ $section === 'menu' ? 'active' : '' }}" href="{{ route('admin.menu.index') }}">🍵 Menu Minuman</a>
                    <a class="{{ $section === 'barang' ? 'active' : '' }}" href="{{ route('admin.menu.index') }}?section=barang">📦 Kelola Barang</a>
                    <a href="{{ route('admin.inventory.index') }}">📦 Gudang Stok</a>
                    <a class="{{ $section === 'kategori' ? 'active' : '' }}" href="{{ route('admin.menu.index') }}?section=kategori">Kategori</a>
                    <a class="{{ $section === 'topping' ? 'active' : '' }}" href="{{ route('admin.menu.index') }}?section=topping">Topping & Add-On</a>
                    <a href="{{ route('admin.inventory.index') }}?section=supplier">Supplier</a>
                    <a href="{{ route('dashboard.admin.section', 'laporan') }}">Laporan</a>

                    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
                        <div style="padding: 10px; font-size: 13px; color: var(--text-gold); display: flex; align-items: center; gap: 8px;">
                            <span>🏢</span>
                            <span>{{ $user['name'] }} (Admin)</span>
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
                        <span class="eyebrow">Admin Workspace</span>
                        <h1>Kelola Produk & Resep</h1>
                        <p class="muted">Tambahkan menu minuman, kelola topping, kategori, ketersediaan produk, dan formulasikan resep bahan baku.</p>
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

                <!-- Panels Grid -->
                <section class="split" style="grid-template-columns: 1fr;">
                    <!-- Left: Products List -->
                    <div class="panel" style="{{ $section === 'barang' ? '' : 'display:none;' }}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <h3>📦 Daftar Barang (Produk Jadi)</h3>
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
                                                    <button onclick='openEditProductModal(@json($p))' class="btn" style="font-size: 11px; padding: 6px 10px; border-radius: 4px; border: 1px solid var(--text-gold); color: var(--text-gold); cursor: pointer; height: 28px; min-height: unset; background: none;">Edit</button>
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

                    <!-- Left: Menu Table list -->
                    <div class="panel" style="{{ $section === 'menu' ? '' : 'display:none;' }}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <h3>🍵 Daftar Menu Minuman Kafe</h3>
                            <button onclick="openAddMenuModal()" class="btn btn-gold" style="font-size: 12px; padding: 6px 12px;">+ Tambah Menu Baru</button>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 13px; color: var(--text-main);">
                                <thead>
                                    <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                        <th style="padding: 10px;">Nama Menu</th>
                                        <th style="padding: 10px;">Kategori</th>
                                        <th style="padding: 10px;">Harga</th>
                                        <th style="padding: 10px;">Status</th>
                                        <th style="padding: 10px;">Resep</th>
                                        <th style="padding: 10px; text-align: right;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($menus as $m)
                                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02);">
                                            <td style="padding: 10px; font-weight: 700;">{{ $m->name }} <p class="muted" style="font-size:11px; font-weight:500; margin: 2px 0 0 0;">{{ $m->description ?? 'Espresso blend & fresh raw ingredients.' }}</p></td>
                                            <td style="padding: 10px; color: var(--text-gold);">{{ $m->category ? $m->category->name : 'N/A' }}</td>
                                            <td style="padding: 10px; font-weight: 700;">Rp {{ number_format($m->price, 0, ',', '.') }}</td>
                                            <td style="padding: 10px;">
                                                <form action="{{ route('admin.menu.toggle', $m->id) }}" method="POST" style="margin:0;">
                                                    @csrf
                                                    <button type="submit" class="pill" style="border:none; cursor:pointer; background: {{ $m->is_available ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)' }}; color: {{ $m->is_available ? '#10b981' : '#ef4444' }}; font-size: 10px; font-weight:800;">
                                                        {{ $m->is_available ? 'Tersedia (Aktif)' : 'Habis (Nonaktif)' }}
                                                    </button>
                                                </form>
                                            </td>
                                            <td style="padding: 10px;">
                                                <button onclick="openRecipeModal({{ $m->id }}, '{{ $m->name }}', {{ json_encode($recipes->get($m->id) ?? []) }})" class="btn" style="font-size: 11px; padding: 4px 8px; border-radius: 4px; border: 1px solid var(--text-gold); color: var(--text-gold);">
                                                    🔬 Resep ({{ $recipes->has($m->id) ? $recipes->get($m->id)->count() : 0 }} bahan)
                                                </button>
                                            </td>
                                            <td style="padding: 10px; text-align: right; display: flex; gap: 6px; justify-content: flex-end;">
                                                <button onclick="openEditMenuModal({{ json_encode($m) }})" class="btn" style="font-size: 11px; padding: 4px 8px; border-radius: 4px;">Edit</button>
                                                <form action="{{ route('admin.menu.destroy', $m->id) }}" method="POST" style="margin: 0; display: inline;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn" style="background: rgba(239, 68, 68, 0.1); color: #ef4444; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;" onclick="return confirm('Apakah Anda yakin ingin menghapus menu ini?')">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="muted" style="padding: 20px; text-align: center;">Belum ada menu terdaftar.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Right: Categories and Toppings -->
                    <div style="display: flex; flex-direction: column; gap: 20px;">
                        <!-- Category Card -->
                        <div class="panel" id="kategori" style="{{ $section === 'kategori' ? '' : 'display:none;' }}">
                            <h3>📁 Kategori Menu</h3>
                            <form action="{{ route('admin.category.store') }}" method="POST" style="margin-top: 14px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px;">
                                @csrf
                                <label for="cat_name" style="font-size: 12px; font-weight: 600;">Tambah Kategori Baru</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" id="cat_name" name="name" required placeholder="misal: Snack, Dessert" style="flex: 1; padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                    <button type="submit" class="btn btn-gold" style="font-size: 12px; padding: 8px 14px;">Simpan</button>
                                </div>
                            </form>

                            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto;">
                                @foreach ($categories as $cat)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(255,255,255,0.02); border-radius: 6px; font-size: 13px;">
                                        <strong>📁 {{ $cat->name }}</strong>
                                        <span class="muted" style="font-size: 11px;">Aktif</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Toppings Card -->
                        <div class="panel" id="topping-addon" style="{{ $section === 'topping' ? '' : 'display:none;' }}">
                            <h3>🍬 Topping Tambahan</h3>
                            <form action="{{ route('admin.topping.store') }}" method="POST" style="margin-top: 14px; margin-bottom: 16px; display: flex; flex-direction: column; gap: 8px;">
                                @csrf
                                <label style="font-size: 12px; font-weight: 600;">Tambah Topping Baru</label>
                                <div style="display: flex; gap: 8px;">
                                    <input type="text" name="name" required placeholder="Nama Topping (mis. Boba)" style="flex: 1.5; padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                    <input type="number" name="price" required placeholder="Harga (mis. 5000)" style="flex: 1; padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                    <button type="submit" class="btn btn-gold" style="font-size: 12px; padding: 8px 12px;">Simpan</button>
                                </div>
                                <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 8px;">
                                    <select name="inventory_id" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                        <option value="">Tidak hubungkan inventory</option>
                                        @foreach ($inventories as $inv)
                                            <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
                                        @endforeach
                                    </select>
                                    <input type="number" step="0.01" min="0" name="inventory_quantity" value="1" placeholder="Qty stok/porsi" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                </div>
                                <label style="font-size: 12px; font-weight: 600;">Hubungkan ke Produk</label>
                                <select name="menu_ids[]" multiple size="4" style="padding: 8px 12px; border-radius: 6px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                    @foreach ($menus as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </form>

                            <div style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto;">
                                @forelse ($toppings as $top)
                                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 12px; background: rgba(255,255,255,0.02); border-radius: 6px; font-size: 13px;">
                                        <div>
                                            <strong>🍬 {{ $top->name }}</strong>
                                            <div class="muted" style="font-size: 11px;">Rp {{ number_format($top->price, 0, ',', '.') }} · {{ $top->is_available ? 'Aktif' : 'Nonaktif' }}</div>
                                            <div class="muted" style="font-size: 11px;">Inventory: {{ $top->inventory ? $top->inventory->name . ' - ' . $top->inventory_quantity . ' ' . $top->inventory->unit . '/porsi' : 'Tidak dihubungkan' }}</div>
                                            <div class="muted" style="font-size: 11px;">Produk: {{ $top->menus->pluck('name')->join(', ') ?: 'Belum dihubungkan' }}</div>
                                        </div>
                                        <button type="button" class="btn" style="font-size: 11px; padding: 4px 8px;" onclick="openEditToppingModal({{ json_encode([
                                            'id' => $top->id,
                                            'name' => $top->name,
                                            'price' => $top->price,
                                            'inventory_id' => $top->inventory_id,
                                            'inventory_quantity' => $top->inventory_quantity,
                                            'is_available' => $top->is_available,
                                            'menu_ids' => $top->menus->pluck('id')->values(),
                                        ]) }})">Edit</button>
                                        <form action="{{ route('admin.topping.destroy', $top->id) }}" method="POST" style="margin:0;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" style="background:none; border:none; color:#ef4444; font-size:12px; cursor:pointer;">✕</button>
                                        </form>
                                    </div>
                                @empty
                                    <div class="muted" style="text-align: center; padding: 10px; font-size:12px;">Belum ada topping ditambahkan.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <!-- Modal 1: Add Menu -->
    <div id="add-menu-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative;">
            <h3>📝 Tambah Menu Baru</h3>
            <button onclick="closeAddMenuModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            
            <form action="{{ route('admin.menu.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="menu_name" style="font-size: 12px; font-weight: 600;">Nama Menu</label>
                    <input type="text" id="menu_name" name="name" required placeholder="misal: Aren Signature Latte" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="menu_category" style="font-size: 12px; font-weight: 600;">Kategori</label>
                        <select id="menu_category" name="category_id" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="menu_price" style="font-size: 12px; font-weight: 600;">Harga (Rupiah)</label>
                        <input type="number" id="menu_price" name="price" required placeholder="misal: 32000" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="menu_desc" style="font-size: 12px; font-weight: 600;">Deskripsi</label>
                    <textarea id="menu_desc" name="description" placeholder="Komposisi bahan menu..." style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 12px; font-weight: 600;">Topping & Add-On yang tersedia</label>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;">
                        @foreach ($toppings as $top)
                            <label style="font-size: 12px; display:flex; align-items:center; gap: 6px;">
                                <input type="checkbox" name="toppings[]" value="{{ $top->id }}">
                                {{ $top->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Menu Baru</button>
            </form>
        </div>
    </div>

    <!-- Modal 2: Edit Menu -->
    <div id="edit-menu-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative;">
            <h3>📝 Edit Menu Produk</h3>
            <button onclick="closeEditMenuModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            
            <form id="edit-menu-form" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Nama Menu</label>
                    <input type="text" id="edit_menu_name" name="name" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Kategori</label>
                        <select id="edit_menu_category" name="category_id" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Harga (Rupiah)</label>
                        <input type="number" id="edit_menu_price" name="price" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Ketersediaan</label>
                        <select id="edit_menu_available" name="is_available" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="1">Tersedia</option>
                            <option value="0">Habis</option>
                        </select>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Status Rekomendasi</label>
                        <select id="edit_menu_featured" name="is_featured" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="0">Biasa</option>
                            <option value="1">Best Seller / Unggulan</option>
                        </select>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Deskripsi</label>
                    <textarea id="edit_menu_desc" name="description" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <label style="font-size: 12px; font-weight: 600;">Topping & Add-On yang tersedia</label>
                    <div style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px;">
                        @foreach ($toppings as $top)
                            <label style="font-size: 12px; display:flex; align-items:center; gap: 6px;">
                                <input type="checkbox" class="edit-menu-topping" name="toppings[]" value="{{ $top->id }}">
                                {{ $top->name }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Perbarui Data Menu</button>
            </form>
        </div>
    </div>

    <!-- Modal 3: Recipe Config (Resep) -->
    <div id="recipe-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(560px, 94%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; max-height:85%; overflow-y:auto;">
            <h3 id="recipe-modal-title">🔬 Formulasi Resep Menu</h3>
            <button onclick="closeRecipeModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            <p class="muted" style="font-size: 12px; margin-top: -6px; margin-bottom: 10px;">Konfigurasikan takaran bahan baku yang otomatis berkurang setiap produk ini terjual (status DONE).</p>
            
            <form id="recipe-form" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                
                <div id="recipe-rows" style="display: flex; flex-direction: column; gap: 10px;">
                    <!-- Added rows via JS -->
                </div>
                
                <button type="button" class="btn" style="border: 1px dashed var(--text-gold); color: var(--text-gold); font-size:12px; padding: 6px; font-weight:700;" onclick="addRecipeRow()">
                    + Tambah Bahan Baku Baru
                </button>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 800; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">
                    💾 Simpan Resep Menu
                </button>
            </form>
        </div>
    </div>

    <!-- Modal 4: Edit Topping -->
    <div id="edit-topping-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(520px, 94%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative; max-height:85%; overflow-y:auto;">
            <h3>Edit Topping & Add-On</h3>
            <button onclick="closeEditToppingModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">x</button>
            <form id="edit-topping-form" method="POST" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                <input type="text" id="edit_topping_name" name="name" required placeholder="Nama topping" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                <input type="number" id="edit_topping_price" name="price" required placeholder="Harga tambahan" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <select id="edit_topping_inventory" name="inventory_id" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                        <option value="">Tidak hubungkan inventory</option>
                        @foreach ($inventories as $inv)
                            <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
                        @endforeach
                    </select>
                    <input type="number" step="0.01" min="0" id="edit_topping_inventory_quantity" name="inventory_quantity" placeholder="Qty stok/porsi" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>
                <select id="edit_topping_available" name="is_available" required style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
                <label style="font-size: 12px; font-weight: 600;">Hubungkan ke Produk</label>
                <select id="edit_topping_menus" name="menu_ids[]" multiple size="6" style="padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    @foreach ($menus as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 800; border: none;">Simpan Perubahan Topping</button>
            </form>
        </div>
    </div>

    <!-- Modal scripts -->
    <script>
        // Modal Add Menu
        function openAddMenuModal() {
            document.getElementById('add-menu-modal').style.display = 'flex';
        }
        function closeAddMenuModal() {
            document.getElementById('add-menu-modal').style.display = 'none';
        }

        // Modal Edit Menu
        function openEditMenuModal(menu) {
            document.getElementById('edit_menu_name').value = menu.name;
            document.getElementById('edit_menu_price').value = menu.price;
            document.getElementById('edit_menu_category').value = menu.category_id;
            document.getElementById('edit_menu_available').value = menu.is_available ? '1' : '0';
            document.getElementById('edit_menu_featured').value = menu.is_featured ? '1' : '0';
            document.getElementById('edit_menu_desc').value = menu.description || '';
            const selectedToppings = (menu.toppings || []).map(topping => String(topping.id));
            document.querySelectorAll('.edit-menu-topping').forEach(input => {
                input.checked = selectedToppings.includes(input.value);
            });
            
            const form = document.getElementById('edit-menu-form');
            form.action = `/admin/menu/${menu.id}/update`;
            
            document.getElementById('edit-menu-modal').style.display = 'flex';
        }
        function closeEditMenuModal() {
            document.getElementById('edit-menu-modal').style.display = 'none';
        }

        function openEditToppingModal(topping) {
            document.getElementById('edit_topping_name').value = topping.name || '';
            document.getElementById('edit_topping_price').value = topping.price || 0;
            document.getElementById('edit_topping_inventory').value = topping.inventory_id || '';
            document.getElementById('edit_topping_inventory_quantity').value = topping.inventory_quantity || 1;
            document.getElementById('edit_topping_available').value = topping.is_available ? '1' : '0';

            const selectedMenus = (topping.menu_ids || []).map(id => String(id));
            document.querySelectorAll('#edit_topping_menus option').forEach(option => {
                option.selected = selectedMenus.includes(option.value);
            });

            document.getElementById('edit-topping-form').action = `/admin/topping/${topping.id}/update`;
            document.getElementById('edit-topping-modal').style.display = 'flex';
        }

        function closeEditToppingModal() {
            document.getElementById('edit-topping-modal').style.display = 'none';
        }

        // Modal Recipe Config
        let inventoryOptionsHtml = `
            <option value="">Pilih Bahan...</option>
            @foreach($inventories as $inv)
                <option value="{{ $inv->id }}">{{ $inv->name }} ({{ $inv->unit }})</option>
            @endforeach
        `;

        function openRecipeModal(menuId, menuName, existingRecipeItems) {
            document.getElementById('recipe-modal-title').innerText = `🔬 Formulasi Resep: ${menuName}`;
            const form = document.getElementById('recipe-form');
            form.action = `/admin/menu/${menuId}/recipe`;

            const rowsContainer = document.getElementById('recipe-rows');
            rowsContainer.innerHTML = '';

            if (existingRecipeItems && existingRecipeItems.length > 0) {
                existingRecipeItems.forEach((item, index) => {
                    addRecipeRow(item.inventory_id, item.quantity, index);
                });
            } else {
                addRecipeRow(); // Add one blank row
            }

            document.getElementById('recipe-modal').style.display = 'flex';
        }

        function closeRecipeModal() {
            document.getElementById('recipe-modal').style.display = 'none';
        }

        function addRecipeRow(inventoryId = '', quantity = '', idx = null) {
            const container = document.getElementById('recipe-rows');
            const rowCount = container.children.length;
            const rowIdx = idx !== null ? idx : rowCount;

            const row = document.createElement('div');
            row.style = 'display: flex; gap: 10px; align-items: center;';
            row.innerHTML = `
                <select name="items[${rowIdx}][inventory_id]" required style="flex: 2; padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    ${inventoryOptionsHtml}
                </select>
                <input type="number" step="0.01" name="items[${rowIdx}][quantity]" required placeholder="Takaran Qty" value="${quantity}" style="flex: 1; padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                <button type="button" style="background:none; border:none; color:#ef4444; font-size:16px; cursor:pointer;" onclick="this.parentElement.remove()">✕</button>
            `;

            container.appendChild(row);

            // Set selected inventory item if editing
            if (inventoryId) {
                row.querySelector('select').value = inventoryId;
            }
        }

        // Modal Add Product
        function openAddProductModal() {
            document.getElementById('add-product-modal').style.display = 'flex';
        }
        function closeAddProductModal() {
            document.getElementById('add-product-modal').style.display = 'none';
        }

        // Modal Edit Product
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
    </script>

    <!-- Modal 3: Add Product -->
    <div id="add-product-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative;">
            <h3>📝 Tambah Barang Baru</h3>
            <button onclick="closeAddProductModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            
            <form action="{{ route('admin.products.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="product_name" style="font-size: 12px; font-weight: 600;">Nama Barang</label>
                        <input type="text" id="product_name" name="name" required placeholder="misal: Coca Cola" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label for="product_barcode" style="font-size: 12px; font-weight: 600;">Nomor Barcode (Opsional)</label>
                        <input type="text" id="product_barcode" name="barcode" placeholder="Scan atau ketik barcode..." style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="product_price" style="font-size: 12px; font-weight: 600;">Harga (Rupiah)</label>
                    <input type="number" id="product_price" name="price" required placeholder="misal: 10000" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label for="product_desc" style="font-size: 12px; font-weight: 600;">Deskripsi</label>
                    <textarea id="product_desc" name="description" placeholder="Keterangan barang..." style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Barang Baru</button>
            </form>
        </div>
    </div>

    <!-- Modal 4: Edit Product -->
    <div id="edit-product-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="panel" style="width: min(500px, 92%); padding: 24px; display: flex; flex-direction: column; gap: 14px; position:relative;">
            <h3>📝 Edit Barang Produk</h3>
            <button onclick="closeEditProductModal()" style="position:absolute; right:20px; top:20px; background:none; border:none; font-size:18px; color:var(--text-muted); cursor:pointer;">✕</button>
            
            <form id="edit-product-form" method="POST" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nama Barang</label>
                        <input type="text" id="edit_product_name" name="name" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Nomor Barcode (Opsional)</label>
                        <input type="text" id="edit_product_barcode" name="barcode" placeholder="Scan atau ketik barcode..." style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Harga (Rupiah)</label>
                        <input type="number" id="edit_product_price" name="price" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Ketersediaan</label>
                        <select id="edit_product_available" name="is_available" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="1">Tersedia</option>
                            <option value="0">Habis</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <label style="font-size: 12px; font-weight: 600;">Status Rekomendasi</label>
                        <select id="edit_product_featured" name="is_featured" required style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px;">
                            <option value="0">Biasa</option>
                            <option value="1">Best Seller / Unggulan</option>
                        </select>
                    </div>
                    <div></div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 4px;">
                    <label style="font-size: 12px; font-weight: 600;">Deskripsi</label>
                    <textarea id="edit_product_desc" name="description" style="padding: 10px 14px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; height: 70px; resize:none; outline:none;"></textarea>
                </div>

                <button type="submit" class="btn btn-gold" style="padding: 12px; font-weight: 700; border: none; cursor: pointer; border-radius: 6px; margin-top: 10px;">Simpan Perubahan Barang</button>
            </form>
        </div>
    </div>
</x-layouts.app>
