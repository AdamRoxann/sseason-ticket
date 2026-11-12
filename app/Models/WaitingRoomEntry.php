<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class WaitingRoomEntry extends Model {
    protected $fillable = ['event_id','user_id','queue_token','status','joined_at','admitted_at','expires_at'];
    protected $casts = ['joined_at'=>'datetime','admitted_at'=>'datetime','expires_at'=>'datetime'];
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
}
