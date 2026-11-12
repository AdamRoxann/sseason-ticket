<?php
namespace App\Events;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
class QueueStatusUpdated implements ShouldBroadcast {
    use Dispatchable, SerializesModels;
    public function __construct(public int $userId, public array $payload) {}
    public function broadcastOn(): array { return [new PrivateChannel('users.'.$this->userId)]; }
    public function broadcastAs(): string { return 'queue.status.updated'; }
    public function broadcastWith(): array { return $this->payload; }
}
