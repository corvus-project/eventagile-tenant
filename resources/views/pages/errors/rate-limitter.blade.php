<x-layouts.frontend>
    <x-slot name="title">
        403 Forbidden
    </x-slot>

    <div class="pb-5 mt-10">
        <div class="mx-auto space-y-6">
            <x-card shadow class="bg-red-100 text-red-800">
                <h3 class="text-2xl pb-10">An error occured!</h3>

                {{$error}}
            </x-card>
        </div>
    </div>


</x-layouts.frontend>