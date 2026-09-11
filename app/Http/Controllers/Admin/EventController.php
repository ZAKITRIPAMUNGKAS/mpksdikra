<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
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
        $canManage = $user->isAdmin();
        $canOperate = $user->isAdmin() || $user->isFasilitator();
        $assignedFasilitatorIds = $event->fasilitator->pluck('id')->all();
        $fasilitatorOptions = $canManage
            ? User::where('role', 'fasilitator')->where('status', 'aktif')->orderBy('name')->get()
            : collect();

        return view('admin.events.show', compact(
            'event',
            'sesi',
            'peserta',
            'totalSesi',
            'canManage',
            'canOperate',
            'fasilitatorOptions',
            'assignedFasilitatorIds'
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
