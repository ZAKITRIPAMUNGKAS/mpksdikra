<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SikaderFeedback extends Model
{
    protected $table = 'sikader_feedback';

    protected $fillable = [
        'event_id',
        'user_id',
        'query',
        'sources',
        'rating',
        'reason',
    ];

    protected function casts(): array
    {
        return ['sources' => 'array'];
    }
}
