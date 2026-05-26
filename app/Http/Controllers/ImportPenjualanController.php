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

    // ================================================================
    // ENDPOINT BARU (Async): Step 1 — Submit job, dapat job_id
    // ================================================================
    
    public function submitMultipleResiJob(Request $request)
    {
        $request->validate([
            'file'           => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480',
            'mode'           => 'nullable|string|in:shopee,tiktok',
            'ekspedisi_mode' => 'nullable|string|max:50',  // ← TAMBAHAN
        ]);
    
        $mode          = $request->input('mode', 'shopee');
        $ekspedisiMode = $request->input('ekspedisi_mode');  // ← TAMBAHAN
        $file          = $request->file('file');
    
        // Build multipart payload ke FastAPI
        $multipart = [
            [
                'name'     => 'file',
                'contents' => file_get_contents($file->getRealPath()),
                'filename' => $file->getClientOriginalName(),
            ],
            [
                'name'     => 'mode',
                'contents' => $mode,
            ],
        ];
    
        // Hanya kirim ekspedisi_mode kalau diisi (tidak kirim string kosong)
        if ($ekspedisiMode) {
            $multipart[] = [
                'name'     => 'ekspedisi_mode',
                'contents' => $ekspedisiMode,
            ];
        }
    
        try {
            // Gunakan multipart manual supaya bisa kirim field campuran
            $response = Http::timeout(30)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($this->fastApiUrl() . '/scan-resi-multiple-async', array_filter([
                    'mode'           => $mode,
                    'ekspedisi_mode' => $ekspedisiMode ?: null,  // ← TAMBAHAN
                ]));
    
            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OCR service error: HTTP ' . $response->status(),
                ], 502);
            }
    
            $payload = $response->json();
    
            return response()->json([
                'success'        => true,
                'job_id'         => $payload['job_id'],
                'total_pages'    => $payload['total_pages'],
                'ekspedisi_mode' => $payload['ekspedisi_mode'] ?? $ekspedisiMode,  // echo balik
                'status'         => 'queued',
            ]);
    
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'OCR service tidak dapat dijangkau.',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
    // ================================================================
    // ENDPOINT BARU (Async): Step 2 — Polling status job
    // ================================================================
    public function pollJobStatus(Request $request, string $jobId)
    {
        try {
            $response = Http::timeout(120)
                ->get($this->fastApiUrl() . '/job-status/' . $jobId);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal cek status job',
                ], 502);
            }

            $payload = $response->json();

            if (isset($payload['error'])) {
                return response()->json([
                    'success' => false,
                    'message' => $payload['error'],
                ], 422);
            }

            // Kalau masih proses, kembalikan progress saja
            if ($payload['status'] !== 'done') {
                return response()->json([
                    'success'      => true,
                    'status'       => $payload['status'],
                    'total_pages'  => $payload['total_pages'],
                    'done_pages'   => $payload['done_pages'],
                    'failed_pages' => $payload['failed_pages'],
                    'progress_pct' => $payload['progress_pct'],
                    // Kirim data parsial yang sudah selesai (opsional)
                    'data'         => $this->normaliseAndValidate($payload['data'] ?? [])['data'],
                    'warnings'     => $this->normaliseAndValidate($payload['data'] ?? [])['warnings'],
                ]);
            }

            // Status = done → proses lengkap
            $result = $this->normaliseAndValidate($payload['data'] ?? []);

            Log::debug('[PollJobStatus] Job selesai', [
                'job_id'        => $jobId,
                'total_pages'   => $payload['total_pages'],
                'done_pages'    => $payload['done_pages'],
                'valid_count'   => count($result['data']),
                'skipped_count' => count($result['skipped']),
            ]);

            return response()->json([
                'success'       => true,
                'status'        => 'done',
                'mode'          => $payload['mode'],
                'total'         => count($result['data']),
                'total_skipped' => count($result['skipped']),
                'total_pages'   => $payload['total_pages'],
                'done_pages'    => $payload['done_pages'],
                'failed_pages'  => $payload['failed_pages'],
                'progress_pct'  => 100,
                'data'          => $result['data'],
                'warnings'      => $result['warnings'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // ENDPOINT LAMA (Sync) — tetap ada untuk backward compatibility
    // ================================================================
    public function importMultipleResi(Request $request)
    {
        set_time_limit(300);
        ini_set('max_execution_time', 300);
        ini_set('memory_limit', '256M');

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480',
            'mode' => 'nullable|string|in:shopee,tiktok',
        ]);

        $mode = $request->input('mode', 'shopee');
        $file = $request->file('file');

        try {
            $response = Http::timeout(300)
                ->attach(
                    'file',
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName()
                )
                ->post($this->fastApiUrl() . '/scan-resi-multiple', [
                    'mode' => $mode,
                ]);

            if ($response->failed()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OCR service error: HTTP ' . $response->status(),
                ], 502);
            }

            $payload  = $response->json();
            $result   = $this->normaliseAndValidate($payload['data'] ?? []);

            return response()->json([
                'success'       => true,
                'mode'          => $payload['mode'] ?? $mode,
                'total'         => count($result['data']),
                'total_skipped' => count($result['skipped']),
                'data'          => $result['data'],
                'warnings'      => $result['warnings'],
            ]);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'success' => false,
                'message' => 'OCR service tidak dapat dijangkau.',
            ], 503);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ================================================================
    // HELPER — normalise + cek duplikat + kumpulkan warnings
    // ================================================================
    private function normaliseAndValidate(array $data): array
    {
        $normalised = $this->normaliseData($data);
        $warnings   = [];
        $valid      = [];
        $skipped    = [];

        foreach ($normalised as $idx => $resiData) {
            $label = 'Halaman ' . ($resiData['page'] ?? ($idx + 1));

            // Cek duplikat resi
            if (!empty($resiData['resi'])) {
                $isDuplicate = \App\Models\Penjualan::where('nomor_resi', $resiData['resi'])->exists();
                if ($isDuplicate) {
                    $resiData['is_duplicate'] = true;
                    $warnings[] = [
                        'type'    => 'duplicate_resi',
                        'page'    => $resiData['page'] ?? ($idx + 1),
                        'message' => "{$label}: Nomor resi <strong>{$resiData['resi']}</strong> sudah ada — dilewati.",
                    ];
                    $skipped[] = $resiData;
                    continue;
                }
            } else {
                $warnings[] = [
                    'type'    => 'no_resi',
                    'page'    => $resiData['page'] ?? ($idx + 1),
                    'message' => "{$label}: Nomor resi tidak terbaca — isi manual.",
                ];
            }

            if (empty($resiData['skus'])) {
                $warnings[] = [
                    'type'    => 'no_sku',
                    'page'    => $resiData['page'] ?? ($idx + 1),
                    'message' => "{$label}: Tidak ada SKU — tambahkan barang manual.",
                ];
            }

            $resiData['is_duplicate'] = false;
            $valid[] = $resiData;
        }

        return [
            'data'     => $valid,
            'skipped'  => $skipped,
            'warnings' => $warnings,
        ];
    }

    private function normaliseData(array $data): array
    {
        return array_map(function ($r) {
            $items = array_map(fn($item) => [
                'sku'     => $item['sku']     ?? $item['SKU']         ?? null,
                'qty'     => (int) ($item['qty'] ?? $item['jumlah']   ?? 1),
                'nama'    => $item['nama']    ?? $item['nama_barang']  ?? null,
                'variasi' => $item['variasi'] ?? null,
            ], $r['items'] ?? []);

            return [
                'page'         => $r['page']         ?? null,
                'resi'         => $r['resi']         ?? null,
                'order_id'     => $r['order_id']     ?? null,
                'items'        => $items,
                'skus'         => $r['skus']         ?? [],
                'image_base64' => $r['image_base64'] ?? null,
            ];
        }, $data);
    }

    // ================================================================
    // Endpoint lain (Tokped, Shopee single)
    // ================================================================
    public function importTokped(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf']);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, ['mode' => 'tiktok']);

        return response()->json($response->json());
    }

    public function importShopee(Request $request)
    {
        $url = env('FASTAPI_URL') . '/scan-resi';
        $request->validate(['file' => 'required|file|mimes:jpg,jpeg,png,pdf']);

        $response = Http::attach(
            'file',
            file_get_contents($request->file('file')->getRealPath()),
            $request->file('file')->getClientOriginalName()
        )->post($url, ['mode' => 'shopee']);

        return response()->json($response->json());
    }
}