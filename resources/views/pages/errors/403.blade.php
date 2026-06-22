<x-layouts.frontend> 
    <x-slot name="title">
    403 Forbidden
    </x-slot>


    @volt('home.index')
    <div class="pb-5">
        <div class="mx-auto space-y-6">
            <x-card shadow>
               <h3 class="text-2xl">403 Forbidden</h3>
               {{$error ?? 'You do not have permission to access this page.'}}
            </x-card>
        </div>
    </div>
    
    @endvolt

</x-layouts.frontend>