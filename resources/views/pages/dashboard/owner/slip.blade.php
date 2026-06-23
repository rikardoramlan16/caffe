<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji - {{ $payroll->user ? $payroll->user->name : 'Staff' }} - Kopi Senja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            background: #fff;
            padding: 40px;
            margin: 0;
        }
        .slip-container {
            max-width: 650px;
            margin: 0 auto;
            border: 1px solid #ccc;
            padding: 30px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px double #333;
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            margin: 0 0 4px 0;
            font-size: 24px;
            color: #5c3c24;
            letter-spacing: 1px;
            font-weight: 800;
        }
        .header p {
            margin: 0;
            font-size: 12px;
            color: #777;
        }
        .meta-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }
        .meta-info div {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .meta-info span {
            font-size: 13px;
        }
        .meta-info strong {
            color: #5c3c24;
        }
        .salary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .salary-table th, 
        .salary-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .salary-table th {
            background-color: #fcf8f2;
            color: #5c3c24;
            font-weight: 700;
            font-size: 13px;
        }
        .total-row {
            font-size: 16px;
            font-weight: bold;
            background-color: #fdfaf7;
            color: #10b981;
        }
        .total-row td {
            border-top: 2px solid #5c3c24;
            border-bottom: 2px solid #5c3c24;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 20px;
        }
        .signature {
            text-align: center;
            width: 180px;
        }
        .signature-line {
            margin-top: 60px;
            border-top: 1px solid #333;
            padding-top: 4px;
            font-weight: bold;
        }
        .btn-print-container {
            max-width: 650px;
            margin: 20px auto 0 auto;
            text-align: right;
        }
        .btn-print {
            background-color: #5c3c24;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-print:hover {
            background-color: #3d2416;
        }
        @media print {
            .btn-print-container {
                display: none;
            }
            body {
                padding: 0;
            }
            .slip-container {
                border: none;
                box-shadow: none;
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <div class="slip-container">
        <!-- Header -->
        <div class="header">
            <h1>KOPI SENJA</h1>
            <p>Premium Coffee, QR Ordering POS & Multi-Branch Systems</p>
            <p style="font-size: 11px; margin-top: 2px;">Jl. Kemang Raya No. 45C, Mampang Prapatan, Jakarta Selatan</p>
        </div>

        <!-- Meta info -->
        <div class="meta-info">
            <div>
                <span><strong>Nama Karyawan:</strong> {{ $payroll->user ? $payroll->user->name : 'N/A' }}</span>
                <span><strong>Jabatan / Role:</strong> {{ $payroll->user ? str($payroll->user->role)->replace('_', ' ')->title() : 'Staf' }}</span>
                <span><strong>Cabang Outlet:</strong> {{ $payroll->user && $payroll->user->branch ? $payroll->user->branch->name : 'Pusat' }}</span>
            </div>
            <div style="text-align: right; align-items: flex-end;">
                <span><strong>Bulan Payroll:</strong> {{ $payroll->month }}</span>
                <span><strong>Status Slip:</strong> <strong style="color: #10b981;">{{ $payroll->status }}</strong></span>
                <span><strong>Tanggal Cetak:</strong> {{ now()->translatedFormat('d F Y') }}</span>
            </div>
        </div>

        <!-- Salary Breakdown Table -->
        <table class="salary-table">
            <thead>
                <tr>
                    <th>Komponen Pendapatan</th>
                    <th style="text-align: right;">Jumlah Rupiah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Gaji Pokok Karyawan</td>
                    <td style="text-align: right;">Rp {{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunjangan Kesejahteraan Staf</td>
                    <td style="text-align: right; color: #10b981;">+ Rp {{ number_format($payroll->allowance, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Bonus Prestasi & Lembur Kerja</td>
                    <td style="text-align: right; color: #10b981;">+ Rp {{ number_format($payroll->bonus, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Potongan Absensi / Denda</td>
                    <td style="text-align: right; color: #ef4444;">- Rp {{ number_format($payroll->deduction, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td>Gaji Bersih Diterima (Take Home Pay)</td>
                    <td style="text-align: right;">Rp {{ number_format($payroll->total_salary, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Signature -->
        <div class="footer">
            <div class="signature">
                <span>Penerima,</span>
                <div class="signature-line">{{ $payroll->user ? $payroll->user->name : 'Staf Karyawan' }}</div>
            </div>
            <div class="signature">
                <span>Menyetujui (Owner),</span>
                <div class="signature-line">Kopi Senja Management</div>
            </div>
        </div>
    </div>

    <!-- Print Button -->
    <div class="btn-print-container">
        <button class="btn-print" onclick="window.print()">📷 Cetak Slip Gaji</button>
    </div>
</body>
</html>
