<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Event;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    /**
     * Kirim pesan WhatsApp secara otomatis via Gateway (Fonnte API)
     *
     * @return array{success: bool, message: string, detail: mixed}
     */
    public static function kirimOtomatis(string $noWa, string $pesan): array
    {
        $token = config('services.whatsapp.token');
        $endpoint = config('services.whatsapp.endpoint', 'https://api.fonnte.com/send');

        if (empty($token)) {
            return [
                'success' => false,
                'message' => 'Token WA Gateway belum diatur.',
                'detail' => null,
            ];
        }

        $target = self::formatNomor($noWa);
        if (empty($target)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid.',
                'detail' => null,
            ];
        }

        try {
            $response = Http::timeout(6)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->asForm()
                ->post($endpoint, [
                    'target' => $target,
                    'message' => $pesan,
                    'countryCode' => '62',
                ]);

            $json = $response->json();
            $isOk = $response->successful() && (($json['status'] ?? false) === true || ($json['status'] ?? '') === 'true');

            if ($isOk) {
                Log::info("Notifikasi WhatsApp otomatis terkirim ke {$target}");

                return [
                    'success' => true,
                    'message' => 'Pesan WhatsApp otomatis berhasil dikirim ke nomor kader.',
                    'detail' => $json,
                ];
            }

            $reason = $json['reason'] ?? $json['message'] ?? 'Respons gateway menolak pengiriman.';
            Log::warning("Gagal mengirim WhatsApp otomatis ke {$target}: {$reason}", ['response' => $json]);

            return [
                'success' => false,
                'message' => "Gateway WhatsApp: {$reason}",
                'detail' => $json,
            ];
        } catch (\Throwable $e) {
            Log::error("Pengecualian saat kirim WhatsApp otomatis ke {$target}: {$e->getMessage()}");

            return [
                'success' => false,
                'message' => 'Koneksi ke gateway WA gagal: '.$e->getMessage(),
                'detail' => null,
            ];
        }
    }

    /**
     * Bersihkan nomor WA ke format internasional (628xxx)
     */
    public static function formatNomor(string $noWa): string
    {
        return NomorWaNormalizer::normalize($noWa);
    }

    /**
     * Buat URL WhatsApp click-to-chat
     */
    public static function makeUrl(string $noWa, string $pesan): string
    {
        $nomor = self::formatNomor($noWa);
        $encodedPesan = urlencode($pesan);

        return "https://api.whatsapp.com/send?phone={$nomor}&text={$encodedPesan}";
    }

    /**
     * Pesan notifikasi verifikasi akun anggota disetujui
     */
    public static function pesanVerifikasiSukses(Anggota $anggota): string
    {
        $nama = $anggota->nama_lengkap;
        $noWa = $anggota->no_wa ?: '-';
        $noWaDisplay = str_starts_with($noWa, '62') ? ('0'.substr($noWa, 2)) : $noWa;
        $portalUrl = route('login');
        $sandi = $anggota->tanggal_lahir
            ? Carbon::parse($anggota->tanggal_lahir)->format('dmY')
            : 'Tanggal Lahir Anda (DDMMYYYY)';

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Alhamdulillah, berkas pendaftaran kader Anda di *MPKSDI PDM Karanganyar* telah *DIVERIFIKASI & DISETUJUI*.\n\n"
            ."Anda kini dapat masuk ke Portal Kader melalui tautan berikut:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Detail Masuk Portal:\n"
            ."👤 Username: *{$noWaDisplay}* (atau *{$noWa}*)\n"
            ."🔑 Kata Sandi: *{$sandi}* (Format: Tanggal & Tahun Lahir DDMMYYYY)\n\n"
            ."Nashrun Minallah wa Fathun Qarib.\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Sekretariat MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan notifikasi pendaftaran ditolak dengan catatan perbaikan
     */
    public static function pesanPerbaikanDitolak(Anggota $anggota): string
    {
        $nama = $anggota->nama_lengkap;
        $catatan = $anggota->catatan_verifikasi ?: 'Mohon periksa kembali kelengkapan biodata Anda.';
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Pemberitahuan pendaftaran kader di *MPKSDI PDM Karanganyar*:\n"
            ."Berkas Anda memerlukan perbaikan dengan catatan verifikator:\n\n"
            ."📝 *\"{$catatan}\"*\n\n"
            ."Silakan masuk ke portal untuk memperbarui data pendaftaran Anda:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Tim Verifikator MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan pengingat event Baitul Arqam
     */
    public static function pesanPengingatEvent(Anggota $anggota, Event $event): string
    {
        $nama = $anggota->nama_lengkap;
        $namaEvent = $event->nama_event ?: ($event->nama ?: 'Baitul Arqam');
        $tanggal = $event->tanggal_mulai?->translatedFormat('d F Y') ?? '-';
        $lokasi = $event->lokasi ?: 'Gedung Dakwah Muhammadiyah Karanganyar';
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Mengingatkan keikutsertaan Anda pada kegiatan perkaderan:\n"
            ."📌 *{$namaEvent}*\n"
            ."🗓 Tanggal: *{$tanggal}*\n"
            ."📍 Lokasi: *{$lokasi}*\n\n"
            ."Silakan masuk ke Portal Kader untuk mengunduh *ID Card Digital* dan materi kegiatan:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Pastikan hadir tepat waktu dan membawa perlengkapan ibadah pribadi.\n\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Panitia Baitul Arqam MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan reset password default oleh admin
     */
    public static function pesanResetPassword(Anggota $anggota, string $passwordBaru): string
    {
        $nama = $anggota->nama_lengkap;
        $noWa = $anggota->no_wa ?: ($anggota->user?->no_wa ?: '-');
        $noWaDisplay = str_starts_with($noWa, '62') ? ('0'.substr($noWa, 2)) : $noWa;
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Kata sandi akun Portal Kader MPKSDI PDM Karanganyar Anda telah di-reset oleh Admin.\n\n"
            ."Detail Akun Masuk Portal:\n"
            ."👤 Username: *{$noWaDisplay}* (atau *{$noWa}*)\n"
            ."🔑 Sandi Baru: *{$passwordBaru}* (Tanggal & Tahun Lahir DDMMYYYY)\n\n"
            ."Silakan masuk ke portal melalui tautan berikut:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Setelah berhasil masuk, Anda dapat memperbarui kata sandi di menu profil bila diinginkan.\n\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Admin MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan notifikasi penonaktifan akun kader oleh admin
     */
    public static function pesanPenonaktifan(Anggota $anggota, string $alasan = ''): string
    {
        $nama = $anggota->nama_lengkap;
        $keterangan = $alasan ?: 'Penyesuaian administrasi kepengurusan kader.';

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Pemberitahuan administrasi kader di *MPKSDI PDM Karanganyar*:\n"
            ."Status akun keanggotaan Anda saat ini telah diubah menjadi *NONAKTIF*.\n\n"
            ."📝 Keterangan / Alasan:\n"
            ."*\"{$keterangan}\"*\n\n"
            ."Apabila terdapat kekeliruan atau membutuhkan klarifikasi lebih lanjut, silakan menghubungi Sekretariat MPKSDI PDM Karanganyar.\n\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Sekretariat MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan notifikasi pengaktifan kembali (reaktivasi) akun kader
     */
    public static function pesanReaktivasi(Anggota $anggota): string
    {
        $nama = $anggota->nama_lengkap;
        $noWa = $anggota->no_wa ?: '-';
        $noWaDisplay = str_starts_with($noWa, '62') ? ('0'.substr($noWa, 2)) : $noWa;
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Alhamdulillah, akun Portal Kader Anda di *MPKSDI PDM Karanganyar* telah *DIAKTIFKAN KEMBALI*.\n\n"
            ."Anda kini dapat kembali masuk dan mengakses portal perkaderan:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Detail Masuk Portal:\n"
            ."👤 Username: *{$noWaDisplay}* (atau *{$noWa}*)\n"
            ."🔑 Kata Sandi: Tanggal Lahir Anda (Format DDMMYYYY)\n\n"
            ."Nashrun Minallah wa Fathun Qarib.\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Sekretariat MPKSDI PDM Karanganyar*';
    }

    /**
     * Pesan bantuan lupa sandi untuk kader ke Helpdesk MPKSDI
     */
    public static function urlBantuanLupaSandi(string $helpdeskNoWa = '6281226000000'): string
    {
        $pesan = "Assalamu'alaikum Admin MPKSDI PDM Karanganyar. Mohon bantuan, saya lupa kata sandi akun Portal Kader saya.\n\n"
            ."Nama Lengkap: \n"
            ."NIK: \n"
            ."NBM: \n\n"
            .'Mohon dapat dibantu reset sandi akun saya. Terima kasih.';

        return self::makeUrl($helpdeskNoWa, $pesan);
    }
}
