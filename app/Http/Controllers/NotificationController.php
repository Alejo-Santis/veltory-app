<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()
            ->latest()
            ->paginate(20)
            ->through(fn ($n) => [
                'id'              => $n->id,
                'data'            => $n->data,
                'read_at'         => $n->read_at,
                'created_at'      => $n->created_at->diffForHumans(),
                'created_at_full' => $n->created_at->format('d/m/Y H:i'),
            ]);

        return Inertia::render('Notifications/Index', compact('notifications'));
    }

    public function markRead(Request $request, string $id)
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back();
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Todas las notificaciones marcadas como leídas.');
    }
}
