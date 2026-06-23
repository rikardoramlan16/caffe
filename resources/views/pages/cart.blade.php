<x-layouts.app title="Keranjang - Kopi Senja">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top" style="background: var(--coffee-deep); color: white; display: flex; justify-content: space-between; align-items: center; padding: 14px 18px;">
                    <div>
                        <a class="brand" href="{{ route('landing') }}" style="display: flex; align-items: center; gap: 8px;">
                            <span class="brand-mark" style="width: 32px; height: 32px; font-size: 14px;">CF</span>
                            <span style="font-size: 14px; font-weight: 800; color: white;">Keranjang</span>
                        </a>
                    </div>
                    <a href="{{ route('menu.preview') }}" style="color: white; font-size: 12px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 4px;">
                        <span>✕ Tutup</span>
                    </a>
                </div>

                <main class="mobile-body" style="padding-bottom: 30px; position: relative; overflow-y: auto; height: calc(100% - 130px);">
                    <div style="padding: 10px 0;">
                        <span class="eyebrow" style="color: var(--text-gold);">Daftar Belanja</span>
                        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Pesanan Anda</h1>
                        
                        <!-- Cart Items List Container -->
                        <div id="cart-list" style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px;">
                            <!-- Dynamically Rendered by Javascript -->
                        </div>

                        <!-- Summary Panel -->
                        <div id="cart-summary" class="panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px; display: none;">
                            <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
                                <span style="color: var(--text-muted);">Subtotal</span>
                                <strong style="color: var(--text-main);" id="subtotal-val">Rp 0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
                                <span style="color: var(--text-muted);">Biaya Layanan</span>
                                <strong style="color: var(--text-main);">Rp 8.000</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; margin-top: 10px; font-size: 15px;">
                                <span style="font-weight: 700; color: var(--text-main);">Total</span>
                                <strong style="color: #10b981;" id="total-val">Rp 0</strong>
                            </div>

                            <a href="{{ route('checkout.view') }}" class="btn btn-gold" style="width:100%; margin-top:16px; padding: 12px 0; font-weight: 800; font-size: 14px; border: none; text-align: center; text-decoration: none; display: block;">
                                ⚡ Lanjut Ke Pembayaran
                            </a>
                        </div>
                    </div>
                </main>

                <nav class="bottom-nav" aria-label="Navigasi keranjang">
                    <a href="{{ route('menu.preview') }}">Menu</a>
                    <a class="active" href="{{ route('cart.view') }}">Cart</a>
                    <a href="{{ route('order.status') }}">Status</a>
                </nav>
            </div>
        </div>
    </div>

    <script>
        let cart = [];

        document.addEventListener('DOMContentLoaded', () => {
            const storedCart = localStorage.getItem('cafeflow_cart');
            if (storedCart) {
                try { cart = JSON.parse(storedCart); } catch (e) { cart = []; }
            }
            renderCartList();
        });

        function saveCart() {
            localStorage.setItem('cafeflow_cart', JSON.stringify(cart));
        }

        function renderCartList() {
            const container = document.getElementById('cart-list');
            const summary = document.getElementById('cart-summary');
            
            container.innerHTML = '';
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.01); border-radius: 12px; border: 1px dashed rgba(255,255,255,0.05);">
                        <div style="font-size: 40px; margin-bottom: 12px;">🛒</div>
                        <strong>Keranjang Belanja Kosong</strong>
                        <p class="muted" style="font-size: 13px; margin: 6px 0 16px 0;">Silakan pilih kopi premium atau cemilan lezat terlebih dahulu.</p>
                        <a href="{{ route('menu.preview') }}" class="btn btn-gold" style="font-size: 12px; padding: 8px 16px; text-decoration: none; display: inline-block;">Pesan Sekarang</a>
                    </div>
                `;
                summary.style.display = 'none';
                return;
            }
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                
                // Construct customization details (size & toppings)
                let customDetails = `Ukuran: ${item.size}`;
                customDetails += ` · Gula: ${item.sugar_level || '100%'}`;
                customDetails += ` · Es: ${item.ice_level || 'Normal'}`;
                if (item.toppings && item.toppings.length > 0) {
                    customDetails += ` · Topping: ${item.toppings.map(t => t.name).join(', ')}`;
                }
                if (item.note) {
                    customDetails += ` · Catatan: "${item.note}"`;
                }

                const card = document.createElement('div');
                card.style = 'background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.04); padding: 14px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center;';
                card.innerHTML = `
                    <div>
                        <strong style="font-size: 14px; color: var(--text-main);">${item.name}</strong>
                        <div style="font-size: 11px; color: var(--text-gold); font-weight: 500; margin-top: 2px;">${customDetails}</div>
                        <div style="font-size: 12px; color: var(--text-muted); font-weight: 600; margin-top: 4px;">Rp ${item.price.toLocaleString('id-ID')}</div>
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
                container.appendChild(card);
            });
            
            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const serviceFee = 8000;
            const total = subtotal + serviceFee;
            
            document.getElementById('subtotal-val').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('total-val').innerText = `Rp ${total.toLocaleString('id-ID')}`;
            summary.style.display = 'block';
        }

        function adjustCartQty(index, val) {
            cart[index].quantity += val;
            if (cart[index].quantity <= 0) {
                cart.splice(index, 1);
            }
            saveCart();
            renderCartList();
        }
    </script>
</x-layouts.app>
