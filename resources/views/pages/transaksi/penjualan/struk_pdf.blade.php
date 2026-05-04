<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }

        .header {
            background: #1e3a5f;
            color: #fff;
            text-align: center;
            padding: 14px;
            margin-bottom: 16px;
        }
        .header h1 { font-size: 20px; letter-spacing: 2px; }
        .header p  { font-size: 10px; opacity: .8; margin-top: 2px; }

        .resi-box {
            border: 1.5px dashed #aaa;
            border-radius: 4px;
            text-align: center;
            height: 90%;
            display: flex;
            align-items: start;
            justify-content: center;
            overflow: hidden;
        }
        .resi-box img { width: 100%; height: auto; }
        .no-resi { color: #999; padding: 30px; font-size: 11px; }

        .spacer { height: 20px; }

        .nomor-box {
            border: 1.5px solid #ddd;
            border-radius: 4px;
            padding: 10px 14px;
            background: #f8f9fa;
        }
        .nomor-label { font-size: 9px; color: #888; text-transform: uppercase; letter-spacing: 1px; }
        .nomor-value { font-size: 16px; font-weight: bold; color: #1e3a5f; margin-top: 3px; }

        .info-table { width: 100%; margin-top: 12px; border-collapse: collapse; }
        .info-table td { padding: 5px 0; font-size: 11px; border-bottom: 1px solid #f0f0f0; }
        .info-table td:last-child { text-align: right; font-weight: bold; }

        .footer {
            margin-top: 16px;
            text-align: center;
            font-size: 20px;
            color: #000;
            border-top: 1px dashed #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    {{-- <div class="header">
        <h1>CHRISBALE</h1>
        <p>Bukti Pengiriman</p>
    </div> --}}

    {{-- Resi --}}
    <div class="resi-box">
        @if($resiBase64 && $resiMime)
            <img src="data:{{ $resiMime }};base64,{{ $resiBase64 }}" alt="Resi">
        @elseif($resiIsPdf)
            <div class="no-resi">📄 File resi berupa PDF — lihat file asli untuk detail</div>
        @else
            <div class="no-resi">Tidak ada file resi dilampirkan</div>
        @endif
    </div>

    {{-- Spacer 20px --}}
    {{-- <div class="spacer"></div> --}}

    {{-- Nomor Struk --}}
    {{-- <div class="nomor-box">
        <div class="nomor-label">Nomor Struk</div>
        <div class="nomor-value">{{ $nomorStruk }}</div>
    </div> --}}

    {{-- Info --}}
    {{-- <table class="info-table">
        <tr>
            <td>Tanggal</td>
            <td>{{ \Carbon\Carbon::parse($penjualan->tanggal)->format('d/m/Y') }}</td>
        </tr>
        @if($penjualan->nomor_resi)
        <tr>
            <td>No. Resi</td>
            <td>{{ $penjualan->nomor_resi }}</td>
        </tr>
        @endif
        <tr>
            <td>Total</td>
            <td>Rp {{ number_format($penjualan->total_harga, 0, ',', '.') }}</td>
        </tr>
    </table> --}}

    <div class="footer" style="font-weight: bold">{{ $nomorStruk }}</div>

</body>
</html>