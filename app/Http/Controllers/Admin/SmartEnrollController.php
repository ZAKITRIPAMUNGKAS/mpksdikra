<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\SikaderFeedback;
use App\Services\QrService;
use App\Services\SmartEnrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SmartEnrollController extends Controller
{
    public function __construct(
        private readonly SmartEnrollService $smartEnroll,
        private readonly QrService $qr
    ) {}

    /**
     * Menampilkan ringkasan input sebelum pencocokan atau pendaftaran dilakukan.
     */
    public function preview(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'names_text' => ['nullable', 'string', 'max:50000'],
            'file' => ['nullable', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        if (! $request->hasFile('file') && blank($data['names_text'] ?? null)) {
            return response()->json([
                'success' => false,
                'message' => 'Pilih berkas atau masukkan daftar nama terlebih dahulu.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            ...$this->smartEnroll->previewInput($data['names_text'] ?? null, $request->file('file')),
        ]);
    }

    /**
     * Menganalisis dan mencocokkan nama massal (dari file atau teks).
     */
    public function match(Request $request, Event $event): JsonResponse
    {
        $request->validate([
            'names_text' => ['nullable', 'string', 'max:50000'],
            'file' => ['nullable', 'file', 'max:5120', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $rawNames = [];

        if ($request->hasFile('file')) {
            $rawNames = $this->smartEnroll->parseNamesFromFile($request->file('file'));
        } elseif ($request->filled('names_text')) {
            $rawNames = $this->smartEnroll->parseNamesFromText($request->input('names_text'));
        }

        if (empty($rawNames)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada nama yang dapat diekstrak dari input yang diberikan.',
                'matched' => [],
                'already_enrolled' => [],
                'unmatched' => [],
                'whatsapp_invite' => '',
            ], 422);
        }

        $result = $this->smartEnroll->matchNames($rawNames, $event);
        $whatsappInvite = $this->smartEnroll->generateWhatsAppInvite($result['unmatched'], $event);

        return response()->json([
            'success' => true,
            'total_input' => $result['total_input'],
            'matched' => $result['matched'],
            'already_enrolled' => $result['already_enrolled'],
            'unmatched' => $result['unmatched'],
            'whatsapp_invite' => $whatsappInvite,
        ]);
    }

    /**
     * Rekomendasi kandidat berbasis kriteria terstruktur.
     */
    public function recommend(Request $request, Event $event): JsonResponse
    {
        $criteria = $request->validate([
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'gender' => ['nullable', 'string', 'in:all,seimbang,L,P'],
            'pcm' => ['nullable', 'string', 'max:100'],
            'aum' => ['nullable', 'string', 'max:150'],
            'prioritas_belum_ba' => ['nullable', 'boolean'],
        ]);

        $candidates = $this->smartEnroll->recommendCandidates($event, $criteria);

        return response()->json([
            'success' => true,
            'candidates' => $candidates,
            'message' => 'Ditemukan '.$candidates->count().' rekomendasi kader yang sesuai kriteria.',
        ]);
    }

    /**
     * Konsultasi bahasa alami dengan AI (Geraikita) atau fallback kriteria lokal.
     */
    public function aiPrompt(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'prompt' => ['required', 'string', 'max:1000'],
        ]);

        $result = $this->smartEnroll->consultAi($data['prompt'], $event);

        return response()->json($result);
    }

    /**
     * Eksekusi enroll massal kandidat terpilih.
     */
    public function execute(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'anggota_ids' => ['required', 'array', 'min:1'],
            'anggota_ids.*' => ['integer', 'exists:anggota,id'],
        ]);

        if ($event->kuota) {
            $currentCount = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->count();
            if ($currentCount + count($data['anggota_ids']) > $event->kuota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pendaftaran melebihi kapasitas kuota event ('.$event->kuota.' kader).',
                ], 422);
            }
        }

        $enrolledCount = 0;
        $createdParticipantIds = [];

        DB::transaction(function () use ($event, $data, &$enrolledCount, &$createdParticipantIds) {
            foreach ($data['anggota_ids'] as $anggotaId) {
                $member = Anggota::where('id', $anggotaId)
                    ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
                    ->first();

                if (! $member) {
                    continue;
                }

                $alreadyEnrolled = EventPeserta::where('event_id', $event->id)
                    ->where('anggota_id', $anggotaId)
                    ->exists();

                if (! $alreadyEnrolled) {
                    $token = method_exists($this->qr, 'generateToken')
                        ? $this->qr->generateToken($event->id, $anggotaId)
                        : Str::random(32);

                    $participant = EventPeserta::create([
                        'event_id' => $event->id,
                        'anggota_id' => $anggotaId,
                        'qr_token' => $token,
                        'status_aktif' => true,
                    ]);
                    $createdParticipantIds[] = $participant->id;
                    $enrolledCount++;
                }
            }
        });

        $undoToken = null;
        $undoExpiresAt = null;
        if ($createdParticipantIds !== []) {
            $undoToken = Str::random(64);
            $undoExpiresAt = now()->addSeconds(10);
            Cache::put($this->undoCacheKey($undoToken), [
                'event_id' => $event->id,
                'admin_id' => $request->user()->id,
                'participant_ids' => $createdParticipantIds,
            ], $undoExpiresAt);
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil mendaftarkan {$enrolledCount} kader terpilih.",
            'enrolled_count' => $enrolledCount,
            'undo_token' => $undoToken,
            'undo_expires_at' => $undoExpiresAt?->toIso8601String(),
            'quota' => $event->kuota,
            'registered_count' => EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->count(),
        ]);
    }

    /**
     * Membatalkan batch yang baru dibuat oleh admin yang sama dalam masa undo.
     */
    public function undo(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate(['undo_token' => ['required', 'string', 'size:64']]);
        $batch = Cache::pull($this->undoCacheKey($data['undo_token']));

        if (! is_array($batch) || $batch['event_id'] !== $event->id || $batch['admin_id'] !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Masa batalkan telah berakhir atau aksi ini tidak dapat dibatalkan.',
            ], 422);
        }

        $removed = DB::transaction(fn () => EventPeserta::query()
            ->where('event_id', $event->id)
            ->whereIn('id', $batch['participant_ids'])
            ->delete());

        return response()->json([
            'success' => true,
            'message' => "Pendaftaran {$removed} kader dibatalkan.",
            'removed_count' => $removed,
            'registered_count' => EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->count(),
        ]);
    }

    /**
     * Menyimpan laporan jawaban yang diragukan dengan data sensitif tersamarkan.
     */
    public function feedback(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate([
            'query' => ['required', 'string', 'max:1000'],
            'sources' => ['nullable', 'array', 'max:20'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        SikaderFeedback::create([
            'event_id' => $event->id,
            'user_id' => $request->user()->id,
            'query' => $this->maskNik($data['query']),
            'sources' => $this->maskNik($data['sources'] ?? []),
            'rating' => $data['rating'],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Laporan telah disimpan untuk ditinjau.']);
    }

    private function undoCacheKey(string $token): string
    {
        return 'smart-enroll:undo:'.$token;
    }

    private function maskNik(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map(fn ($item) => $this->maskNik($item), $value);
        }

        return is_string($value)
            ? preg_replace('/\b(\d{6})\d{4,14}\b/', '$1****', $value)
            : $value;
    }
}
