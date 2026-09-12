<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\EventPeserta;
use App\Models\User;

class EventPolicy
{
    public function participate(User $user, Event $event): bool
    {
        $anggota = $user->anggota;

        return $anggota !== null && EventPeserta::where('event_id', $event->id)
            ->where('anggota_id', $anggota->id)
            ->where('status_aktif', true)
            ->exists();
    }
}
