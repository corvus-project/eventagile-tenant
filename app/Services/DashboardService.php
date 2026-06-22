<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\EventRegistration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get key metrics for the dashboard
     */
    public function getKeyMetrics(): array
    {
        return [
            'total_users' => User::count(),
            'total_events' => Event::count(),
            'total_registrations' => EventRegistration::count(),
            'pending_registrations' => EventRegistration::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get daily registrations for the last 30 days
     */
    public function getDailyRegistrations(): array
    {
        $data = EventRegistration::selectRaw('DATE(registered_at) as date, COUNT(*) as count')
            ->where('registered_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $counts = [];

        $data->each(function ($item) use (&$dates, &$counts) {
            $dates[] = $item->date;
            $counts[] = $item->count;
        });

        return [
            'dates' => $dates,
            'counts' => $counts,
        ];
    }

    /**
     * Get event status distribution
     */
    public function getEventStatusDistribution(): array
    {
        $data = Event::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        $labels = [];
        $values = [];

        $data->each(function ($item) use (&$labels, &$values) {
            $labels[] = ucfirst($item->status->value);
            $values[] = $item->count;
        });

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get upcoming and past events
     */
    public function getEventTimeline(): array
    {
        $now = now();

        $upcoming = Event::where('start_time', '>', $now)
            ->count();

        $past = Event::where('start_time', '<', $now)
            ->count();

        return [
            'labels' => ['Upcoming', 'Past'],
            'values' => [$upcoming, $past],
        ];
    }

    /**
     * Get registration metrics
     */
    public function getRegistrationMetrics(): array
    {
        // Top 5 events by registrations
        $topEvents = EventRegistration::selectRaw('events.title, COUNT(*) as count')
            ->join('events', 'event_registrations.event_id', '=', 'events.id')
            ->groupBy('events.id', 'events.title')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $labels = [];
        $values = [];

        $topEvents->each(function ($item) use (&$labels, &$values) {
            $labels[] = strlen($item->title) > 15 ? substr($item->title, 0, 15) . '...' : $item->title;
            $values[] = $item->count;
        });

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get attendance status distribution
     */
    public function getAttendanceStatus(): array
    {
        $attending = EventRegistration::where('is_attending', true)->count();
        $notAttending = EventRegistration::where('is_attending', false)->count();
        $pending = EventRegistration::whereNull('is_attending')->count();

        return [
            'labels' => ['Attending', 'Not Attending', 'Pending'],
            'values' => [$attending, $notAttending, $pending],
        ];
    }

    /**
     * Get recent registrations
     */
    public function getRecentRegistrations(int $limit = 5): array
    {
        return EventRegistration::with(['event', 'user'])
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($reg) {
                return [
                    'user_name' => $reg->user?->name ?? $reg->name ?? 'Unknown',
                    'event_title' => $reg->event?->title ?? 'Unknown Event',
                    'status' => $reg->status->value,
                    'created_at' => $reg->created_at->format('M d, Y H:i'),
                ];
            })
            ->toArray();
    }

    /**
     * Get recent events
     */
    public function getRecentEvents(int $limit = 5): array
    {
        return Event::latest('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($event) {
                return [
                    'title' => $event->title,
                    'status' => $event->status->value,
                    'start_time' => $event->start_time->format('M d, Y H:i'),
                    'registrations' => $event->registrations()->count(),
                ];
            })
            ->toArray();
    }
}
