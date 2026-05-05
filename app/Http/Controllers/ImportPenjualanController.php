<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImportPenjualanController extends Controller
{


    private function fastApiUrl(): string
    {
        return env('FASTAPI_URL');
    }


    // ============================================================
    // PATCH 1: Controller — tambah image_base64 di normaliseData()
    // ============================================================
    // Cari fungsi normaliseData() di controller yang sama,
    // pastikan field image_base64 ikut di-map.
    // Contoh yang SALAH (image_base64 tidak disertakan):
    //
    // private function normaliseData(array $data): array
    // {
    //     return array_map(fn($r) => [
    //         'page'     => $r['page']     ?? null,
    //         'resi'     => $r['resi']     ?? null,
    //         'order_id' => $r['order_id'] ?? null,
    //         'items'    => $r['items']    ?? [],
    //         'skus'     => $r['skus']     ?? [],
    //         // ← image_base64 HILANG
    //     ], $data);
    // }
    //
    // Fix — ganti dengan ini:

    // ============================================================
    // PATCH 2: importMultipleResi() — debug log + pass image_base64
    // ============================================================
    // Tambahkan log setelah $payload = $response->json(); untuk
    // verifikasi FastAPI memang mengirim image_base64:

    public function importMultipleResi(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480',
            'mode' => 'nullable|string|in:shopee,tiktok',
        ]);

        $mode = $request->input('mode', 'shopee');
        $file = $request->file('file');

        try {
            $response = Http::withoutVerifying()->timeout(120)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($this->fastApiUrl() . '/scan-resi-multiple', [
                    'mode' => $mode,
                ]);

            if ($response->failed()) {
                Log::error('[ImportMultipleResi] FastAPI error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'OCR service error: HTTP ' . $response->status(),
                ], 502);
            }

            $payload = $response->json();

            // ★ DEBUG LOG — cek apakah FastAPI mengirim image_base64
            // Hapus setelah masalah resolved
            $firstPage = $payload['data'][0] ?? null;
            Log::debug('[ImportMultipleResi] FastAPI response check', [
                'total_pages'           => count($payload['data'] ?? []),
                'first_page_has_image'  => isset($firstPage['image_base64']) && !empty($firstPage['image_base64']),
                'first_page_image_len'  => strlen($firstPage['image_base64'] ?? ''),
                'first_page_resi'       => $firstPage['resi'] ?? null,
            ]);

            if (isset($payload['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $payload['error'],
                ], 422);
            }

            $normalised = $this->normaliseData($payload['data'] ?? []);

            $warnings = [];

            foreach ($normalised as $idx => &$resiData) {
                $label = 'Halaman ' . ($resiData['page'] ?? ($idx + 1));

                if (!empty($resiData['resi'])) {
                    $isDuplicate = \App\Models\Penjualan::where('nomor_resi', $resiData['resi'])->exists();
                    if ($isDuplicate) {
                        $resiData['is_duplicate'] = true;
                        $warnings[] = [
                            'type'    => 'duplicate_resi',
                            'page'    => $resiData['page'] ?? ($idx + 1),
                            'message' => "{$label}: Nomor resi <strong>{$resiData['resi']}</strong> sudah ada di database — akan dilewati.",
                        ];
                    } else {
                        $resiData['is_duplicate'] = false;
                    }
                } else {
                    $resiData['is_duplicate'] = false;
                    $warnings[] = [
                        'type'    => 'no_resi',
                        'page'    => $resiData['page'] ?? ($idx + 1),
                        'message' => "{$label}: Nomor resi tidak terbaca — tetap ditambahkan, isi manual.",
                    ];
                }

                if (empty($resiData['skus'])) {
                    $warnings[] = [
                        'type'    => 'no_sku',
                        'page'    => $resiData['page'] ?? ($idx + 1),
                        'message' => "{$label}: Tidak ada SKU yang terbaca — tambahkan barang secara manual.",
                    ];
                }
            }
            unset($resiData);

            $validData   = array_values(array_filter($normalised, fn($r) => !($r['is_duplicate'] ?? false)));
            $skippedData = array_values(array_filter($normalised, fn($r) => ($r['is_duplicate'] ?? false)));

            return response()->json([
                'success'       => true,
                'mode'          => $payload['mode'] ?? $mode,
                'total'         => count($validData),
                'total_skipped' => count($skippedData),
                'data'          => $validData,   // ★ image_base64 sudah ikut via normaliseData
                'warnings'      => $warnings,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[ImportMultipleResi] Tidak bisa konek ke FastAPI', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'OCR service tidak dapat dijangkau. Pastikan FastAPI berjalan.',
            ], 503);
        } catch (\Exception $e) {
            Log::error('[ImportMultipleResi] Exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // HELPER — pastikan tiap item di data punya key yang konsisten
    // supaya JS tidak perlu defensive check terlalu banyak
    // ================================================================
    // private function normaliseData(array $data): array
    // {
    //     return array_map(function ($resi) {
    //         return [
    //             'page'     => $resi['page']     ?? null,
    //             'resi'     => $resi['resi']     ?? null,
    //             'order_id' => $resi['order_id'] ?? null,
    //             'skus'     => $resi['skus']     ?? [],
    //             'items'    => array_map(function ($item) {
    //                 return [
    //                     'nama'    => $item['nama']    ?? '',
    //                     'sku'     => $item['sku']     ?? '',
    //                     'variasi' => $item['variasi'] ?? '',
    //                     'qty'     => (int) ($item['qty'] ?? 1),
    //                 ];
    //             }, $resi['items'] ?? []),
    //         ];
    //     }, $data);
    // }

    private function normaliseData(array $data): array
    {
        return array_map(function ($r) {
            // Normalkan items: pastikan tiap item punya field sku & qty
            $items = array_map(fn($item) => [
                'sku'     => $item['sku']     ?? $item['SKU']  ?? null,
                'qty'     => (int) ($item['qty'] ?? $item['jumlah'] ?? 1),
                'nama'    => $item['nama']    ?? $item['nama_barang'] ?? null,
                'variasi' => $item['variasi'] ?? null,
            ], $r['items'] ?? []);

            return [
                'page'         => $r['page']         ?? null,
                'resi'         => $r['resi']         ?? null,
                'order_id'     => $r['order_id']     ?? null,
                'items'        => $items,
                'skus'         => $r['skus']         ?? [],
                // ★ FIX UTAMA: sertakan image_base64 dari FastAPI
                'image_base64' => $r['image_base64'] ?? null,
            ];
        }, $data);
    }


    public function importTokped(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, [
            'mode' => 'tiktok',
        ]);

        return response()->json($response->json());
    }

    public function importShopee(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, [
            'mode' => 'shopee',
        ]);

        return response()->json($response->json());
    }
}
