<x-layouts.app title="Status Pesanan - Kopi Senja">
    <div class="app-shell customer-shell">
        <div class="mobile-stage">
            <div class="mobile-app">
                <div class="mobile-top" style="background: var(--coffee-deep); color: white; display: flex; justify-content: space-between; align-items: center; padding: 14px 18px;">
                    <div>
                        <a class="brand" href="{{ route('landing') }}" style="display: flex; align-items: center; gap: 8px;">
                            <span class="brand-mark" style="width: 32px; height: 32px; font-size: 14px;">@if(!empty($appLogo))<img src="{{ asset($appLogo) }}" alt="Logo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">@else CF @endif</span>
                            <span style="font-size: 14px; font-weight: 800; color: white;">Status Pesanan</span>
                        </a>
                    </div>
                    <a href="{{ route('menu.preview') }}" style="color: white; font-size: 12px; font-weight: 700; text-decoration: none;">
                        <span>Menu</span>
                    </a>
                </div>

                <main class="mobile-body" style="padding-bottom: 30px; position: relative; overflow-y: auto; height: calc(100% - 130px);">
                    <div style="padding: 10px 0; text-align: center;">
                        <span class="eyebrow" style="color: var(--text-gold);">Live Order Status</span>
                        <h1 style="font-size: 24px; font-weight: 800; margin-bottom: 6px; color: var(--text-main);">Pelacak Pesanan</h1>
                        <div id="invoice-label" class="muted" style="font-size: 12px; font-family: monospace; font-weight: 700; margin-bottom: 24px; color: var(--text-gold);">INV-XXXX</div>
                        
                        <!-- 5 Production Steps Progress Tracker -->
                        <div style="display: flex; flex-direction: column; gap: 16px; text-align: left; background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px; margin-bottom: 20px;">
                            
                            <!-- Step 1: Pesanan Diterima -->
                            <div id="status-step-1" style="display: flex; gap: 14px; align-items: center; opacity: 0.35; transition: opacity 0.3s;">
                                <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">1</span>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">✓ Pesanan Diterima</strong>
                                    <div class="muted" style="font-size: 11px;">Pesanan Anda berhasil dikirim ke sistem.</div>
                                </div>
                            </div>
                            
                            <!-- Step 2: Pembayaran Dikonfirmasi -->
                            <div id="status-step-2" style="display: flex; gap: 14px; align-items: center; opacity: 0.35; transition: opacity 0.3s;">
                                <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">2</span>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">✓ Pembayaran Dikonfirmasi</strong>
                                    <div class="muted" style="font-size: 11px;">Pembayaran sukses atau dikonfirmasi oleh kasir.</div>
                                </div>
                            </div>
                            
                            <!-- Step 3: Sedang Dibuat -->
                            <div id="status-step-3" style="display: flex; gap: 14px; align-items: center; opacity: 0.35; transition: opacity 0.3s;">
                                <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">3</span>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">✓ Sedang Dibuat</strong>
                                    <div class="muted" style="font-size: 11px;">Barista kami sedang meracik minuman lezat Anda.</div>
                                </div>
                            </div>
                            
                            <!-- Step 4: Siap Diambil -->
                            <div id="status-step-4" style="display: flex; gap: 14px; align-items: center; opacity: 0.35; transition: opacity 0.3s;">
                                <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">4</span>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">✓ Siap Diambil</strong>
                                    <div class="muted" style="font-size: 11px; color: #10b981; font-weight: 700;">Pesanan selesai! Silakan ambil di konter barista.</div>
                                </div>
                            </div>

                            <!-- Step 5: Selesai -->
                            <div id="status-step-5" style="display: flex; gap: 14px; align-items: center; opacity: 0.35; transition: opacity 0.3s;">
                                <span class="step-num" style="background: #6b7280; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px;">5</span>
                                <div>
                                    <strong style="color: var(--text-main); font-size: 13px;">✓ Selesai</strong>
                                    <div class="muted" style="font-size: 11px;">Selamat menikmati sajian Kopi Senja!</div>
                                </div>
                            </div>
                        </div>

                        <!-- Realtime notification note -->
                        <div style="background: rgba(199, 154, 75, 0.05); border: 1px dashed rgba(199, 154, 75, 0.2); padding: 14px; border-radius: 10px;">
                            <span style="font-weight: 700; color: var(--text-gold); font-size: 12px; display: block; margin-bottom: 2px;">Terhubung secara Realtime</span>
                            <p class="muted" style="font-size: 11px; margin: 0; line-height: 1.5;">Status pesanan Anda akan terupdate secara otomatis saat kasir memverifikasi pembayaran atau barista meracik minuman Anda.</p>
                        </div>
                    </div>
                </main>

                <nav class="bottom-nav" aria-label="Navigasi status">
                    <a href="{{ route('menu.preview') }}">Menu</a>
                    <a href="{{ route('cart.view') }}">Cart</a>
                    <a class="active" href="#">Status</a>
                </nav>
            </div>
        </div>
    </div>

    <script>
        let pollingInterval = null;

        document.addEventListener('DOMContentLoaded', () => {
            const invoice = localStorage.getItem('cafeflow_active_invoice');
            const label = document.getElementById('invoice-label');
            
            if (!invoice) {
                label.innerText = 'Belum Ada Pesanan Aktif';
                return;
            }
            
            label.innerText = `NOMOR ORDER: ${invoice}`;
            
            // Immediate check and poll every 3 seconds
            pollStatus(invoice);
            pollingInterval = setInterval(() => {
                pollStatus(invoice);
            }, 3000);
        });

        function pollStatus(invoice) {
            fetch(`/customer/order-status/${invoice}`)
            .then(res => res.json())
            .then(data => {
                updateStatusSteps(data.status);
            })
            .catch(err => console.error('Gagal polling status:', err));
        }

        function updateStatusSteps(status) {
            const step1 = document.getElementById('status-step-1');
            const step2 = document.getElementById('status-step-2');
            const step3 = document.getElementById('status-step-3');
            const step4 = document.getElementById('status-step-4');
            const step5 = document.getElementById('status-step-5');
            
            // Reset opacity and colors
            const steps = [step1, step2, step3, step4, step5];
            steps.forEach(step => {
                step.style.opacity = '0.35';
                const num = step.querySelector('.step-num');
                num.style.background = '#6b7280';
                num.innerText = num.innerText.replace('✓', '').trim();
            });

            const num1 = step1.querySelector('.step-num');
            const num2 = step2.querySelector('.step-num');
            const num3 = step3.querySelector('.step-num');
            const num4 = step4.querySelector('.step-num');
            const num5 = step5.querySelector('.step-num');

            if (status === 'WAITING_PAYMENT') {
                step1.style.opacity = '1';
                num1.style.background = '#f59e0b';
                num1.innerText = '✓';
            } 
            else if (status === 'PAID') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '1';
                num1.style.background = '#10b981';
                num1.innerText = '✓';
                num2.style.background = '#3b82f6';
                num2.innerText = '✓';
            } 
            else if (status === 'MAKING') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '0.6';
                step3.style.opacity = '1';
                num1.style.background = '#10b981';
                num1.innerText = '✓';
                num2.style.background = '#10b981';
                num2.innerText = '✓';
                num3.style.background = 'var(--text-gold)';
                num3.innerText = '✓';
            } 
            else if (status === 'READY') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '0.6';
                step3.style.opacity = '0.6';
                step4.style.opacity = '1';
                num1.style.background = '#10b981';
                num1.innerText = '✓';
                num2.style.background = '#10b981';
                num2.innerText = '✓';
                num3.style.background = '#10b981';
                num3.innerText = '✓';
                num4.style.background = '#10b981';
                num4.innerText = '✓';
            } 
            else if (status === 'DONE') {
                step1.style.opacity = '0.6';
                step2.style.opacity = '0.6';
                step3.style.opacity = '0.6';
                step4.style.opacity = '0.6';
                step5.style.opacity = '1';
                num1.style.background = '#10b981';
                num1.innerText = '✓';
                num2.style.background = '#10b981';
                num2.innerText = '✓';
                num3.style.background = '#10b981';
                num3.innerText = '✓';
                num4.style.background = '#10b981';
                num4.innerText = '✓';
                num5.style.background = '#10b981';
                num5.innerText = '✓';
                
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            } 
            else if (status === 'CANCEL') {
                document.getElementById('invoice-label').innerText = `ORDER DIBATALKAN`;
                step1.style.opacity = '1';
                num1.style.background = '#ef4444';
                num1.innerText = '✕';
                
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }
        }

        window.addEventListener('beforeunload', () => {
            if (pollingInterval) clearInterval(pollingInterval);
        });
    </script>
</x-layouts.app>
