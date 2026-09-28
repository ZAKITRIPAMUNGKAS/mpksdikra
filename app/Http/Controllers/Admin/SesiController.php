<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventSesi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SesiController extends Controller
{
    public function store(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:materi,sholat,outbound'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable'],
            'jam_selesai' => ['nullable'],
            'pemateri' => ['nullable', 'string', 'max:255'],
            'link_materi' => ['nullable', 'url', 'max:500'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file_materi' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx,zip', 'max:15360'],
        ]);

        if ($request->hasFile('file_materi')) {
            $data['file_materi'] = $request->file('file_materi')->store('materi_sesi', 'local');
        }

        $urutan = (int) EventSesi::where('event_id', $event->id)->max('urutan') + 1;

        EventSesi::create(array_merge($data, [
            'event_id' => $event->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Sesi ditambahkan.');
    }

    public function update(Request $request, Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        $data = $request->validate([
            'nama_sesi' => ['required', 'string', 'max:255'],
            'jenis' => ['required', 'in:materi,sholat,outbound'],
            'tanggal' => ['nullable', 'date'],
            'jam_mulai' => ['nullable'],
            'jam_selesai' => ['nullable'],
            'pemateri' => ['nullable', 'string', 'max:255'],
            'link_materi' => ['nullable', 'url', 'max:500'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'file_materi' => ['nullable', 'file', 'mimes:pdf,ppt,pptx,doc,docx,zip', 'max:15360'],
        ]);

        if ($request->hasFile('file_materi')) {
            if ($sesi->file_materi && Storage::disk('local')->exists($sesi->file_materi)) {
                Storage::disk('local')->delete($sesi->file_materi);
            }
            $data['file_materi'] = $request->file('file_materi')->store('materi_sesi', 'local');
        }

        $sesi->update($data);

        return back()->with('success', 'Sesi diperbarui.');
    }

    public function destroy(Event $event, EventSesi $sesi): RedirectResponse
    {
        if ((int) $sesi->event_id !== (int) $event->id) {
            abort(403);
        }

        if ($sesi->file_materi && Storage::disk('local')->exists($sesi->file_materi)) {
            Storage::disk('local')->delete($sesi->file_materi);
        }

        $sesi->delete();

        return back()->with('success', 'Sesi dihapus.');
    }

    public function downloadMateri(EventSesi $sesi): BinaryFileResponse
    {
        abort_unless($sesi->file_materi && Storage::disk('local')->exists($sesi->file_materi), 404, 'Berkas materi tidak ditemukan.');

        $user = auth()->user();
        $canAccess = $user->isAdminDaerah()
            || $user->isExecutive()
            || ($user->isFasilitator() && $sesi->event->fasilitator()->where('users.id', $user->id)->exists())
            || ($user->anggota && $sesi->event->pesertaAktif()->where('anggota_id', $user->anggota->id)->exists());

        abort_unless($canAccess, 403, 'Akses materi perkaderan tidak diizinkan.');

        $fullPath = Storage::disk('local')->path($sesi->file_materi);

        return response()->download($fullPath, basename($sesi->file_materi));
    }
}
