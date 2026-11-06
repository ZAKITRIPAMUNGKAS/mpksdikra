<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfektifJawaban;
use App\Models\Event;
use App\Models\Keaktifan;
use App\Models\OutboundKelompok;
use App\Models\PenilaianAkhir;
use App\Models\PesertaTesMulai;
use App\Models\PsikomotorNilai;
use App\Models\PsikomotorTemplate;
use App\Models\Rtl;
use App\Models\RtlSoal;
use App\Models\SesiTes;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $events = Event::withCount('pesertaAktif')
            ->when($user->isFasilitator(), fn ($q) => $q->whereHas('fasilitator', fn ($f) => $f->whereKey($user->id)))
            ->latest('tanggal_mulai')
            ->paginate(10);

        return view('admin.events.index', compact('events'));
    }

    public function create(): View
    {
        return view('admin.events.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['created_by'] = auth()->id();

        $event = Event::create($data);

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event berhasil dibuat.');
    }

    public function show(Event $event): View
    {
        $user = auth()->user();
        $event->load([
            'sesi' => fn ($q) => $q->withCount('absensi'),
            'pesertaAktif.anggota.user',
            'fasilitator',
        ]);

        $sesi = $event->sesi;
        $peserta = $event->pesertaAktif;
        $totalSesi = $sesi->count();
        $totalPeserta = $peserta->count();

        $canManage = $user->isAdmin();
        $canOperate = $user->isAdmin() || $user->isFasilitator();
        $assignedFasilitatorIds = $event->fasilitator->pluck('id')->all();
        $fasilitatorOptions = $canManage
            ? User::where('role', 'fasilitator')->where('status', 'aktif')->orderBy('name')->get()
            : collect();

        // Demografi peserta
        $totalLaki = $peserta->filter(fn ($p) => ($p->anggota->jenis_kelamin ?? 'L') === 'L')->count();
        $totalPerempuan = $totalPeserta - $totalLaki;
        $pcmCounts = $peserta->groupBy(fn ($p) => $p->anggota->pcm ?: ($p->anggota->kecamatan ?: 'Lainnya'))
            ->map->count()
            ->sortDesc()
            ->take(5);

        // Status Sesi Tes
        $sesiPretest = SesiTes::where('event_id', $event->id)->where('tipe', 'pretest')->first();
        $sesiPosttest = SesiTes::where('event_id', $event->id)->where('tipe', 'posttest')->first();

        $pretestSelesaiCount = PesertaTesMulai::where('event_id', $event->id)
            ->where('tipe', 'pretest')
            ->whereNotNull('waktu_selesai')
            ->distinct('anggota_id')
            ->count('anggota_id');

        $posttestSelesaiCount = PesertaTesMulai::where('event_id', $event->id)
            ->where('tipe', 'posttest')
            ->whereNotNull('waktu_selesai')
            ->distinct('anggota_id')
            ->count('anggota_id');

        $afektifSelesaiCount = AfektifJawaban::where('event_id', $event->id)
            ->distinct('anggota_id')
            ->count('anggota_id');

        $rtlSelesaiCount = Rtl::where('event_id', $event->id)
            ->where('status', 'terkirim')
            ->count();

        $psikomotorDinilaiCount = PsikomotorNilai::where('event_id', $event->id)
            ->distinct('anggota_id')
            ->count('anggota_id');

        $outboundKelompokCount = OutboundKelompok::where('event_id', $event->id)->count();
        $keaktifanCount = Keaktifan::where('event_id', $event->id)->count();
        $rtlSoalCount = RtlSoal::where('event_id', $event->id)->count();
        $psikomotorTemplateCount = PsikomotorTemplate::where('event_id', $event->id)->count();
        $soalPretestCount = Soal::where('event_id', $event->id)->where('tipe', 'pretest')->count();
        $soalPosttestCount = Soal::where('event_id', $event->id)->where('tipe', 'posttest')->count();

        $totalLulus = PenilaianAkhir::where('event_id', $event->id)->where('status_kelulusan', 'Lulus')->count();
        $totalBersyarat = PenilaianAkhir::where('event_id', $event->id)->where('status_kelulusan', 'Lulus Bersyarat')->count();
        $totalTidakLulus = PenilaianAkhir::where('event_id', $event->id)->where('status_kelulusan', 'Tidak Lulus')->count();

        $rankingPreview = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->whereNotNull('ranking')
            ->orderBy('ranking')
            ->take(10)
            ->get();

        $recentRtls = Rtl::where('event_id', $event->id)
            ->with('anggota')
            ->latest()
            ->take(5)
            ->get();

        // Sesi Terkini / Live Session
        $liveSesi = $sesi->first(function ($s) {
            return $s->tanggal && $s->tanggal->isToday();
        }) ?: $sesi->first();

        // Leaderboard Top 5
        $leaderboard = PenilaianAkhir::where('event_id', $event->id)
            ->with('anggota')
            ->whereNotNull('nilai_akhir')
            ->orderByDesc('nilai_akhir')
            ->take(5)
            ->get();

        $rerataNilai = PenilaianAkhir::where('event_id', $event->id)->avg('nilai_akhir') ?? 0;

        return view('admin.events.show', compact(
            'event',
            'sesi',
            'peserta',
            'totalSesi',
            'totalPeserta',
            'totalLaki',
            'totalPerempuan',
            'pcmCounts',
            'canManage',
            'canOperate',
            'fasilitatorOptions',
            'assignedFasilitatorIds',
            'sesiPretest',
            'sesiPosttest',
            'pretestSelesaiCount',
            'posttestSelesaiCount',
            'afektifSelesaiCount',
            'rtlSelesaiCount',
            'psikomotorDinilaiCount',
            'outboundKelompokCount',
            'keaktifanCount',
            'rtlSoalCount',
            'psikomotorTemplateCount',
            'soalPretestCount',
            'soalPosttestCount',
            'totalLulus',
            'totalBersyarat',
            'totalTidakLulus',
            'rankingPreview',
            'recentRtls',
            'liveSesi',
            'leaderboard',
            'rerataNilai'
        ));
    }

    public function edit(Event $event): View
    {
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $event->update($this->validated($request, $event));

        return redirect()
            ->route('admin.events.show', $event)
            ->with('success', 'Event diperbarui.');
    }

    public function updateStatus(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate(['status' => 'required|in:persiapan,berlangsung,selesai']);

        if ($event->status === 'selesai' && $data['status'] !== 'selesai') {
            return back()->with('error', 'Status selesai tidak dapat diubah kembali.');
        }

        $event->update($data);

        return back()->with('success', 'Status event diperbarui.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        if ($event->ttd_kiri_path && Storage::disk('public')->exists($event->ttd_kiri_path)) {
            Storage::disk('public')->delete($event->ttd_kiri_path);
        }
        if ($event->ttd_kanan_path && Storage::disk('public')->exists($event->ttd_kanan_path)) {
            Storage::disk('public')->delete($event->ttd_kanan_path);
        }

        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event dihapus.');
    }

    private function validated(Request $request, ?Event $event = null): array
    {
        $data = $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kuota' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'status' => ['required', 'in:persiapan,berlangsung,selesai'],
            'format_nomor_sertifikat' => ['nullable', 'string', 'max:255'],
            'tanggal_terbit_sertifikat' => ['nullable', 'date'],
            'nama_penandatangan_sertifikat' => ['nullable', 'string', 'max:255'],
            'nbm_penandatangan_sertifikat' => ['nullable', 'string', 'max:50'],
            'jabatan_penandatangan_sertifikat' => ['nullable', 'string', 'max:255'],
            'nama_penandatangan_kiri' => ['nullable', 'string', 'max:255'],
            'jabatan_penandatangan_kiri' => ['nullable', 'string', 'max:255'],
            'keterangan_penandatangan_kiri' => ['nullable', 'string', 'max:255'],
            'ttd_kiri' => ['nullable', 'image', 'mimes:png', 'max:2048'],
            'ttd_kanan' => ['nullable', 'image', 'mimes:png', 'max:2048'],
            'hapus_ttd_kiri' => ['nullable', 'boolean'],
            'hapus_ttd_kanan' => ['nullable', 'boolean'],
            'bobot_kognitif' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bobot_psikomotor' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bobot_afektif' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'bobot_keaktifan' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'gunakan_grade' => ['boolean'],
        ]);

        if ($request->boolean('hapus_ttd_kiri')) {
            if ($event?->ttd_kiri_path && Storage::disk('public')->exists($event->ttd_kiri_path)) {
                Storage::disk('public')->delete($event->ttd_kiri_path);
            }
            $data['ttd_kiri_path'] = null;
        } elseif ($request->hasFile('ttd_kiri')) {
            if ($event?->ttd_kiri_path && Storage::disk('public')->exists($event->ttd_kiri_path)) {
                Storage::disk('public')->delete($event->ttd_kiri_path);
            }
            $data['ttd_kiri_path'] = $request->file('ttd_kiri')->store('signatures', 'public');
        }

        if ($request->boolean('hapus_ttd_kanan')) {
            if ($event?->ttd_kanan_path && Storage::disk('public')->exists($event->ttd_kanan_path)) {
                Storage::disk('public')->delete($event->ttd_kanan_path);
            }
            $data['ttd_kanan_path'] = null;
        } elseif ($request->hasFile('ttd_kanan')) {
            if ($event?->ttd_kanan_path && Storage::disk('public')->exists($event->ttd_kanan_path)) {
                Storage::disk('public')->delete($event->ttd_kanan_path);
            }
            $data['ttd_kanan_path'] = $request->file('ttd_kanan')->store('signatures', 'public');
        }

        unset($data['ttd_kiri'], $data['ttd_kanan'], $data['hapus_ttd_kiri'], $data['hapus_ttd_kanan']);

        foreach (['bobot_kognitif', 'bobot_psikomotor', 'bobot_afektif', 'bobot_keaktifan'] as $field) {
            if (isset($data[$field]) && $data[$field] !== null && $data[$field] !== '') {
                $val = (float) $data[$field];
                $data[$field] = $val > 1.0 ? round($val / 100, 4) : round($val, 4);
            } else {
                $data[$field] = null;
            }
        }

        return $data;
    }
}
