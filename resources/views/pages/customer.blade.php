<x-layouts.app title="Halaman Pelanggan - CafeFlow">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top">
                    <a class="brand" href="{{ route('landing') }}"><span class="brand-mark">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span><span id="table-label">Meja A1</span></a>
                    <button class="btn btn-icon" type="button" data-theme-toggle title="Ganti tema">◐</button>
                </div>
                
                <main class="mobile-body" style="padding-bottom: 80px; position: relative;">
                    <!-- Notification Toast -->
                    <div id="toast" style="position: absolute; top: 12px; left: 12px; right: 12px; background: #10b981; color: white; padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 700; text-align: center; box-shadow: 0 4px 12px rgba(0,0,0,0.15); display: none; z-index: 1000; animation: fadeIn 0.2s;">
                        ✅ Berhasil ditambahkan!
                    </div>

                    @if ($state === 'scan-qr')
                        <div style="padding: 20px 0; text-align: center;">
                            <span class="eyebrow" style="color: var(--text-gold);">QR Code Scanner</span>
                            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 8px; color: var(--text-main);">Scan QR meja Anda.</h1>
                            <p class="muted" style="font-size: 14px; margin-bottom: 24px;">Silakan klik tombol di bawah untuk membuka menu digital dan memesan secara otomatis di Meja A1.</p>
                            
                            <div style="width: 180px; height: 180px; background: rgba(255,255,255,0.03); border: 2px dashed rgba(255,255,255,0.15); border-radius: 12px; margin: 0 auto 24px auto; display: flex; align-items: center; justify-content: center; position: relative;">
                                <div style="width: 130px; height: 130px; background-image: url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 width=%22100%22 height=%22100%22 viewBox=%220 0 100 100%22><rect width=%2220%22 height=%2220%22 x=%220%22 y=%220%22/><rect width=%2220%22 height=%2220%22 x=%2280%22 y=%220%22/><rect width=%2220%22 height=%2220%22 x=%220%22 y=%2280%22/><rect width=%2210%22 height=%2210%22 x=%2240%22 y=%2240%22/><rect width=%2210%22 height=%2210%22 x=%2250%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 x=%2210%22 y=%2250%22/><rect width=%2210%22 height=%2210%22 x=%2250%22 y=%2210%22/><rect width=%2210%22 height=%2210%22 x=%2280%22 y=%2260%22/><rect width=%2210%22 height=%2210%22 x=%2220%22 y=%2220%22/></svg>'); background-size: cover; opacity: 0.85;"></div>
                                <div style="position: absolute; width: 100%; height: 2px; background: var(--text-gold); top: 0; left: 0; box-shadow: 0 0 8px var(--text-gold); animation: qrScan 2s infinite linear;"></div>
                            </div>
                            
                            <a class="btn btn-primary" style="width:100%; padding: 12px; font-weight: 700;" href="{{ route('customer', 'menu') }}">Buka Menu Digital</a>
                        </div>
                    @elseif ($state === 'cart')
                        <div style="padding: 10px 0;">
                            <span class="eyebrow" style="color: var(--text-gold);">Keranjang Belanja</span>
                            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Pesanan Anda</h1>
                            
                            <div id="cart-list" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                                <!-- Dynamically Rendered Cart Items -->
                            </div>

                            <div id="cart-summary" class="panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; display: none;">
                                <div class="order-row" style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                    <span style="color: var(--text-muted);">Subtotal</span>
                                    <strong style="color: var(--text-main);" id="subtotal-val">Rp 0</strong>
                                </div>
                                <div class="order-row" style="display:flex; justify-content:space-between; margin-bottom: 8px;">
                                    <span style="color: var(--text-muted);">Biaya Layanan</span>
                                    <strong style="color: var(--text-main);">Rp {{ number_format($serviceFee, 0, ',', '.') }}</strong>
                                </div>
                                <div class="order-row" style="display:flex; justify-content:space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; margin-top: 10px; font-size: 16px;">
                                    <span style="font-weight: 700; color: var(--text-main);">Total</span>
                                    <strong style="color: #10b981;" id="total-val">Rp 0</strong>
                                </div>
                                
                                <div style="margin-top: 16px; display: flex; flex-direction: column; gap: 6px;">
                                    <label for="general-note" style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Catatan Pesanan Tambahan (Opsional)</label>
                                    <input type="text" id="general-note" placeholder="Contoh: Kurangi es batu, sendok plastik..." style="width: 100%; padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                                </div>

                                <button id="btn-checkout" class="btn btn-gold" style="width:100%; margin-top:16px; padding: 12px; font-weight: 800; border: none; font-size: 14px;" onclick="submitOrder()">
                                    ⚡ Pesan & Bayar QRIS
                                </button>
                            </div>
                        </div>
                    @elseif ($state === 'status')
                        <div style="padding: 10px 0; text-align: center;">
                            <span class="eyebrow" style="color: var(--text-gold);">Live Order Status</span>
                            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 6px; color: var(--text-main);">Melacak Pesanan</h1>
                            <div id="invoice-label" class="muted" style="font-size: 13px; font-weight: 700; margin-bottom: 24px;">INV-XXXX</div>
                            
                            <div style="display: flex; flex-direction: column; gap: 14px; text-align: left; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                                <div id="status-step-1" class="order-row" style="display: flex; gap: 14px; align-items: center; opacity: 0.3; transition: opacity 0.3s;">
                                    <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">1</span>
                                    <div>
                                        <strong style="color: var(--text-main);">Order Diterima</strong>
                                        <div class="muted" style="font-size: 12px;">Menunggu pembayaran dikonfirmasi kasir.</div>
                                    </div>
                                </div>
                                <div id="status-step-2" class="order-row" style="display: flex; gap: 14px; align-items: center; opacity: 0.3; transition: opacity 0.3s;">
                                    <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">2</span>
                                    <div>
                                        <strong style="color: var(--text-main);">Pembayaran Sukses</strong>
                                        <div class="muted" style="font-size: 12px;">Pembayaran terkonfirmasi. Antrean barista.</div>
                                    </div>
                                </div>
                                <div id="status-step-3" class="order-row" style="display: flex; gap: 14px; align-items: center; opacity: 0.3; transition: opacity 0.3s;">
                                    <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">3</span>
                                    <div>
                                        <strong style="color: var(--text-main);">Sedang Dibuat</strong>
                                        <div class="muted" style="font-size: 12px;">Barista sedang meracik minuman Anda.</div>
                                    </div>
                                </div>
                                <div id="status-step-4" class="order-row" style="display: flex; gap: 14px; align-items: center; opacity: 0.3; transition: opacity 0.3s;">
                                    <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">4</span>
                                    <div>
                                        <strong style="color: var(--text-main);">Siap Diambil</strong>
                                        <div class="muted" style="font-size: 12px; color: #10b981; font-weight: 700;">Pesanan selesai! Silakan ambil di konter.</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="modal-preview" style="margin-top:20px; background: rgba(16, 185, 129, 0.1); border: 1px dashed rgba(16, 185, 129, 0.2); padding: 16px; border-radius: 10px;">
                                <h3 style="color: #10b981; margin-bottom: 4px;">Alur Kerja Realtime Dinamis</h3>
                                <p class="muted" style="font-size: 12px; margin: 0;">Status di atas akan berubah secara otomatis dan instan ketika Kasir mengonfirmasi bayar atau Barista menekan tombol pembuatan di dashboard mereka!</p>
                            </div>
                        </div>
                    @else
                        <!-- State: Menu (Default) -->
                        <div style="padding: 10px 0;">
                            <span class="eyebrow" style="color: var(--text-gold);">Menu Digital</span>
                            <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Pesan Minuman</h1>
                            
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach ($menu as $item)
                                    <div class="menu-item" style="background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); padding: 14px; border-radius: 10px; cursor: pointer; transition: transform 0.1s;" onclick="openAddToCartModal('{{ $item->id }}', '{{ $item->name }}', {{ $item->price }}, '{{ $item->description }}')">
                                        <div style="display:flex; justify-content:space-between; gap:12px; align-items: flex-start;">
                                            <div style="flex: 1;">
                                                <strong style="font-size: 14px; color: var(--text-main);">{{ $item->name }}</strong>
                                                <p class="muted" style="font-size: 11px; margin: 4px 0 6px 0;">{{ $item->description ?? 'Espresso blend & raw ingredients.' }}</p>
                                                <strong style="color: var(--text-gold); font-size: 13px;">Rp {{ number_format($item->price, 0, ',', '.') }}</strong>
                                            </div>
                                            <div style="text-align: right; display:flex; flex-direction:column; align-items:flex-end; gap: 8px;">
                                                <span class="pill" style="font-size: 9px; background: rgba(255,255,255,0.05); color: var(--text-gold);">{{ $item->category ? $item->category->name : 'Beverage' }}</span>
                                                <button class="btn btn-gold" style="font-size: 11px; padding: 4px 10px; font-weight: 700; border-radius: 4px;">+ Tambah</button>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Floating Cart Banner -->
                    <div id="floating-cart" style="position: fixed; bottom: 74px; left: 16px; right: 16px; background: var(--bg-surface); border: 1px solid var(--text-gold); border-radius: 10px; padding: 12px 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.25); display: none; align-items: center; justify-content: space-between; z-index: 90; backdrop-filter: blur(10px);">
                        <div>
                            <span id="floating-cart-count" style="font-size: 12px; font-weight: 700; color: var(--text-gold); background: rgba(212,175,55,0.1); padding: 2px 6px; border-radius: 4px; display: inline-block; margin-bottom: 2px;">0 Item</span>
                            <div id="floating-cart-total" style="font-size: 14px; font-weight: 800; color: var(--text-main);">Rp 0</div>
                        </div>
                        <a href="{{ route('customer', 'cart') }}" class="btn btn-gold" style="font-size: 12px; padding: 8px 14px; font-weight: 800; border: none; text-decoration: none;">🛒 Lihat Keranjang</a>
                    </div>
                </main>
                
                <nav class="bottom-nav" aria-label="Navigasi pelanggan">
                    <a class="{{ $state === 'scan-qr' ? 'active' : '' }}" href="{{ route('customer', 'scan-qr') }}">Scan</a>
                    <a class="{{ $state === 'menu' ? 'active' : '' }}" href="{{ route('customer', 'menu') }}">Menu</a>
                    <a class="{{ $state === 'cart' ? 'active' : '' }}" href="{{ route('customer', 'cart') }}">Cart <span id="nav-cart-badge" style="background: var(--text-gold); color: var(--bg-app); font-weight: 800; font-size: 9px; padding: 1px 4px; border-radius: 4px; margin-left: 2px; display: none;">0</span></a>
                    <a class="{{ $state === 'status' ? 'active' : '' }}" href="{{ route('customer', 'status') }}">Status</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bottom Sheet Pop-Up Modal -->
    <div id="bottom-sheet" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); display: none; z-index: 1000; align-items: flex-end; backdrop-filter: blur(4px);" onclick="closeAddToCartModal()">
        <div style="background: var(--bg-surface); width: 100%; border-radius: 16px 16px 0 0; padding: 24px; box-shadow: 0 -8px 32px rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.05); display: flex; flex-direction: column; gap: 16px; animation: slideUp 0.25s ease-out;" onclick="event.stopPropagation()">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h2 id="modal-item-name" style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 4px 0;">Item Name</h2>
                    <p id="modal-item-desc" class="muted" style="font-size: 12px; margin: 0;">Description of item goes here.</p>
                </div>
                <button style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;" onclick="closeAddToCartModal()">✕</button>
            </div>

            <!-- Customization Form -->
            <div style="display: flex; flex-direction: column; gap: 6px;">
                <label for="modal-item-note" style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Instruksi Khusus (mis. Less ice, no sugar)</label>
                <input type="text" id="modal-item-note" placeholder="Tulis instruksi barista..." style="width: 100%; padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
            </div>

            <!-- Quantity Selector & Price -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 14px;">
                <div>
                    <span class="muted" style="font-size: 12px; display: block; margin-bottom: 2px;">Harga</span>
                    <strong id="modal-item-price-label" style="font-size: 16px; color: var(--text-gold);">Rp 0</strong>
                </div>
                
                <div style="display: flex; align-items: center; gap: 14px; background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); border-radius: 6px; padding: 4px 10px;">
                    <button style="background:none; border:none; font-size: 18px; font-weight:800; color: var(--text-gold); cursor:pointer; width:20px; text-align:center;" onclick="adjustModalQty(-1)">-</button>
                    <span id="modal-item-qty" style="font-size: 15px; font-weight: 800; color: var(--text-main); min-width: 16px; text-align: center;">1</span>
                    <button style="background:none; border:none; font-size: 18px; font-weight:800; color: var(--text-gold); cursor:pointer; width:20px; text-align:center;" onclick="adjustModalQty(1)">+</button>
                </div>
            </div>

            <button id="modal-add-button" class="btn btn-gold" style="width: 100%; padding: 12px; font-weight: 800; font-size: 14px; border: none; margin-top: 8px;" onclick="confirmAddToCart()">
                Tambahkan Ke Keranjang · <span id="modal-item-total-price">Rp 0</span>
            </button>
        </div>
    </div>

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
        .customer-shell .mobile-stage {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            min-height: 100vh;
            background: #111;
        }
        .customer-shell .mobile-app {
            background: var(--bg-app);
            border-radius: 0;
            box-shadow: none;
            width: 100%;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            position: relative;
        }
        @media (min-width: 480px) {
            .customer-shell .mobile-app {
                width: 410px;
                height: 820px;
                border-radius: 36px;
                border: 12px solid #222;
                box-shadow: 0 24px 64px rgba(0,0,0,0.8);
            }
            .customer-shell .mobile-stage {
                padding: 40px 0;
            }
        }
    </style>

    <!-- Client-Side Realtime Interactive Cart Javascript -->
    <script>
        // Global variables representing cart and items
        let currentItem = null;
        let cart = [];

        // Load cart from localStorage on page load
        document.addEventListener('DOMContentLoaded', () => {
            const storedCart = localStorage.getItem('cafeflow_cart');
            if (storedCart) {
                try {
                    cart = JSON.parse(storedCart);
                } catch (e) {
                    cart = [];
                }
            }
            updateFloatingCartBanner();
            
            // If we are on the cart state page, render the list
            if (document.getElementById('cart-list')) {
                renderCartList();
            }

            // If we are on status page, poll order status in realtime
            if (document.getElementById('invoice-label')) {
                initOrderStatusTracker();
            }
        });

        // Save cart to local storage and update indicators
        function saveCart() {
            localStorage.setItem('cafeflow_cart', JSON.stringify(cart));
            updateFloatingCartBanner();
        }

        // Update floating cart visibility, totals and badges
        function updateFloatingCartBanner() {
            const totalQty = cart.reduce((sum, item) => sum + item.quantity, 0);
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
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

            // Floating banner on menu list page
            const banner = document.getElementById('floating-cart');
            if (banner) {
                // Show banner only if cart has items and we are on menu state
                const isMenuPage = !window.location.search.includes('state=') || window.location.href.endsWith('/customer') || window.location.href.endsWith('/customer/menu');
                if (totalQty > 0 && isMenuPage) {
                    document.getElementById('floating-cart-count').innerText = `${totalQty} Item`;
                    document.getElementById('floating-cart-total').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
                    banner.style.display = 'flex';
                } else {
                    banner.style.display = 'none';
                }
            }
        }

        // Open Bottom Sheet popup for specific menu item
        function openAddToCartModal(id, name, price, description) {
            currentItem = { id, name, price, quantity: 1, note: '' };
            
            document.getElementById('modal-item-name').innerText = name;
            document.getElementById('modal-item-desc').innerText = description || 'Brewed fresh daily by certified baristas.';
            document.getElementById('modal-item-price-label').innerText = `Rp ${price.toLocaleString('id-ID')}`;
            document.getElementById('modal-item-qty').innerText = '1';
            document.getElementById('modal-item-note').value = '';
            document.getElementById('modal-item-total-price').innerText = `Rp ${price.toLocaleString('id-ID')}`;
            
            document.getElementById('bottom-sheet').style.display = 'flex';
        }

        // Close Bottom Sheet popup
        function closeAddToCartModal() {
            document.getElementById('bottom-sheet').style.display = 'none';
            currentItem = null;
        }

        // Adjust modal quantity +/-
        function adjustModalQty(val) {
            if (!currentItem) return;
            currentItem.quantity = Math.max(1, currentItem.quantity + val);
            
            document.getElementById('modal-item-qty').innerText = currentItem.quantity;
            const totalPrice = currentItem.price * currentItem.quantity;
            document.getElementById('modal-item-total-price').innerText = `Rp ${totalPrice.toLocaleString('id-ID')}`;
        }

        // Confirm Add to Cart inside Modal
        function confirmAddToCart() {
            if (!currentItem) return;
            
            currentItem.note = document.getElementById('modal-item-note').value;
            
            // Check if item with same note already in cart to increment instead of duplicate
            const existingIdx = cart.findIndex(item => item.id === currentItem.id && item.note === currentItem.note);
            if (existingIdx !== -1) {
                cart[existingIdx].quantity += currentItem.quantity;
            } else {
                cart.push({...currentItem});
            }
            
            saveCart();
            closeAddToCartModal();
            
            // Show toast notification
            const toast = document.getElementById('toast');
            if (toast) {
                toast.innerText = `✅ ${currentItem.name} ditambahkan ke keranjang!`;
                toast.style.display = 'block';
                setTimeout(() => {
                    toast.style.display = 'none';
                }, 2000);
            }
        }

        // RENDER CART LIST (State = Cart)
        function renderCartList() {
            const listContainer = document.getElementById('cart-list');
            const summaryContainer = document.getElementById('cart-summary');
            
            if (!listContainer) return;
            
            listContainer.innerHTML = '';
            
            if (cart.length === 0) {
                listContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.01); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.05);">
                        <div style="font-size: 32px; margin-bottom: 12px;">🛒</div>
                        <strong>Keranjang Belanja Kosong</strong>
                        <p class="muted" style="font-size: 13px; margin: 6px 0 16px 0;">Menu lezat kami sedang menanti Anda.</p>
                        <a href="${window.location.pathname.replace('/cart', '/menu')}" class="btn btn-gold" style="font-size: 12px; padding: 8px 16px; text-decoration: none; display: inline-block;">Pesan Minuman</a>
                    </div>
                `;
                summaryContainer.style.display = 'none';
                return;
            }
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                const noteHtml = item.note ? `<div class="muted" style="font-size: 11px; font-style: italic; color: var(--text-gold); margin-top: 2px;">Note: ${item.note}</div>` : '';
                
                const card = document.createElement('div');
                card.style.background = 'rgba(255,255,255,0.01)';
                card.style.border = '1px solid rgba(255,255,255,0.04)';
                card.style.padding = '12px 16px';
                card.style.borderRadius = '10px';
                card.style.display = 'flex';
                card.style.justifyContent = 'space-between';
                card.style.alignItems = 'center';
                
                card.innerHTML = `
                    <div>
                        <strong style="font-size: 14px; color: var(--text-main);">${item.name}</strong>
                        <div style="font-size: 12px; color: var(--text-gold); font-weight: 700; margin-top: 2px;">Rp ${item.price.toLocaleString('id-ID')}</div>
                        ${noteHtml}
                    </div>
                    <div style="display:flex; flex-direction:column; align-items:flex-end; gap: 8px;">
                        <div style="font-size: 13px; font-weight: 800; color: var(--text-main);">Rp ${itemTotal.toLocaleString('id-ID')}</div>
                        
                        <div style="display: flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 4px; padding: 2px 8px;">
                            <button style="background:none; border:none; font-weight:800; color: var(--text-gold); cursor:pointer; font-size: 14px;" onclick="adjustCartQty(${index}, -1)">-</button>
                            <span style="font-size: 13px; font-weight: 800; color: var(--text-main); min-width: 14px; text-align: center;">${item.quantity}</span>
                            <button style="background:none; border:none; font-weight:800; color: var(--text-gold); cursor:pointer; font-size: 14px;" onclick="adjustCartQty(${index}, 1)">+</button>
                        </div>
                    </div>
                `;
                listContainer.appendChild(card);
            });
            
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const serviceFee = {{ $serviceFee }};
            const total = subtotal + serviceFee;
            
            document.getElementById('subtotal-val').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('total-val').innerText = `Rp ${total.toLocaleString('id-ID')}`;
            summaryContainer.style.display = 'block';
        }

        // Adjust cart quantity +/- in cart state list
        function adjustCartQty(index, val) {
            cart[index].quantity += val;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            saveCart();
            renderCartList();
        }

        // SUBMIT ORDER TO BACKEND CREATING ORDER RECORD
        function submitOrder() {
            const btn = document.getElementById('btn-checkout');
            btn.disabled = true;
            btn.innerText = '⌛ Memproses Pesanan...';
            
            const noteInput = document.getElementById('general-note');
            const generalNote = noteInput ? noteInput.value : '';
            
            fetch('{{ route('customer.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cart: cart,
                    note: generalNote
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Clear cart
                    cart = [];
                    saveCart();
                    
                    // Save active invoice for polling
                    localStorage.setItem('cafeflow_active_invoice', data.invoice);
                    
                    // Redirect to status page
                    window.location.href = '{{ route('customer', 'status') }}';
                } else {
                    alert('Terjadi kesalahan: ' + (data.message || 'Gagal memproses.'));
                    btn.disabled = false;
                    btn.innerText = '⚡ Pesan & Bayar QRIS';
                }
            })
            .catch(err => {
                console.error(err);
                alert('Gagal tersambung ke server.');
                btn.disabled = false;
                btn.innerText = '⚡ Pesan & Bayar QRIS';
            });
        }

        // REALTIME ORDER STATUS POLLING (State = Status)
        let pollingInterval = null;

        function initOrderStatusTracker() {
            const invoice = localStorage.getItem('cafeflow_active_invoice');
            const label = document.getElementById('invoice-label');
            
            if (!invoice) {
                label.innerText = 'Belum Ada Pesanan Aktif';
                return;
            }
            
            label.innerText = `NOMOR ORDER: ${invoice}`;
            
            // Immediate check, then poll every 3 seconds
            pollStatus(invoice);
            pollingInterval = setInterval(() => {
                pollStatus(invoice);
            }, 3000);
        }

        function pollStatus(invoice) {
            fetch(`/customer/order-status/${invoice}`)
            .then(res => res.json())
            .then(data => {
                updateStatusSteps(data.status);
            })
            .catch(err => console.error('Gagal polling status:', err));
        }

        function updateStatusSteps(status) {
            // Steps objects
            const step1 = document.getElementById('status-step-1');
            const step2 = document.getElementById('status-step-2');
            const step3 = document.getElementById('status-step-3');
            const step4 = document.getElementById('status-step-4');
            
            // Base styles
            step1.style.opacity = '0.35';
            step2.style.opacity = '0.35';
            step3.style.opacity = '0.35';
            step4.style.opacity = '0.35';
            
            const num1 = step1.querySelector('.step-num');
            const num2 = step2.querySelector('.step-num');
            const num3 = step3.querySelector('.step-num');
            const num4 = step4.querySelector('.step-num');
            
            num1.style.background = '#6b7280';
            num2.style.background = '#6b7280';
            num3.style.background = '#6b7280';
            num4.style.background = '#6b7280';

            if (status === 'WAITING_PAYMENT') {
                step1.style.opacity = '1';
                num1.style.background = '#f59e0b'; // Amber warning
            } else if (status === 'PAID') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '1';
                num1.style.background = '#10b981'; // Green success
                num2.style.background = '#3b82f6'; // Blue active
            } else if (status === 'MAKING') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '0.6';
                step3.style.opacity = '1';
                num1.style.background = '#10b981';
                num2.style.background = '#10b981';
                num3.style.background = 'var(--text-gold)'; // Gold brewing
            } else if (status === 'READY' || status === 'DONE') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '0.6';
                step3.style.opacity = '0.6';
                step4.style.opacity = '1';
                num1.style.background = '#10b981';
                num2.style.background = '#10b981';
                num3.style.background = '#10b981';
                num4.style.background = '#10b981'; // Green ready
                
                // Clear interval once done
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } else if (status === 'CANCEL') {
                label.innerText = `ORDER DICANCEL · ${invoice}`;
                step1.style.opacity = '1';
                num1.style.background = '#ef4444';
                num1.innerText = '✕';
            }
        }

        // Clean polling on page leave
        window.addEventListener('beforeunload', () => {
            if (pollingInterval) {
                clearInterval(pollingInterval);
            }
        });
    </script>
</x-layouts.app>
