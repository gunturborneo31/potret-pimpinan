<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PermohonanCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public $permohonan;

    public function __construct($permohonan)
    {
        $this->permohonan = $permohonan;
    }

    public function via($notifiable)
    {
        return ['database']; // Bisa tambahkan 'mail' atau 'broadcast'
    }

    public function toDatabase($notifiable)
    {
        return [
            'id' => $this->permohonan->id,
            'kategori' => $this->permohonan->kategori,
            'prioritas' => $this->permohonan->prioritas,
            'user_id' => $this->permohonan->user_id, // UUID
            'pesan' => "Permohonan baru diajukan oleh " . $this->permohonan->user->name,
        ];
    }
}
