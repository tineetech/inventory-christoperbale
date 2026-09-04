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
            display: flex;
            align-items: start;
            justify-content: center;
        }
        .resi-box img { width: 100%; height: auto; max-height: none; }
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

        /* Page break antar halaman struk */
        .struk-page { page-break-after: always; }
        .struk-page:last-child { page-break-after: auto; }
    </style>
</head>
<body>
    
    @if(empty($resiChunks) && !$resiIsPdf)
        {{-- Tidak ada gambar resi, render 1 halaman saja --}}
        <div class="struk-page">
            <div class="resi-box">
                <div class="no-resi">Tidak ada file resi dilampirkan</div>
            </div>
            <div class="footer" style="font-weight: bold">{{ $nomorStruk }}</div>
        </div>
    @elseif($resiIsPdf)
        {{-- File PDF asli --}}
        <div class="struk-page">
            <div class="resi-box">
                <div class="no-resi">📄 File resi berupa PDF — lihat file asli untuk detail</div>
            </div>
            <div class="footer" style="font-weight: bold">{{ $nomorStruk }}</div>
        </div>
    @else
        {{-- Ada resiChunks (1 = normal, 2 = panjang split 50/50) --}}
        {{-- @php $isMulti = count($resiChunks) > 1; @endphp --}}
        @foreach($resiChunks as $chunkIndex => $chunkBase64)
            <div class="{{ count($resiChunks) > 1 && $chunkIndex === count($resiChunks) - 1 ? 'struk-page' : '' }}">
                <div class="resi-box">
                    <img src="data:image/jpeg;base64,{{ $chunkBase64 }}" alt="Resi"
                        @if($isMulti && $chunkIndex === count($resiChunks) - 1)
                            style="max-height: 55%;" {{-- Halaman terakhir: kecilkan height buat ruang footer --}}
                        @endif
                    >
                </div>
                {{-- Footer nomor struk di halaman terakhir chunk --}}
                @if($chunkIndex === count($resiChunks) - 1)
                    <div class="footer" style="font-weight: bold">{{ $nomorStruk }}</div>
                @endif
            </div>
        @endforeach
    @endif

</body>
</html>