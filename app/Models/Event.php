<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'name', 'description', 'event_starts_at', 'venue',
        'registration_opens_at', 'registration_closes_at',
        'floor_plan_path', 'is_active',
    ];

    protected $casts = [
        'event_starts_at' => 'datetime',
        'registration_opens_at' => 'datetime',
        'registration_closes_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function categories(): HasMany { return $this->hasMany(TicketCategory::class); }
    public function queueEntries(): HasMany { return $this->hasMany(WaitingRoomEntry::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
}
