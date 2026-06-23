<?php

namespace App\Support;

class CafeDemoData
{
    public static function metrics(): array
    {
        return [
            ['label' => 'Total Transaksi', 'value' => '18.420', 'trend' => '+18%'],
            ['label' => 'Total Pendapatan', 'value' => 'Rp 842 Jt', 'trend' => '+24%'],
            ['label' => 'Total User', 'value' => '186', 'trend' => '98 aktif'],
        ];
    }

    public static function activities(): array
    {
        return [
            ['time' => '10:42', 'text' => 'Kasir Kemang menerima pembayaran QRIS #INV-2048'],
            ['time' => '10:37', 'text' => 'Barista BSD menandai 6 pesanan siap antar'],
            ['time' => '10:31', 'text' => 'Admin Bandung memperbarui stok Matcha Latte'],
            ['time' => '10:28', 'text' => 'Pelayan Surabaya memindahkan meja A4 ke B2'],
        ];
    }

    public static function orders(): array
    {
        return [
            ['code' => '#ORD-9281', 'table' => 'A4', 'items' => '2 Cappuccino, 1 Croissant', 'status' => 'Menunggu Bayar', 'amount' => 'Rp 118.000'],
            ['code' => '#ORD-9280', 'table' => 'B2', 'items' => '1 Aren Latte, 1 Cold Brew', 'status' => 'Diproses', 'amount' => 'Rp 76.000'],
            ['code' => '#ORD-9279', 'table' => 'C1', 'items' => '3 Espresso Tonic', 'status' => 'Siap Antar', 'amount' => 'Rp 135.000'],
            ['code' => '#ORD-9278', 'table' => 'A1', 'items' => '1 Manual Brew, 2 Donut', 'status' => 'Selesai', 'amount' => 'Rp 92.000'],
        ];
    }

    public static function menu(): array
    {
        return [
            ['name' => 'Aren Signature Latte', 'price' => 'Rp 32.000', 'tag' => 'Terlaris'],
            ['name' => 'Cappuccino Reserve', 'price' => 'Rp 34.000', 'tag' => 'Premium'],
            ['name' => 'Cold Brew Citrus', 'price' => 'Rp 38.000', 'tag' => 'Fresh'],
            ['name' => 'Butter Croissant', 'price' => 'Rp 28.000', 'tag' => 'Pastry'],
        ];
    }

    public static function dashboards(): array
    {
        return [
            'super-admin' => [
                'title' => 'Dashboard Super Admin',
                'subtitle' => 'Monitoring transaksi, user, stok, dan aktivitas sistem.',
                'widgets' => ['Total Transaksi', 'Total Pendapatan', 'Total User', 'Stok', 'Grafik Penjualan', 'Aktivitas Sistem'],
            ],
            'admin' => [
                'title' => 'Dashboard Admin',
                'subtitle' => 'Operasional harian, performa menu, order aktif, dan aktivitas staff.',
                'widgets' => ['Total Order Hari Ini', 'Total Pendapatan Hari Ini', 'Menu Terlaris', 'Order Aktif', 'Grafik Penjualan', 'Aktivitas Staff'],
            ],
            'kasir' => [
                'title' => 'Dashboard Kasir',
                'subtitle' => 'Order masuk, pembayaran, riwayat transaksi, dan cetak struk.',
                'widgets' => ['Order Masuk', 'Menunggu Pembayaran', 'Riwayat Pembayaran', 'Cetak Struk'],
            ],
            'barista' => [
                'title' => 'Dashboard Barista',
                'subtitle' => 'Queue produksi realtime dari kasir dan pelanggan QR.',
                'widgets' => ['Queue Pesanan', 'Sedang Dibuat', 'Siap Diantar', 'Statistik Produksi'],
            ],
            'pelayan' => [
                'title' => 'Dashboard Pelayan',
                'subtitle' => 'Pesanan siap antar, status pengantaran, dan riwayat servis meja.',
                'widgets' => ['Pesanan Siap Antar', 'Riwayat Antar', 'Status Pengantaran'],
            ],
        ];
    }

    public static function userFlow(): array
    {
        return ['Scan QR', 'Pesan Menu', 'Bayar', 'Barista Membuat', 'Pesanan Diantar'];
    }
}
