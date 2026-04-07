<?php

namespace App\Events;

use App\Models\Permohonan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PermohonanDisposisiUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $permohonan;

    public function __construct(Permohonan $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    public function broadcastOn()
    {
        // Private channel agar hanya user terkait yang bisa menerima
        return new PrivateChannel('permohonan.' . $this->permohonan->user_id);
    }

    public function broadcastAs()
    {
        return 'disposisi.updated';
    }
}
