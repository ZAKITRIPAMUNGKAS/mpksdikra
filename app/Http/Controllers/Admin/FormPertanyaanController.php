<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FormPertanyaan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FormPertanyaanController extends Controller
{
    public function index(Request $request): View
    {
        $seksiList = FormPertanyaan::SEKSI;
        $tipeList = FormPertanyaan::TIPE;

        $activeSeksi = $request->query('seksi', 'diri');
        if (! array_key_exists($activeSeksi, $seksiList)) {
            $activeSeksi = 'diri';
        }

        $pertanyaanGrouped = FormPertanyaan::ordered()->get()->groupBy('seksi');

        $totalAktif = FormPertanyaan::aktif()->count();
        $totalPertanyaan = FormPertanyaan::count();

        return view('admin.form-pertanyaan.index', compact(
            'seksiList',
            'tipeList',
            'activeSeksi',
            'pertanyaanGrouped',
            'totalAktif',
            'totalPertanyaan'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'seksi' => ['required', Rule::in(array_keys(FormPertanyaan::SEKSI))],
            'label' => ['required', 'string', 'max:255'],
            'tipe' => ['required', Rule::in(array_keys(FormPertanyaan::TIPE))],
            'opsi_raw' => ['nullable', 'string'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'bantuan' => ['nullable', 'string', 'max:1000'],
            'is_wajib' => ['nullable', 'boolean'],
        ]);

        $opsi = null;
        if (in_array($validated['tipe'], ['select', 'checkbox']) && ! empty($validated['opsi_raw'])) {
            $opsi = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $validated['opsi_raw']))));
        }

        $baseKode = 'custom_'.Str::slug(Str::limit($validated['label'], 30, ''));
        $kode = $baseKode;
        $counter = 1;
        while (FormPertanyaan::where('kode', $kode)->exists()) {
            $kode = $baseKode.'_'.$counter++;
        }

        $maxUrutan = (int) FormPertanyaan::where('seksi', $validated['seksi'])->max('urutan');

        FormPertanyaan::create([
            'seksi' => $validated['seksi'],
            'kode' => $kode,
            'label' => $validated['label'],
            'tipe' => $validated['tipe'],
            'opsi' => $opsi,
            'placeholder' => $validated['placeholder'] ?? null,
            'bantuan' => $validated['bantuan'] ?? null,
            'is_wajib' => $request->boolean('is_wajib'),
            'is_aktif' => true,
            'is_custom' => true,
            'urutan' => $maxUrutan + 1,
        ]);

        return redirect()->route('admin.form-kader.index', ['seksi' => $validated['seksi']])
            ->with('success', 'Pertanyaan baru berhasil ditambahkan ke formulir pendaftaran.');
    }

    public function update(Request $request, FormPertanyaan $pertanyaan): RedirectResponse
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'bantuan' => ['nullable', 'string', 'max:1000'],
            'opsi_raw' => ['nullable', 'string'],
            'is_wajib' => ['nullable', 'boolean'],
            'is_aktif' => ['nullable', 'boolean'],
            'seksi' => ['nullable', Rule::in(array_keys(FormPertanyaan::SEKSI))],
        ]);

        $opsi = $pertanyaan->opsi;
        if (in_array($pertanyaan->tipe, ['select', 'checkbox']) && isset($validated['opsi_raw'])) {
            $opsi = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n|,/', $validated['opsi_raw']))));
        }

        $pertanyaan->update([
            'label' => $validated['label'],
            'placeholder' => $validated['placeholder'] ?? null,
            'bantuan' => $validated['bantuan'] ?? null,
            'opsi' => $opsi,
            'is_wajib' => $request->boolean('is_wajib'),
            'is_aktif' => $request->boolean('is_aktif'),
            'seksi' => $validated['seksi'] ?? $pertanyaan->seksi,
        ]);

        return redirect()->route('admin.form-kader.index', ['seksi' => $pertanyaan->seksi])
            ->with('success', 'Pengaturan pertanyaan berhasil diperbarui.');
    }

    public function destroy(FormPertanyaan $pertanyaan): RedirectResponse
    {
        if (! $pertanyaan->is_custom) {
            return back()->with('error', 'Pertanyaan standar bawaan sistem tidak dapat dihapus, silakan nonaktifkan bila tidak ingin ditampilkan.');
        }

        $seksi = $pertanyaan->seksi;
        $pertanyaan->delete();

        return redirect()->route('admin.form-kader.index', ['seksi' => $seksi])
            ->with('success', 'Pertanyaan berhasil dihapus dari formulir.');
    }

    public function toggleAktif(FormPertanyaan $pertanyaan): RedirectResponse
    {
        $pertanyaan->update(['is_aktif' => ! $pertanyaan->is_aktif]);

        return back()->with('success', 'Status visibilitas pertanyaan diperbarui.');
    }

    public function toggleWajib(FormPertanyaan $pertanyaan): RedirectResponse
    {
        $pertanyaan->update(['is_wajib' => ! $pertanyaan->is_wajib]);

        return back()->with('success', 'Status kewajiban pertanyaan diperbarui.');
    }

    public function reorder(Request $request, FormPertanyaan $pertanyaan): RedirectResponse
    {
        $arah = $request->input('arah', 'down');

        $neighbor = $arah === 'up'
            ? FormPertanyaan::where('seksi', $pertanyaan->seksi)->where('urutan', '<', $pertanyaan->urutan)->orderByDesc('urutan')->first()
            : FormPertanyaan::where('seksi', $pertanyaan->seksi)->where('urutan', '>', $pertanyaan->urutan)->orderBy('urutan')->first();

        if ($neighbor) {
            $temp = $pertanyaan->urutan;
            $pertanyaan->update(['urutan' => $neighbor->urutan]);
            $neighbor->update(['urutan' => $temp]);
        }

        return back();
    }
}
