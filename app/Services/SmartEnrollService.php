<?php

namespace App\Services;

use App\Models\Absensi;
use App\Models\Anggota;
use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\EventSesi;
use App\Models\PenilaianAkhir;
use App\Models\Soal;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class SmartEnrollService
{
    /**
     * Daftar gelar awalan (prefix) yang dieliminasi saat normalisasi.
     */
    private const PREFIX_TITLES = [
        'prof.', 'prof', 'k.h.', 'kh.', 'kh', 'drs.', 'drs', 'dra.', 'dra',
        'drg.', 'drg', 'dr.', 'dr', 'ir.', 'ir', 'h.', 'h ', 'hj.', 'hj ',
        'ust.', 'ust ', 'ustadz', 'ustazah', 'kyai',
    ];

    /**
     * Daftar gelar akhiran (suffix) yang dieliminasi saat normalisasi.
     */
    private const SUFFIX_TITLES = [
        'm.pd.i', 's.pd.i', 'm.pd', 's.pd', 'm.t', 's.t', 'm.m', 's.e',
        'm.kom', 's.kom', 's.ked', 's.kep', 'ns.', 'ns', 'm.ag', 's.ag',
        'm.h', 's.h', 'm.si', 's.sos', 'sp.a', 'sp.pd', 'sp.b', 'ph.d',
        'a.md', 'b.a', 'm.a', 'l.c', 'lc.', 'lc',
    ];

    /**
     * Normalisasi nama kader: huruf kecil, bersihkan gelar akademik & tanda baca.
     */
    public function normalizeName(string $name): string
    {
        $clean = mb_strtolower(trim($name));

        // Bersihkan tanda petik dan kurung
        $clean = preg_replace('/[\(\)\[\]"\'`]/', ' ', $clean);

        // Hapus gelar suffix setelah koma jika ada
        if (str_contains($clean, ',')) {
            $parts = explode(',', $clean);
            $clean = $parts[0];
        }

        // Hapus prefix
        foreach (self::PREFIX_TITLES as $prefix) {
            $prefixRegex = '/^'.preg_quote($prefix, '/').'\s+/i';
            $clean = preg_replace($prefixRegex, '', $clean);
        }

        // Hapus suffix jika masih tersisa
        foreach (self::SUFFIX_TITLES as $suffix) {
            $suffixRegex = '/\s+'.preg_quote($suffix, '/').'$/i';
            $clean = preg_replace($suffixRegex, '', $clean);
        }

        // Hapus tanda baca titik dan koma yang tersisa
        $clean = preg_replace('/[.,]/', ' ', $clean);

        // Satukan spasi ganda
        $clean = preg_replace('/\s+/', ' ', $clean);

        return trim($clean);
    }

    /**
     * Mengekstrak daftar nama dari string teks (WhatsApp/Word/Clipboard).
     */
    public function parseNamesFromText(string $text): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $text);
        $names = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            // Hapus nomor urut (e.g. "1. ", "2) ", "- ", "* ", "• ")
            $cleaned = preg_replace('/^(\d+[\.\)]|\-|\*|•|[a-zA-Z][\.\)])\s*/u', '', $line);
            $cleaned = trim($cleaned);

            if (! empty($cleaned) && mb_strlen($cleaned) >= 2) {
                $names[] = $cleaned;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Mengekstrak nama-nama dari file Excel (.xlsx/.xls) atau CSV.
     */
    public function parseNamesFromFile(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $names = [];

        if (in_array($extension, ['csv', 'txt'], true)) {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle !== false) {
                $header = null;
                $nameColIndex = 0;

                while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                    if (empty(array_filter($row))) {
                        continue;
                    }

                    if ($header === null) {
                        $header = array_map(fn ($c) => mb_strtolower(trim((string) $c)), $row);
                        foreach ($header as $idx => $col) {
                            if (str_contains($col, 'nama') || str_contains($col, 'peserta') || str_contains($col, 'kader')) {
                                $nameColIndex = $idx;
                                break;
                            }
                        }

                        continue;
                    }

                    if (isset($row[$nameColIndex]) && ! empty(trim((string) $row[$nameColIndex]))) {
                        $names[] = trim((string) $row[$nameColIndex]);
                    }
                }
                fclose($handle);
            }
        } else {
            // Excel via Maatwebsite Excel
            $data = Excel::toArray([], $file);
            if (! empty($data) && ! empty($data[0])) {
                $sheet = $data[0];
                $nameColIndex = 0;
                $firstRow = $sheet[0] ?? [];

                $hasHeader = false;
                foreach ($firstRow as $idx => $cell) {
                    $cellLower = mb_strtolower(trim((string) $cell));
                    if (str_contains($cellLower, 'nama') || str_contains($cellLower, 'peserta') || str_contains($cellLower, 'kader')) {
                        $nameColIndex = $idx;
                        $hasHeader = true;
                        break;
                    }
                }

                $rows = $hasHeader ? array_slice($sheet, 1) : $sheet;
                foreach ($rows as $row) {
                    if (isset($row[$nameColIndex]) && ! empty(trim((string) $row[$nameColIndex]))) {
                        $names[] = trim((string) $row[$nameColIndex]);
                    }
                }
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Merangkum input sebelum pencocokan tanpa menyimpan atau mendaftarkan peserta.
     */
    public function previewInput(?string $text = null, ?UploadedFile $file = null): array
    {
        $rows = [];

        if ($file && in_array(strtolower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            $handle = fopen($file->getRealPath(), 'r');
            $header = $handle ? fgetcsv($handle, 1000, ',') : false;
            $headers = array_map(fn ($value) => mb_strtolower(trim((string) $value)), $header ?: []);
            $nameIndex = 0;
            $nikIndex = null;

            foreach ($headers as $index => $label) {
                if (str_contains($label, 'nama') || str_contains($label, 'peserta') || str_contains($label, 'kader')) {
                    $nameIndex = $index;
                }
                if ($label === 'nik' || str_contains($label, 'nik peserta')) {
                    $nikIndex = $index;
                }
            }

            while ($handle && ($row = fgetcsv($handle, 1000, ',')) !== false) {
                $name = trim((string) ($row[$nameIndex] ?? ''));
                if ($name !== '') {
                    $rows[] = ['name' => $name, 'nik' => $nikIndex === null ? null : trim((string) ($row[$nikIndex] ?? ''))];
                }
            }

            if ($handle) {
                fclose($handle);
            }
        } else {
            foreach ($this->parseNamesFromText((string) $text) as $name) {
                $rows[] = ['name' => $name, 'nik' => null];
            }
        }

        $seen = [];
        $duplicateRows = 0;
        $invalidNikRows = 0;

        foreach ($rows as $index => &$row) {
            $normalized = $this->normalizeName($row['name']);
            $row['status'] = isset($seen[$normalized]) ? 'duplicate' : 'ready';
            $seen[$normalized] = true;

            if ($row['status'] === 'duplicate') {
                $duplicateRows++;
            }

            if ($row['nik'] !== null && ! preg_match('/^\d{16}$/', $row['nik'])) {
                $invalidNikRows++;
                $row['nik_status'] = 'invalid';
            } else {
                $row['nik_status'] = $row['nik'] === null ? 'missing' : 'valid';
            }

            unset($row['nik']);
            $row['row'] = $index + 1;
        }
        unset($row);

        return [
            'total_rows' => count($rows),
            'unique_rows' => count($rows) - $duplicateRows,
            'duplicate_rows' => $duplicateRows,
            'invalid_nik_rows' => $invalidNikRows,
            'rows' => $rows,
        ];
    }

    /**
     * Mencocokkan nama dengan database anggota aktif.
     */
    public function matchNames(array $rawNames, Event $event): array
    {
        $matched = [];
        $alreadyEnrolled = [];
        $unmatched = [];

        $enrolledIds = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->pluck('anggota_id')
            ->flip()
            ->all();

        // Ambil seluruh kader aktif untuk in-memory smart matching
        $activeCandidates = Anggota::with(['user'])
            ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->get();

        $candidatesByNorm = [];
        foreach ($activeCandidates as $c) {
            $norm = $this->normalizeName($c->nama_lengkap);
            $candidatesByNorm[$norm][] = $c;
        }

        foreach ($rawNames as $rawName) {
            $normRaw = $this->normalizeName($rawName);
            if (empty($normRaw)) {
                continue;
            }

            $foundCandidate = null;

            // Tier 1: Exact match pada nama yang dinormalisasi
            if (isset($candidatesByNorm[$normRaw])) {
                $foundCandidate = $candidatesByNorm[$normRaw][0];
            } else {
                // Tier 2: Fuzzy match
                foreach ($candidatesByNorm as $candidateNorm => $list) {
                    if (levenshtein($normRaw, $candidateNorm) <= 2) {
                        $foundCandidate = $list[0];
                        break;
                    }
                    similar_text($normRaw, $candidateNorm, $percent);
                    if ($percent >= 88.0) {
                        $foundCandidate = $list[0];
                        break;
                    }
                }
            }

            if ($foundCandidate) {
                $item = [
                    'raw_name' => $rawName,
                    'anggota' => $foundCandidate,
                    'pcm' => $foundCandidate->pcm ?: $foundCandidate->kecamatan,
                    'aum' => $foundCandidate->nama_aum,
                    'ortom' => is_array($foundCandidate->keaktifan_ortom) ? array_filter($foundCandidate->keaktifan_ortom, fn ($x) => $x !== 'Tidak aktif') : [],
                ];

                if (isset($enrolledIds[$foundCandidate->id])) {
                    $alreadyEnrolled[] = $item;
                } else {
                    $matched[] = $item;
                }
            } else {
                $unmatched[] = [
                    'raw_name' => $rawName,
                ];
            }
        }

        return [
            'matched' => $matched,
            'already_enrolled' => $alreadyEnrolled,
            'unmatched' => $unmatched,
            'total_input' => count($rawNames),
        ];
    }

    /**
     * Menghasilkan teks undangan WhatsApp ramah bagi nama yang belum memiliki akun.
     */
    public function generateWhatsAppInvite(array $unmatchedNames, Event $event): string
    {
        $namesList = collect($unmatchedNames)
            ->pluck('raw_name')
            ->filter()
            ->values()
            ->map(fn ($name, $i) => ($i + 1).'. '.$name)
            ->implode("\n");

        $tglMulai = $event->tanggal_mulai ? $event->tanggal_mulai->format('d M Y') : 'Segera';
        $lokasi = $event->lokasi ?: 'Karanganyar';
        $registerUrl = route('anggota.register');

        return "*UNDANGAN REGISTRASI KADER BAITUL ARQAM*\n"
            ."*MPKSDI PDM KARANGANYAR*\n\n"
            ."Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\n"
            ."Semoga Bapak/Ibu/Saudara calon peserta Baitul Arqam senantiasa dalam lindungan Allah SWT.\n\n"
            ."Panitia menginformasikan bahwa nama Anda telah direkomendasikan untuk mengikuti kegiatan perkaderan:\n"
            ."📌 *{$event->nama_event}*\n"
            ."🗓️ *Tanggal:* {$tglMulai}\n"
            ."📍 *Lokasi:* {$lokasi}\n\n"
            ."Mengingat nama berikut belum terdata pada database portal MPKSDI:\n"
            ."{$namesList}\n\n"
            ."Mohon untuk segera melakukan registrasi mandiri melalui tautan resmi:\n"
            ."👉 {$registerUrl}\n\n"
            ."Silakan lengkapi biodata, asal PCM/AUM/ORTOM, dan pas foto resmi agar panitia dapat segera meng-enroll Anda ke sistem.\n\n"
            ."Terima kasih atas kerja samanya.\n"
            ."Nashrun minallah wa fathun qarib.\n"
            .'_Panitia Pelaksana MPKSDI PDM Karanganyar_';
    }

    /**
     * Rekomendasi kandidat berbasis kriteria lokal (Rule-Based).
     */
    public function recommendCandidates(Event $event, array $criteria): Collection
    {
        $limit = max(1, min(100, (int) ($criteria['limit'] ?? 20)));
        $gender = $criteria['gender'] ?? 'all'; // all, seimbang, L, P
        $pcm = $criteria['pcm'] ?? null;
        $aum = $criteria['aum'] ?? null;
        $sort = $criteria['sort'] ?? null;
        $sholatMasjid = ! empty($criteria['sholat_masjid']);
        $takmir = ! empty($criteria['takmir']);
        $hafalanQuran = ! empty($criteria['hafalan_quran']);

        // Default: prioritaskan yang belum pernah BA kecuali diminta kader sering ikut BA / kriteria lain
        $prioritizeNeverEnrolled = isset($criteria['prioritas_belum_ba'])
            ? (bool) $criteria['prioritas_belum_ba']
            : ($sort !== 'paling_sering_ba');

        $enrolledIds = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->pluck('anggota_id')
            ->all();

        $query = Anggota::with(['user', 'eventPeserta'])
            ->withCount('eventPeserta')
            ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->whereNotIn('id', $enrolledIds);

        if (! empty($pcm)) {
            $query->where(fn ($sub) => $sub->where('pcm', $pcm)->orWhere('kecamatan', $pcm));
        }

        if (! empty($aum)) {
            $query->where('nama_aum', $aum);
        }

        if ($sholatMasjid) {
            $query->where(function ($sub) {
                $sub->where('aktivitas_sholat_masjid', 'like', '%Selalu%')
                    ->orWhere('aktivitas_sholat_masjid', 'like', '%Sering%')
                    ->orWhere('aktifitas_jamaah_masjid', 'like', '%aktif%');
            });
        }

        if ($takmir) {
            $query->where('keaktifan_takmir', 'Ya');
        }

        if ($hafalanQuran) {
            $query->whereNotNull('hafalan_quran')->where('hafalan_quran', '!=', '');
        }

        if ($gender === 'seimbang') {
            $half = (int) ceil($limit / 2);

            $maleQuery = (clone $query)->where('jenis_kelamin', 'L');
            $femaleQuery = (clone $query)->where('jenis_kelamin', 'P');

            if ($sort === 'paling_sering_ba') {
                $maleQuery->orderByDesc('event_peserta_count')->orderByDesc('id');
                $femaleQuery->orderByDesc('event_peserta_count')->orderByDesc('id');
            } elseif ($prioritizeNeverEnrolled) {
                $maleQuery->orderBy('event_peserta_count', 'asc')->latest();
                $femaleQuery->orderBy('event_peserta_count', 'asc')->latest();
            } else {
                $maleQuery->latest();
                $femaleQuery->latest();
            }

            $male = $maleQuery->limit($half)->get();
            $female = $femaleQuery->limit($limit - $male->count())->get();

            return $male->concat($female)->take($limit);
        }

        if (in_array($gender, ['L', 'P'], true)) {
            $query->where('jenis_kelamin', $gender);
        }

        if ($sort === 'paling_sering_ba') {
            $query->orderByDesc('event_peserta_count')->orderByDesc('id');

            return $query->limit($limit)->get();
        }

        if ($prioritizeNeverEnrolled) {
            $query->orderBy('event_peserta_count', 'asc')->latest();

            return $query->limit($limit)->get();
        }

        return $query->latest()->limit($limit)->get();
    }

    /**
     * Ekstraksi kriteria pencarian cerdas dari bahasa alami (Local Fallback Parser).
     */
    public function extractLocalCriteria(string $prompt): array
    {
        $lower = mb_strtolower($prompt);
        $criteria = [
            'limit' => 20,
            'gender' => 'all',
            'pcm' => null,
            'aum' => null,
            'sort' => 'belum_ba',
            'prioritas_belum_ba' => true,
            'sholat_masjid' => false,
            'takmir' => false,
            'hafalan_quran' => false,
        ];

        // Ekstraksi preferensi partisipasi BA (paling sering vs belum pernah)
        if (preg_match('/\b(paling sering|sering ikut|banyak ikut|terbanyak|berpengalaman|senior|alumni|pernah ikut)\b/iu', $lower)) {
            $criteria['sort'] = 'paling_sering_ba';
            $criteria['prioritas_belum_ba'] = false;
        } elseif (preg_match('/\b(belum pernah|belum ikut|pemula|baru|regenerasi)\b/iu', $lower)) {
            $criteria['sort'] = 'belum_ba';
            $criteria['prioritas_belum_ba'] = true;
        }

        // Ekstraksi kebiasaan sholat di masjid
        if (preg_match('/\b(sholat|shalat|masjid|jamaah)\b/iu', $lower)) {
            $criteria['sholat_masjid'] = true;
        }

        // Ekstraksi hafalan quran
        if (preg_match('/\b(hafalan|tahfidz|hafidz|juz)\b/iu', $lower)) {
            $criteria['hafalan_quran'] = true;
        }

        // Ekstraksi takmir masjid
        if (preg_match('/\b(takmir|pengurus masjid)\b/iu', $lower)) {
            $criteria['takmir'] = true;
        }

        // Ekstraksi batas kuota angka (misal: "10 kader", "15 peserta")
        if (preg_match('/(\d+)\s*(kader|orang|peserta|nama)?/i', $prompt, $matches)) {
            $num = (int) $matches[1];
            if ($num > 0 && $num <= 100) {
                $criteria['limit'] = $num;
            }
        }

        // Ekstraksi preferensi gender
        if (preg_match('/\b(seimbang|50:50|setara|pria wanita|campur)\b/i', $lower)) {
            $criteria['gender'] = 'seimbang';
        } elseif (preg_match('/\b(putri|perempuan|akhwat|wanita|ibu|nasyiah|aisyiyah)\b/i', $lower)) {
            $criteria['gender'] = 'P';
        } elseif (preg_match('/\b(putra|laki|ikhwan|pria|bapak|pemuda)\b/i', $lower)) {
            $criteria['gender'] = 'L';
        }

        // Ekstraksi wilayah PCM Karanganyar
        $pcmList = config('arqam.opsi_form.cabang_pcm', []);
        foreach ($pcmList as $pcm) {
            if (str_contains($lower, mb_strtolower($pcm))) {
                $criteria['pcm'] = $pcm;
                break;
            }
        }

        // Ekstraksi Amal Usaha Muhammadiyah (AUM)
        if (str_contains($lower, 'pku') || str_contains($lower, 'rumah sakit') || str_contains($lower, 'kesehatan')) {
            $criteria['aum'] = 'RS PKU Muhammadiyah Karanganyar';
        } elseif (str_contains($lower, 'sekolah') || str_contains($lower, 'guru') || str_contains($lower, 'smp') || str_contains($lower, 'sma') || str_contains($lower, 'sd') || str_contains($lower, 'mim')) {
            $criteria['aum'] = 'Sekolah/Madrasah';
        }

        return $criteria;
    }

    /**
     * Mengambil konteks pengetahuan riil sistem untuk RAG (Retrieval-Augmented Generation).
     */
    public function retrieveEventKnowledge(Event $event): array
    {
        $kuota = $event->kuota;
        $terdaftar = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->count();
        $sisaKuota = $kuota !== null ? max(0, $kuota - $terdaftar) : null;

        $totalKaderAktif = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))->count();
        $totalBelumBa = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->doesntHave('eventPeserta')
            ->count();

        $genderStats = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->select('jenis_kelamin', DB::raw('count(*) as count'))
            ->groupBy('jenis_kelamin')
            ->pluck('count', 'jenis_kelamin')
            ->toArray();

        $topPcm = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->whereNotNull('pcm')
            ->where('pcm', '!=', '')
            ->select('pcm', DB::raw('count(*) as count'))
            ->groupBy('pcm')
            ->orderByDesc('count')
            ->limit(6)
            ->pluck('count', 'pcm')
            ->toArray();

        $topAum = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->whereNotNull('nama_aum')
            ->where('nama_aum', '!=', '')
            ->select('nama_aum', DB::raw('count(*) as count'))
            ->groupBy('nama_aum')
            ->orderByDesc('count')
            ->limit(4)
            ->pluck('count', 'nama_aum')
            ->toArray();

        $topBaCadres = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->withCount('eventPeserta')
            ->orderByDesc('event_peserta_count')
            ->limit(5)
            ->get(['nama_lengkap', 'pcm', 'event_peserta_count']);

        $totalSholatMasjid = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->where(fn ($sub) => $sub->where('aktivitas_sholat_masjid', 'like', '%Selalu%')->orWhere('aktivitas_sholat_masjid', 'like', '%Sering%'))
            ->count();

        $totalTakmir = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->where('keaktifan_takmir', 'Ya')
            ->count();

        $totalHafalan = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->whereNotNull('hafalan_quran')
            ->where('hafalan_quran', '!=', '')
            ->count();

        $fasilitatorList = $event->fasilitator()->pluck('name')->filter()->values()->all();

        $bobot = $event->getBobotKomposisi();
        $sesiCollection = $event->sesi()->orderBy('urutan')->get(['nama_sesi', 'jenis', 'pemateri', 'jam_mulai', 'jam_selesai', 'deskripsi']);
        $sesiList = $sesiCollection->map(function ($s) {
            $pemateri = ! empty($s->pemateri) ? " (Pemateri: {$s->pemateri})" : '';

            return "{$s->nama_sesi}{$pemateri}";
        })->all();

        $pesertaCollection = EventPeserta::where('event_id', $event->id)
            ->where('status_aktif', true)
            ->with('anggota')
            ->orderBy('id', 'asc')
            ->limit(25)
            ->get();

        $pesertaList = $pesertaCollection->map(function ($p) {
            $a = $p->anggota;

            return [
                'id' => $a?->id,
                'nama_lengkap' => $a?->nama_lengkap ?? 'Kader Terdaftar',
                'pcm' => $a?->pcm ?: ($a?->kecamatan ?: '-'),
                'aum' => $a?->nama_aum,
            ];
        })->all();

        return [
            'nama_event' => $event->nama_event,
            'deskripsi' => $event->deskripsi ?: 'Perkaderan Baitul Arqam formal untuk pimpinan, anggota, dan pengelola Amal Usaha Muhammadiyah (AUM) di lingkungan PDM Karanganyar.',
            'status' => $event->status,
            'lokasi' => $event->lokasi ?: 'Karanganyar',
            'tanggal_mulai' => $event->tanggal_mulai ? $event->tanggal_mulai->format('d M Y') : 'Segera',
            'tanggal_selesai' => $event->tanggal_selesai ? $event->tanggal_selesai->format('d M Y') : null,
            'kuota' => $kuota,
            'peserta_terdaftar' => $terdaftar,
            'sisa_kuota' => $sisaKuota,
            'is_penuh' => $kuota !== null && $sisaKuota <= 0,
            'fasilitator' => $fasilitatorList,
            'bobot_kognitif_pct' => (int) round(($bobot['kognitif'] ?? 0.40) * 100),
            'bobot_psikomotor_pct' => (int) round(($bobot['psikomotor'] ?? 0.35) * 100),
            'bobot_afektif_pct' => (int) round(($bobot['afektif'] ?? 0.25) * 100),
            'bobot_keaktifan_pct' => (int) round(($bobot['keaktifan'] ?? 0.10) * 100),
            'sesi_list' => $sesiList,
            'peserta_list' => $pesertaList,
            'total_kader_aktif' => $totalKaderAktif,
            'total_belum_ba' => $totalBelumBa,
            'top_ba_cadres' => $topBaCadres,
            'total_sholat_masjid' => $totalSholatMasjid,
            'total_takmir' => $totalTakmir,
            'total_hafalan' => $totalHafalan,
            'gender_l' => (int) ($genderStats['L'] ?? 0),
            'gender_p' => (int) ($genderStats['P'] ?? 0),
            'top_pcm' => $topPcm,
            'top_aum' => $topAum,
        ];
    }

    /**
     * Format konteks RAG menjadi string terstruktur untuk injeksi LLM.
     */
    public function formatRagContextForPrompt(array $k): string
    {
        $kuotaStr = $k['kuota'] !== null ? "{$k['kuota']} peserta (Terdaftar: {$k['peserta_terdaftar']}, Sisa: {$k['sisa_kuota']} kursi)" : "Terbuka (Terdaftar: {$k['peserta_terdaftar']} peserta)";
        $fasilitatorStr = ! empty($k['fasilitator']) ? implode(', ', $k['fasilitator']) : 'Belum ditentukan';
        $sesiStr = ! empty($k['sesi_list']) ? implode('; ', array_slice($k['sesi_list'], 0, 8)) : 'Materi Ideologi AIK, Manhaj Tarjih, Kepemimpinan, dan Ibadah Praktis Sesuai HPT';

        $pcmList = [];
        foreach ($k['top_pcm'] as $p => $c) {
            $pcmList[] = "{$p} ({$c})";
        }
        $pcmStr = ! empty($pcmList) ? implode(', ', $pcmList) : '-';

        $pesertaNames = [];
        foreach (array_slice($k['peserta_list'] ?? [], 0, 15) as $p) {
            $pcmStrP = $p['pcm'] !== '-' ? " (PCM {$p['pcm']})" : '';
            $pesertaNames[] = "{$p['nama_lengkap']}{$pcmStrP}";
        }
        $pesertaStr = ! empty($pesertaNames) ? implode(', ', $pesertaNames) : 'Belum ada peserta terdaftar';

        $topBaList = [];
        foreach ($k['top_ba_cadres'] ?? [] as $tbc) {
            $topBaList[] = "{$tbc->nama_lengkap} (PCM {$tbc->pcm}, {$tbc->event_peserta_count}x BA)";
        }
        $topBaStr = ! empty($topBaList) ? implode(', ', $topBaList) : '-';

        return "=== KONTEKS PENGETAHUAN PERKADERAN MPKSDI PDM KARANGANYAR (REAL-TIME RAG) ===\n"
            ."- Nama Kegiatan: {$k['nama_event']} (BA = Baitul Arqam, perkaderan formal kepemimpinan & ideologi Muhammadiyah)\n"
            ."- Deskripsi Kegiatan: {$k['deskripsi']}\n"
            ."- Waktu & Tempat: {$k['tanggal_mulai']}".($k['tanggal_selesai'] ? " s.d. {$k['tanggal_selesai']}" : '').", Lokasi: {$k['lokasi']}\n"
            ."- Kapasitas Kuota: {$kuotaStr}\n"
            ."- Peserta Terdaftar Saat Ini ({$k['peserta_terdaftar']} orang): {$pesertaStr}\n"
            ."- Tim Fasilitator / Instruktur: {$fasilitatorStr}\n"
            ."- Agenda & Sesi Materi: {$sesiStr}\n"
            ."- Evaluasi Kelulusan 4 Pilar: Kognitif ({$k['bobot_kognitif_pct']}%), Psikomotor Praktik Ibadah ({$k['bobot_psikomotor_pct']}%), Afektif Sikap ({$k['bobot_afektif_pct']}%), dan RTL/Keaktifan ({$k['bobot_keaktifan_pct']}%)\n"
            ."- Pangkalan Data Kader Aktif: Total {$k['total_kader_aktif']} kader (Belum pernah BA: {$k['total_belum_ba']} kader, Putra: {$k['gender_l']}, Putri: {$k['gender_p']})\n"
            ."- Riwayat Partisipasi BA: Tersedia riwayat lengkap keikutsertaan event. Kader terbanyak mengikuti BA (hingga 3x): {$topBaStr}\n"
            ."- Profil Keagamaan Formulir Kader: Tercatat {$k['total_sholat_masjid']} kader aktif dengan catatan selalu/sering sholat berjamaah di masjid, {$k['total_takmir']} pengurus takmir masjid, dan {$k['total_hafalan']} kader berhafalan Al-Qur'an\n"
            ."- Persebaran Cabang (PCM): {$pcmStr}\n"
            ."- Panduan Smart Enroll: Rekomendasi otomatis seimbang/AUM/PCM, tempel daftar nama teks (WA), unggah Excel/CSV, atau pencarian nama manual.\n"
            .'======================================================================';
    }

    /**
     * Mengklasifikasikan intensi pengguna secara cerdas (Intent Classification).
     */
    /**
     * Ekstraksi nama kader murni dari pertanyaan bahasa alami (menghilangkan kata instruksi/pencarian).
     */
    public function extractCadreSearchQuery(string $prompt): string
    {
        $clean = mb_strtolower(trim($prompt));
        $clean = preg_replace('/\b(tolong|mohon|bisa|coba|carikan|cari|cek|lihat|tampilkan|spill|info|data|profil|biodata|riwayat|perkaderan|baitul\s+arqam|ba|pelatihan|keikutsertaan|track\s+record|rekam\s+jejak|kader|nama|bernama|apakah|siapakah|siapa|sudah|pernah|ikut|terdaftar|lulus|di\s+kegiatan|pada\s+kegiatan|se-kabupaten|karanganyar)\b/iu', ' ', $clean);
        $clean = trim(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $clean));
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        return $clean;
    }

    /**
     * Mengambil riwayat lengkap perkaderan Baitul Arqam dari arsip SIMKader dan riwayat event.
     */
    public function getCadreHistory(Anggota $cadre): array
    {
        $history = [];

        // 1. Dari custom_fields['riwayat_ba'] (arsip impor SIMKader MPKSDI)
        $custom = is_array($cadre->custom_fields)
            ? $cadre->custom_fields
            : json_decode($cadre->custom_fields ?? '[]', true);

        if (! empty($custom['riwayat_ba']) && is_array($custom['riwayat_ba'])) {
            foreach ($custom['riwayat_ba'] as $r) {
                $history[] = [
                    'event' => $r['nama_event'] ?? ($r['event'] ?? 'Baitul Arqam'),
                    'lokasi' => $r['lokasi'] ?? '-',
                    'tanggal' => $r['tanggal'] ?? '-',
                    'status' => $r['status'] ?? 'Lulus',
                    'sumber' => 'Arsip SIMKader MPKSDI',
                ];
            }
        }

        // 2. Dari relasi eventPeserta
        foreach ($cadre->eventPeserta as $ep) {
            $ev = $ep->event;
            $eventName = $ev ? $ev->nama_event : "Baitul Arqam #{$ep->event_id}";

            $already = false;
            foreach ($history as $h) {
                if (mb_strtolower(trim($h['event'])) === mb_strtolower(trim($eventName))) {
                    $already = true;
                    break;
                }
            }

            if (! $already) {
                $tgl = $ev && $ev->tanggal_mulai ? $ev->tanggal_mulai->format('d/m/Y') : '-';
                $statusKelulusan = $ep->status_kelulusan ? ($ep->status_kelulusan === 'lulus' ? 'Lulus' : ucfirst($ep->status_kelulusan)) : 'Peserta Terdaftar';
                $history[] = [
                    'event' => $eventName,
                    'lokasi' => $ev ? ($ev->lokasi ?: '-') : '-',
                    'tanggal' => $tgl,
                    'status' => $statusKelulusan,
                    'sumber' => 'Sistem Portal MPKSDI',
                ];
            }
        }

        return $history;
    }

    /**
     * Pencarian cerdas kader aktif berbasis nama dengan dukungan fuzzy token, metaphone, dan normalisasi.
     */
    public function searchCadres(string $query, int $limit = 5): Collection
    {
        $cleanQuery = $this->extractCadreSearchQuery($query);
        if (empty($cleanQuery)) {
            $cleanQuery = mb_strtolower(trim($query));
        }

        if (empty($cleanQuery)) {
            return collect();
        }

        $queryNorm = $this->normalizeName($cleanQuery);
        $queryTokens = array_values(array_filter(explode(' ', $queryNorm), fn ($t) => mb_strlen($t) >= 2));

        $activeCadres = Anggota::with(['user', 'eventPeserta.event'])
            ->whereHas('user', fn ($q) => $q->where('status', 'aktif'))
            ->get();

        $scored = [];
        foreach ($activeCadres as $c) {
            $candNorm = $this->normalizeName($c->nama_lengkap);
            $candTokens = array_values(array_filter(explode(' ', $candNorm), fn ($t) => mb_strlen($t) >= 2));

            // Tier 1: Exact match pada nama dinormalisasi
            if ($candNorm === $queryNorm) {
                $scored[] = ['cadre' => $c, 'score' => 100];

                continue;
            }

            // Tier 2: Substring containment langsung
            if (str_contains($candNorm, $queryNorm)) {
                $scored[] = ['cadre' => $c, 'score' => 95];

                continue;
            }

            // Tier 3: Token-level matching (exact token, substring, levenshtein <= 2, atau metaphone)
            $matchedTokenCount = 0;
            foreach ($queryTokens as $qt) {
                $tokenFound = false;
                foreach ($candTokens as $ct) {
                    if ($qt === $ct || str_contains($ct, $qt) || str_contains($qt, $ct) || levenshtein($qt, $ct) <= 2 || metaphone($qt) === metaphone($ct)) {
                        $tokenFound = true;
                        break;
                    }
                }
                if ($tokenFound) {
                    $matchedTokenCount++;
                }
            }

            if (! empty($queryTokens) && $matchedTokenCount === count($queryTokens)) {
                similar_text($queryNorm, $candNorm, $sim);
                $scored[] = ['cadre' => $c, 'score' => 80 + ($sim * 0.15)];
            } elseif (count($queryTokens) > 1 && $matchedTokenCount >= ceil(count($queryTokens) * 0.6)) {
                similar_text($queryNorm, $candNorm, $sim);
                $scored[] = ['cadre' => $c, 'score' => 50 + ($sim * 0.2)];
            }
        }

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score']);

        return collect(array_slice($scored, 0, $limit))->pluck('cadre');
    }

    public function classifyIntent(string $prompt): string
    {
        $clean = mb_strtolower(trim($prompt));

        // 0. Slash Commands (Shortcut Aksi Cepat)
        if (str_starts_with($clean, '/')) {
            $cmd = explode(' ', ltrim($clean, '/'))[0];

            return match ($cmd) {
                'kuota' => 'info',
                'rekap', 'analitik', 'nilai', 'evaluasi' => 'analytics',
                'undangan', 'wa', 'blast' => 'whatsapp_invite',
                'ranking', 'peringkat' => 'analytics',
                'export', 'unduh', 'cetak' => 'export_docs',
                'soal', 'cbt' => 'cbt_soal',
                default => 'info',
            };
        }

        // CBT, Soal Pretest & Posttest, atau Salin Soal
        if (preg_match('/\b(salin\s+soal|copy\s+soal|klon(ing)?\s+soal|duplika(si|t)\s+soal|pindah(kan)?\s+soal|posttest\s+ke\s+pretest|pretest\s+ke\s+posttest|soal\s+posttest|soal\s+pretest|bank\s+soal)\b/iu', $clean)
            || (preg_match('/\b(soal|cbt)\b/iu', $clean) && preg_match('/\b(salin|copy|klon|duplikat|pindah|sama|pretest|posttest)\b/iu', $clean))) {
            return 'cbt_soal';
        }

        // Undangan WhatsApp, Broadcast, Blast
        if (preg_match('/\b(undangan\s+wa|blast\s+wa|buatkan\s+undangan|format\s+undangan|pesan\s+wa|broadcast|teks\s+wa|reminder\s+wa|kirim\s+wa)\b/iu', $clean)) {
            return 'whatsapp_invite';
        }

        // Analitik Penilaian, Nilai, KKM, Kehadiran, Risiko, Yudisium
        if (preg_match('/\b(analitik|analisis\s+nilai|rekap\s+nilai|evaluasi\s+nilai|nilai\s+akhir|passing\s+grade|kkm|berisiko|risiko|simulasi\s+yudisium|rekap\s+kelulusan|peringkat|ranking)\b/iu', $clean)
            || (preg_match('/\b(kehadiran|absen)\b/iu', $clean) && preg_match('/\b(kurang|bawah|rendah|75%|rekap|cek)\b/iu', $clean))) {
            if (! preg_match('/\b(bagaimana|apa\s+syarat|syarat\s+kelulusan|bobot|materi|kurikulum)\b/iu', $clean)) {
                if (! preg_match('/\b(rekomendasi|carikan|pilihkan|enroll)\b/iu', $clean)) {
                    return 'analytics';
                }
            }
        }

        // Export Dokumen / Unduh Rekap
        if (preg_match('/\b(export\s+excel|unduh\s+excel|download\s+excel|unduh\s+rekap|cetak\s+presensi|berita\s+acara|id\s+card\s+batch|sertifikat\s+batch)\b/iu', $clean)) {
            return 'export_docs';
        }

        // 1. Sapaan & obrolan santai (Greeting / Chat)
        if (preg_match('/^(hi+|hai+|halo+|helo+|hello+|hallo+|hola+|hey+|hei+|assalam.*|salam|pagi|siang|sore|malam|met\s+(pagi|siang|sore|malam)|test|tes|ping|p|oy|oi|permisi|punten|kulo\s+nuwun|apa\s+kabar|gimana\s+kabarnya|siapa\s+kamu|kamu\s+siapa|kamu\s+itu\s+apa|kamu\s+ini\s+apa|bisa\s+apa|bisa\s+bantu\s+apa|apa\s+tugasmu|terima\s+kasih|makasih|maturnuwun|suwun|syukron|jazakallah|ok|oke|sipp|mantap|keren)\b/iu', $clean)
            || preg_match('/\b(kamu itu apa|kamu ini apa|siapa kamu|kamu siapa|apa fungsimu|apa tugasmu|kamu bisa apa|apa saja yang bisa kamu bantu|apa yang bisa kamu bantu|bisa bantu apa|apa kabar|how are you)\b/iu', $clean)) {
            if (! preg_match('/\b(rekomendasi|carikan|pilihkan|enroll|jelaskan\s+materi|syarat\s+kelulusan)\b/iu', $clean)) {
                return 'chat';
            }
        }

        // 2. Pencarian eksplisit nama atau riwayat perkaderan kader tertentu
        $isExplicitSearch = (bool) (preg_match('/\b(riwayat\s+perkaderan|riwayat\s+ba|riwayat\s+kader|riwayat\s+pelatihan|track\s+record|rekam\s+jejak|keikutsertaan|pernah\s+ikut\s+ba|sudah\s+ikut\s+ba|ikut\s+ba\s+apa|profil\s+kader|biodata\s+kader|status\s+kader|cek\s+kader|data\s+kader|cari\s+kader|kader\s+bernama|cari\s+nama|cek\s+nama)\b/iu', $clean)
            || preg_match('/^(tolong\s+carikan|carikan|cari|cek|lihat|tampilkan|spill)\s+(riwayat|data|profil|nama|kader)\b/iu', $clean)
            || preg_match('/^(apakah|siapakah|siapa)\s+(.+)\s+(sudah|pernah|ikut|terdaftar|lulus)/iu', $clean)
            || preg_match('/^(cari nama|cek nama|kader bernama|profil kader|status kader)\s+(.+)/iu', $clean));

        if ($isExplicitSearch) {
            return 'search';
        }

        // 3. Pertanyaan edukatif, informasi event, materi, kelulusan, kuota, ATAU DAFTAR PESERTA TERDAFTAR (Informational & Educational Q&A)
        // Cek secara khusus kueri daftar peserta kegiatan (misal: "list pesertanya coba", "siapa saja pesertanya", "daftar peserta")
        if (preg_match('/\b(list\s+peserta|daftar\s+peserta|siapa\s+saja\s+peserta|siapa\s+aja\s+peserta|pesertanya\s+siapa|siapa\s+yang\s+ikut|siapa\s+yang\s+daftar|cek\s+peserta|lihat\s+peserta|tampilkan\s+peserta|data\s+peserta)\b/iu', $clean)
            || (preg_match('/\b(peserta|pesertanya|pendaftar)\b/iu', $clean) && preg_match('/\b(list|daftar|siapa|ada|berapa|lihat|liat|cek|tampil|tampilkan|spill|coba)\b/iu', $clean))) {
            if (! preg_match('/\b(rekomendasi|carikan|pilihkan|enroll\s+\d+)\b/iu', $clean)) {
                return 'info';
            }
        }

        if (preg_match('/\b(jelaskan|penjelasan|ceritakan|apa itu|apa maksud|tentang apa|tentang|soal ba|soal|ba ini|baitul arqam|darul arqam|bagaimana|mengapa|kenapa|materi|kurikulum|sesi|jadwal|agenda|susunan|tujuan|manfaat|kegiatan|acara|kelulusan|lulus|syarat|penilaian|bobot|nilai|kognitif|afektif|psikomotor|rtl|sertifikat|fasilitator|instruktur|pemateri|kuota|sisa kuota|kapasitas|berapa|kapan|lokasi|tempat|di mana|dimana|siapa|ada berapa|jumlah|cara daftar|cara enroll|bagaimana daftarnya|bantu daftar|cara pakai|muhammadiyah|mpksdi|tarjih|hpt|ortom|aum|pku|pendiri muhammadiyah)\b/iu', $clean)) {
            if (! preg_match('/\b(rekomendasi|carikan|pilihkan|tampilkan daftar|enroll 1|enroll 2|enroll 3)\b/iu', $clean)) {
                return 'info';
            }
        }

        // 4. Deteksi nama pendek murni (hanya diklasifikasikan 'search' jika benar-benar ada kader yang cocok di database)
        $words = preg_split('/\s+/', $clean);
        $wordCount = count($words);
        $isPureNamePattern = preg_match('/^[a-zA-Z\s\.,\']{3,40}$/u', $clean);
        $hasStopwords = (bool) preg_match('/\b(jelaskan|apa|bagaimana|ceritakan|soal|tentang|ba|acara|kegiatan|materi|tujuan|syarat|kelulusan|sertifikat|fasilitator|kuota|kursi|jadwal|waktu|lokasi|tempat|kapan|kenapa|mengapa|siapa|cara|daftar|daftarnya|enroll|rekomendasi|carikan|pilihkan|tampilkan|tampil|lihat|liat|list|cek|coba|kader|kadernya|peserta|pesertanya|pendaftar|seimbang|putra|putri|pria|wanita|aum|pcm|cabang|ada|ikut|ikutan|masuk|terdaftar|bro|sis|min|admin|muhammadiyah|mpksdi|hpt|tarjih)\b/iu', $clean);
        $startsWithCommand = (bool) preg_match('/^(list|daftar|siapa|siapakah|tampilkan|lihat|liat|cek|coba|ada|berapa|kasih|rekomendasi|bantu|tolong)\b/iu', $clean);

        if ($isPureNamePattern && ! $hasStopwords && ! $startsWithCommand && $wordCount >= 1 && $wordCount <= 4) {
            // Verifikasi apakah nama ini benar-benar ada di database kader aktif
            if ($this->searchCadres($clean, 1)->isNotEmpty()) {
                return 'search';
            }

            // Jika kata tunggal dan bukan nama kader, lebih alami diklasifikasikan sebagai chat (sapaan/obrolan)
            if ($wordCount === 1) {
                return 'chat';
            }

            return 'info';
        }

        // 5. Permintaan rekomendasi kandidat
        return 'recommendation';
    }

    /**
     * Membentuk daftar sumber RAG terverifikasi yang dikutip dalam jawaban.
     */
    public function buildRagSources(array $k): array
    {
        return [
            [
                'id' => 1,
                'title' => 'Pangkalan Data & Kuota Kegiatan',
                'badge' => 'Database: events',
                'snippet' => "Kegiatan: {$k['nama_event']}, Kuota: ".($k['kuota'] !== null ? "{$k['kuota']} peserta (sisa {$k['sisa_kuota']} kursi)" : 'Terbuka').", Lokasi: {$k['lokasi']}, Waktu: {$k['tanggal_mulai']}.",
            ],
            [
                'id' => 2,
                'title' => 'Kurikulum & Jadwal Sesi Materi',
                'badge' => 'Database: event_sesi',
                'snippet' => ! empty($k['sesi_list']) ? implode(', ', array_slice($k['sesi_list'], 0, 4)) : 'Kurikulum Pokok Baitul Arqam (AIK, Manhaj Tarjih, PHIWM, Kepemimpinan).',
            ],
            [
                'id' => 3,
                'title' => 'Sistem Evaluasi 4 Pilar MPKSDI',
                'badge' => 'Pedoman: SPM Karanganyar',
                'snippet' => "Komposisi Bobot: Kognitif {$k['bobot_kognitif_pct']}%, Psikomotor {$k['bobot_psikomotor_pct']}%, Afektif {$k['bobot_afektif_pct']}%, RTL/Keaktifan {$k['bobot_keaktifan_pct']}%.",
            ],
            [
                'id' => 4,
                'title' => 'Pangkalan Data Master Anggota',
                'badge' => 'Database: master_anggota',
                'snippet' => "Total: {$k['total_kader_aktif']} kader aktif, Belum pernah BA: {$k['total_belum_ba']} kader, Rasio gender: {$k['gender_l']} putra / {$k['gender_p']} putri.",
            ],
        ];
    }

    /**
     * Menghasilkan jawaban cerdas berbasis RAG lokal yang deterministik dan grounded pada database.
     */
    /**
     * Copilot Analitik Penilaian 4 Pilar & Deteksi Risiko Peserta.
     */
    public function analyzeEventEvaluation(Event $event, array $k, array $allSources): array
    {
        $peserta = EventPeserta::with('anggota')
            ->where('event_id', $event->id)
            ->where('status_aktif', true)
            ->get();
        $totalPeserta = $peserta->count();

        $penilaian = PenilaianAkhir::with('anggota')
            ->where('event_id', $event->id)
            ->get();

        $avgPre = round($penilaian->avg('nilai_pretest') ?? 0, 1);
        $avgPost = round($penilaian->avg('nilai_posttest') ?? 0, 1);
        $avgAkhir = round($penilaian->avg('nilai_akhir') ?? 0, 1);
        $gain = max(0, round($avgPost - $avgPre, 1));

        $belowKKM = $penilaian->filter(fn ($p) => ($p->nilai_akhir ?? 0) < 70)->values();

        $lulus = $penilaian->filter(fn ($p) => strtolower($p->status_kelulusan ?? '') === 'lulus')->count();
        $bersyarat = $penilaian->filter(fn ($p) => str_contains(strtolower($p->status_kelulusan ?? ''), 'bersyarat'))->count();
        $tidakLulus = $penilaian->filter(fn ($p) => str_contains(strtolower($p->status_kelulusan ?? ''), 'tidak'))->count();

        // Deteksi kehadiran sesi
        $totalSesi = EventSesi::where('event_id', $event->id)->count();
        $lowAttendance = [];
        if ($totalSesi > 0) {
            foreach ($peserta as $p) {
                $hadirCount = Absensi::where('event_id', $event->id)
                    ->where('anggota_id', $p->anggota_id)
                    ->count();
                $pct = round(($hadirCount / $totalSesi) * 100);
                if ($pct < 75) {
                    $lowAttendance[] = [
                        'nama' => $p->anggota->nama_lengkap ?? "Kader #{$p->anggota_id}",
                        'hadir' => $hadirCount,
                        'total' => $totalSesi,
                        'pct' => $pct,
                    ];
                }
            }
        }

        $msg = "📊 <strong>Copilot Analitik Penilaian & Deteksi Risiko 4 Pilar:</strong> [3]\n\n"
            ."• Total Peserta Aktif: <strong>{$totalPeserta} kader</strong>\n"
            ."• Peserta Terekam Penilaian: <strong>{$penilaian->count()} kader</strong>\n"
            ."• Rata-rata Pre-test: <strong>{$avgPre}</strong> | Post-test: <strong>{$avgPost}</strong> (Peningkatan: <strong>+{$gain}</strong> poin)\n"
            ."• Rata-rata Nilai Akhir: <strong>{$avgAkhir} / 100</strong>\n\n"
            ."⚖️ <strong>Simulasi Yudisium & Predikat Kelulusan:</strong>\n"
            ."• ✅ Lulus Murni: <strong>{$lulus} kader</strong>\n"
            ."• ⚠️ Lulus Bersyarat: <strong>{$bersyarat} kader</strong>\n"
            ."• ❌ Belum / Tidak Lulus: <strong>{$tidakLulus} kader</strong>\n\n";

        if ($belowKKM->isNotEmpty()) {
            $msg .= "⚠️ <strong>Peserta Berisiko Nilai di Bawah Passing Grade (< 70):</strong>\n";
            foreach ($belowKKM->take(5) as $idx => $b) {
                $num = $idx + 1;
                $nama = $b->anggota->nama_lengkap ?? 'Kader';
                $msg .= "{$num}. <strong>{$nama}</strong> — Nilai Akhir: <span class=\"text-rose-600 font-bold\">{$b->nilai_akhir}</span> (Status: {$b->status_kelulusan})\n";
            }
            $msg .= "\n<em>Rekomendasi: Berikan bimbingan remedial pada pilar Kognitif & RTL sebelum penandatanganan Berita Acara yudisium.</em>\n\n";
        }

        if (! empty($lowAttendance)) {
            $msg .= "⏰ <strong>Peserta dengan Kehadiran di Bawah 75%:</strong>\n";
            foreach (array_slice($lowAttendance, 0, 5) as $idx => $la) {
                $num = $idx + 1;
                $msg .= "{$num}. <strong>{$la['nama']}</strong> — Hadir {$la['hadir']}/{$la['total']} sesi ({$la['pct']}%)\n";
            }
            $msg .= "\n<em>Catatan: Sesuai SPM MPKSDI, kehadiran minimal adalah 75% untuk mendapatkan Sertifikat Kelulusan resmi [3].</em>\n\n";
        }

        $msg .= 'Gunakan tombol aksi di bawah untuk membuka rekap penilaian lengkap atau mengunduh lembar Excel:';

        return [
            'success' => true,
            'source' => 'rag_local',
            'intent' => 'analytics',
            'needs_candidate_card' => false,
            'candidates' => [],
            'sources' => [$allSources[2], $allSources[0]],
            'action_buttons' => [
                [
                    'label' => 'Lihat Rekap Nilai & Ranking',
                    'url' => route('admin.events.ranking', $event),
                    'icon' => 'ranking',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Unduh Rekap Excel',
                    'url' => route('admin.events.exportExcel', $event),
                    'icon' => 'excel',
                    'style' => 'excel',
                ],
                [
                    'label' => 'Berita Acara Yudisium',
                    'url' => route('admin.events.berita-acara', $event),
                    'icon' => 'document',
                    'style' => 'primary',
                ],
            ],
            'message' => trim($msg),
        ];
    }

    /**
     * Generator Pesan Undangan WhatsApp Resmi Berbasis Template MPKSDI.
     */
    public function generateWhatsAppBroadcast(Event $event, array $k, array $allSources): array
    {
        $tgl = $k['tanggal_mulai'];
        if ($k['tanggal_selesai'] && $k['tanggal_selesai'] !== $k['tanggal_mulai']) {
            $tgl .= " s.d. {$k['tanggal_selesai']}";
        }
        $lokasi = $k['lokasi'];
        $kuotaStr = $k['kuota'] !== null ? "{$k['kuota']} peserta (Sisa: {$k['sisa_kuota']} kursi)" : 'Terbuka';

        $inviteText = "📢 *UNDANGAN RESMI BAITUL ARQAM MPKSDI PDM KARANGANYAR*\n\n"
            ."Assalamu'alaikum Wr. Wb.\n\n"
            ."Kepada Yth. Pimpinan Cabang/Ranting & Sahabat Kader Muhammadiyah,\n\n"
            ."MPKSDI PDM Karanganyar mengundang kehadiran Anda dalam kegiatan perkaderan formal:\n"
            ."📌 *Kegiatan:* {$event->nama_event}\n"
            ."🗓 *Waktu:* {$tgl}\n"
            ."📍 *Lokasi:* {$lokasi}\n"
            ."👥 *Kapasitas Kuota:* {$kuotaStr}\n\n"
            ."Konfirmasi keikutsertaan & pendaftaran mandiri dapat diakses melalui portal resmi:\n"
            .route('anggota.register')."\n\n"
            ."_\"Meneguhkan Ideologi, Menggerakkan Persyarikatan.\"_\n\n"
            ."Nasrun Minallah wa Fathun Qarib,\n"
            .'*MPKSDI PDM Karanganyar*';

        $waUrl = 'https://api.whatsapp.com/send?text='.urlencode($inviteText);

        $msg = "📱 <strong>Generator Undangan & Broadcast WhatsApp:</strong> [1]\n\n"
            ."Berikut draft pesan undangan resmi yang siap dibagikan ke grup WhatsApp Cabang (PCM), Ranting (PRM), atau Amal Usaha Muhammadiyah (AUM):\n\n"
            ."<div class=\"p-3 rounded-xl bg-emerald-50/80 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-[11px] font-sans leading-relaxed text-emerald-950 dark:text-emerald-100\">\n"
            .nl2br(e($inviteText))
            ."\n</div>\n\n"
            ."Klik tombol <strong>'Kirim via WhatsApp'</strong> di bawah untuk langsung membuka WhatsApp Web atau aplikasi ponsel Anda:";

        return [
            'success' => true,
            'source' => 'rag_local',
            'intent' => 'whatsapp_invite',
            'needs_candidate_card' => false,
            'candidates' => [],
            'sources' => [$allSources[0]],
            'action_buttons' => [
                [
                    'label' => 'Kirim via WhatsApp',
                    'url' => $waUrl,
                    'icon' => 'whatsapp',
                    'style' => 'success',
                ],
            ],
            'message' => trim($msg),
        ];
    }

    /**
     * Tautan Cepat Ekspor Berkas & Dokumen Resmi Kegiatan.
     */
    public function generateExportLinks(Event $event, array $k, array $allSources): array
    {
        $msg = "📥 <strong>Akses Cepat Unduh Dokumen & Rekap Perkaderan:</strong> [1]\n\n"
            ."Dokumen resmi kegiatan <strong>{$event->nama_event}</strong> siap diunduh dan dicetak:\n"
            ."• <strong>Rekap Data & Nilai (Excel):</strong> Seluruh data peserta, presensi sesi, dan perolehan 4 pilar evaluasi.\n"
            ."• <strong>Lembar Ranking:</strong> Rekap urutan peringkat peserta berdasarkan nilai akhir.\n"
            ."• <strong>Berita Acara Yudisium:</strong> Dokumen formal hasil kelulusan yang siap ditandatangani Tim Master of Training (MoT) & MPKSDI.\n\n"
            .'Silakan klik tautan dokumen yang Anda butuhkan di bawah ini:';

        return [
            'success' => true,
            'source' => 'rag_local',
            'intent' => 'export_docs',
            'needs_candidate_card' => false,
            'candidates' => [],
            'sources' => [$allSources[0]],
            'action_buttons' => [
                [
                    'label' => 'Unduh Rekap Excel',
                    'url' => route('admin.events.exportExcel', $event),
                    'icon' => 'excel',
                    'style' => 'excel',
                ],
                [
                    'label' => 'Lihat Peringkat & Yudisium',
                    'url' => route('admin.events.ranking', $event),
                    'icon' => 'ranking',
                    'style' => 'primary',
                ],
                [
                    'label' => 'Berita Acara Yudisium',
                    'url' => route('admin.events.berita-acara', $event),
                    'icon' => 'document',
                    'style' => 'primary',
                ],
            ],
            'message' => trim($msg),
        ];
    }

    /**
     * Panduan & Akses Cepat Bank Soal CBT Pretest/Posttest.
     */
    public function handleCbtSoalInquiry(Event $event, array $k, array $allSources): array
    {
        $pretestCount = Soal::where('event_id', $event->id)->where('tipe', 'pretest')->count();
        $posttestCount = Soal::where('event_id', $event->id)->where('tipe', 'posttest')->count();

        $msg = "📝 <strong>Salin & Sinkronisasi Soal CBT (Pretest & Posttest):</strong> [3]\n\n"
            ."<strong>Ya, tentu saja!</strong> Sistem perkaderan Arqam MPKSDI sangat mendukung penyalinan butir soal antara <strong>Pretest</strong> dan <strong>Posttest</strong>, baik <strong>di dalam kegiatan yang sama ({$k['nama_event']})</strong> maupun dari kegiatan Baitul Arqam lainnya [3].\n\n"
            ."🎯 <strong>Mengapa Pretest dan Posttest Identik di Baitul Arqam?</strong>\n"
            ."Dalam metodologi perkaderan formal Muhammadiyah, butir soal evaluasi kognitif pretest dan posttest memang seharusnya identik agar pengukuran peningkatan pemahaman materi melalui rumus <strong>N-Gain Score</strong> valid secara ilmiah [3].\n\n"
            ."📊 <strong>Status Bank Soal Kegiatan Ini ({$k['nama_event']}):</strong>\n"
            ."• Pretest: <strong>{$pretestCount} butir soal</strong>\n"
            ."• Posttest: <strong>{$posttestCount} butir soal</strong>\n\n"
            ."⚡ <strong>Cara Salin Soal 1-Klik di Web Admin:</strong>\n"
            ."1. Buka halaman Bank Soal tujuan (misalnya jika ingin mengisi Posttest, buka <strong>Bank Soal Posttest</strong>).\n"
            ."2. Di bilah atas (atau pada kotak notifikasi jika soal masih kosong), klik tombol hijau <strong>\"Salin dari Pretest Kegiatan Ini\"</strong>.\n"
            ."3. Seluruh butir pertanyaan, pilihan jawaban A-D, dan kunci jawaban akan langsung terduplikasi otomatis!\n\n"
            .'Silakan klik tombol aksi di bawah untuk langsung menuju halaman Bank Soal:';

        return [
            'success' => true,
            'source' => 'rag_local',
            'intent' => 'cbt_soal',
            'needs_candidate_card' => false,
            'candidates' => [],
            'sources' => [$allSources[2], $allSources[0]],
            'action_buttons' => [
                [
                    'label' => "Bank Soal Pretest ({$pretestCount})",
                    'url' => route('admin.events.soal.index', [$event, 'pretest']),
                    'icon' => 'document',
                    'style' => 'primary',
                ],
                [
                    'label' => "Bank Soal Posttest ({$posttestCount})",
                    'url' => route('admin.events.soal.index', [$event, 'posttest']),
                    'icon' => 'document',
                    'style' => 'primary',
                ],
            ],
            'message' => trim($msg),
        ];
    }

    public function answerWithRag(string $prompt, Event $event, array $k, string $intent): array
    {
        $clean = mb_strtolower(trim($prompt));
        $allSources = $this->buildRagSources($k);

        // 👉 INTENT: CBT & SALIN SOAL PRETEST/POSTTEST
        if ($intent === 'cbt_soal') {
            return $this->handleCbtSoalInquiry($event, $k, $allSources);
        }

        // 👉 INTENT: ANALYTICS & RISK DETECTION (Copilot Analitik Penilaian 4 Pilar)
        if ($intent === 'analytics') {
            return $this->analyzeEventEvaluation($event, $k, $allSources);
        }

        // 👉 INTENT: WHATSAPP INVITE & BROADCAST
        if ($intent === 'whatsapp_invite') {
            return $this->generateWhatsAppBroadcast($event, $k, $allSources);
        }

        // 👉 INTENT: EXPORT & DOCUMENT LINKS
        if ($intent === 'export_docs') {
            return $this->generateExportLinks($event, $k, $allSources);
        }

        // ── INTENT: CHAT / GREETING ──
        if ($intent === 'chat') {
            // Tentukan sapaan balik yang alami & welcome
            if (preg_match('/\b(hi+|hai+|hey+|hei+|hello+|helo+|hallo+|hola+)\b/iu', $clean)) {
                $salutation = 'Hai juga! 👋';
            } elseif (preg_match('/\b(assalam|assalamu|salam)\b/iu', $clean)) {
                $salutation = "Wa'alaikumussalam Warahmatullahi Wabarakatuh! 👋";
            } elseif (preg_match('/\b(terima\s+kasih|makasih|maturnuwun|syukron|jazakallah)\b/iu', $clean)) {
                $salutation = 'Sama-sama! Senang bisa mendampingi Anda di perkaderan MPKSDI. 😊';
            } elseif (preg_match('/\bpagi\b/iu', $clean)) {
                $salutation = 'Selamat pagi! 👋';
            } elseif (preg_match('/\bsiang\b/iu', $clean)) {
                $salutation = 'Selamat siang! 👋';
            } elseif (preg_match('/\bsore\b/iu', $clean)) {
                $salutation = 'Selamat sore! 👋';
            } elseif (preg_match('/\bmalam\b/iu', $clean)) {
                $salutation = 'Selamat malam! 👋';
            } else {
                $salutation = 'Halo! Senang bisa menyapa Anda! 👋';
            }

            $sisaText = $k['kuota'] !== null ? "tersisa <strong>{$k['sisa_kuota']} kursi</strong> dari total kuota <strong>{$k['kuota']}</strong>" : 'kuota kapasitas <strong>terbuka</strong>';
            $msg = "{$salutation}\n\n"
                ."Saya <strong>SiKader</strong>, asisten AI perkaderan resmi MPKSDI PDM Karanganyar untuk kegiatan <strong>{$k['nama_event']}</strong> di <strong>{$k['lokasi']}</strong> [1]!\n\n"
                ."Ada yang bisa saya bantu hari ini? Anda dapat meminta saya untuk:\n"
                ."• 🤖 <strong>Rekomendasi kader terbaik:</strong> Misal <em>\"Rekomendasikan 10 kader dari Colomadu\"</em> atau <em>\"Carikan kader putri dari RS PKU\"</em> [4]\n"
                ."• 📊 <strong>Cek status kuota & kursi:</strong> Saat ini {$sisaText} [1]\n"
                ."• 📖 <strong>Pelajari kurikulum & evaluasi:</strong> Tanya materi pokok atau syarat kelulusan 4 pilar Baitul Arqam [3]\n"
                ."• 📋 <strong>Pendaftaran kilat:</strong> Tempel daftar nama hasil copy-paste WhatsApp atau unggah berkas Excel/CSV\n\n"
                .'Santai saja, silakan tulis instruksi atau pertanyaan Anda di kolom chat ya! 😊';

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'chat',
                'needs_candidate_card' => false,
                'candidates' => [],
                'sources' => [$allSources[0], $allSources[3], $allSources[2]],
                'message' => $msg,
            ];
        }

        // ── INTENT: INFORMATIONAL & EDUCATIONAL Q&A ──
        if ($intent === 'info') {
            // 1. Tanya Kelulusan / Penilaian / Bobot / Sertifikat (Lebih spesifik)
            if (preg_match('/\b(lulus|kelulusan|syarat|penilaian|bobot|nilai|kognitif|afektif|psikomotor|rtl|sertifikat)\b/iu', $clean)) {
                $msg = "⚖️ <strong>Sistem Evaluasi & Kriteria Kelulusan Baitul Arqam:</strong> [3]\n\n"
                    ."Kelulusan peserta dinilai secara komprehensif melalui <strong>4 Pilar Evaluasi</strong> [3]:\n\n"
                    ."1. 🧠 <strong>Kognitif ({$k['bobot_kognitif_pct']}%):</strong> Pre-test, Post-test materi AIK, dan N-Gain pemahaman materi [3].\n"
                    ."2. 🤲 <strong>Psikomotor ({$k['bobot_psikomotor_pct']}%):</strong> Praktik ibadah shalat fardhu, shalat jenazah, wudhu, dan tayammum sesuai Himpunan Putusan Tarjih (HPT) [3].\n"
                    ."3. 🤝 <strong>Afektif ({$k['bobot_afektif_pct']}%):</strong> Kedisiplinan kehadiran (minimal 75-100%), adab islami, akhlak, dan kepatuhan tata tertib [3].\n"
                    ."4. 📝 <strong>Keaktifan & RTL ({$k['bobot_keaktifan_pct']}%):</strong> Keaktifan forum tanya-jawab dan penyusunan Rencana Tindak Lanjut (RTL) pasca perkaderan [3].\n\n"
                    .'Peserta yang dinyatakan lulus berhak mendapatkan <strong>Sertifikat Resmi MPKSDI</strong> dengan nomor baku dan verifikasi QR token digital [3].';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[2], $allSources[0]],
                    'message' => $msg,
                ];
            }

            // 2. Tanya Materi / Kurikulum / Sesi (Lebih spesifik)
            if (preg_match('/\b(materi|kurikulum|sesi|agenda|susunan acara)\b/iu', $clean)) {
                if (! empty($k['sesi_list'])) {
                    $listSesi = implode("\n", array_map(fn ($s, $i) => ($i + 1).". <strong>{$s}</strong>", $k['sesi_list'], array_keys($k['sesi_list'])));
                    $msg = "📚 <strong>Kurikulum & Sesi Materi {$k['nama_event']}:</strong> [2]\n\n{$listSesi}\n\n"
                        .'Seluruh materi di atas dievaluasi melalui pre-test & post-test kognitif, keaktifan forum, serta praktik ibadah [3].';
                } else {
                    $msg = "📚 <strong>Materi Pokok Perkaderan Baitul Arqam MPKSDI:</strong> [2]\n\n"
                        ."1. <strong>Muqaddimah Anggaran Dasar & Kepribadian Muhammadiyah</strong>\n"
                        ."2. <strong>Matan Keyakinan dan Cita-cita Hidup Muhammadiyah (MKCH)</strong>\n"
                        ."3. <strong>Pedoman Hidup Islami Warga Muhammadiyah (PHIWM)</strong>\n"
                        ."4. <strong>Manhaj Tarjih & Ibadah Praktis sesuai Himpunan Putusan Tarjih</strong>\n"
                        ."5. <strong>Revitalisasi Gerakan Dakwah & Etos Kerja di AUM</strong>\n"
                        ."6. <strong>Penyusunan Rencana Tindak Lanjut (RTL)</strong>\n\n"
                        .'Fasilitator dapat melengkapi sesi spesifik kegiatan melalui menu Jadwal & Sesi [2].';
                }

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[1], $allSources[2]],
                    'message' => $msg,
                ];
            }

            // 3. Tanya Panduan Cara Daftar / Alur Enroll
            if (preg_match('/\b(cara daftar|cara enroll|bagaimana daftarnya|bantu daftar|cara pakai|bagaimana cara)\b/iu', $clean)) {
                $msg = "💡 <strong>Panduan Pendaftaran Kader (Smart Enroll) SiKader:</strong>\n\n"
                    ."Anda dapat mendaftarkan kader ke kegiatan ini dengan beberapa cara mudah:\n\n"
                    ."1. 🤖 <strong>Rekomendasi Cerdas:</strong> Cukup ketik perintah seperti <em>\"Rekomendasikan 10 kader dari PCM Colomadu\"</em> atau <em>\"Carikan kader putri seimbang\"</em> [4].\n"
                    ."2. 📋 <strong>Tempel Daftar Nama (WhatsApp):</strong> Klik tombol <em>\"Tempel daftar nama\"</em> lalu paste daftar nama dari chat WhatsApp grup. Sistem akan mencocokkan otomatis ke basis data anggota [4].\n"
                    ."3. 📄 <strong>Unggah File Excel/CSV:</strong> Klik <em>\"Unggah Excel / CSV\"</em> untuk mendaftarkan kader secara massal dari spreadsheet.\n"
                    .'4. 🔍 <strong>Pencarian Nama:</strong> Ketik nama kader (misal: <em>"Ahmad Dahlan"</em>) untuk mengecek dan mendaftarkannya perorangan [4].';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[3], $allSources[0]],
                    'message' => $msg,
                ];
            }

            // 4. Tanya tentang Muhammadiyah / Sejarah / K.H. Ahmad Dahlan
            if (preg_match('/\b(muhammadiyah|ahmad\s+dahlan|kauman|1912|gerakan\s+islam|amar\s+ma[\'`]?ruf)\b/iu', $clean)) {
                $msg = "🌟 <strong>Tentang Persyarikatan Muhammadiyah:</strong>\n\n"
                    ."<strong>Muhammadiyah</strong> didirikan oleh pahlawan nasional <strong>K.H. Ahmad Dahlan</strong> pada tanggal <strong>8 Dzulhijjah 1330 H (18 November 1912)</strong> di Kampung Kauman, Yogyakarta.\n\n"
                    ."Gerakan ini berwatak <strong>Gerakan Islam, Dakwah Amar Ma'ruf Nahi Munkar</strong>, dan <strong>Tajdid (pembaruan)</strong> yang berpedoman teguh pada <strong>Al-Qur'an dan As-Sunnah As-Shahihah</strong>. Misi utamanya adalah menegakkan dan menjunjung tinggi Agama Islam sehingga terwujud masyarakat Islam yang sebenar-benarnya.\n\n"
                    .'Di Kabupaten Karanganyar, perkaderan Baitul Arqam ini diselenggarakan oleh <strong>MPKSDI PDM Karanganyar</strong> untuk meneguhkan ideologi dan militansi kepemimpinan kader persyarikatan [1].';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[0], $allSources[2]],
                    'message' => $msg,
                ];
            }

            // 5. Tanya tentang MPKSDI
            if (preg_match('/\b(mpksdi|majelis\s+pembinaan\s+kader|tugas\s+mpksdi|peran\s+mpksdi)\b/iu', $clean)) {
                $msg = "🏛️ <strong>Majelis Pembinaan Kader dan Sumber Daya Insani (MPKSDI):</strong>\n\n"
                    ."<strong>MPKSDI</strong> merupakan unsur pembantu pimpinan persyarikatan Muhammadiyah di tingkat Daerah (PDM) yang bertugas:\n\n"
                    ."1. 📚 <strong>Penyelenggaraan Perkaderan Formal:</strong> Merancang dan mengawal kurikulum perkaderan utama (Baitul Arqam & Darul Arqam) bagi pimpinan, kader ortom, dan pegawai Amal Usaha Muhammadiyah [2].\n"
                    ."2. 📊 <strong>Sistem Informasi & Pangkalan Data Kader (SIMKader):</strong> Mengelola master data kader aktif se-Kabupaten Karanganyar ({$k['total_kader_aktif']} kader) serta memetakan keikutsertaan Baitul Arqam [4].\n"
                    ."3. ⚖️ <strong>Penjaminan Mutu & Standar Kompetensi:</strong> Mengukur kelulusan kader melalui 4 Pilar Evaluasi (Kognitif, Psikomotorik Ibadah Tarjih, Afektif Akhlak, dan RTL) serta menerbitkan sertifikat ber-QR resmi [3].\n\n"
                    .'Ada yang ingin Anda tanyakan lebih lanjut seputar program perkaderan?';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[3], $allSources[2]],
                    'message' => $msg,
                ];
            }

            // 6. Tanya tentang HPT (Himpunan Putusan Tarjih) / Ibadah Praktis
            if (preg_match('/\b(hpt|himpunan\s+putusan\s+tarjih|manhaj\s+tarjih|tarjih|ibadah\s+praktis|praktik\s+ibadah)\b/iu', $clean)) {
                $msg = "📖 <strong>Himpunan Putusan Tarjih (HPT) dalam Baitul Arqam:</strong> [3]\n\n"
                    ."<strong>HPT</strong> adalah kodifikasi resmi pedoman pengamalan ibadah praktis warga Muhammadiyah yang dirumuskan oleh Majelis Tarjih dan Tajdid berlandaskan dalil-dalil Al-Qur'an dan As-Sunnah yang shahih/maqbul.\n\n"
                    ."Dalam evaluasi perkaderan Baitul Arqam, penguasaan ibadah sesuai HPT dinilai pada <strong>Pilar Psikomotorik ({$k['bobot_psikomotor_pct']}%)</strong> [3], meliputi:\n"
                    ."• 🤲 Tata cara wudhu dan tayammum sesuai sunnah\n"
                    ."• 🕌 Praktik shalat fardhu (bacaan thuma'ninah dan gerakan sesuai HPT)\n"
                    ."• ⚰️ Tata cara shalat jenazah\n"
                    ."• 📖 Kebiasaan sholat berjamaah di masjid dan hafalan Al-Qur'an [4].";

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[2], $allSources[3]],
                    'message' => $msg,
                ];
            }

            // 7. Tanya tentang Ortom & AUM
            if (preg_match('/\b(ortom|organisasi\s+otonom|aum|amal\s+usaha|pku|sekolah|ipm|imm|pemuda\s+muhammadiyah|nasyiatul|hizbul\s+wathan|tapak\s+suci)\b/iu', $clean)) {
                $msg = "🏢 <strong>Struktur Ortom & Amal Usaha Muhammadiyah (AUM):</strong> [4]\n\n"
                    ."Persyarikatan menggerakkan dakwah melalui dua pilar utama:\n\n"
                    ."1. 👥 <strong>7 Organisasi Otonom (Ortom):</strong> Aisyiyah, Pemuda Muhammadiyah, Nasyiatul Aisyiyah (NA), Ikatan Pelajar Muhammadiyah (IPM), Ikatan Mahasiswa Muhammadiyah (IMM), Gerakan Kepanduan Hizbul Wathan (HW), dan Tapak Suci Putera Muhammadiyah.\n"
                    ."2. 🏥 <strong>Amal Usaha Muhammadiyah (AUM):</strong> Pelayanan nyata bagi umat di bidang kesehatan (RS PKU Muhammadiyah), pendidikan (SD/MIM, SMP, SMA/SMK, Perguruan Tinggi), panti asuhan sosial, dan lembaga ekonomi/BTM [4].\n\n"
                    .'Seluruh kader dari Ortom maupun unit AUM di Karanganyar terintegrasi di SIMKader dan dapat direkomendasikan langsung untuk perkaderan Baitul Arqam ini [1].';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[3], $allSources[0]],
                    'message' => $msg,
                ];
            }

            // 8. Tanya tentang Event / Kegiatan / Penjelasan Baitul Arqam ini
            if (preg_match('/\b(jelaskan\s+soal\s+ba|soal\s+ba|tentang\s+acara|tentang\s+kegiatan|tentang\s+ba|apa\s+ini|kegiatan\s+ini|acara\s+ini|apa\s+itu\s+baitul\s+arqam|apa\s+itu\s+ba\b|spm)\b/iu', $clean)
                || (str_contains($clean, 'baitul arqam') && preg_match('/\b(jelaskan|apa|tentang|tujuan|maksud)\b/iu', $clean))) {
                $tgl = $k['tanggal_selesai'] && $k['tanggal_selesai'] !== $k['tanggal_mulai']
                    ? "{$k['tanggal_mulai']} s.d. {$k['tanggal_selesai']}"
                    : $k['tanggal_mulai'];
                $sisaText = $k['kuota'] !== null ? "tersisa <strong>{$k['sisa_kuota']} kursi</strong> dari total kuota {$k['kuota']}" : 'kapasitas terbuka';
                $fasilitatorStr = ! empty($k['fasilitator']) ? implode(', ', $k['fasilitator']) : 'Tim Instruktur MPKSDI Karanganyar';

                $sesiPreview = ! empty($k['sesi_list'])
                    ? "\n\n📚 <strong>Agenda & Sesi Materi:</strong> [2]\n".implode("\n", array_map(fn ($s, $i) => ($i + 1).". {$s}", array_slice($k['sesi_list'], 0, 5), array_keys(array_slice($k['sesi_list'], 0, 5))))
                    : "\n\n📚 <strong>Kurikulum Inti:</strong> Muqaddimah AD/ART, MKCH, PHIWM, Manhaj Tarjih & Ibadah Praktis sesuai HPT, serta Etos Kerja di AUM [2].";

                $msg = "📖 <strong>Tentang Kegiatan {$k['nama_event']}:</strong> [1]\n\n"
                    ."{$k['deskripsi']}\n\n"
                    ."<strong>Baitul Arqam (BA)</strong> merupakan perkaderan formal utama persyarikatan Muhammadiyah yang dikelola oleh MPKSDI. Tujuannya adalah menyamakan persepsi, menanamkan nilai ideologi persyarikatan, mengokohkan etos integritas kepemimpinan, dan meningkatkan militansi ber-Muhammadiyah baik di jajaran pimpinan maupun Amal Usaha Muhammadiyah (AUM).\n\n"
                    ."📌 <strong>Rincian Pelaksanaan:</strong>\n"
                    ."• 🗓️ <strong>Waktu:</strong> {$tgl} [1]\n"
                    ."• 🏢 <strong>Lokasi:</strong> {$k['lokasi']} [1]\n"
                    ."• 👥 <strong>Peserta:</strong> {$k['peserta_terdaftar']} terdaftar aktif ({$sisaText}) [1]\n"
                    ."• 👨‍🏫 <strong>Fasilitator:</strong> {$fasilitatorStr} [1]\n"
                    ."• ⚖️ <strong>Evaluasi Kelulusan:</strong> Kognitif ({$k['bobot_kognitif_pct']}%), Praktik Ibadah ({$k['bobot_psikomotor_pct']}%), Afektif Sikap ({$k['bobot_afektif_pct']}%), dan RTL ({$k['bobot_keaktifan_pct']}%) [3]"
                    .$sesiPreview."\n\n"
                    .'Ada yang ingin Anda tanyakan lebih lanjut seputar materi, kelulusan, atau ingin saya carikan rekomendasi kader untuk didaftarkan?';

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => $allSources,
                    'message' => $msg,
                ];
            }

            // 5. Tanya Peserta Terdaftar (misal: "list pesertanya coba", "siapa saja pesertanya", "daftar peserta")
            if (preg_match('/\b(peserta|pesertanya|pendaftar|yang ikut|yang daftar|yang terdaftar)\b/iu', $clean)
                && ! preg_match('/\b(rekomendasi|carikan|pilihkan|enroll\s+\d+)\b/iu', $clean)) {

                $pesertaList = $k['peserta_list'] ?? [];
                if (! empty($pesertaList)) {
                    $lines = [];
                    foreach ($pesertaList as $idx => $p) {
                        $num = $idx + 1;
                        $pcm = $p['pcm'] ?: '-';
                        $aumStr = ! empty($p['aum']) ? " &bull; <span class=\"text-slate-500\">{$p['aum']}</span>" : '';
                        $lines[] = "{$num}. 👤 <strong>{$p['nama_lengkap']}</strong> — PCM {$pcm}{$aumStr}";
                    }
                    $listStr = implode("\n", $lines);
                    $sisaText = $k['kuota'] !== null ? "tersisa <strong>{$k['sisa_kuota']} kursi</strong> dari total kuota {$k['kuota']} peserta" : 'kapasitas terbuka';

                    $msg = "📋 <strong>Daftar Peserta Terdaftar ({$k['nama_event']}):</strong> [1]\n\n"
                        ."Saat ini tercatat ada <strong>{$k['peserta_terdaftar']} kader</strong> yang telah resmi terdaftar sebagai peserta [1]:\n\n"
                        ."{$listStr}\n\n"
                        ."📌 <strong>Status Kuota:</strong> {$sisaText} [1].\n\n"
                        .($k['sisa_kuota'] > 0
                            ? 'Apakah Anda ingin saya rekomendasikan kader lain untuk mengisi sisa kursi yang masih kosong? [4]'
                            : 'Kapasitas kuota kegiatan saat ini sudah terpenuhi optimal.');
                } else {
                    $msg = "Saat ini <strong>belum ada peserta yang terdaftar</strong> untuk kegiatan <strong>{$k['nama_event']}</strong> [1].\n\n"
                        .'Anda dapat meminta saya merekomendasikan kader (misal: <em>"Rekomendasikan 10 kader dari PCM Colomadu"</em>) atau menempel daftar nama dari WhatsApp untuk didaftarkan sekaligus [4].';
                }

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[0], $allSources[3]],
                    'message' => $msg,
                ];
            }

            // Tanya Kuota
            if (str_contains($clean, 'kuota') || str_contains($clean, 'sisa') || str_contains($clean, 'kursi') || str_contains($clean, 'kapasitas')) {
                if ($k['kuota'] !== null) {
                    $msg = "📌 <strong>Status Kuota Peserta:</strong> [1]\n\n"
                        ."• Kapasitas Kuota: <strong>{$k['kuota']} peserta</strong> [1]\n"
                        ."• Terdaftar Aktif: <strong>{$k['peserta_terdaftar']} kader</strong> [1]\n"
                        ."• Sisa Kursi Kosong: <strong>{$k['sisa_kuota']} kursi</strong> [1]\n\n"
                        .($k['sisa_kuota'] > 0
                            ? 'Masih ada kursi tersedia. Apakah Anda ingin saya rekomendasikan kader yang diprioritaskan belum pernah mengikuti Baitul Arqam?'
                            : '⚠️ Kuota saat ini sudah penuh terisi.');
                } else {
                    $msg = "Untuk kegiatan <strong>{$k['nama_event']}</strong>, kapasitas kuota tidak dibatasi (unlimited) [1]. Saat ini telah terdaftar <strong>{$k['peserta_terdaftar']} kader aktif</strong>.";
                }

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[0]],
                    'message' => $msg,
                ];
            }

            // Tanya Fasilitator
            if (str_contains($clean, 'fasilitator') || str_contains($clean, 'instruktur') || str_contains($clean, 'pemateri')) {
                if (! empty($k['fasilitator'])) {
                    $list = implode("\n", array_map(fn ($f, $i) => ($i + 1).". <strong>{$f}</strong>", $k['fasilitator'], array_keys($k['fasilitator'])));
                    $msg = "👨‍🏫 <strong>Tim Fasilitator / Instruktur {$k['nama_event']}:</strong> [1]\n\n{$list}";
                } else {
                    $msg = "Saat ini belum ada fasilitator yang ditugaskan secara khusus untuk kegiatan <strong>{$k['nama_event']}</strong> di sistem [1].";
                }

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[0]],
                    'message' => $msg,
                ];
            }

            // Tanya Waktu / Lokasi
            if (str_contains($clean, 'kapan') || str_contains($clean, 'tanggal') || str_contains($clean, 'lokasi') || str_contains($clean, 'tempat') || str_contains($clean, 'dimana')) {
                $tgl = $k['tanggal_selesai'] && $k['tanggal_selesai'] !== $k['tanggal_mulai']
                    ? "{$k['tanggal_mulai']} s.d. {$k['tanggal_selesai']}"
                    : $k['tanggal_mulai'];

                $msg = "📍 <strong>Waktu & Tempat Pelaksanaan:</strong> [1]\n\n"
                    ."• 🗓️ <strong>Waktu:</strong> {$tgl} [1]\n"
                    ."• 🏢 <strong>Lokasi:</strong> {$k['lokasi']} [1]\n"
                    ."• 👥 <strong>Peserta Saat Ini:</strong> {$k['peserta_terdaftar']} kader terdaftar [1].";

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[0]],
                    'message' => $msg,
                ];
            }

            // Tanya Cabang / PCM Tertentu
            $pcmList = config('arqam.opsi_form.cabang_pcm', []);
            $targetedPcm = null;
            foreach ($pcmList as $p) {
                if (str_contains($clean, mb_strtolower($p))) {
                    $targetedPcm = $p;
                    break;
                }
            }

            if ($targetedPcm) {
                $kaderInPcm = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
                    ->where(fn ($sub) => $sub->where('pcm', $targetedPcm)->orWhere('kecamatan', $targetedPcm))
                    ->count();
                $enrolledInPcm = EventPeserta::where('event_id', $event->id)
                    ->where('status_aktif', true)
                    ->whereHas('anggota', fn ($q) => $q->where('pcm', $targetedPcm)->orWhere('kecamatan', $targetedPcm))
                    ->count();
                $belumBaInPcm = Anggota::whereHas('user', fn ($q) => $q->where('status', 'aktif'))
                    ->where(fn ($sub) => $sub->where('pcm', $targetedPcm)->orWhere('kecamatan', $targetedPcm))
                    ->doesntHave('eventPeserta')
                    ->count();

                $msg = "📊 <strong>Data Kader PCM {$targetedPcm}:</strong> [4]\n\n"
                    ."• Total kader aktif di portal: <strong>{$kaderInPcm} orang</strong> [4]\n"
                    ."• Belum pernah mengikuti Baitul Arqam: <strong>{$belumBaInPcm} orang</strong> [4]\n"
                    ."• Sudah terdaftar di kegiatan ini: <strong>{$enrolledInPcm} orang</strong> [1]\n\n"
                    ."Apakah Anda ingin saya tampilkan rekomendasi kader dari PCM {$targetedPcm} untuk didaftarkan?";

                return [
                    'success' => true,
                    'source' => 'rag_local',
                    'intent' => 'info',
                    'needs_candidate_card' => false,
                    'candidates' => [],
                    'sources' => [$allSources[3], $allSources[0]],
                    'message' => $msg,
                ];
            }

            // Default Info Overview
            $msg = "📈 <strong>Ringkasan Data Perkaderan MPKSDI:</strong> [4]\n\n"
                ."• Total kader aktif terdaftar di portal: <strong>{$k['total_kader_aktif']} orang</strong> [4]\n"
                ."• Kader belum pernah Baitul Arqam: <strong>{$k['total_belum_ba']} orang</strong> (prioritas regenerasi) [4]\n"
                ."• Rasio gender master anggota: <strong>{$k['gender_l']} putra</strong> & <strong>{$k['gender_p']} putri</strong> [4]\n\n"
                .'Tulis perintah spesifik jika ingin merekomendasikan kader, misal: <em>"Rekomendasikan 10 kader dari PCM Karanganyar"</em>.';

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'info',
                'needs_candidate_card' => false,
                'candidates' => [],
                'sources' => [$allSources[3], $allSources[0]],
                'message' => $msg,
            ];
        }

        // ── INTENT: SEARCH NAMA TUNGGAL & RIWAYAT PERKADERAN ──
        if ($intent === 'search') {
            $candidates = $this->searchCadres($prompt, 5);

            if ($candidates->isNotEmpty()) {
                $enrolledIds = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->pluck('anggota_id')->all();
                $readyToEnroll = $candidates->filter(fn ($c) => ! in_array($c->id, $enrolledIds, true))->values();
                $alreadyEnrolled = $candidates->filter(fn ($c) => in_array($c->id, $enrolledIds, true))->values();

                $isAskingHistory = (bool) preg_match('/\b(riwayat|perkaderan|baitul\s+arqam|ba|track\s+record|rekam\s+jejak|keikutsertaan|pernah|sudah|lulus)\b/iu', $clean);

                // Jika pengguna menanyakan riwayat perkaderan atau pencarian spesifik 1 kader
                if ($isAskingHistory || $candidates->count() === 1) {
                    $cadre = $candidates->first();
                    $history = $this->getCadreHistory($cadre);
                    $pcmStr = $cadre->pcm ?: ($cadre->kecamatan ?: '-');
                    $nbmStr = $cadre->nbm ? "NBM: <strong>{$cadre->nbm}</strong>" : 'Kader Aktif Terverifikasi';
                    $aumStr = $cadre->nama_aum ? " &bull; AUM: <strong>{$cadre->nama_aum}</strong>" : ($cadre->pekerjaan ? " &bull; <strong>{$cadre->pekerjaan}</strong>" : '');

                    $isEnrolledInEvent = in_array($cadre->id, $enrolledIds, true);

                    $msg = "🔍 <strong>Data Kader Ditemukan:</strong> [4]\n\n"
                        ."• Nama Lengkap: <strong>{$cadre->nama_lengkap}</strong>\n"
                        ."• Asal Cabang: <strong>PCM {$pcmStr}</strong>\n"
                        ."• Identitas: {$nbmStr}{$aumStr} [4]\n\n"
                        ."📜 <strong>Riwayat Perkaderan Baitul Arqam (BA):</strong> [4]\n";

                    if (! empty($history)) {
                        foreach ($history as $idx => $h) {
                            $num = $idx + 1;
                            $tgl = $h['tanggal'] !== '-' ? " ({$h['tanggal']})" : '';
                            $lok = $h['lokasi'] !== '-' ? " di {$h['lokasi']}" : '';
                            $statusBadge = ($h['status'] === 'Lulus' || $h['status'] === 'lulus')
                                ? '✅ <strong>Lulus</strong>'
                                : "<strong>{$h['status']}</strong>";
                            $msg .= "{$num}. <strong>{$h['event']}</strong>{$tgl}{$lok} &mdash; Status: {$statusBadge} [4]\n";
                        }
                    } else {
                        $msg .= "• <em>Kader ini belum memiliki riwayat partisipasi Baitul Arqam formal yang tercatat di pangkalan data SIMKader (Kandidat Prioritas Regenerasi Perkaderan).</em> [4]\n";
                    }

                    $msg .= "\n📌 <strong>Status pada Kegiatan Ini ({$k['nama_event']}):</strong>\n";
                    if ($isEnrolledInEvent) {
                        $msg .= 'Kader saat ini <strong>sudah terdaftar</strong> sebagai peserta aktif pada kegiatan ini. [1]';
                    } else {
                        $msg .= 'Kader saat ini <strong>belum terdaftar</strong> di kegiatan ini. Anda dapat mencentang nama di bawah untuk langsung mendaftarkannya ke kegiatan ini:';
                    }

                    return [
                        'success' => true,
                        'source' => 'rag_local',
                        'intent' => 'search',
                        'needs_candidate_card' => ! $isEnrolledInEvent,
                        'candidates' => ! $isEnrolledInEvent ? collect([$cadre]) : [],
                        'sources' => [$allSources[3], $allSources[0]],
                        'message' => $msg,
                    ];
                }

                // Jika pencarian umum banyak nama
                if ($readyToEnroll->isNotEmpty()) {
                    $names = $readyToEnroll->pluck('nama_lengkap')->implode(', ');
                    $msg = "Saya menemukan kader aktif <strong>{$names}</strong> yang cocok dengan pencarian Anda dan belum terdaftar di kegiatan ini. Silakan centang nama di bawah untuk langsung mendaftarkan:";

                    return [
                        'success' => true,
                        'source' => 'rag_local',
                        'intent' => 'search',
                        'needs_candidate_card' => true,
                        'candidates' => $readyToEnroll,
                        'sources' => [$allSources[0], $allSources[3]],
                        'message' => $msg,
                    ];
                }

                if ($alreadyEnrolled->isNotEmpty()) {
                    $names = $alreadyEnrolled->pluck('nama_lengkap')->implode(', ');
                    $msg = "Kader <strong>{$names}</strong> saat ini <strong>sudah terdaftar</strong> sebagai peserta aktif di kegiatan {$k['nama_event']}. [1]";

                    return [
                        'success' => true,
                        'source' => 'rag_local',
                        'intent' => 'search',
                        'needs_candidate_card' => false,
                        'candidates' => [],
                        'sources' => [$allSources[0], $allSources[3]],
                        'message' => $msg,
                    ];
                }
            }

            // Tidak ditemukan di DB
            $extractedQuery = $this->extractCadreSearchQuery($prompt) ?: $prompt;
            $msg = "Nama <strong>\"{$extractedQuery}\"</strong> belum ditemukan di pangkalan data kader aktif MPKSDI Karanganyar. [4]\n\n"
                .'Pastikan nama yang dimasukkan sesuai, atau salin tautan registrasi mandiri agar kader bersangkutan membuat akun terlebih dahulu.';

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'search',
                'needs_candidate_card' => false,
                'candidates' => [],
                'sources' => [$allSources[3]],
                'message' => $msg,
            ];
        }

        // ── INTENT: RECOMMENDATION (DEFAULT) ──
        $criteria = $this->extractLocalCriteria($prompt);
        $candidates = $this->recommendCandidates($event, $criteria);

        $isActionCommand = (bool) preg_match('/^(daftarkan|enroll|masukkan|jadikan peserta)\b/iu', $clean);
        if ($isActionCommand && $candidates->isNotEmpty()) {
            $msg = "Siap! Sesuai instruksi Anda, saya telah menyiapkan <strong>{$candidates->count()} kandidat kader</strong> terpilih dari pangkalan data [4].\n\n"
                ."Silakan klik tombol <strong>'Enroll Kader Terpilih'</strong> di bawah untuk langsung memproses pendaftaran resmi ke kegiatan {$k['nama_event']}:";

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'recommendation',
                'criteria' => $criteria,
                'needs_candidate_card' => true,
                'candidates' => $candidates,
                'sources' => [$allSources[3], $allSources[0]],
                'message' => $msg,
            ];
        }

        // Kasus 1: Permintaan kader paling sering ikut BA / berpengalaman / senior
        if ($criteria['sort'] === 'paling_sering_ba') {
            $msg = "Berdasarkan riwayat kegiatan perkaderan di SIMKader MPKSDI Karanganyar [4], saya menemukan <strong>{$candidates->count()} rekomendasi kader yang paling sering dan berpengalaman mengikuti Baitul Arqam</strong> (telah mengikuti hingga 3x perkaderan) [4].\n\n"
                .'Kader-kader senior ini sangat potensial untuk dilibatkan kembali atau membantu mentoring kegiatan perkaderan. Silakan centang nama di bawah untuk mendaftarkan:';

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'recommendation',
                'criteria' => $criteria,
                'needs_candidate_card' => $candidates->isNotEmpty(),
                'candidates' => $candidates,
                'sources' => [$allSources[3], $allSources[0]],
                'message' => $msg,
            ];
        }

        // Kasus 2: Permintaan kebiasaan sholat di masjid
        if (! empty($criteria['sholat_masjid'])) {
            $msg = "Masya Allah! 🕌 Berdasarkan formulir profil keagamaan SIMKader MPKSDI Karanganyar [4], saya menemukan <strong>{$candidates->count()} rekomendasi kader</strong> dengan catatan <strong>aktif sholat berjamaah di masjid</strong> [4].\n\n"
                ."Dalam Baitul Arqam ini, <strong>Praktik Ibadah Shalat sesuai Himpunan Putusan Tarjih (HPT)</strong> merupakan salah satu pilar penilaian utama ({$k['bobot_psikomotor_pct']}%) [3]. Silakan periksa dan centang nama di bawah ini:";

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'recommendation',
                'criteria' => $criteria,
                'needs_candidate_card' => $candidates->isNotEmpty(),
                'candidates' => $candidates,
                'sources' => [$allSources[3], $allSources[2]],
                'message' => $msg,
            ];
        }

        // Kasus 3: Permintaan hafalan Al-Qur'an
        if (! empty($criteria['hafalan_quran'])) {
            $msg = "Alhamdulillah! 📖 Berdasarkan pangkalan data SIMKader MPKSDI Karanganyar [4], saya menemukan <strong>{$candidates->count()} rekomendasi kader</strong> yang memiliki catatan <strong>hafalan Al-Qur'an</strong> [4]. Silakan periksa dan centang nama di bawah ini:";

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'recommendation',
                'criteria' => $criteria,
                'needs_candidate_card' => $candidates->isNotEmpty(),
                'candidates' => $candidates,
                'sources' => [$allSources[3], $allSources[0]],
                'message' => $msg,
            ];
        }

        $summaryDesc = [];
        if ($criteria['gender'] === 'seimbang') {
            $summaryDesc[] = 'komposisi seimbang 50:50 putra-putri';
        } elseif ($criteria['gender'] === 'P') {
            $summaryDesc[] = 'kader putri';
        } elseif ($criteria['gender'] === 'L') {
            $summaryDesc[] = 'kader putra';
        }
        if ($criteria['pcm']) {
            $summaryDesc[] = 'wilayah PCM '.$criteria['pcm'];
        }
        if ($criteria['aum']) {
            $summaryDesc[] = 'unit '.$criteria['aum'];
        }

        $criteriaText = ! empty($summaryDesc) ? ' berbasis '.implode(', ', $summaryDesc) : '';

        if ($candidates->isNotEmpty()) {
            $msg = "Berdasarkan pangkalan data SIMKader MPKSDI Karanganyar [4], saya menemukan <strong>{$candidates->count()} rekomendasi kader</strong>{$criteriaText} yang siap didaftarkan.\n\n"
                .'Kandidat ini berstatus aktif dan diprioritaskan yang belum pernah mengikuti perkaderan Baitul Arqam agar regenerasi merata [4]. Silakan periksa dan centang nama di bawah ini:';

            return [
                'success' => true,
                'source' => 'rag_local',
                'intent' => 'recommendation',
                'criteria' => $criteria,
                'needs_candidate_card' => true,
                'candidates' => $candidates,
                'sources' => [$allSources[3], $allSources[0]],
                'message' => $msg,
            ];
        }

        return [
            'success' => true,
            'source' => 'rag_local',
            'intent' => 'recommendation',
            'criteria' => $criteria,
            'needs_candidate_card' => false,
            'candidates' => [],
            'sources' => [$allSources[3], $allSources[0]],
            'message' => "Maaf, saat ini tidak ditemukan kader aktif{$criteriaText} yang belum terdaftar di kegiatan ini. Coba perluas kriteria pencarian Anda.",
        ];
    }

    /**
     * Mengubah format markdown sederhana (bold, italic, bullets) menjadi HTML terstruktur untuk obrolan chat.
     */
    public function formatMarkdownToHtml(string $text): string
    {
        // 1. Bold: **teks** -> <strong>teks</strong>
        $formatted = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        // 2. Italic: *teks* atau _teks_ -> <em>teks</em>
        $formatted = preg_replace('/(?<!\*)\*([^*\n]+?)\*(?!\*)/', '<em>$1</em>', $formatted);
        // 3. Bullet list: '- ' atau '* ' di awal baris -> '• '
        $formatted = preg_replace('/^\s*[-*]\s+/m', '• ', $formatted);

        return trim($formatted);
    }

    /**
     * Konsultasi AI via Geraikita (OpenAI-compatible) dengan arsitektur RAG andal dan fallback cerdas.
     */
    public function consultAi(string $prompt, Event $event): array
    {
        $knowledge = $this->retrieveEventKnowledge($event);
        $intent = $this->classifyIntent($prompt);

        // Operasi Deterministik & Slash Commands langsung dieksekusi tanpa jeda LLM eksternal
        if (in_array($intent, ['analytics', 'whatsapp_invite', 'export_docs', 'cbt_soal'], true) || str_starts_with(trim($prompt), '/')) {
            return $this->answerWithRag($prompt, $event, $knowledge, $intent);
        }

        $apiKey = config('services.geraikita.api_key');
        $baseUrl = config('services.geraikita.base_url', 'https://ai.geraikita.com/v1');
        $model = config('services.geraikita.model', 'qwen3-32b');

        $circuitOpen = Cache::has('geraikita_points_exhausted');

        // Cek apakah prompt berkaitan dengan pencarian nama kader / riwayat perkaderan
        $matchedCadres = collect();
        $cleanPrompt = mb_strtolower(trim($prompt));
        if ($intent === 'search' || preg_match('/\b(riwayat|perkaderan|baitul\s+arqam|ba|kader|nama)\b/iu', $cleanPrompt)) {
            $matchedCadres = $this->searchCadres($prompt, 3);
        }

        $cadreKnowledge = '';
        if ($matchedCadres->isNotEmpty()) {
            $cadreLines = [];
            foreach ($matchedCadres as $mc) {
                $history = $this->getCadreHistory($mc);
                $histStr = ! empty($history)
                    ? implode('; ', array_map(fn ($h) => "{$h['event']} ({$h['tanggal']}, Lokasi: {$h['lokasi']}, Status: {$h['status']})", $history))
                    : 'Belum memiliki riwayat BA formal (Kandidat Prioritas Regenerasi)';
                $isEnrolled = $mc->eventPeserta->where('event_id', $event->id)->where('status_aktif', true)->isNotEmpty()
                    ? 'Sudah Terdaftar'
                    : 'Belum Terdaftar';
                $cadreLines[] = "- {$mc->nama_lengkap} (PCM: {$mc->pcm}, NBM: ".($mc->nbm ?: '-').") | Riwayat Perkaderan: [{$histStr}] | Status Kegiatan Ini: {$isEnrolled}";
            }
            $cadreKnowledge = "\n=== HASIL PENCARIAN DATA KADER DI SIMKADER MPKSDI (VALID & GROUNDED) ===\n"
                .implode("\n", $cadreLines)."\n"
                ."PENTING: Data kader di atas adalah DATA RIIL dari SIMKader MPKSDI. JANGAN katakan kader tidak ditemukan! Jelaskan profil dan riwayat perkaderannya dengan ramah dan terstruktur.\n"
                ."========================================================================\n";
        }

        if (! empty($apiKey) && ! $circuitOpen) {
            try {
                $ragContext = $this->formatRagContextForPrompt($knowledge).$cadreKnowledge;

                $systemPrompt = <<<PROMPT
Kamu adalah SiKader, asisten AI perkaderan resmi MPKSDI (Majelis Pembinaan Kader dan Sumber Daya Insani) Pimpinan Daerah Muhammadiyah (PDM) Karanganyar.
Karakter & Gaya Bahasa: Sangat ramah, bersahabat, islami, cerdas, solutif, santun, tidak kaku (jangan birokratis), dan selalu berpatokan pada data riil sistem (grounded).

{$ragContext}

ATURAN GAYA BAHASA & FORMAT TAMPILAN (SANGAT PENTING):
1. **Bahasa Welcome & Hangat**:
   - Sapalah pengguna dengan akrab dan santun layaknya rekan sejawat perkaderan, hindari bahasa formal yang kaku atau membosankan.
   - Sisipkan emoji ramah yang relevan (misal: 👋, 😊, 📚, 🏛️, 📋, ✨, 💡, 🎯).
   - Buat format pesan lega dan terstruktur. Gunakan baris baru dan bullet points agar nyaman dibaca di layar chat.
2. **Cetak Tebal (Bold) Setiap Informasi Kunci (MANDATORY)**:
   - SELALU cetak tebal (gunakan markdown **teks**) pada setiap kata/frasa penting:
     * Nama identitas asisten: **SiKader**
     * Nama kegiatan: **{$knowledge['nama_event']}**
     * Lokasi & Tempat: **{$knowledge['lokasi']}**
     * Waktu pelaksanaan & Tanggal
     * Angka kuota, sisa kursi, dan jumlah peserta
     * Nama cabang PCM (misal: **PCM Colomadu**, **PCM Karanganyar**) atau unit AUM (misal: **RS PKU**, **Sekolah**)
     * Nama kurikulum, sesi materi, atau 4 pilar evaluasi (**Kognitif**, **Psikomotor Ibadah sesuai HPT**, **Afektif**, **RTL**)
     * Nama orang/kader
     * Poin kesimpulan atau instruksi penting

KAPABILITAS PANGKALAN DATA SIMKADER MPKSDI KARANGANYAR:
Pangkalan data SIMKader Karanganyar memiliki data profil lengkap para kader yang siap dieksplorasi:
1. **Riwayat Partisipasi Baitul Arqam (BA)**:
   - Sistem mencatat seluruh riwayat kegiatan perkaderan yang pernah diikuti kader (tersimpan di riwayat event & pendaftaran).
   - Frekuensi partisipasi kader berkisar dari yang belum pernah (0x), 1x, 2x, hingga yang paling sering (3x BA).
   - JIKA pengguna meminta kader yang **paling sering ikut BA**, **kader senior**, atau **berpengalaman**:
     * Jawab bahwa sistem mencatat riwayat perkaderan tersebut dan tampilkan kader-kader yang paling sering/aktif mengikuti BA (hingga 3x).
     * SET "intent": "recommendation", "needs_candidate_card": true, dan "criteria": {"sort": "paling_sering_ba", "prioritas_belum_ba": false}!
   - JIKA pengguna meminta kader yang **belum pernah ikut BA** atau **prioritas regenerasi**:
     * SET "intent": "recommendation", "needs_candidate_card": true, dan "criteria": {"sort": "belum_ba", "prioritas_belum_ba": true}!
2. **Profil Keagamaan Mandiri Formulir Registrasi**:
   - Sistem mencatat data formulir keagamaan mandiri dari masing-masing kader:
     * Kebiasaan sholat di masjid (kolom `aktivitas_sholat_masjid`: tercatat "Selalu sholat 5 waktu di masjid" dan "Sering sholat 5 waktu di masjid").
     * Hafalan Al-Qur'an (kolom `hafalan_quran`: tercatat "1 juz", "1-5 surat").
     * Keaktifan takmir & jamaah masjid (kolom `keaktifan_takmir`: "Ya", imam rawatib).
   - JIKA pengguna meminta kader yang **sholat 5 waktu di masjid** atau **rajin ke masjid**:
     * Jawab dengan hangat dan apresiatif bahwa data profil formulir anggota mencatat kebiasaan sholat berjamaah di masjid.
     * Hubungkan dengan pilar Praktik Ibadah sesuai HPT (35%) dalam evaluasi BA.
     * SET "intent": "recommendation", "needs_candidate_card": true, dan "criteria": {"sholat_masjid": true}!
3. **Profil Administratif, Cabang, dan Amal Usaha**:
   - Asal 17 Cabang PCM se-Kabupaten Karanganyar dan Ranting PRM.
   - Unit Amal Usaha Muhammadiyah (AUM): RS PKU Muhammadiyah, Sekolah SD/MIM/SMP/SMA/SMK, BTM, dll.
   - Keaktifan Ortom: Pemuda Muhammadiyah, Nasyiatul Aisyiyah, IPM, IMM, HW, Tapak Suci.
   - Jenis Kelamin: Putra ('L') dan Putri ('P').

ATURAN PERCAKAPAN & SAPAAN:
- Jawab sapaan dengan senada. JANGAN mengucapkan "Wa'alaikumussalam" jika pengguna TIDAK mengucapkan salam islami ("assalamu'alaikum").
- Jika pengguna bertanya siapa kamu, apa fungsimu, atau apa tugasmu (misal: "kamu itu apa", "bisa bantu apa", "siapa kamu"), jelaskan peranmu sebagai asisten AI SiKader MPKSDI PDM Karanganyar dengan ramah dan terstruktur, dan SET "needs_candidate_card": false serta "intent": "chat"!

PANDUAN INTENT & PENALARAN:
1. "chat":
   - Jika pengguna menyapa (misal: "hi", "hai", "halo", "assalamualaikum", "pagi"), berbincang santai, atau bertanya siapa kamu/bisa bantu apa.
   - Balas dengan sapaan balik yang senada, ramah, dan sangat welcome.
   - Tanyakan secara hangat: "Ada yang bisa saya bantu hari ini?" dan sebutkan peranmu membantu perkaderan {$knowledge['nama_event']}.
   - JANGAN keluarkan kartu kandidat kader! Set "needs_candidate_card": false.

2. "info":
   - PENTING: Istilah "BA" singkatan dari "Baitul Arqam" (perkaderan formal utama Muhammadiyah). Jika pengguna bertanya "jelaskan soal ba ini", "tentang apa acara ini", dsb., jelaskan mengenai kegiatan Baitul Arqam yang sedang dibuka ini secara komprehensif (tujuan, materi, waktu, lokasi, kuota, syarat kelulusan).
   - Jika pengguna meminta daftar/list peserta terdaftar (misal: "list pesertanya coba", "siapa saja pesertanya", "daftar peserta"), sebutkan daftar peserta yang sudah resmi terdaftar dan sisa kuota kursi yang masih kosong berdasarkan data [1].
   - Jika pengguna menanyakan materi, kurikulum, sistem evaluasi kelulusan 4 pilar (kognitif, psikomotor ibadah sesuai HPT, afektif akhlak, RTL), sertifikat ber-QR, sisa kuota, fasilitator, atau data cabang (PCM).
   - Berikan penjelasan naratif yang luas, terstruktur dengan emoji dan poin-poin yang mudah dipahami.
   - Set "needs_candidate_card": false.

3. "search":
   - HANYA jika pengguna mencari atau mengecek nama kader perorangan tertentu (misal: "cari nama Ahmad", "apakah Siti Walidah sudah daftar").
   - JANGAN PERNAH menganggap pertanyaan instruksi seperti "list pesertanya coba", "daftar peserta", "jelaskan soal ba ini", dsb. sebagai nama orang!

4. "recommendation":
   - HANYA jika pengguna MEMINTA rekomendasi, pemilihan, atau pencarian daftar kader untuk didaftarkan ke kegiatan ini (misal: "rekomendasikan 10 kader", "carikan kader yang paling sering ikut BA", "carikan kader putri dari RS PKU", "carikan kader yang sholat 5 waktu di masjid").
   - Tentukan kriteria pencarian dan set "needs_candidate_card": true.

PANDUAN SITASI DATA RAG (CITATIONS):
Jika menyebut fakta data riil berikut di dalam narasi balasan Anda, bubuhkan nomor sitasi dalam kurung siku di akhir klausa fakta:
- [1] untuk data kegiatan, kuota, sisa kursi, jadwal waktu, dan lokasi acara
- [2] untuk kurikulum, agenda, dan sesi materi perkaderan
- [3] untuk evaluasi 4 pilar (kognitif, psikomotor, afektif, RTL) dan sertifikat resmi
- [4] untuk data master kader, sebaran cabang PCM, dan unit AUM
Contoh: "Kegiatan ini dilaksanakan di Balai Diklat dengan kapasitas 30 kursi [1] serta kurikulum AIK dan PHIWM [2]."

Kembalikan HANYA format JSON valid tanpa code fence markdown:
{
  "intent": "chat"|"info"|"recommendation"|"search",
  "reply": "Jawaban naratif yang kaya wawasan, jujur, ramah, islami, terstruktur, dengan info penting dicetak tebal (**bold**)",
  "needs_candidate_card": true|false,
  "criteria": {
    "limit": integer (default 20),
    "gender": "all"|"seimbang"|"L"|"P",
    "pcm": string nama cabang Karanganyar atau null,
    "aum": string atau null,
    "sort": "paling_sering_ba"|"belum_ba"|"terbaru",
    "prioritas_belum_ba": boolean,
    "sholat_masjid": boolean,
    "takmir": boolean,
    "hafalan_quran": boolean
  }
}
PROMPT;

                $response = Http::withToken($apiKey)
                    ->timeout(16)
                    ->acceptJson()
                    ->post(rtrim($baseUrl, '/').'/chat/completions', [
                        'model' => $model,
                        'messages' => [
                            ['role' => 'system', 'content' => $systemPrompt],
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 1024,
                    ]);

                if ($response->successful()) {
                    $body = $response->json();
                    $content = $body['choices'][0]['message']['content'] ?? '{}';

                    // Bersihkan tag penalaran <think>...</think> jika ada
                    $cleanContent = preg_replace('/<think>.*?<\/think>/is', '', $content);
                    $cleanJson = trim(preg_replace('/```json|```/', '', $cleanContent));
                    $data = json_decode($cleanJson, true);

                    if (is_array($data)) {
                        $aiIntent = $data['intent'] ?? $intent;
                        $rawReply = $data['reply'] ?? ($data['conversational_reply'] ?? null);

                        // Jika LLM tidak menghasilkan reply spesifik, gunakan narasi RAG lokal
                        if (empty($rawReply)) {
                            $ragFallback = $this->answerWithRag($prompt, $event, $knowledge, $aiIntent);
                            $reply = $ragFallback['message'];
                        } else {
                            $reply = $this->formatMarkdownToHtml($rawReply);
                        }

                        // Jika pencarian kader cocok namun LLM berhalusinasi "tidak ditemukan", alihkan ke RAG lokal akurat
                        if ($aiIntent === 'search' && $matchedCadres->isNotEmpty()) {
                            if (preg_match('/(tidak ditemukan|belum ditemukan|tidak tercatat|belum terdaftar dalam pangkalan|tidak ada dalam pangkalan)/iu', (string) $rawReply)) {
                                return $this->answerWithRag($prompt, $event, $knowledge, 'search');
                            }
                        }

                        $needsCard = isset($data['needs_candidate_card'])
                            ? (bool) $data['needs_candidate_card']
                            : ($aiIntent === 'recommendation');

                        if ($aiIntent === 'recommendation' || preg_match('/^(daftarkan|enroll|masukkan|rekomendasi|carikan|pilihkan)\b/iu', $cleanPrompt)) {
                            $needsCard = true;
                        }

                        $candidates = collect();
                        if ($aiIntent === 'search' && $matchedCadres->isNotEmpty()) {
                            $enrolledIds = EventPeserta::where('event_id', $event->id)->where('status_aktif', true)->pluck('anggota_id')->all();
                            $candidates = $matchedCadres->filter(fn ($c) => ! in_array($c->id, $enrolledIds, true))->values();
                            $needsCard = $candidates->isNotEmpty();
                        } elseif ($needsCard) {
                            $criteria = $data['criteria'] ?? $data;
                            $candidates = $this->recommendCandidates($event, $criteria);
                            if ($candidates->isEmpty()) {
                                $localCriteria = $this->extractLocalCriteria($prompt);
                                $candidates = $this->recommendCandidates($event, $localCriteria);
                            }
                        }

                        $actionButtons = [];
                        if ($aiIntent === 'analytics') {
                            $actionButtons = [
                                ['label' => 'Lihat Rekap Nilai & Ranking', 'url' => route('admin.events.ranking', $event), 'icon' => 'ranking', 'style' => 'primary'],
                                ['label' => 'Unduh Rekap Excel', 'url' => route('admin.events.exportExcel', $event), 'icon' => 'excel', 'style' => 'excel'],
                                ['label' => 'Berita Acara Yudisium', 'url' => route('admin.events.berita-acara', $event), 'icon' => 'document', 'style' => 'primary'],
                            ];
                        } elseif ($aiIntent === 'whatsapp_invite') {
                            $waData = $this->generateWhatsAppBroadcast($event, $knowledge, $this->buildRagSources($knowledge));
                            $actionButtons = $waData['action_buttons'] ?? [];
                        } elseif ($aiIntent === 'export_docs') {
                            $expData = $this->generateExportLinks($event, $knowledge, $this->buildRagSources($knowledge));
                            $actionButtons = $expData['action_buttons'] ?? [];
                        }

                        return [
                            'success' => true,
                            'source' => 'geraikita_ai',
                            'intent' => $aiIntent,
                            'needs_candidate_card' => $needsCard && $candidates->isNotEmpty(),
                            'candidates' => $candidates,
                            'sources' => ($aiIntent !== 'chat' ? $this->buildRagSources($knowledge) : []),
                            'action_buttons' => $actionButtons,
                            'message' => $reply,
                        ];
                    }
                }

                if ($response->status() === 402 && ! app()->environment('testing')) {
                    Cache::put('geraikita_points_exhausted', true, now()->addMinutes(2));
                    Log::warning('Geraikita GeraiPoints habis (402). Mengalihkan ke RAG lokal cepat (circuit breaker 2 menit).');
                }
            } catch (\Throwable $e) {
                Log::warning('Geraikita API call skipped/failed: '.$e->getMessage().'. Menggunakan RAG lokal.');
            }
        }

        // RAG Local Reasoning Fallback
        return $this->answerWithRag($prompt, $event, $knowledge, $intent);
    }
}
