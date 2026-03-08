<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')
            ->latest();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('log_name', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%")
                  ->orWhere('subject_type', 'ilike', "%{$search}%");
            });
        }

        if ($event = $request->input('event')) {
            $query->where('event', $event);
        }

        if ($from = $request->input('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->input('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(50)->withQueryString();

        // Map to a clean structure for the frontend
        $logs->getCollection()->transform(function (Activity $log) {
            $subjectClass = $log->subject_type ? class_basename($log->subject_type) : null;
            $props = $log->properties->toArray();

            return [
                'id'           => $log->id,
                'event'        => $log->event,
                'description'  => $log->description,
                'subject_type' => $subjectClass,
                'subject_id'   => $log->subject_id,
                'causer'       => $log->causer ? ['name' => $log->causer->name, 'email' => $log->causer->email] : null,
                'old'          => $props['old'] ?? null,
                'attributes'   => $props['attributes'] ?? null,
                'created_at'   => $log->created_at->toIso8601String(),
            ];
        });

        return Inertia::render('ActivityLog/Index', [
            'logs'    => $logs,
            'filters' => $request->only(['search', 'event', 'from', 'to']),
            'events'  => ['created', 'updated', 'deleted'],
        ]);
    }
}
