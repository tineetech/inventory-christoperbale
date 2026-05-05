<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk - {{ $nomorStruk }}</title>
    <style>
        /* ========== RESET & BASE ========== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f0f0f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 30px 20px;
            min-height: 100vh;
        }

        /* ========== TOMBOL (hanya tampil di layar, hilang saat print) ========== */
        .action-bar {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: opacity .2s;
        }

        .btn:hover { opacity: .85; }

        .btn-print {
            background: #f8fafc;
            color: black;
        }
        
        .btn-download {
            background: #f8fafc;
            color: black;
        }
        
        .btn-back {
            background: #f8fafc;
            color: black;
        }

        /* ========== STRUK / KARTU ========== */
        .struk-wrapper {
            background: #fff;
            width: 100%;
            max-width: 420px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,.12);
            overflow: hidden;
        }

        /* Header toko */
        .struk-header {
            background: #1e3a5f;
            color: #fff;
            text-align: center;
            padding: 18px 20px 14px;
        }

        .struk-header .toko-nama {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 2px;
        }

        .struk-header .toko-sub {
            font-size: 11px;
            opacity: .75;
            margin-top: 2px;
        }

        /* Body */
        .struk-body {
            padding: 20px 24px;
        }

        /* Area gambar / PDF resi */
        .resi-container {
            width: 100%;
            border: 1.5px dashed #d1d5db;
            border-radius: 6px;
            overflow: hidden;
            background: red;
            display: flex;
            align-items: start;
            justify-content: center;
            height: auto;
        }

        .resi-container img {
            width: 100%;
            height: auto;
            object-fit: contain;
            display: block;
        }

        .resi-container iframe {
            width: 100%;
            height: 280px;
            border: none;
        }

        .no-resi-placeholder {
            color: #9ca3af;
            font-size: 13px;
            text-align: center;
            padding: 30px;
        }

        /* Spacer 20px antara resi dan nomor */
        .spacer-20 {
            height: 20px;
        }

        /* Nomor struk */
        .nomor-struk-box {
            border: 1.5px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px 16px;
            background: #f8fafc;
        }

        .nomor-struk-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .nomor-struk-value {
            font-size: 17px;
            font-weight: 700;
            color: #1e3a5f;
            letter-spacing: 1.5px;
            word-break: break-all;
        }

        /* Info tambahan */
        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #374151;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #f3f4f6;
        }

        .info-row span:last-child {
            font-weight: 600;
        }

        /* Footer */
        .struk-footer {
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #000;
            padding: 12px 20px 16px;
            border-top: 1px dashed #e5e7eb;
        }

        /* ========== PRINT STYLES ========== */
        @media print {
            @page {
                size: A5 portrait;
                margin: 10mm;
            }

            body {
                background: #fff;
                padding: 0;
            }

            /* Sembunyikan tombol saat print */
            .action-bar {
                display: none !important;
            }

            .struk-wrapper {
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
                width: 100%;
            }

            .struk-header {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    {{-- ===== TOMBOL AKSI ===== --}}
    <div class="action-bar">
        <button class="btn btn-print" onclick="window.print()">
            🖨️ Print Struk
        </button>
        <a class="btn btn-download" href="{{ route('penjualan.struk.download', $penjualan->id) }}">
            ⬇️ Download PDF
        </a>
        <a class="btn btn-back" href="{{ route('penjualan.index') }}">
            ← Kembali
        </a>
    </div>

    {{-- ===== STRUK ===== --}}
    <div class="struk-wrapper" id="struk-cetak">

        {{-- Header --}}
        {{-- <div class="struk-header">
            <div class="toko-nama">CHRISBALE</div>
            <div class="toko-sub">Bukti Pengiriman</div>
        </div> --}}

        {{-- Body --}}
        <div class="struk-body">

            {{-- Gambar / PDF Resi --}}
            <div class="resi-container">
                @if($penjualan->file_resi)
                    @php
                        $ext = strtolower(pathinfo($penjualan->file_resi, PATHINFO_EXTENSION));
                    @endphp

                    @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']))
                        {{-- Tampilkan sebagai gambar --}}
                        <img src="{{ asset('storage/' . $penjualan->file_resi) }}"
                             alt="File Resi">
                    @elseif($ext === 'pdf')
                        {{-- Tampilkan sebagai iframe PDF --}}
                        <iframe src="{{ asset('storage/' . $penjualan->file_resi) }}#toolbar=0"
                                title="File Resi PDF"></iframe>
                    @endif
                @else
                    <div class="no-resi-placeholder">
                        📋 Tidak ada file resi dilampirkan
                    </div>
                @endif
            </div>

            {{-- Spacer 20px --}}
            <div class="spacer-20"></div>

        </div>

        {{-- Footer --}}
        <div class="struk-footer">
            {{ $nomorStruk }}
        </div>

    </div>

    <script>
        // Auto-trigger print dialog saat halaman terbuka
        window.addEventListener('load', function () {
            // Delay kecil agar gambar/iframe sempat render
            setTimeout(function () {
                window.print();
            }, 800);
        });
    </script>

</body>
</html>