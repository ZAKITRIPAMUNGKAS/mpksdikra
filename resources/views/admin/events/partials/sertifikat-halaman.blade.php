@php
    // $sisi: 'duplex' (depan + belakang), 'depan' (sertifikat saja), 'belakang' (transkrip saja)
    $sisi = $sisi ?? 'duplex';
    $statusLulus = in_array($nilai->status_kelulusan ?? null, ['Lulus', 'Lulus Bersyarat'], true);
    $statusTeks = $statusLulus ? $nilai->status_kelulusan : 'Peserta';
@endphp
@if($sisi !== 'belakang')
    {{-- ==================== HALAMAN 1: SERTIFIKAT UTAMA (F4 landscape, mengisi lembar) ==================== --}}
    @php
        $namaPenerima = strtoupper($peserta->anggota->nama_lengkap);
        $ukuranNama = mb_strlen($namaPenerima) > 60 ? 20 : (mb_strlen($namaPenerima) > 40 ? 24 : 30);
        $ukuranKegiatan = mb_strlen($event->nama_event) > 70 ? 11 : (mb_strlen($event->nama_event) > 45 ? 12.5 : 14);
    @endphp
    <div class="page-content" style="text-align: center; padding-top: 7mm;">

        {{-- Kop: logo & lembaga --}}
        <table style="margin: 0 auto; border-collapse: collapse;">
            <tr>
                <td style="vertical-align: middle; padding-right: 5mm;">
                    @if($logoMuhammadiyah)
                        <img src="{{ $logoMuhammadiyah }}" style="height: 14mm; width: auto;" alt="Logo Muhammadiyah">
                    @endif
                </td>
                <td style="vertical-align: middle; padding-right: 5mm; color: #c59b27; font-size: 18pt; line-height: 1;">|</td>
                <td style="vertical-align: middle;">
                    @if($logoMpksdi)
                        <img src="{{ $logoMpksdi }}" style="height: 14mm; width: auto;" alt="Logo MPKSDI">
                    @endif
                </td>
            </tr>
        </table>
        <div style="font-size: 13pt; font-weight: bold; color: #0a3d34; letter-spacing: 1px; margin-top: 1.8mm;">
            PIMPINAN DAERAH MUHAMMADIYAH KARANGANYAR
        </div>
        <div style="font-size: 10pt; font-weight: bold; color: #0284c7; letter-spacing: 0.6px; margin-top: 0.8mm;">
            MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)
        </div>

        {{-- Judul & Nomor --}}
        <div class="serif" style="font-size: 38pt; font-weight: bold; color: #064e3b; letter-spacing: 8px; line-height: 1; margin-top: 3.5mm;">
            SERTIFIKAT
        </div>
        <div style="margin: 1.5mm auto 1.2mm; font-size: 8pt; color: #c59b27;">
            &#8212;&#8212;&#8212;&#8212;&#8212;
            @if($goldDiamond)
                <img src="{{ $goldDiamond }}" style="height: 2.6mm; width: 2.6mm; vertical-align: middle;" alt="&bull;">
                <img src="{{ $goldDiamond }}" style="height: 3.2mm; width: 3.2mm; vertical-align: middle;" alt="&bull;">
                <img src="{{ $goldDiamond }}" style="height: 2.6mm; width: 2.6mm; vertical-align: middle;" alt="&bull;">
            @else
                &bull; &bull; &bull;
            @endif
            &#8212;&#8212;&#8212;&#8212;&#8212;
        </div>
        <div style="font-size: 10pt; font-weight: bold; color: #1e293b; letter-spacing: 0.4px;">
            Nomor: {{ $event->generateNomorSertifikat($peserta->id) }}
        </div>

        {{-- Penerima --}}
        <div style="font-size: 12pt; font-style: italic; color: #475569; margin-top: 3.5mm;">
            Diberikan kepada:
        </div>
        <div class="serif" style="font-size: {{ $ukuranNama }}pt; font-weight: bold; color: #064e3b; line-height: 1.15; margin-top: 1mm;">
            {{ $namaPenerima }}
        </div>
        <div style="width: 150mm; margin: 1.5mm auto 0; border-top: 1.2px solid #c59b27; height: 1px;"></div>
        <div style="font-size: 11pt; font-weight: bold; color: #334155; margin-top: 2mm;">
            @if($peserta->anggota->nbm)
                NBM: {{ $peserta->anggota->nbm }} &nbsp;&bull;&nbsp;
            @endif
            Utusan: PCM {{ $peserta->anggota->pcm ?: ($peserta->anggota->kecamatan ?: 'Karanganyar') }}
        </div>

        {{-- Pernyataan & kegiatan --}}
        <div style="font-size: 11pt; color: #334155; margin-top: 3.5mm;">
            {{ ! $statusLulus ? 'Telah mengikuti' : ($nilai->status_kelulusan === 'Lulus Bersyarat' ? 'Telah memenuhi persyaratan kelulusan bersyarat' : 'Telah memenuhi persyaratan kelulusan') }} pada kegiatan:
        </div>
        <div style="margin: 2mm auto 0;">
            <div style="display: inline-block; background: #064e3b; border: 2px solid #d4af37; border-radius: 24px; padding: 6px 34px; color: #ffffff; font-weight: bold; font-size: {{ $ukuranKegiatan }}pt; letter-spacing: 0.8px; text-transform: uppercase; max-width: 250mm;">
                {{ $event->nama_event }}
            </div>
        </div>
        <div style="font-size: 10pt; color: #334155; line-height: 1.4; margin: 2.5mm auto 0;">
            yang diselenggarakan pada tanggal <b>{{ $tanggalEventFormatted }}</b>
            @if($event->lokasi)bertempat di <b>{{ $event->lokasi }}</b>@endif,
            {{ $statusLulus ? 'dan berdasarkan evaluasi dinyatakan:' : 'sebagai:' }}
        </div>

        {{-- Status --}}
        <table style="margin: 2mm auto 0; border-collapse: collapse;">
            <tr>
                <td style="width: 45mm; vertical-align: middle; padding-right: 5mm;">
                    <div style="border-top: 1.2px solid #c59b27; height: 1px;"></div>
                </td>
                <td style="vertical-align: middle; text-align: center; white-space: nowrap;">
                    <span class="serif" style="font-size: 21pt; font-weight: bold; color: #064e3b; letter-spacing: 7px;">
                        {{ strtoupper($statusTeks) }}
                    </span>
                    @if($statusLulus && $nilai->grade)
                        <span style="font-size: 11pt; font-weight: bold; color: #334155; letter-spacing: 1.5px; margin-left: 2.5mm;">
                            (PREDIKAT {{ strtoupper($nilai->grade) }})
                        </span>
                    @endif
                </td>
                <td style="width: 45mm; vertical-align: middle; padding-left: 5mm;">
                    <div style="border-top: 1.2px solid #c59b27; height: 1px;"></div>
                </td>
            </tr>
        </table>

        {{-- Tanda tangan & QR --}}
        <table style="width: 100%; border-collapse: collapse; margin-top: 2mm;">
            <tr>
                {{-- MoT / Penandatangan Kiri --}}
                <td style="width: 38%; text-align: center; vertical-align: top; font-size: 9.5pt; color: #1e293b;">
                    Karanganyar, {{ $tanggalTerbit }}<br>
                    <b>{{ $jabatanKiri }}</b>
                    <div style="height: 15mm;">
                        @if($signatureMot)
                            <img src="{{ $signatureMot }}" style="height: 14.5mm; max-width: 55mm; display: block; margin: 0.3mm auto 0;" alt="Ttd MoT">
                        @endif
                    </div>
                    <b><u>{{ $namaKiri }}</u></b><br>
                    <span style="font-size: 8.5pt; color: #475569;">{{ $keteranganKiri }}</span>
                </td>

                {{-- QR Center --}}
                <td style="width: 24%; text-align: center; vertical-align: middle;">
                    @if(isset($qrPng) && $qrPng)
                        <div style="display: inline-block; padding: 1.2mm; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px;">
                            <img src="{{ $qrPng }}" style="width: 21mm; height: 21mm; display: block; margin: 0 auto;" alt="QR Code">
                        </div>
                    @endif
                    <div style="font-size: 7.5pt; color: #64748b; margin-top: 1.2mm;">
                        Verifikasi Pangkalan Data<br>
                        <span style="display: inline-block; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; padding: 1px 7px; font-family: monospace; font-size: 7.5pt; color: #334155; margin-top: 0.6mm;">
                            {{ substr($nilai->verification_hash ?? '9e493fefe15faa89', 0, 16) }}
                        </span>
                    </div>
                </td>

                {{-- Ketua MPKSDI / Penandatangan Kanan --}}
                <td style="width: 38%; text-align: center; vertical-align: top; font-size: 9.5pt; color: #1e293b;">
                    Karanganyar, {{ $tanggalTerbit }}<br>
                    <b>{{ $jabatanKanan }}</b>
                    <div style="height: 15mm;">
                        @if($signatureKetua)
                            <img src="{{ $signatureKetua }}" style="height: 14.5mm; max-width: 55mm; display: block; margin: 0.3mm auto 0;" alt="Ttd Ketua">
                        @endif
                    </div>
                    <b><u>{{ $namaKanan }}</u></b><br>
                    <span style="font-size: 8.5pt; color: #475569;">{{ $nbmKanan }}</span>
                </td>
            </tr>
        </table>

    </div>

@endif
@if($sisi === 'duplex')
    <div class="page-break"></div>
@endif
@if($sisi !== 'depan')
    {{-- ==================== HALAMAN 2: TRANSKRIP MATERI ==================== --}}
    <div class="page-content">

        {{-- Header Top: Logo Muhammadiyah Centered --}}
        <div style="text-align: center; margin-bottom: 1.5mm;">
            @if($logoMuhammadiyah)
                <img src="{{ $logoMuhammadiyah }}" style="height: 13mm; width: auto; margin-bottom: 1mm;" alt="Logo Muhammadiyah">
            @endif
            <div style="font-size: 10pt; font-weight: bold; color: #0a3d34; letter-spacing: 0.5px;">
                PIMPINAN DAERAH MUHAMMADIYAH KARANGANYAR
            </div>
            <div style="font-size: 8.2pt; font-weight: bold; color: #064e3b; letter-spacing: 0.5px; margin-top: 0.5mm;">
                MAJELIS PEMBINAAN KADER DAN SUMBER DAYA INSANI (MPKSDI)
            </div>
            <div style="font-size: 6.5pt; color: #64748b; margin-top: 0.5mm;">
                Gedung Dakwah Muhammadiyah, Jl. Lawu, Karanganyar, Jawa Tengah 57712
            </div>
            <div style="margin: 1.5mm auto 2mm; font-size: 6.5pt; color: #c59b27;">
                &#8212;&#8212;&#8212;&#8212;
                @if($goldDiamond)
                    <img src="{{ $goldDiamond }}" style="height: 2.2mm; width: 2.2mm; vertical-align: middle;" alt="&bull;">
                @else
                    &bull;
                @endif
                &#8212;&#8212;&#8212;&#8212;
            </div>
        </div>

        {{-- Title & Subtitle --}}
        <div style="text-align: center;">
            <div class="serif" style="font-size: 18pt; font-weight: bold; color: #064e3b; line-height: 1;">
                Transkrip Capaian Kompetensi
            </div>
            <div style="font-size: 7.8pt; font-weight: bold; color: #064e3b; letter-spacing: 3px; margin-top: 1mm;">
                <span style="color: #c59b27; letter-spacing: 0;">&#8212;&#8212;</span>
                &nbsp;PERKADERAN BAITUL ARQAM&nbsp;
                <span style="color: #c59b27; letter-spacing: 0;">&#8212;&#8212;</span>
            </div>
            <div style="margin: 1.5mm auto 2mm;">
                <span style="display: inline-block; background: #fefce8; border: 1px solid #fde047; border-radius: 12px; padding: 2px 14px; font-size: 7pt; color: #334155;">
                    Lampiran Sertifikat Nomor: <b>{{ $event->generateNomorSertifikat($peserta->id) }}</b> &bull; {{ $event->nama_event }}
                </span>
            </div>
        </div>

        {{-- Biodata Peserta --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2mm; font-size: 7.5pt; color: #1e293b;">
            <tr>
                <td style="width: 50%; padding-left: 2mm;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td style="width: 26mm; color: #475569; font-weight: bold;">Nama Lengkap</td>
                            <td style="width: 3mm;">:</td>
                            <td style="font-weight: bold;">{{ $peserta->anggota->nama_lengkap }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569; font-weight: bold;">NBM</td>
                            <td>:</td>
                            <td>{{ $peserta->anggota->nbm ?: '-' }}</td>
                        </tr>
                    </table>
                </td>
                <td style="width: 50%; padding-left: 5mm;">
                    <table style="border-collapse: collapse;">
                        <tr>
                            <td style="width: 26mm; color: #475569; font-weight: bold;">Cabang (PCM)</td>
                            <td style="width: 3mm;">:</td>
                            <td style="font-weight: bold;">{{ $peserta->anggota->pcm ?: ($peserta->anggota->kecamatan ?: '-') }}</td>
                        </tr>
                        <tr>
                            <td style="color: #475569; font-weight: bold;">Nomor Peserta</td>
                            <td>:</td>
                            <td>{{ sprintf('%03d', $peserta->id) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        {{-- Dua Kolom Tabel --}}
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                {{-- Kiri: Materi & Sesi --}}
                <td style="width: 53%; vertical-align: top; padding-right: 2.5mm;">
                    <table class="grid-table">
                        <thead>
                            <tr>
                                <th style="width: 7%; text-align: center;">No</th>
                                <th style="width: 45%; text-align: left;">Mata Pembinaan / Materi Sesi</th>
                                <th style="width: 28%; text-align: left;">Narasumber</th>
                                <th style="width: 20%; text-align: center;">Durasi Aktual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($sesiList as $idx => $s)
                                @php
                                    $menit = ($s->jam_mulai && $s->jam_selesai)
                                        ? max(0, (strtotime($s->jam_selesai) - strtotime($s->jam_mulai)) / 60)
                                        : (($s->waktu_mulai && $s->waktu_selesai) ? max(0, $s->waktu_mulai->diffInMinutes($s->waktu_selesai)) : null);
                                    $durasiText = $menit !== null ? ($menit . ' menit') : '-';
                                    $namaMateri = $s->judul_materi ?: ($s->nama_sesi ?: 'Sesi Materi Perkaderan');
                                    $pemateriText = $s->pemateri ?: ($event->fasilitator->first()?->name ?? 'Tim Instruktur MPKSDI');
                                @endphp
                                <tr>
                                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                                    <td><b>{{ $namaMateri }}</b></td>
                                    <td>{{ $pemateriText }}</td>
                                    <td style="text-align: center;">{{ $durasiText }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td style="text-align: center;">1</td>
                                    <td><b>Kontrak Belajar &amp; Orientasi Baitul Arqam</b></td>
                                    <td>Tim Instruktur MPKSDI</td>
                                    <td style="text-align: center;">120 menit</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;">2</td>
                                    <td><b>Pedoman Hidup Islami Warga Muhammadiyah (PHIWM)</b></td>
                                    <td>Drs. H. Sriyanto, M.Pd.</td>
                                    <td style="text-align: center;">105 menit</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;">3</td>
                                    <td><b>Matan Keyakinan dan Cita-cita Hidup Muhammadiyah (MKCHM)</b></td>
                                    <td>Dr. Muhammad Ihsan, M.Ag.</td>
                                    <td style="text-align: center;">225 menit</td>
                                </tr>
                                <tr>
                                    <td style="text-align: center;">4</td>
                                    <td><b>Rencana Tindak Lanjut (RTL) &amp; Penutupan</b></td>
                                    <td>Ketua PDM Karanganyar</td>
                                    <td style="text-align: center;">75 menit</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr style="background: #ecfdf5; font-weight: bold;">
                                <td colspan="3" style="padding: 4px 6px; border: 1px solid #cbd5e1; text-align: right;">Total Durasi Tercatat:</td>
                                <td style="padding: 4px 4px; border: 1px solid #cbd5e1; text-align: center; color: #064e3b;">{{ $totalMenit }} menit</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>

                {{-- Kanan: Evaluasi 3 Aspek --}}
                <td style="width: 47%; vertical-align: top; padding-left: 2.5mm;">
                    <table class="grid-table">
                        <thead>
                            <tr>
                                <th style="width: 50%; text-align: left;">Aspek Penilaian Evaluasi</th>
                                <th style="width: 25%; text-align: center;">Skor (0-100)</th>
                                <th style="width: 25%; text-align: center;">Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <b>I. Aspek Kognitif</b><br>
                                    <span style="font-size: 6.2pt; color: #64748b;">Posttest &amp; N-Gain Ideologi</span>
                                </td>
                                <td style="text-align: center; font-weight: bold;">{{ number_format((float) ($nilai->nilai_posttest ?? 0), 1) }}</td>
                                <td style="text-align: center; color: #94a3b8;">-</td>
                            </tr>
                            <tr>
                                <td>
                                    <b>II. Aspek Psikomotorik</b><br>
                                    <span style="font-size: 6.2pt; color: #64748b;">Praktik Ibadah &amp; Sholat HPT</span>
                                </td>
                                <td style="text-align: center; font-weight: bold;">{{ number_format((float) ($nilai->nilai_psikomotor ?? 0), 1) }}</td>
                                <td style="text-align: center; color: #94a3b8;">-</td>
                            </tr>
                            <tr>
                                <td>
                                    <b>III. Aspek Afektif</b><br>
                                    <span style="font-size: 6.2pt; color: #64748b;">Integritas &amp; Kedisiplinan</span>
                                </td>
                                <td style="text-align: center; font-weight: bold;">{{ number_format((float) ($nilai->nilai_afektif ?? 0), 1) }}</td>
                                <td style="text-align: center; color: #94a3b8;">-</td>
                            </tr>
                            <tr style="background: #e0f2fe; font-weight: bold;">
                                <td><b>NILAI AKHIR KOMPREHENSIF</b></td>
                                <td style="text-align: center; font-size: 8.5pt; color: #064e3b;">{{ number_format((float) ($nilai->nilai_akhir ?? 0), 1) }}</td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; background: #064e3b; color: #ffffff; border-radius: 8px; padding: 1.5px 8px; font-size: 6.8pt; font-weight: bold;">
                                        {{ $statusTeks }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Keterangan Box --}}
                    <div style="margin-top: 2mm; font-size: 6.5pt; color: #475569; border: 1px solid #e2d5bc; padding: 2mm; border-radius: 5px; background: #fdfbf7; line-height: 1.35;">
                        <span style="font-weight: bold; color: #854d0e;">Keterangan:</span><br>
                        Nilai dan status kelulusan merupakan hasil rekapitulasi evaluasi yang ditetapkan panitia kegiatan.
                    </div>

                    {{-- Pengesahan Transkrip --}}
                    <div style="margin-top: 2.5mm; text-align: right; font-size: 7.5pt; padding-right: 2mm; color: #1e293b;">
                        Karanganyar, {{ $tanggalTerbit }}<br>
                        <b>{{ $jabatanKanan }}</b>
                        <div style="height: 13mm; position: relative;">
                            @if($signatureKetua)
                                <img src="{{ $signatureKetua }}" style="height: 12mm; max-width: 40mm; position: absolute; right: 0; top: 0;" alt="Ttd Ketua">
                            @endif
                        </div>
                        <b><u>{{ $namaKanan }}</u></b><br>
                        <span style="font-size: 6.8pt; color: #475569;">{{ $nbmKanan }}</span>
                    </div>
                </td>
            </tr>
        </table>

    </div>
@endif
