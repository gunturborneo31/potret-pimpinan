<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'emote' => 'required|in:senyum-lebar,senyum-tipis,merengut',
            'slug' => 'required|string',
        ]);

        Feedback::create([
            'id' => Str::uuid(),
            'emote' => $request->emote,
            'slug' => $request->slug,
        ]);

        return response()->json(['message' => 'Feedback berhasil']);
    }
}
