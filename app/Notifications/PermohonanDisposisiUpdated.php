<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PermohonanDisposisiUpdated extends Notification implements ShouldQueue
{
    use Queueable;

   public $permohonan;

    public function __construct($permohonan)
    {
        $this->permohonan = $permohonan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'permohonan_id' => $this->permohonan->id,
            'judul' => $this->permohonan->judul,
            'status' => $this->permohonan->status,
            'pesan' => 'Status permohonan Anda diperbarui menjadi: ' . $this->permohonan->status,
        ];
    }
}
