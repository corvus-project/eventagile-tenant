<?php

use App\Services\DashboardService;
use Livewire\Component;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    public array $metrics = [];
    public array $dailyRegistrations = [];
    public array $eventStatus = [];
    public array $eventTimeline = [];
    public array $registrationMetrics = [];
    public array $attendanceStatus = [];
    public array $recentRegistrations = [];
    public array $recentEvents = [];
    public array $dashboardData = [];

    public array $eventStatusChart = [];
    public array $dailyRegistrationsChart = [];

    public function mount()
    {
        $service = new DashboardService();
        $this->metrics = $service->getKeyMetrics();
        $this->dailyRegistrations = $service->getDailyRegistrations();
        $this->eventStatus = $service->getEventStatusDistribution();
        $this->eventTimeline = $service->getEventTimeline();
        $this->registrationMetrics = $service->getRegistrationMetrics();
        $this->attendanceStatus = $service->getAttendanceStatus();
        $this->recentRegistrations = $service->getRecentRegistrations();
        $this->recentEvents = $service->getRecentEvents();

        $this->dailyRegistrationsChart = [
            'type' => 'bar',
            'data' => [
                'labels' => $this->dailyRegistrations['dates'] ?? ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Daily Registrations',
                        'data' => $this->dailyRegistrations['counts'] ?? [0],
                    ]
                ]
            ]
        ];

        $this->eventStatusChart = [
            'type' => 'pie',
            'data' => [
                'labels' => $this->eventStatus['labels'] ?? ['No Data'],
                'datasets' => [
                    [
                        'label' => 'Event Status Distribution',
                        'data' => $this->eventStatus['values'] ?? [0],
                    ]
                ]
            ]
        ];


        $this->dashboardData = [
            'dailyRegistrations' => $dailyRegistrations ?? ['dates' => [], 'counts' => []],
            'eventStatus' => $eventStatus ?? ['labels' => [], 'values' => []],
            'eventTimeline' => $eventTimeline ?? ['labels' => [], 'values' => []],
            'registrationMetrics' => $registrationMetrics ?? ['labels' => [], 'values' => []],
            'attendanceStatus' => $attendanceStatus ?? ['labels' => [], 'values' => []],
        ];



        //dd($this->dailyRegistrations);
        // dd($this->dailyRegistrations, $this->eventStatus, $this->eventTimeline, $this->registrationMetrics, $this->attendanceStatus, $this->recentRegistrations, $this->recentEvents);
    }
};
?>

<x-slot name="title">
    {{ __('Dashboard') }}
</x-slot>

<x-slot name="header">
    <h2 class="text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Dashboard') }}
    </h2>
</x-slot>

<div class="py-1">
    <div class="w-full mx-auto">
        <!-- Section 1: Key Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Users -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Users</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['total_users'] ?? 0 }}</p>
                    </div>
                    <div class="text-blue-500 text-3xl">👥</div>
                </div>
            </div>

            <!-- Total Events -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Events</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['total_events'] ?? 0 }}</p>
                    </div>
                    <div class="text-green-500 text-3xl">📅</div>
                </div>
            </div>

            <!-- Total Registrations -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Total Registrations</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['total_registrations'] ?? 0 }}</p>
                    </div>
                    <div class="text-purple-500 text-3xl">✍️</div>
                </div>
            </div>

            <!-- Pending Registrations -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Pending</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $metrics['pending_registrations'] ?? 0 }}</p>
                    </div>
                    <div class="text-orange-500 text-3xl">⏳</div>
                </div>
            </div>
        </div>

        <!-- Charts Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Section 2: Daily Registrations Chart -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Daily Registrations (Last 30 Days)</h3>
                <x-chart wire:model="dailyRegistrationsChart" class="h-80" />
            </div>

            <!-- Section 3: Event Status Distribution -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Status Distribution</h3>
                <x-chart wire:model="eventStatusChart" class="h-80" />
            </div>
        </div>

        <!-- Section 7: Recent Activity Feed -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Registrations -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Registrations</h3>
                <div class="space-y-4">
                    @forelse($recentRegistrations as $registration)
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $registration['user_name'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $registration['event_title'] }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $registration['created_at'] }}</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ 
                                $registration['status'] === 'confirmed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                ($registration['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')
                            }}">
                            {{ ucfirst($registration['status']) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No registrations yet</p>
                    @endforelse
                </div>
            </div>

            <!-- Recent Events -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Recent Events</h3>
                <div class="space-y-4">
                    @forelse($recentEvents as $event)
                    <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        <div>
                            <p class="font-medium text-gray-900 dark:text-white">{{ $event['title'] }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $event['start_time'] }}</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $event['registrations'] }} registrations</p>
                        </div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ 
                                $event['status'] === 'published' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' :
                                ($event['status'] === 'draft' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' :
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200')
                            }}">
                            {{ ucfirst($event['status']) }}
                        </span>
                    </div>
                    @empty
                    <p class="text-gray-500 dark:text-gray-400 text-center py-8">No events yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>