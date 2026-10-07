<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\PenilaianAkhir;
use App\Services\QrService;
use App\Services\WhatsAppNotificationService;
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
        $cabang = $request->string('cabang')->toString();

        $enrolled = EventPeserta::where('event_id', $event->id)->pluck('anggota_id')->all();

        $kandidat = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nama_lengkap', 'like', "%{$query}%")
                        ->orWhere('nik', 'like', "%{$query}%");
                });
            })
            ->when($cabang, function ($q) use ($cabang) {
                $q->where(function ($sub) use ($cabang) {
                    $sub->where('pcm', $cabang)
                        ->orWhere('kecamatan', $cabang);
                });
            })
            ->whereNotIn('id', $enrolled)
            ->paginate(20)
            ->withQueryString();

        $event->loadCount('pesertaAktif');

        $pcmList = collect(config('arqam.opsi_form.cabang_pcm', []))
            ->merge(Anggota::whereNotNull('pcm')->pluck('pcm'))
            ->merge(Anggota::whereNotNull('kecamatan')->pluck('kecamatan'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('admin.events.enroll', compact('event', 'kandidat', 'query', 'cabang', 'pcmList'));
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

    public function remind(Event $event, EventPeserta $peserta): RedirectResponse
    {
        if ((int) $peserta->event_id !== (int) $event->id) {
            abort(403);
        }

        $anggota = $peserta->anggota;
        if (! $anggota || ! $anggota->no_wa) {
            return back()->with('error', 'Peserta tidak memiliki nomor WhatsApp yang valid.');
        }

        $pesan = WhatsAppNotificationService::pesanPengingatEvent($anggota, $event);
        $result = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        $waUrl = WhatsAppNotificationService::makeUrl($anggota->no_wa, $pesan);

        if ($result['success']) {
            return back()->with('success', "Pesan pengingat WhatsApp berhasil dikirim ke {$anggota->nama_lengkap}.");
        }

        return back()
            ->with('warning', "Gateway WA: {$result['message']}. Anda dapat mengirim secara manual via WhatsApp Web.")
            ->with('wa_url', $waUrl)
            ->with('wa_nama', $anggota->nama_lengkap);
    }

    public function remindBatch(Event $event): RedirectResponse
    {
        $pesertaAktif = $event->pesertaAktif()->with('anggota')->get();

        if ($pesertaAktif->isEmpty()) {
            return back()->with('error', 'Tidak ada peserta aktif pada kegiatan ini.');
        }

        $sukses = 0;
        $gagal = 0;

        foreach ($pesertaAktif as $p) {
            $anggota = $p->anggota;
            if (! $anggota || ! $anggota->no_wa) {
                $gagal++;

                continue;
            }

            $pesan = WhatsAppNotificationService::pesanPengingatEvent($anggota, $event);
            $res = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);

            if ($res['success']) {
                $sukses++;
            } else {
                $gagal++;
            }
        }

        if ($sukses > 0 && $gagal === 0) {
            return back()->with('success', "Pesan pengingat WhatsApp berhasil dikirim ke seluruh {$sukses} peserta.");
        }

        if ($sukses > 0 && $gagal > 0) {
            return back()->with('warning', "Pengingat WA terkirim ke {$sukses} peserta. Sebanyak {$gagal} peserta tidak terkirim.");
        }

        return back()->with('error', 'Gagal mengirim pengingat WA ke seluruh peserta. Pastikan token WhatsApp Gateway sudah aktif di konfigurasi.');
    }
}
