<?php

use App\Exports\RegistrationsExport;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;

use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Illuminate\View\View;
use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {

    use Toast;
    use WithPagination;

    public  Event $event;


    public function rendering(View $view): void
    {
        $view->title('My Dynamic Title');
    }

    public function mount(Event $event)
    {
        $this->event = $event;
        Gate::authorize('view-event', $event);
    }

    public function download(string $type)
    {
        Gate::authorize('view-event', $this->event);

        if (! in_array($type, ['csv', 'excel'], true)) {
            abort(422, 'Unsupported export format.');
        }

        $id = $this->event->getKey();

        if ($type === 'csv') {
            return (new RegistrationsExport($id))->download('registrations-' . $id . '.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        // Default to Excel format      
        return (new RegistrationsExport($id))->download('registrations-' . $id . '.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
};
?>

<x-slot name="title">
    Export Registrations for Event: {{ $event->title }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        Export Registrations for Event: {{ $event->title }}
    </h2>
</x-slot>
<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">

            <div class="pb-5">
                <div class="mx-auto space-y-6">
                    <x-card shadow>

                        <div class="flex justify-end mb-4">
                            <x-ui.text-link href="{{ route('dashboard.events.update', ['event' => $event->slug]) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                Update Event
                            </x-ui.text-link>

                            <x-ui.text-link href="{{ route('dashboard.events.registrations.show', ['event' => $event->slug]) }}" class="btn-ghost btn-sm text-sm text-blue-600 p-2">
                                Registration List
                            </x-ui.text-link>

                        </div>


                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                            <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">Export Registrations</h3>
                            <p class="mb-4">You can download the registration list for the event in CSV format.</p>
                            <p class="mb-4">Click the button below to download the registration list.</p>

                            <div class="flex items-center py-2 border-b">
                                <a href="#" wire:click.prevent="download('excel')" class="btn btn-info btn-sm m-2">
                                    Download Excel
                                </a>

                                <a href="#" wire:click.prevent="download('csv')" class="btn btn-info btn-sm m-2">
                                    Download CSV
                                </a>
                            </div>
                        </div>
                    </x-card>
                </div>
            </div>
        </div>
    </div>
</div>