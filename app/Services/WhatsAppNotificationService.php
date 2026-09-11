<?php

namespace App\Services;

use App\Models\Anggota;
use App\Models\Event;

class WhatsAppNotificationService
{
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
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Alhamdulillah, berkas pendaftaran kader Anda di *MPKSDI PDM Karanganyar* telah *DIVERIFIKASI & DISETUJUI*.\n\n"
            ."Anda kini dapat masuk ke Portal Kader melalui tautan berikut:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Gunakan alamat email dan kata sandi yang telah Anda daftarkan.\n\n"
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
        $namaEvent = $event->nama;
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
        $email = $anggota->user?->email ?: '-';
        $portalUrl = route('login');

        return "Assalamu'alaikum Wr. Wb.\n\n"
            ."Yth. Bapak/Ibu *{$nama}*,\n\n"
            ."Kata sandi akun Portal Kader MPKSDI PDM Karanganyar Anda telah di-reset oleh Admin.\n\n"
            ."Detail Akun Baru Anda:\n"
            ."📧 Email: *{$email}*\n"
            ."🔑 Sandi Baru: *{$passwordBaru}*\n\n"
            ."Silakan masuk ke portal melalui tautan berikut:\n"
            ."🔗 {$portalUrl}\n\n"
            ."Setelah berhasil masuk, Anda dapat memperbarui kata sandi di menu profil.\n\n"
            ."Wassalamu'alaikum Wr. Wb.\n\n"
            .'— *Admin MPKSDI PDM Karanganyar*';
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
