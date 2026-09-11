<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FasilitatorController extends Controller
{
    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $request->validate([
            'fasilitator_ids' => ['nullable', 'array'],
            'fasilitator_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = User::whereIn('id', $data['fasilitator_ids'] ?? [])
            ->where('role', 'fasilitator')
            ->where('status', 'aktif')
            ->pluck('id')
            ->all();

        $event->fasilitator()->sync($ids);

        return back()->with('success', 'Penugasan fasilitator diperbarui.');
    }
}
