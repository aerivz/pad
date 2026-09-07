<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function read(Request $request, UserNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->usuario_id === (int) $request->user()->id, 403);

        if ($notification->leida_en === null) {
            $notification->update(['leida_en' => now()]);
        }

        return back();
    }
}
