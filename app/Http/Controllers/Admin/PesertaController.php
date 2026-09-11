<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Services\QrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PesertaController extends Controller
{
    public function __construct(private readonly QrService $qr) {}

    public function search(Request $request, Event $event): View
    {
        $query = $request->string('q')->toString();

        $enrolled = EventPeserta::where('event_id', $event->id)->pluck('anggota_id')->all();

        $kandidat = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->when($query, function ($q) use ($query) {
                $q->where('nama_lengkap', 'like', "%{$query}%")
                    ->orWhere('nik', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $enrolled)
            ->limit(50)
            ->get();

        return view('admin.events.enroll', compact('event', 'kandidat', 'query'));
    }

    public function enroll(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'anggota_ids' => ['required', 'array', 'min:1'],
            'anggota_ids.*' => ['integer', 'exists:anggota,id'],
        ]);

        if ($event->kuota) {
            $current = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->count();
            if ($current + count($data['anggota_ids']) > $event->kuota) {
                return back()->with('error', 'Melebihi kuota event.');
            }
        }

        DB::transaction(function () use ($event, $data) {
            foreach ($data['anggota_ids'] as $anggotaId) {
                $member = Anggota::find($anggotaId);

                if (! $member || $member->user->status !== 'aktif') {
                    continue;
                }

                EventPeserta::create([
                    'event_id' => $event->id,
                    'anggota_id' => $anggotaId,
                    'qr_token' => $this->qr->generateToken(),
                ]);

                PenilaianAkhir::firstOrCreate([
                    'event_id' => $event->id,
                    'anggota_id' => $anggotaId,
                ]);
            }
        });

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Peserta berhasil di-enroll.');
    }

    public function remove(Event $event, EventPeserta $peserta): RedirectResponse
    {
        if ((int) $peserta->event_id !== (int) $event->id) {
            abort(403);
        }

        $peserta->update(['status_aktif' => false]);

        return back()->with('success', 'Peserta dinonaktifkan.');
    }
}
