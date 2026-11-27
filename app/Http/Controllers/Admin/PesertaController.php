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
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class PesertaController extends Controller
{
    public function __construct(private readonly QrService $qr) {}

    public function search(Request $request, Event $event): View
    {
        $query = $request->string('q')->toString();
        $cabang = $request->string('cabang')->toString();
        $kategori = $request->string('kategori')->toString();
        $aum = $request->string('aum')->toString();
        $ortom = $request->string('ortom')->toString();
        $sort = $request->string('sort')->toString();

        $enrolled = $event->pesertaAktif()->pluck('anggota_id')->all();

        $kandidat = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('nama_lengkap', 'like', "%{$query}%")
                        ->orWhere('nik', 'like', "%{$query}%")
                        ->orWhere('nbm', 'like', "%{$query}%")
                        ->orWhere('nama_aum', 'like', "%{$query}%")
                        ->orWhere('jabatan_aum', 'like', "%{$query}%");
                });
            })
            ->when($kategori === 'muhammadiyah', function ($q) {
                $q->whereNotNull('keaktifan_muhammadiyah')
                    ->where('keaktifan_muhammadiyah', '!=', '[]')
                    ->whereJsonDoesntContain('keaktifan_muhammadiyah', 'Tidak aktif');
            })
            ->when($kategori === 'aum', function ($q) {
                $q->where(function ($sub) {
                    $sub->where(function ($s2) {
                        $s2->whereNotNull('nama_aum')->where('nama_aum', '!=', '');
                    })->orWhere(function ($s2) {
                        $s2->whereNotNull('jabatan_aum')->where('jabatan_aum', '!=', '');
                    });
                });
            })
            ->when($kategori === 'ortom', function ($q) {
                $q->whereNotNull('keaktifan_ortom')
                    ->where('keaktifan_ortom', '!=', '[]')
                    ->whereJsonDoesntContain('keaktifan_ortom', 'Tidak aktif');
            })
            ->when($aum, function ($q) use ($aum) {
                $q->where('nama_aum', $aum);
            })
            ->when($ortom, function ($q) use ($ortom) {
                $q->whereJsonContains('keaktifan_ortom', $ortom);
            })
            ->when($cabang, function ($q) use ($cabang) {
                $q->where(function ($sub) use ($cabang) {
                    $sub->where('pcm', $cabang)
                        ->orWhere('kecamatan', $cabang);
                });
            })
            ->when($sort === 'nama_asc', fn ($q) => $q->orderBy('nama_lengkap', 'asc'))
            ->when($sort === 'nama_desc', fn ($q) => $q->orderBy('nama_lengkap', 'desc'))
            ->when($sort === 'aum_asc', fn ($q) => $q->orderByRaw("CASE WHEN nama_aum IS NULL OR nama_aum = '' THEN 1 ELSE 0 END, nama_aum ASC"))
            ->when($sort === 'cabang_asc', fn ($q) => $q->orderByRaw("COALESCE(NULLIF(pcm, ''), kecamatan) ASC"))
            ->when(! $sort || $sort === 'terbaru', fn ($q) => $q->latest())
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

        $aumList = collect(config('arqam.opsi_form.nama_aum', []))
            ->merge(Anggota::whereNotNull('nama_aum')->where('nama_aum', '!=', '')->pluck('nama_aum'))
            ->filter(fn ($x) => ! in_array($x, ['Lainnya', ''], true))
            ->unique()
            ->sort()
            ->values()
            ->all();

        $ortomList = collect(config('arqam.opsi_form.keaktifan_ortom', []))
            ->filter(fn ($x) => $x !== 'Tidak aktif')
            ->values()
            ->all();

        return view('admin.events.enroll', compact('event', 'kandidat', 'query', 'cabang', 'kategori', 'aum', 'ortom', 'sort', 'pcmList', 'aumList', 'ortomList'));
    }

    public function enroll(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'anggota_ids' => ['required', 'array', 'min:1'],
            'anggota_ids.*' => ['integer', 'exists:anggota,id'],
        ]);

        return DB::transaction(function () use ($event, $data) {
            $event = Event::whereKey($event->id)->lockForUpdate()->firstOrFail();
            $anggotaIds = Anggota::whereIn('id', $data['anggota_ids'])
                ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
                ->whereNotIn('id', $event->pesertaAktif()->select('anggota_id'))
                ->orderBy('id')
                ->pluck('id');

            if ($event->kuota && $event->pesertaAktif()->count() + $anggotaIds->count() > $event->kuota) {
                return back()->with('error', 'Melebihi kuota event.');
            }

            foreach ($anggotaIds as $anggotaId) {
                $peserta = EventPeserta::firstOrCreate([
                    'event_id' => $event->id,
                    'anggota_id' => $anggotaId,
                ], [
                    'qr_token' => $this->qr->generateToken(),
                    'status_aktif' => true,
                ]);
                if (! $peserta->status_aktif) {
                    $peserta->update(['status_aktif' => true]);
                }

                PenilaianAkhir::firstOrCreate([
                    'event_id' => $event->id,
                    'anggota_id' => $anggotaId,
                ]);
            }

            return redirect()
                ->route('admin.events.show', $event)
                ->with('success', 'Peserta berhasil di-enroll.');
        }, 3);
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
        $waUrl = WhatsAppNotificationService::makeUrl($anggota->no_wa, $pesan);

        if ($this->sisaKuotaPengingat() === 0) {
            return back()
                ->with('warning', $this->pesanKuotaHabis().' Kirim manual lewat tombol berikut.')
                ->with('wa_url', $waUrl)
                ->with('wa_nama', $anggota->nama_lengkap);
        }

        $result = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);
        if ($result['success']) {
            $this->catatPengingatTerkirim();
        }

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
        $tertunda = 0;

        if ($this->sisaKuotaPengingat() === 0) {
            return back()->with('warning', $this->pesanKuotaHabis().' Gunakan "Bagikan via WhatsApp" ke grup peserta.');
        }

        foreach ($pesertaAktif as $p) {
            $anggota = $p->anggota;
            if (! $anggota || ! $anggota->no_wa) {
                $gagal++;

                continue;
            }

            if ($this->sisaKuotaPengingat() === 0) {
                $tertunda++;

                continue;
            }

            $pesan = WhatsAppNotificationService::pesanPengingatEvent($anggota, $event);
            $res = WhatsAppNotificationService::kirimOtomatis($anggota->no_wa, $pesan);

            if ($res['success']) {
                $sukses++;
                $this->catatPengingatTerkirim();
            } else {
                $gagal++;
            }
        }

        if ($tertunda > 0) {
            return back()->with('warning', "Pengingat WA terkirim ke {$sukses} peserta. {$tertunda} peserta belum dikirim karena batas aman "
                .config('arqam.wa_pengingat_per_jam').' pesan per jam tercapai'.($gagal > 0 ? ", {$gagal} gagal" : '')
                .'. Lanjutkan satu jam lagi atau bagikan via grup WhatsApp.');
        }

        if ($sukses > 0 && $gagal === 0) {
            return back()->with('success', "Pesan pengingat WhatsApp berhasil dikirim ke seluruh {$sukses} peserta.");
        }

        if ($sukses > 0 && $gagal > 0) {
            return back()->with('warning', "Pengingat WA terkirim ke {$sukses} peserta. Sebanyak {$gagal} peserta tidak terkirim.");
        }

        return back()->with('error', 'Gagal mengirim pengingat WA ke seluruh peserta. Pastikan token WhatsApp Gateway sudah aktif di konfigurasi.');
    }

    /** Sisa kuota pengingat jam ini; null = tanpa batas. */
    private function sisaKuotaPengingat(): ?int
    {
        $batas = (int) config('arqam.wa_pengingat_per_jam', 10);
        if ($batas <= 0) {
            return null;
        }

        return max(0, $batas - RateLimiter::attempts('wa-pengingat-event'));
    }

    private function catatPengingatTerkirim(): void
    {
        if ((int) config('arqam.wa_pengingat_per_jam', 10) > 0) {
            RateLimiter::hit('wa-pengingat-event', 3600);
        }
    }

    private function pesanKuotaHabis(): string
    {
        $menit = max(1, (int) ceil(RateLimiter::availableIn('wa-pengingat-event') / 60));

        return 'Batas aman '.config('arqam.wa_pengingat_per_jam').' pengingat WA per jam sudah tercapai (agar nomor gateway tidak diblokir). Coba lagi sekitar '.$menit.' menit lagi.';
    }
}
