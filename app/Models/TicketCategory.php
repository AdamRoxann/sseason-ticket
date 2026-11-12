<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TicketCategory extends Model {
    protected $fillable = ['event_id','name','quota','sold','max_per_order','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function getRemainingAttribute(): int { return max(0, $this->quota - $this->sold); }
}
