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
    
    @foreach($struks as $index => $struk)
        @php
            $penjualan  = $struk['penjualan'];
            $nomorStruk = $struk['nomorStruk'];
            $resiChunks = $struk['resiChunks'] ?? [];
            $resiMime   = $struk['resiMime'] ?? null;
            $resiIsPdf  = $struk['resiIsPdf'] ?? false;
        @endphp

        @if(empty($resiChunks) && !$resiIsPdf)
            {{-- Tidak ada gambar resi, render 1 halaman saja --}}
            <div class="struk-page">
                <div class="resi-box">
                    <div class="no-resi"  style="max-height: 75%;">Tidak ada file resi dilampirkan</div>
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
            @foreach($resiChunks as $chunkIndex => $chunkBase64)
                <div class="struk-page">
                    <div class="resi-box">
                        <img src="data:image/jpeg;base64,{{ $chunkBase64 }}" alt="Resi"
                            @if(count($resiChunks) > 1 && $chunkIndex === count($resiChunks) - 1)
                                style="max-height: 75%;" {{-- Halaman terakhir: kecilkan height buat ruang footer --}}
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
    @endforeach

</body>
</html>