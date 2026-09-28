<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AfektifJawaban;
use App\Models\Event;
use App\Models\JawabanPeserta;
use App\Models\PenilaianAkhir;
use App\Models\PsikomotorNilai;
use App\Models\Rtl;
use App\Models\SesiTes;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $pretestSelesaiCount = JawabanPeserta::where('event_id', $event->id)
            ->where('tipe', 'pretest')
            ->distinct('anggota_id')
            ->count('anggota_id');

        $posttestSelesaiCount = JawabanPeserta::where('event_id', $event->id)
            ->where('tipe', 'posttest')
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
        $event->update($this->validated($request));

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
        $event->delete();

        return redirect()
            ->route('admin.events.index')
            ->with('success', 'Event dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama_event' => ['required', 'string', 'max:255'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kuota' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'status' => ['required', 'in:persiapan,berlangsung,selesai'],
            'format_nomor_sertifikat' => ['nullable', 'string', 'max:255'],
        ]);
    }
}
