<x-layouts.frontend>
    <x-slot name="title">
        500 Internal Server Error
    </x-slot>


    @volt('home.index')
    <div class="pb-5">
        <div class="mx-auto space-y-6">
            <x-card shadow>
                <h3 class="text-2xl">500 Internal Server Error</h3>
                {{$error ?? 'An internal server error occurred.'}}
            </x-card>
        </div>
    </div>

    @endvolt

</x-layouts.frontend>