<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Tandai semua notifikasi user yang sedang login sebagai sudah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        // Tandai semua notifikasi yang belum dibaca sebagai sudah dibaca
        $user->unreadNotifications->markAsRead();

        return redirect()->route('permohonan.index')->with('success', 'Notifikasi sudah dibaca');

    }

    /**
     * Ambil semua notifikasi (opsional, sesuai script Vue)
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->latest()->get();

        $unreadCount = $user->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }
}
