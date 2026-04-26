<?php

namespace App\Http\Controllers;

use App\Imports\BarangImport;
use App\Exports\BarangTemplateExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BarangImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        try {
            $import = new BarangImport();
            Excel::import($import, $request->file('file'));

            // Gabung skipLog + errorLog untuk detail lengkap
            $allSkipped = array_merge($import->skipLog, $import->errorLog);

            return response()->json([
                'message'  => 'Import selesai.',
                'imported' => $import->imported,
                'skipped'  => $import->skipped,
                'details'  => $allSkipped, // array [{sku, alasan}]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import gagal total: ' . $e->getMessage(),
            ], 422);
        }
    }

    public function downloadTemplate()
    {
        return Excel::download(new BarangTemplateExport(), 'template_import_barang.xlsx');
    }
}