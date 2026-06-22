<?php

use App\Models\User;
use Illuminate\Support\Facades\Gate as FacadesGate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Livewire\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {

    use Toast;
    use WithPagination;

    public int $perPage = 10;

    public string $search = '';
    public array $sortBy = ['column' => 'start_time', 'direction' => 'desc'];

    #[Computed()]
    public function users()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $search = Str::lower($this->search);
                Log::debug('Searching users with query', ['search' => $search]);
                $query->where(function ($query) use ($search) {
                    $query->whereRaw('LOWER(name) LIKE ?', ['%' . $search . '%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%' . $search . '%']);
                });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }


    public function updatedSearch()
    {
        Log::debug('Search term updated', ['search' => $this->search]);

        $this->resetPage();
    }

    public function sortByColumn(string $column): void
    {
        if ($this->sortBy['column'] === $column) {
            $this->sortBy['direction'] = $this->sortBy['direction'] === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = ['column' => $column, 'direction' => 'asc'];
        }

        $this->resetPage();
    }

    public function delete(int $id)
    {
        FacadesGate::authorize('delete-user', User::findOrFail($id));
        $product = User::findOrFail($id);
        $product->delete();
        $this->toast('success', 'User deleted successfully');
    }

    public function edit(int $id)
    {
        $slug = User::findOrFail($id);
        return redirect()->route('users.update', ['user' => $slug]);
    }

    public function show(int $id)
    {
        $slug = User::findOrFail($id);
        return redirect()->route('users.show', ['user' => $slug]);
    }
};
?>
<x-slot name="title">
    {{ 'List all users' }}
</x-slot>

<x-slot name="header">
    <h2 class="text-lg font-semibold leading-tight text-gray-800 dark:text-gray-200">
        {{ __('Users') }}
    </h2>
</x-slot>
<div class="flex flex-col flex-1">
    <div class="flex flex-col  flex-1 pb-5 mx-auto  w-full">
        <div class="relative flex-1 w-full ">

            <div class="mx-auto ">
                <div class="shadow p-4 dark:bg-gray-800 sm:rounded-lg  bg-slate-50  rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between ">
                        <div class="w-full md:w-1/2">
                            <x-ui.input
                                id="search"
                                type="search"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Search users by name or email"
                                class="w-full" />
                        </div>
                        <div class="flex flex-wrap items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                            <span class="font-medium">Sort by:</span>
                            <button type="button" wire:click="sortByColumn('name')" class="btn-ghost btn-xs">Name</button>
                            <button type="button" wire:click="sortByColumn('email')" class="btn-ghost btn-xs">Email</button>
                        </div>
                    </div>

                    <div class="overflow-x-auto ">
                        <table class="min-w-full text-left divide-y divide-gray-200 dark:divide-gray-700 block sm:table">
                            <thead class="bg-gray-50 dark:bg-gray-900 hidden sm:table-header-group">
                                <tr class="sm:table-row">
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer block sm:table-cell" wire:click="sortByColumn('name')">
                                        Name
                                        @if($sortBy['column'] === 'name')
                                        <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer block sm:table-cell" wire:click="sortByColumn('email')">
                                        Email
                                        @if($sortBy['column'] === 'email')
                                        <span>{{ $sortBy['direction'] === 'asc' ? '↑' : '↓' }}</span>
                                        @endif
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase cursor-pointer block sm:table-cell" wire:click="sortByColumn('status')">
                                        Status
                                        @if($sortBy['column'] === 'email_verified_at')
                                        @endif
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase block sm:table-cell">
                                        Registration Date
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase block sm:table-cell">
                                        Role
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-xs font-semibold tracking-wider uppercase text-right block sm:table-cell">Registrations</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700 block sm:table-row-group">
                                @foreach($this->users as $user)
                                <tr class="block sm:table-row">
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Name:</span>
                                        {{ $user->name }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Email:</span>
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Status:</span>
                                        {{ $user->email_verified_at ? 'Verified' : 'Unverified' }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Registration Date:</span>
                                        {{ \Carbon\Carbon::parse($user->created_at)->format('F j, Y H:i') }}
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Role:</span>
                                        {{ $user->roles->pluck('name')->join(', ') }}
                                    </td>

                                    <td class="px-4 py-4 text-xs text-left sm:text-right text-gray-600 dark:text-gray-300 block sm:table-cell">
                                        <span class="font-semibold text-gray-500 dark:text-gray-400 sm:hidden">Registrations:</span>
                                        <x-button label="Registrations" link="{{ route('dashboard.users.registrations', $user->id) }}" class="btn-info btn-sm" icon="o-users" tooltip="Registrations!" />
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $this->users->links() }}
                    </div>

                </div>


            </div>
        </div>
    </div>
</div>