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

        if (preg_match('/\b(ubah|ganti|perbarui|update|edit)\b/iu', $data['prompt'])) {
            $changes = $this->parseEventChanges($data['prompt']);
            if ($changes === []) {
                return response()->json([
                    'success' => true,
                    'intent' => 'event_update_help',
                    'message' => 'Saya bisa membantu mengubah nama kegiatan, tanggal mulai/selesai, lokasi, atau kuota. Sebutkan perubahan yang diinginkan, misalnya: “Ubah lokasi menjadi Gedung Dakwah dan kuota menjadi 40”.',
                ]);
            }

            $token = Str::random(48);
            Cache::put('sikader:event-update:'.$event->id.':'.auth()->id().':'.$token, $changes, now()->addMinutes(10));

            return response()->json([
                'success' => true,
                'intent' => 'event_update_confirmation',
                'event_update' => [
                    'token' => $token,
                    'changes' => collect($changes)->map(fn ($value, $key) => [
                        'field' => $key,
                        'label' => ['nama_event' => 'Nama kegiatan', 'tanggal_mulai' => 'Tanggal mulai', 'tanggal_selesai' => 'Tanggal selesai', 'lokasi' => 'Lokasi', 'kuota' => 'Kuota', 'status' => 'Status kegiatan'][$key],
                        'value' => $value,
                    ])->values(),
                ],
                'message' => 'Saya siapkan perubahan berikut. Perubahan baru disimpan setelah Anda menekan Konfirmasi.',
            ]);
        }

        return response()->json($result);
    }

    public function confirmEventUpdate(Request $request, Event $event): JsonResponse
    {
        $data = $request->validate(['token' => ['required', 'string', 'size:48']]);
        $key = 'sikader:event-update:'.$event->id.':'.auth()->id().':'.$data['token'];
        $changes = Cache::pull($key);

        if (! is_array($changes) || $changes === []) {
            return response()->json(['success' => false, 'message' => 'Pratinjau perubahan sudah kedaluwarsa. Silakan minta perubahan lagi.'], 422);
        }

        $validated = validator($changes, [
            'nama_event' => ['sometimes', 'string', 'max:255'],
            'tanggal_mulai' => ['sometimes', 'date'],
            'tanggal_selesai' => ['sometimes', 'date', 'after_or_equal:tanggal_mulai'],
            'lokasi' => ['sometimes', 'nullable', 'string', 'max:255'],
            'kuota' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'status' => ['sometimes', 'in:persiapan,berlangsung,selesai'],
        ])->validate();

        $event->fill($validated)->save();

        return response()->json(['success' => true, 'message' => 'Perubahan kegiatan berhasil disimpan.', 'event' => [
            'nama_event' => $event->nama_event,
            'tanggal_mulai' => $event->tanggal_mulai->format('Y-m-d'),
            'tanggal_selesai' => $event->tanggal_selesai->format('Y-m-d'),
            'lokasi' => $event->lokasi,
            'kuota' => $event->kuota,
        ]]);
    }

    private function parseEventChanges(string $prompt): array
    {
        $changes = [];
        $patterns = [
            'nama_event' => '/(?:nama(?: kegiatan)?(?: menjadi| dengan)?|judul(?: menjadi)?)\s+["“]?(.+?)["”]?(?=\s+(?:lokasi|kuota|tanggal|mulai|selesai)\b|$)/iu',
            'lokasi' => '/(?:lokasi|tempat)(?:\s+(?:menjadi|ke|diganti))?\s+["“]?(.+?)["”]?(?=\s+(?:dan|nama|kuota|tanggal|mulai|selesai)\b|$)/iu',
        ];
        foreach ($patterns as $field => $pattern) {
            if (preg_match($pattern, $prompt, $match)) {
                $changes[$field] = trim($match[1], " \\t\\n\\r\\0\\x0B\\\"'“”.,");
            }
        }
        if (preg_match('/\bkuota\s+(?:menjadi\s+)?(\d{1,5})\b/iu', $prompt, $match)) {
            $changes['kuota'] = (int) $match[1];
        }
        if (preg_match('/\bstatus(?: kegiatan)?\s+(?:menjadi\s+)?(persiapan|berlangsung|selesai)\b/iu', $prompt, $match)) {
            $changes['status'] = mb_strtolower($match[1]);
        }
        foreach (['tanggal_mulai' => 'mulai', 'tanggal_selesai' => 'selesai'] as $field => $label) {
            if (preg_match('/(?:tanggal\s+'.$label.'|'.$label.')(?:\s+(?:menjadi|tanggal))?\s+(\d{4}-\d{2}-\d{2})/iu', $prompt, $match)) {
                $changes[$field] = $match[1];
            }
        }

        return $changes;
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
