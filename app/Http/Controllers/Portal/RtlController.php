<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Rtl;
use App\Models\RtlJawaban;
use App\Models\RtlSoal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RtlController extends Controller
{
    public function index(Event $event): View
    {
        $this->authorize('participate', $event);
        $soal = RtlSoal::where('event_id', $event->id)->orderBy('urutan')->get();
        $anggota = auth()->user()->anggota;
        $rtl = Rtl::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();
        $jawaban = $rtl ? $rtl->jawaban->keyBy('soal_id') : collect();

        return view('portal.rtl', compact('event', 'soal', 'rtl', 'jawaban'));
    }

    public function submit(Request $request, Event $event): RedirectResponse
    {
        $this->authorize('participate', $event);
        $anggota = auth()->user()->anggota;
        $rtl = Rtl::where('event_id', $event->id)->where('anggota_id', $anggota->id)->first();

        if ($rtl && $rtl->status === 'terkirim') {
            return back()->with('error', 'RTL sudah terkirim dan tidak dapat diubah.');
        }

        $soal = RtlSoal::where('event_id', $event->id)->orderBy('urutan')->get();
        $rules = [];

        foreach ($soal as $s) {
            $key = 'jawaban.'.$s->id;
            $rules[$key] = $s->tipe === 'essay'
                ? ['required', 'string', 'max:5000']
                : ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'];
        }

        $data = $request->validate($rules);

        DB::transaction(function () use ($event, $anggota, $soal, $data, $rtl) {
            $rtl = $rtl ?? Rtl::create([
                'event_id' => $event->id,
                'anggota_id' => $anggota->id,
                'status' => 'draf',
            ]);

            foreach ($soal as $s) {
                $value = $data['jawaban'][$s->id] ?? null;
                $attributes = ['jawaban' => null, 'file_bukti' => null];

                if ($s->tipe === 'upload' && $value) {
                    $attributes['file_bukti'] = $value->store('rtl/'.$rtl->id, 'local');
                } else {
                    $attributes['jawaban'] = is_string($value) ? $value : null;
                }

                RtlJawaban::updateOrCreate(
                    ['rtl_id' => $rtl->id, 'soal_id' => $s->id],
                    $attributes
                );
            }

            $rtl->update(['status' => 'terkirim']);
        });

        return back()->with('success', 'RTL terkirim.');
    }
}
