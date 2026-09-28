<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Services\NilaiService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RtlAdminController extends Controller
{
    public function index(Event $event): View
    {
        $soal = RtlSoal::where('event_id', $event->id)->orderBy('urutan')->get();
        $rtls = Rtl::where('event_id', $event->id)->with('anggota')->orderBy('status')->latest()->get();

        return view('admin.events.rtl', compact('event', 'soal', 'rtls'));
    }

    public function show(Event $event, Rtl $rtl): View
    {
        abort_unless((int) $rtl->event_id === (int) $event->id, 404);
        $rtl->load(['anggota', 'jawaban.soal']);

        return view('admin.events.rtl-show', compact('event', 'rtl'));
    }

    public function storeSoal(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'pertanyaan' => ['required', 'string', 'max:1000'],
            'tipe' => ['required', 'in:essay,upload'],
        ]);

        $urutan = (int) RtlSoal::where('event_id', $event->id)->max('urutan') + 1;

        RtlSoal::create(array_merge($data, [
            'event_id' => $event->id,
            'urutan' => $urutan,
        ]));

        return back()->with('success', 'Pertanyaan RTL ditambahkan.');
    }

    public function destroySoal(Event $event, RtlSoal $soal): RedirectResponse
    {
        abort_unless((int) $soal->event_id === (int) $event->id, 404);
        $soal->delete();

        return back()->with('success', 'Pertanyaan dihapus.');
    }

    public function review(Request $request, Event $event, Rtl $rtl): RedirectResponse
    {
        abort_unless((int) $rtl->event_id === (int) $event->id, 404);

        $data = $request->validate(['status' => ['required', 'in:disetujui,ditolak']]);

        $rtl->update(['status' => $data['status']]);

        app(NilaiService::class)->hitungUlangPeserta($event, $rtl->anggota_id);

        return redirect()
            ->route('admin.events.rtl.show', [$event, $rtl])
            ->with('success', 'RTL ditandai '.$data['status'].'.');
    }

    public function downloadBukti(Event $event, Rtl $rtl, int $jawabanId)
    {
        abort_unless((int) $rtl->event_id === (int) $event->id, 404);

        $jawaban = $rtl->jawaban()->findOrFail($jawabanId);

        if (! $jawaban->file_bukti || ! Storage::disk('local')->exists($jawaban->file_bukti)) {
            abort(404, 'Bukti tidak ditemukan.');
        }

        return Storage::disk('local')->download($jawaban->file_bukti);
    }
}
