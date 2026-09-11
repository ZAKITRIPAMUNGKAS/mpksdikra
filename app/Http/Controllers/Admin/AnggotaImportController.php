<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AnggotaTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\AnggotaImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AnggotaImportController extends Controller
{
    public function show(): View
    {
        return view('admin.anggota.import');
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new AnggotaTemplateExport, 'template-impor-kader-mpksdi.xlsx');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file_excel' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ], [
            'file_excel.required' => 'Silakan pilih berkas Excel atau CSV terlebih dahulu.',
            'file_excel.mimes' => 'Berkas harus berupa spreadsheet Excel (.xlsx, .xls) atau .csv.',
            'file_excel.max' => 'Ukuran berkas maksimal adalah 5MB.',
        ]);

        $import = new AnggotaImport;
        Excel::import($import, $request->file('file_excel'));

        $msg = "Proses impor selesai. {$import->successCount} kader baru berhasil ditambahkan ke pangkalan data.";

        return redirect()
            ->route('admin.anggota.import')
            ->with('success', $msg)
            ->with('import_success_count', $import->successCount)
            ->with('import_skipped', $import->skipped)
            ->with('import_errors', $import->errors);
    }
}
