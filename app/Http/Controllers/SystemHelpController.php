<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class SystemHelpController extends Controller
{
    /**
     * Tampilkan halaman Panduan & Tutorial Penggunaan Sistem.
     */
    public function panduan(): View
    {
        return view('system.panduan');
    }

    /**
     * Tampilkan halaman Catatan Pembaruan Sistem (Changelog & Release Notes).
     */
    public function updates(): View
    {
        return view('system.updates');
    }
}
