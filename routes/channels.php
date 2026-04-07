<?php

use Illuminate\Support\Facades\Broadcast;

// private channel untuk semua user yang login (semua role)
Broadcast::channel('permohonan', function ($user) {
    // return $user !== null; // asalkan authenticated
    return new PrivateChannel('staff-permohonan');
});
