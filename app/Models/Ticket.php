<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Ticket extends Model {
    protected $fillable = ['event_id','ticket_category_id','user_id','waiting_room_entry_id','public_code','qr_path','status','checked_in_at'];
    protected $casts = ['checked_in_at'=>'datetime'];
    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function category(): BelongsTo { return $this->belongsTo(TicketCategory::class, 'ticket_category_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function queueEntry(): BelongsTo { return $this->belongsTo(WaitingRoomEntry::class, 'waiting_room_entry_id'); }
}
