<x-layouts.app title="Checkout - Kopi Senja">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top" style="background: var(--coffee-deep); color: white; display: flex; justify-content: space-between; align-items: center; padding: 14px 18px;">
                    <div>
                        <a class="brand" href="{{ route('landing') }}" style="display: flex; align-items: center; gap: 8px;">
                            <span class="brand-mark" style="width: 32px; height: 32px; font-size: 14px;">CF</span>
                            <span style="font-size: 14px; font-weight: 800; color: white;">Checkout</span>
                        </a>
                    </div>
                    <a href="{{ route('cart.view') }}" style="color: white; font-size: 12px; font-weight: 700; text-decoration: none;">
                        <span>Kembali</span>
                    </a>
                </div>

                <main class="mobile-body" style="padding-bottom: 30px; position: relative; overflow-y: auto; height: calc(100% - 130px);">
                    <div style="padding: 10px 0;">
                        <span class="eyebrow" style="color: var(--text-gold);">Finalisasi Order</span>
                        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 16px; color: var(--text-main);">Konfirmasi Pesanan</h1>
                        
                        <!-- Order Details Panel -->
                        <div class="panel" style="padding: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 20px; display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span class="muted">Nomor Antrean (Estimasi)</span>
                                <strong style="color: var(--text-gold);">{{ $queueNumber }}</strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 13px;">
                                <span class="muted">Nomor Meja</span>
                                <strong style="color: var(--text-main);">📍 Meja {{ $table->number }} ({{ $table->code }})</strong>
                            </div>
                        </div>

                        <!-- Summary of items -->
                        <div id="checkout-items" style="display: flex; flex-direction: column; gap: 8px; margin-bottom: 20px;">
                            <!-- Items loaded via JS -->
                        </div>

                        <!-- Payment Method Section -->
                        <div class="panel" style="padding: 16px; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; margin-bottom: 20px;">
                            <h3 style="font-size: 13px; font-weight: 800; margin-bottom: 12px; color: var(--text-gold); text-transform: uppercase;">Pilih Metode Pembayaran</h3>
                            
                            <div style="display: flex; flex-direction: column; gap: 10px;">
                                <label style="border: 1px solid var(--line); border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: rgba(255,255,255,0.01);">
                                    <div>
                                        <strong style="font-size: 13px; display: block;">⚡ QRIS (Bayar Instan)</strong>
                                        <span class="muted" style="font-size: 11px;">Konfirmasi otomatis e-wallet / m-banking</span>
                                    </div>
                                    <input type="radio" name="payment_method" value="QRIS" checked>
                                </label>
                                
                                <label style="border: 1px solid var(--line); border-radius: 8px; padding: 12px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: rgba(255,255,255,0.01);">
                                    <div>
                                        <strong style="font-size: 13px; display: block;">💵 Bayar di Kasir (Cash)</strong>
                                        <span class="muted" style="font-size: 11px;">Konfirmasi manual oleh kasir di konter</span>
                                    </div>
                                    <input type="radio" name="payment_method" value="Cash">
                                </label>
                            </div>
                        </div>

                        <!-- General Note -->
                        <div style="margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px;">
                            <label for="general-note" style="font-size: 12px; font-weight: 700; color: var(--text-gold);">CATATAN PESANAN TAMBAHAN (OPSIONAL)</label>
                            <input type="text" id="general-note" placeholder="Contoh: Sendok plastik, es batu pisah..." style="width: 100%; padding: 10px; border-radius: 6px; background: var(--bg-app); border: 1px solid rgba(255,255,255,0.08); color: var(--text-main); font-size: 13px; outline: none;">
                        </div>

                        <!-- Final Totals and Checkout CTA -->
                        <div class="panel" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 16px;">
                            <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
                                <span class="muted">Subtotal</span>
                                <strong style="color: var(--text-main);" id="subtotal-val">Rp 0</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom: 8px; font-size: 13px;">
                                <span class="muted">Biaya Layanan</span>
                                <strong>Rp 8.000</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 10px; margin-top: 10px; font-size: 16px;">
                                <span style="font-weight: 700; color: var(--text-main);">Total Bayar</span>
                                <strong style="color: #10b981;" id="total-val">Rp 0</strong>
                            </div>

                            <button id="btn-submit" class="btn btn-gold" style="width:100%; margin-top:16px; padding: 12px 0; font-weight: 800; font-size: 14px; border: none;" onclick="submitOrder()">
                                ⚡ Pesan Sekarang
                            </button>
                        </div>
                    </div>
                </main>

                <nav class="bottom-nav" aria-label="Navigasi checkout">
                    <a href="{{ route('menu.preview') }}">Menu</a>
                    <a href="{{ route('cart.view') }}">Cart</a>
                    <a class="active" href="#">Checkout</a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Realtime processing simulator -->
    <div id="processing-modal" style="position: fixed; top: 0; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.85); display: none; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(5px);">
        <div style="background: var(--bg-surface); width: min(320px, 90%); border-radius: 16px; padding: 30px; box-shadow: 0 12px 40px rgba(0,0,0,0.5); border: 1px solid rgba(255,255,255,0.05); text-align: center; display: flex; flex-direction: column; gap: 16px;">
            <div style="font-size: 40px; animation: rotate 2s infinite linear; display: inline-block;">⌛</div>
            <div>
                <h2 style="font-size: 18px; font-weight: 800; color: var(--text-main); margin: 0 0 6px 0;">Membuat Pesanan...</h2>
                <p class="muted" style="font-size: 12px; margin: 0; line-height: 1.5;">Menyambung ke gerbang pembayaran online aman dan mengirimkan pesanan Anda ke konter Kasir.</p>
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
            renderCheckoutItems();
        });

        function renderCheckoutItems() {
            const container = document.getElementById('checkout-items');
            if (cart.length === 0) {
                window.location.href = "{{ route('menu.preview') }}";
                return;
            }
            
            container.innerHTML = '';
            cart.forEach(item => {
                const itemTotal = item.price * item.quantity;
                let details = `Ukuran: ${item.size}`;
                details += ` · Gula: ${item.sugar_level || '100%'}`;
                details += ` · Es: ${item.ice_level || 'Normal'}`;
                if (item.toppings && item.toppings.length > 0) {
                    details += ` · Topping: ${item.toppings.map(t => t.name).join(', ')}`;
                }

                const card = document.createElement('div');
                card.style = 'background: rgba(255,255,255,0.01); border: 1px solid rgba(255,255,255,0.03); border-radius: 8px; padding: 10px 14px; display: flex; justify-content: space-between; font-size: 12px; align-items: center;';
                card.innerHTML = `
                    <div>
                        <strong>${item.name} <span style="color: var(--text-gold);">x${item.quantity}</span></strong>
                        <div class="muted" style="font-size: 10px; margin-top: 2px;">${details}</div>
                    </div>
                    <strong>Rp ${itemTotal.toLocaleString('id-ID')}</strong>
                `;
                container.appendChild(card);
            });

            const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            const serviceFee = 8000;
            const total = subtotal + serviceFee;
            
            document.getElementById('subtotal-val').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('total-val').innerText = `Rp ${total.toLocaleString('id-ID')}`;
        }

        function submitOrder() {
            document.getElementById('processing-modal').style.display = 'flex';
            
            const noteVal = document.getElementById('general-note').value;
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

            fetch('{{ route('customer.checkout') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    cart: cart.map(item => ({
                        id: item.id,
                        price: item.price,
                        quantity: item.quantity,
                        size: item.size,
                        sugar_level: item.sugar_level,
                        ice_level: item.ice_level,
                        note: item.note,
                        toppings: item.toppings
                    })),
                    note: noteVal,
                    payment_method: paymentMethod
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Clear cart
                    cart = [];
                    localStorage.setItem('cafeflow_cart', JSON.stringify(cart));
                    
                    // Save invoice number for status page
                    localStorage.setItem('cafeflow_active_invoice', data.invoice);
                    
                    setTimeout(() => {
                        window.location.href = "{{ route('order.status') }}";
                    }, 1200);
                } else {
                    document.getElementById('processing-modal').style.display = 'none';
                    alert('Gagal memproses pesanan: ' + (data.message || 'Error'));
                }
            })
            .catch(err => {
                console.error(err);
                document.getElementById('processing-modal').style.display = 'none';
                alert('Terjadi kesalahan koneksi ke server.');
            });
        }
    </script>

    <style>
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</x-layouts.app>
