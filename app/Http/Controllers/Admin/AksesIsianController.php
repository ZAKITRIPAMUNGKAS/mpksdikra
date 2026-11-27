<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Buka/tutup pengisian online Afektif dan Angket Evaluasi, seperti sesi Pretest/Posttest.
 */
class AksesIsianController extends Controller
{
    private const LABEL = [
        'afektif' => 'Kuesioner Afektif',
        'angket' => 'Angket Evaluasi',
    ];

    public function update(Request $request, Event $event, string $jenis): RedirectResponse
    {
        abort_unless(in_array($jenis, Event::ISIAN_BUKA_TUTUP, true), 404);

        $data = $request->validate(['status' => ['required', 'in:aktif,tutup']]);

        $event->forceFill([$jenis.'_status' => $data['status']])->save();

        $label = self::LABEL[$jenis];

        $tab = $jenis === 'afektif' ? ['tab' => 'cbt', 'sub' => 'afektif'] : ['tab' => 'feedback'];

        return redirect()->route('admin.events.show', [$event, ...$tab])->with('success', $data['status'] === 'aktif'
            ? $label.' dibuka. Peserta sekarang bisa mengisi dari portal.'
            : $label.' ditutup. Peserta tidak bisa mengisi sampai dibuka kembali.');
    }
}
