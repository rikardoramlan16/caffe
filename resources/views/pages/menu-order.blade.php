<x-layouts.app title="Pesan Minuman - Kopi Senja">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top" style="background: var(--coffee-deep); color: white; display: flex; justify-content: space-between; align-items: center; padding: 14px 18px;">
                    <div>
                        <a class="brand" href="{{ route('landing') }}" style="display: flex; align-items: center; gap: 8px;">
                            <span class="brand-mark" style="width: 32px; height: 32px; font-size: 14px;">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span>
                            <span style="font-size: 14px; font-weight: 800; color: white;">Kopi Senja</span>
                        </a>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="pill" style="background: var(--text-gold); color: var(--bg-app); font-weight: 800; font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                            📍 Meja {{ $table->number }}
                        </span>
                        <button class="btn btn-icon" style="width:32px; height:32px; font-size:12px; padding:0; border:none;" type="button" data-theme-toggle title="Ganti tema">◐</button>
                    </div>
                </div>

                <main class="mobile-body" style="padding-bottom: 80px; position: relative; overflow-y: auto; height: calc(100% - 130px);">
                    <!-- Notification Toast -->
                    <div id="toast" style="position: absolute; top: 12px; left: 12px; right: 12px; background: #10b981; color: white; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; z-index: 1000; animation: fadeIn 0.2s;">
                        ✅ Berhasil ditambahkan!
                    </div>

                    <!-- Search & Categories -->
                    <div style="padding: 10px 0 0 0;">
                        <!-- Welcome message -->
                        <div style="margin-bottom: 16px;">
                            <span class="muted" style="font-size: 12px; font-weight: 600; display: block;">CUSTOMER SESSION TOKEN:</span>
                            <span style="font-family: monospace; font-size: 10px; color: var(--text-gold); word-break: break-all;">{{ $token }}</span>
                        </div>

                        <!-- Search Bar -->
                        <div style="margin-bottom: 16px;">
                            <input type="text" id="search-input" onkeyup="filterMenu()" placeholder="🔍 Cari kopi, sirup, cemilan..." style="width: 100%; padding: 10px 14px; border-radius: 8px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                        </div>

                        <!-- Category Filters -->
                        <div style="display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 8px;" class="hide-scrollbar">
                            <button class="btn cat-btn active" style="font-size: 11px; padding: 6px 12px; border-radius: 20px; font-weight: 700;" onclick="filterCategory('All', this)">Semua</button>
                            @foreach ($categories as $cat)
                                <button class="btn cat-btn" style="font-size: 11px; padding: 6px 12px; border-radius: 20px; font-weight: 700;" onclick="filterCategory('{{ $cat->name }}', this)">{{ $cat->name }}</button>
                            @endforeach
                        </div>

                        <!-- Menu Cards Grid -->
                        <div id="menu-container" style="display: flex; flex-direction: column; gap: 14px; margin-top: 10px;">
                            @foreach ($menu as $item)
                                <div class="menu-item-card" data-category="{{ $item->category ? $item->category->name : 'N/A' }}" data-name="{{ strtolower($item->name) }}" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); padding: 14px; border-radius: 10px; cursor: pointer; transition: transform 0.1s;" onclick="openCustomizeModal({{ $item->id }})">
                                    <div style="display:flex; justify-content:space-between; gap:12px; align-items: center;">
                                        <div style="flex: 1;">
                                            <div style="display: flex; gap: 6px; align-items: center; margin-bottom: 4px;">
                                                <strong style="font-size: 14px; color: var(--text-main);">{{ $item->name }}</strong>
                                                @if($item->is_featured)
                                                    <span class="pill" style="font-size: 8px; padding: 2px 6px; background: #e0b766; color: #120d0a;">Best Seller</span>
                                                @endif
                                            </div>
                                            <p class="muted" style="font-size: 11px; margin: 4px 0 6px 0;">{{ $item->description ?? 'Espresso blend & raw ingredients.' }}</p>
                                            <strong style="color: var(--text-gold); font-size: 13px;">Rp {{ number_format($item->price, 0, ',', '.') }}</strong>
                                        </div>
                                        <div style="text-align: right; display:flex; flex-direction:column; align-items:flex-end; gap: 8px;">
                                            <div style="width: 60px; height: 60px; border-radius: 8px; background: var(--cream); border: 1px solid var(--line); display:flex; align-items:center; justify-content:center; font-size: 24px;">
                                                ☕
                                            </div>
                                            <button class="btn btn-gold" style="font-size: 10px; padding: 4px 10px; font-weight: 700; border-radius: 4px; border: none;">+ Tambah</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </main>

                <!-- Floating Cart Banner -->
                <div id="floating-cart" style="position: fixed; bottom: 74px; left: 16px; right: 16px; background: var(--bg-surface); border: 1px solid var(--text-gold); border-radius: 10px; padding: 12px 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.25); display: none; align-items: center; justify-content: space-between; z-index: 90; backdrop-filter: blur(10px);">
                    <div>
                        <span id="floating-cart-count" style="font-size: 12px; font-weight: 700; color: var(--text-gold); background: rgba(212,175,55,0.1); padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 2px;">0 Item</span>
                        <div id="floating-cart-total" style="font-size: 14px; font-weight: 800; color: var(--text-main);">Rp 0</div>
                    </div>
                    <a href="{{ route('cart.view') }}" class="btn btn-gold" style="font-size: 12px; padding: 8px 14px; font-weight: 800; border: none; text-decoration: none;">🛒 Lihat Keranjang</a>
                </div>

                <nav class="bottom-nav" aria-label="Navigasi utama">
                    <a class="active" href="{{ route('qr.login', $table->code) }}">Menu</a>
                    <a href="{{ route('cart.view') }}">Cart <span id="nav-cart-badge" style="background: var(--text-gold); color: var(--bg-app); font-weight: 800; font-size: 9px; padding: 1px 4px; border-radius: 4px; margin-left: 2px; display: none;">0</span></a>
                    <a href="{{ route('order.status') }}">Status</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Active Order Popup Modal -->
    @if ($activeOrder && !$showTransferPopup)
        <div id="active-order-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); display: flex; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div style="background: var(--bg-surface); width: min(340px, 90%); border-radius: 16px; padding: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 16px;">
                <div style="font-size: 40px; color: var(--text-gold);">☕</div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">Pesanan Aktif Ditemukan</h2>
                    <p class="muted" style="font-size: 12px; margin: 0 0 10px 0;">Anda memiliki pesanan aktif di Meja ini.</p>
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 10px; font-size: 13px; text-align: left; display: flex; flex-direction: column; gap: 4px;">
                        <div><strong>Order Invoice:</strong> <span style="color: var(--text-gold);">{{ $activeOrder->invoice_number }}</span></div>
                        <div><strong>Status:</strong> <span class="pill" style="font-size: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981;">{{ $activeOrder->status }}</span></div>
                    </div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <a href="{{ route('order.status') }}" class="btn btn-gold" style="flex: 1; padding: 10px 0; font-weight: 800; font-size: 13px; text-decoration: none;">Lihat Status</a>
                    <button class="btn" style="flex: 1; padding: 10px 0; font-weight: 700; font-size: 13px;" onclick="closeActiveOrderModal()">Lanjut Pesan</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Table Transfer Popup Modal -->
    @if ($showTransferPopup)
        <div id="transfer-table-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.8); display: flex; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
            <div style="background: var(--bg-surface); width: min(340px, 90%); border-radius: 16px; padding: 24px; box-shadow: 0 12px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 16px;">
                <div style="font-size: 40px; color: var(--text-gold);">🔄 Meja Pindah</div>
                <div>
                    <h2 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">Pesanan Aktif Ditemukan</h2>
                    <p class="muted" style="font-size: 12px; margin: 0 0 10px 0;">Kami mendeteksi Anda memindai kode QR meja baru.</p>
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 8px; padding: 10px; font-size: 13px; text-align: left; display: flex; flex-direction: column; gap: 6px;">
                        <div><strong>Order Invoice:</strong> {{ $activeOrder->invoice_number }}</div>
                        <div style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 6px; margin-top: 6px;">
                            <span>Meja Lama: <strong style="color: #ef4444;">{{ $oldTableCode }}</strong></span>
                            <span>Meja Baru: <strong style="color: #10b981;">{{ $table->code }}</strong></span>
                        </div>
                    </div>
                </div>
                
                <p class="muted" style="font-size: 11px; margin: 0;">Apakah Anda ingin memindahkan pesanan Anda ke meja baru ini?</p>
                
                <div style="display: flex; gap: 10px; margin-top: 8px;">
                    <button class="btn btn-gold" style="flex: 1; padding: 10px; font-weight: 800; font-size: 13px;" onclick="confirmTableTransfer()">Ya, Pindahkan</button>
                    <button class="btn" style="flex: 1; padding: 10px; font-weight: 700; font-size: 13px;" onclick="closeTransferModal()">Batal</button>
                </div>
            </div>
        </div>
    @endif

    <!-- Popup Product Customization Modal (Starbucks / GoFood style) -->
    <div id="customize-sheet" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); display: none; z-index: 1000; align-items: flex-end; backdrop-filter: blur(4px);" onclick="closeCustomizeModal()">
        <div style="background: var(--bg-surface); width: 100%; border-radius: 16px 16px 0 0; padding: 24px; box-shadow: 0 -8px 32px rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 16px; animation: slideUp 0.25s ease-out; max-height: 85%; overflow-y: auto;" onclick="event.stopPropagation()">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 id="modal-item-name" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">Item Name</h2>
                    <p id="modal-item-desc" class="muted" style="font-size: 12px; margin: 0;">Description of item goes here.</p>
                </div>
                <button style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;" onclick="closeCustomizeModal()">✕</button>
            </div>

            <!-- Size Options -->
            <div>
                <span style="font-size: 12px; font-weight: 700; color: var(--text-gold); display: block; margin-bottom: 8px;">UKURAN</span>
                <div style="display: flex; gap: 12px;">
                    <label style="flex: 1; border: 1px solid var(--line); border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: rgba(255,255,255,0.01);">
                        <div style="font-size: 13px; font-weight: 700;">Regular</div>
                        <input type="radio" name="size" value="Regular" checked onclick="updateModalPrice()">
                    </label>
                    <label style="flex: 1; border: 1px solid var(--line); border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: rgba(255,255,255,0.01);">
                        <div style="font-size: 13px; font-weight: 700;">Large (+Rp3.000)</div>
                        <input type="radio" name="size" value="Large" onclick="updateModalPrice()">
                    </label>
                </div>
            </div>

            <!-- Topping Options -->
            <div>
                <span style="font-size: 12px; font-weight: 700; color: var(--text-gold); display: block; margin-bottom: 8px;">TOPPING</span>
                <div id="modal-topping-options" style="display: flex; flex-direction: column; gap: 8px;">
                </div>
            </div>

            <div>
                <span style="font-size: 12px; font-weight: 700; color: var(--text-gold); display: block; margin-bottom: 8px;">LEVEL GULA</span>
                <div style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 8px;">
                    @foreach (['0%', '25%', '50%', '75%', '100%'] as $level)
                        <label style="border: 1px solid var(--line); border-radius: 8px; padding: 8px 6px; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; background: rgba(255,255,255,0.01); font-size: 12px; font-weight: 700;">
                            <input type="radio" name="sugar_level" value="{{ $level }}" {{ $level === '100%' ? 'checked' : '' }} onclick="updateModalPrice()">
                            {{ $level }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <span style="font-size: 12px; font-weight: 700; color: var(--text-gold); display: block; margin-bottom: 8px;">LEVEL ES</span>
                <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px;">
                    @foreach (['Tanpa Es', 'Sedikit Es', 'Normal'] as $level)
                        <label style="border: 1px solid var(--line); border-radius: 8px; padding: 8px 6px; display: flex; align-items: center; justify-content: center; gap: 4px; cursor: pointer; background: rgba(255,255,255,0.01); font-size: 12px; font-weight: 700; text-align:center;">
                            <input type="radio" name="ice_level" value="{{ $level }}" {{ $level === 'Normal' ? 'checked' : '' }} onclick="updateModalPrice()">
                            {{ $level }}
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Notes -->
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="modal-item-note" style="font-size: 12px; font-weight: 700; color: var(--text-gold);">CATATAN</label>
                <div style="position: relative; display: flex; align-items: center; width: 100%;">
                    <input type="text" id="modal-item-note" placeholder="Tulis instruksi tambahan..." style="width: 100%; padding: 10px 40px 10px 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                    <button type="button" id="start-voice-btn" style="position: absolute; right: 8px; background: none; border: none; color: var(--text-gold); cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 6px; border-radius: 50%; width: 28px; height: 28px; transition: background-color 0.2s;" onclick="toggleVoiceRecognition(event)" title="Gunakan Voice to Text">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                            <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                            <line x1="12" y1="19" x2="12" y2="23"></line>
                            <line x1="8" y1="23" x2="16" y2="23"></line>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Quantity Selector & Action Button -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 14px;">
                <div>
                    <span class="muted" style="font-size: 11px; display: block; margin-bottom: 2px;">Total Harga</span>
                    <strong id="modal-item-price-label" style="font-size: 18px; color: var(--text-gold);">Rp 0</strong>
                </div>
                
                <div style="display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; padding: 4px 10px;">
                    <button style="background:none; border:none; font-size: 18px; font-weight:800; color: var(--text-gold); cursor:pointer; width:20px; text-align:center;" onclick="adjustModalQty(-1)">-</button>
                    <span id="modal-item-qty" style="font-size: 15px; font-weight: 800; color: var(--text-main); min-width: 16px; text-align: center;">1</span>
                    <button style="background:none; border:none; font-size: 18px; font-weight:800; color: var(--text-gold); cursor:pointer; width:20px; text-align:center;" onclick="adjustModalQty(1)">+</button>
                </div>
            </div>

            <button id="modal-add-button" class="btn btn-gold" style="width: 100%; padding: 12px; font-weight: 800; font-size: 14px; border: none; margin-top: 8px;" onclick="confirmAddToCart()">
                Tambahkan Ke Keranjang
            </button>
        </div>
    </div>

    <!-- Client-side logic for search, category filter, active order detection and table transfer -->
    <script>
        let currentItem = null;
        let cart = [];
        const sizePrices = { Regular: 0, Large: 3000 };
        @php
            $menuCustomizationData = $menu->mapWithKeys(fn ($item) => [$item->id => [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'toppings' => $item->toppings->map(fn ($topping) => [
                    'id' => $topping->id,
                    'name' => $topping->name,
                    'price' => $topping->price,
                ])->values(),
            ]]);
        @endphp
        const menuCustomizationData = @json($menuCustomizationData);

        document.addEventListener('DOMContentLoaded', () => {
            // Load cart
            const storedCart = localStorage.getItem('cafeflow_cart');
            if (storedCart) {
                try { cart = JSON.parse(storedCart); } catch (e) { cart = []; }
            }
            updateCartSummary();

            // Save active invoice if active order details loaded in blade
            @if ($activeOrder)
                localStorage.setItem('cafeflow_active_invoice', '{{ $activeOrder->invoice_number }}');
            @endif
        });

        // Search filter
        function filterMenu() {
            const query = document.getElementById('search-input').value.toLowerCase();
            const cards = document.getElementsByClassName('menu-item-card');
            
            for (let card of cards) {
                const name = card.getAttribute('data-name');
                if (name.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        }

        // Category filter
        function filterCategory(catName, btn) {
            // Toggle active button style
            const buttons = document.getElementsByClassName('cat-btn');
            for(let b of buttons) {
                b.classList.remove('active');
            }
            btn.classList.add('active');

            const cards = document.getElementsByClassName('menu-item-card');
            for (let card of cards) {
                const cat = card.getAttribute('data-category');
                if (catName === 'All' || cat === catName) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            }
        }

        // Customize PopUp
        function openCustomizeModal(id) {
            const menuItem = menuCustomizationData[id];
            if (!menuItem) return;

            currentItem = {
                id: menuItem.id,
                name: menuItem.name,
                basePrice: menuItem.price,
                price: menuItem.price,
                quantity: 1,
                size: 'Regular',
                sugar_level: '100%',
                ice_level: 'Normal',
                toppings: [],
                note: ''
            };

            document.getElementById('modal-item-name').innerText = menuItem.name;
            document.getElementById('modal-item-desc').innerText = menuItem.description || 'Brewed fresh daily by certified baristas.';
            document.getElementById('modal-item-qty').innerText = '1';
            document.getElementById('modal-item-note').value = '';

            // Reset inputs
            document.querySelector('input[name="size"][value="Regular"]').checked = true;
            document.querySelector('input[name="sugar_level"][value="100%"]').checked = true;
            document.querySelector('input[name="ice_level"][value="Normal"]').checked = true;

            const toppingContainer = document.getElementById('modal-topping-options');
            toppingContainer.innerHTML = '';
            if (menuItem.toppings.length === 0) {
                toppingContainer.innerHTML = '<div class="muted" style="font-size: 12px; padding: 8px 0;">Tidak ada topping untuk produk ini.</div>';
            } else {
                menuItem.toppings.forEach(topping => {
                    const label = document.createElement('label');
                    label.style = 'border: 1px solid var(--line); border-radius: 8px; padding: 10px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: rgba(255,255,255,0.01);';
                    label.innerHTML = `
                        <div style="font-size: 13px; font-weight: 600;">${topping.name} (+Rp${topping.price.toLocaleString('id-ID')})</div>
                        <input type="checkbox" name="topping" value="${topping.id}" data-price="${topping.price}" data-name="${topping.name}" onclick="updateModalPrice()">
                    `;
                    toppingContainer.appendChild(label);
                });
            }

            updateModalPrice();
            document.getElementById('customize-sheet').style.display = 'flex';
        }

        function closeCustomizeModal() {
            document.getElementById('customize-sheet').style.display = 'none';
            currentItem = null;
        }

        function updateModalPrice() {
            if (!currentItem) return;

            let price = currentItem.basePrice;
            
            // Size adjustment
            const sizeVal = document.querySelector('input[name="size"]:checked').value;
            currentItem.size = sizeVal;
            price += sizePrices[sizeVal] || 0;
            currentItem.sugar_level = document.querySelector('input[name="sugar_level"]:checked').value;
            currentItem.ice_level = document.querySelector('input[name="ice_level"]:checked').value;

            // Toppings adjustment
            currentItem.toppings = [];
            document.querySelectorAll('input[name="topping"]:checked').forEach(cb => {
                const topPrice = parseInt(cb.getAttribute('data-price'));
                const topName = cb.getAttribute('data-name');
                const topId = cb.value;
                currentItem.toppings.push({ id: topId, name: topName, price: topPrice });
                price += topPrice;
            });

            currentItem.price = price;
            const totalPrice = price * currentItem.quantity;
            document.getElementById('modal-item-price-label').innerText = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        }

        function adjustModalQty(val) {
            if (!currentItem) return;
            currentItem.quantity = Math.max(1, currentItem.quantity + val);
            document.getElementById('modal-item-qty').innerText = currentItem.quantity;
            updateModalPrice();
        }

        function confirmAddToCart() {
            if (!currentItem) return;

            currentItem.note = document.getElementById('modal-item-note').value;

            // Generate unique customization string to identify identical customizations
            const toppingKey = currentItem.toppings.map(t => t.id).sort().join(',');
            const cartKey = `${currentItem.id}-${currentItem.size}-${currentItem.sugar_level}-${currentItem.ice_level}-${toppingKey}-${currentItem.note}`;

            const existingIdx = cart.findIndex(item => {
                const itemToppingKey = item.toppings ? item.toppings.map(t => t.id).sort().join(',') : '';
                const itemKey = `${item.id}-${item.size}-${item.sugar_level || '100%'}-${item.ice_level || 'Normal'}-${itemToppingKey}-${item.note}`;
                return itemKey === cartKey;
            });

            if (existingIdx !== -1) {
                cart[existingIdx].quantity += currentItem.quantity;
            } else {
                cart.push({ ...currentItem });
            }

            const addedName = currentItem.name;
            localStorage.setItem('cafeflow_cart', JSON.stringify(cart));
            updateCartSummary();
            closeCustomizeModal();

            // Show Toast
            const toast = document.getElementById('toast');
            toast.innerText = `Berhasil: ${addedName} ditambahkan!`;
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2000);
        }

        function updateCartSummary() {
            const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalAmount = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);

            // Badge in bottom nav
            const badge = document.getElementById('nav-cart-badge');
            if (badge) {
                if (totalQty > 0) {
                    badge.innerText = totalQty;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }

            // Floating banner
            const banner = document.getElementById('floating-cart');
            if (banner) {
                if (totalQty > 0) {
                    document.getElementById('floating-cart-count').innerText = `${totalQty} Item`;
                    document.getElementById('floating-cart-total').innerText = `Rp ${totalAmount.toLocaleString('id-ID')}`;
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            }
        }

        // Active Order Modal
        function closeActiveOrderModal() {
            document.getElementById('active-order-modal').style.display = 'none';
        }

        // Table Transfer Modal
        function closeTransferModal() {
            document.getElementById('transfer-table-modal').style.display = 'none';
        }

        function confirmTableTransfer() {
            fetch('{{ route('qr.transfer') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    closeTransferModal();
                    
                    // Show success visual loading
                    const toast = document.createElement('div');
                    toast.style = 'position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#10b981; color:white; padding:12px 24px; border-radius:8px; font-weight:800; font-size:14px; box-shadow:0 8px 24px rgba(0,0,0,0.3); z-index:2000;';
                    toast.innerText = '✅ Meja pesanan aktif berhasil dipindahkan!';
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    alert('Gagal memindahkan meja: ' + (data.message || 'Error'));
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menyambung ke server.');
            });
        }

        // Voice to Text Feature
        let recognition = null;
        let isRecording = false;

        function toggleVoiceRecognition(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;

            if (!window.SpeechRecognition) {
                alert('Browser Anda tidak mendukung fitur Voice to Text (Web Speech API). Harap gunakan Google Chrome atau Safari.');
                return;
            }

            const voiceBtn = document.getElementById('start-voice-btn');
            const noteInput = document.getElementById('modal-item-note');

            if (!recognition) {
                recognition = new window.SpeechRecognition();
                recognition.lang = 'id-ID';
                recognition.interimResults = false;
                recognition.maxAlternatives = 1;

                recognition.onstart = () => {
                    isRecording = true;
                    if (voiceBtn) {
                        voiceBtn.style.color = '#ef4444';
                        voiceBtn.classList.add('recording-pulse');
                    }
                    if (noteInput) {
                        noteInput.placeholder = 'Mendengarkan... Bicara sekarang.';
                    }
                };

                recognition.onresult = (event) => {
                    const transcript = event.results[0][0].transcript;
                    if (noteInput) {
                        if (noteInput.value) {
                            noteInput.value += ' ' + transcript;
                        } else {
                            noteInput.value = transcript;
                        }
                    }
                };

                recognition.onerror = (event) => {
                    console.error('Speech recognition error:', event.error);
                    stopVoiceRecognition();
                    if (event.error === 'not-allowed') {
                        alert('Akses mikrofon ditolak. Harap izinkan akses mikrofon di pengaturan browser Anda.');
                    }
                };

                recognition.onend = () => {
                    stopVoiceRecognition();
                };
            }

            if (isRecording) {
                recognition.stop();
            } else {
                recognition.start();
            }
        }

        function stopVoiceRecognition() {
            isRecording = false;
            const voiceBtn = document.getElementById('start-voice-btn');
            const noteInput = document.getElementById('modal-item-note');
            if (voiceBtn) {
                voiceBtn.style.color = 'var(--text-gold)';
                voiceBtn.classList.remove('recording-pulse');
            }
            if (noteInput) {
                noteInput.placeholder = 'Tulis instruksi tambahan...';
            }
        }
    </script>

    <style>
        @keyframes qrScan {
            0% { top: 0%; }
            50% { top: 100%; }
            100% { top: 0%; }
        }
        @keyframes slideUp {
            from { transform: translateY(100%); }
            to { transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }
        .recording-pulse {
            animation: pulse 1.5s infinite ease-in-out;
            background-color: rgba(239, 68, 68, 0.1);
        }
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</x-layouts.app>
