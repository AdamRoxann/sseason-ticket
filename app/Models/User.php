<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
class User extends Authenticatable {
    use HasFactory, Notifiable;
    protected $fillable = ['name','email','password','phone','is_admin'];
    protected $casts = ['is_admin' => 'boolean'];
    protected $hidden = ['password','remember_token'];
    protected function casts(): array { return ['email_verified_at'=>'datetime','password'=>'hashed']; }
    public function queueEntries(): HasMany { return $this->hasMany(WaitingRoomEntry::class); }
    public function tickets(): HasMany { return $this->hasMany(Ticket::class); }
}
