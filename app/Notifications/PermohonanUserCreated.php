<?php

namespace App\Notifications;

use App\Models\Permohonan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PermohonanUserCreated extends Notification
{
    use Queueable;

    public $permohonan;

    public function __construct(Permohonan $permohonan)
    {
        $this->permohonan = $permohonan;
    }

    public function via($notifiable)
    {
        // kirim ke database + broadcast (pusher)
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        $pengaju = $this->permohonan->nama_pemohon
            ?? optional($this->permohonan->user)->name
            ?? 'Pemohon';

        return [
            'user_id' => $this->permohonan->user_id,
            'permohonan_id' => $this->permohonan->id,
            'title' => $this->permohonan->title,
            'status' => $this->permohonan->status,
            'message' => 'Permohonan dari : '.$pengaju,
        ];
        
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'id'        => $this->permohonan->id,
            'title'     => $this->permohonan->title,
            'kategori'  => $this->permohonan->kategori,
            'priority'  => $this->permohonan->priority,
            'status'    => $this->permohonan->status,
            'user_id'   => $this->permohonan->user_id,
            'created_at'=> $this->permohonan->created_at,
        ]);
    }
}
