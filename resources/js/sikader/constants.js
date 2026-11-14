/**
 * SiKader UI Constants (Bahasa Indonesia)
 * Terpusat untuk seluruh teks antarmuka, sapaan, pesan proses RAG, dan aksi cepat.
 */

export const SIKADER_UI = {
    identity: {
        name: 'SiKader',
        badge: 'Aktif',
        subtitle: 'Asisten Perkaderan MPKSDI',
        ariaChatDialog: 'Percakapan SiKader',
    },

    greeting: {
        intro: (eventTitle) => `Assalamu'alaikum, Admin. Siap membantu kegiatan <strong>${eventTitle}</strong>.\nMau mulai dari mana?`,
    },

    composer: {
        placeholder: 'Tulis instruksi atau tempel daftar nama...',
        keyboardHint: 'Enter kirim • Shift+Enter baris baru',
        sendAria: 'Kirim pesan',
        sendTitle: 'Kirim pesan',
        attachFileAria: 'Unggah berkas Excel atau CSV',
        attachFileTitle: 'Unggah berkas (.xlsx, .xls, .csv, .txt)',
        pasteActionAria: 'Tempel daftar nama kader',
        pasteActionTitle: 'Tempel daftar nama (WhatsApp/Teks)',
        uploadActionAria: 'Unggah Excel atau CSV',
        uploadActionTitle: 'Unggah spreadsheet Excel / CSV',
    },

    quickActions: [
        {
            action: 'paste',
            label: 'Tempel daftar nama',
            tooltip: 'Salin tempel daftar nama kader dari grup WhatsApp atau dokumen',
        },
        {
            action: 'file',
            label: 'Unggah Excel / CSV',
            tooltip: 'Unggah file spreadsheet untuk mencocokkan dan mendaftarkan kader secara massal',
        },
        {
            action: 'recommend_balanced',
            label: 'Rekomendasi seimbang',
            tooltip: 'Rekomendasi kader komposisi seimbang 50% putra dan 50% putri',
        },
        {
            action: 'recommend_ba',
            label: 'Prioritaskan belum aktif',
            tooltip: 'Prioritaskan kader yang belum pernah mengikuti perkaderan Baitul Arqam',
        },
        {
            action: 'recommend_aum',
            label: 'Prioritaskan kader AUM',
            tooltip: 'Rekomendasi kader dari unit Amal Usaha Muhammadiyah (RS PKU, Sekolah, dll)',
        },
    ],

    buildLog: {
        title: 'Proses RAG',
        stepsDefault: [
            'Membaca dokumen dan kurikulum perkaderan',
            'Mencocokkan dengan database kader aktif',
            'Menyusun rekomendasi berbasis data riil',
        ],
        stepsPaste: [
            'Membaca dan membersihkan daftar nama masukan',
            'Mencocokkan nama dengan basis data master anggota',
            'Memvalidasi status pendaftaran kegiatan',
        ],
        stepsFile: [
            'Membaca berkas spreadsheet Excel/CSV',
            'Mengekstrak baris data dan nomor identitas',
            'Memverifikasi kecocokan data peserta',
        ],
        stepsRecommend: [
            'Menyaring pangkalan data kader aktif',
            'Menganalisis riwayat keikutsertaan BA & profil',
            'Menyusun peringkat rekomendasi terbaik',
        ],
        toggleShow: (count) => `Lihat proses (${count} langkah)`,
        toggleHide: (count) => `Sembunyikan proses (${count} langkah)`,
        statusLiveAria: 'Status pemrosesan SiKader',
    },

    previewPanel: {
        title: 'Rujukan Dokumen Sumber',
        closeAria: 'Tutup panel rujukan',
        confidenceGrounded: 'RAG Terverifikasi',
        confidenceLow: 'Perlu Konfirmasi',
        uncertainNotice: 'Saya kurang yakin dengan informasi ini karena berada di luar basis data resmi MPKSDI.',
        activeCitationAria: 'Sitasi terpilih',
        emptyNotice: 'Pilih salah satu nomor sitasi [1], [2] pada jawaban untuk melihat cuplikan dokumen sumber.',
    },

    confirmation: {
        singleEnrollPrompt: (name) => `Daftarkan <strong>${name}</strong> ke kegiatan ini?`,
        bulkEnrollPrompt: (count) => `Anda akan mendaftarkan <strong>${count} kader</strong>. Lanjutkan?`,
        confirmBtn: 'Konfirmasi Daftarkan',
        cancelBtn: 'Batal',
    },

    settings: {
        menuAria: 'Menu pengaturan',
        menuTitle: 'Pengaturan SiKader',
        textSize: 'Ukuran teks',
        themeToggle: 'Tema tampilan',
        resetChat: 'Mulai ulang chat',
        closeModal: 'Tutup SiKader',
    },
};
