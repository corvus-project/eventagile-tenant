<?php


use App\Enums\RegistrationStatus;
use App\Livewire\Forms\EventRegistrationForm;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

new #[Layout('layouts.tenant')]  class extends Component
{
    use WithPagination;

    public Event $event;
    public ?string $name;
    public ?string $captchaToken = null;
    public EventRegistrationForm $form;
    public int $registrations_count = 0;

    public function mount(Event $event)
    {
        $this->form->name = auth()?->user()->name ?? '';
        $this->form->email = auth()?->user()->email ?? '';
        $this->form->user_id = auth()?->id() ?? null;
        $this->event = $event;
        $this->form->setEvent($event);
        $this->registrations_count = $this->event->registrations()->where('status', RegistrationStatus::CONFIRMED->value)->count();
    }

    public function save($token = null)
    {
        if ($token) {
            $this->captchaToken = $token;
        }

        $query = http_build_query([
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $this->captchaToken,
        ]);

        $response = Http::post('https://www.google.com/recaptcha/api/siteverify?' . $query);
        $captchaLevel = $response->json('score');

        throw_if($captchaLevel <= 0.5, ValidationException::withMessages([
            'captchaToken' => __('Error on captcha verification. Please, refresh the page and try again.')
        ]));
        Log::info('User registered for event', ['event_id' => $this->event->id, 'registrations_count' => $this->registrations_count]);
        $this->form->store();
    }
}
?>
<x-slot name="title">
    {{ tenant('name') }} ~ {{ $event->title }}
</x-slot>
<x-slot name="header">
    <h2 class="text-3xl font-semibold leading-tight text-gray-800 dark:text-gray-20 px-6 lg:px-2">
        <a href="{{ route('tenant.home') }}" class="text-blue-500 hover:text-blue-700">{{ tenant('name') }}</a>
    </h2>
</x-slot>
<div class="shadow-lg rounded-lg p-2 bg-white dark:bg-gray-800 dark:border dark:border-gray-200/10">

    <div class="flex flex-col lg:flex-row items-start  justify-between space-y-4 lg:space-y-0  min-h-[400px] p-6">

        <div class="mx-auto px-2 space-y-6 align-top text-base/8 w-full lg:w-2/3">

            <h3 class="text-2xl">{{ $event->title }}</h3>
            <p>{{ $event->description }}</p>
            <p><span class="font-bold">Location:</span> <br>{{ $event->location }}</p>

            <p><span class="font-bold">Organizer:</span> <br>{{ $event->organizer }}</p>
            <p><span class="font-bold">Start Time:</span> <br>{{ $event->start_time->format('d M Y H:i') }}</p>

            <p><span class="font-bold">Registration Deadline:</span> <br>
                Please register until {{ $event->registration_deadline ? $event->registration_deadline->format('F j, Y H:i') : 'N/A' }}.
            </p>
        </div>

        <div class="mx-auto px-1 lg:ml-8 lg:mt-0 mt-8 w-full lg:w-1/3">
            <div class="mx-auto space-y-6">
                <section
                    class="shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg  bg-blue-50 p-6 rounded-lg dark:bg-gray-900/50 dark:border dark:border-gray-200/10">
                    @if (session('register-status'))
                    <div class="alert alert-warning mb-4">
                        {{ session('register-status') }}
                    </div>
                    @endif
                    @if( $event->status !== \App\Enums\EventStatus::SCHEDULED)
                    <div class="alert alert-warning mb-4">
                        This event is not open for registration.
                    </div>
                    @elseif( $event->registration_ends_at && $event->registration_ends_at < now())
                        <div class="alert alert-warning mb-4">
                        Registration ends at {{ $event->registration_ends_at ? $event->registration_ends_at?->format('d M Y H:i') : 'N/A'   }}.
            </div>
            @elseif($this->registrations_count >= $event->capacity)
            <div class="alert alert-warning mb-4">
                This event has reached its capacity.
            </div>
            @else
            <h3 class="text-lg font-semibold mb-4">Register for Event</h3>

            @if(!auth()->user())
            <a href="{{ route("login") }}" class="text-blue-500 hover:text-blue-700">Please log in to register for the event.</a>
            @else

            <p class="mb-4">Please fill in your details to register for the event.</p>

            @error('captchaToken')
            <div class="bg-red-300 text-red-700 p-3 rounded">{{ $message }}</div>
            @enderror

            @error('form.user_id')
            <div class="bg-red-300 text-red-700 p-3 rounded">{{ $message }}</div>
            @enderror


            <form onsubmit="handleSubmit(event)" class="mt-1 space-y-2">
                <x-input label="Name" wire:model="form.name" readonly />
                <x-input label="Email" wire:model="form.email" value="{{ $this->user->email ?? '' }}" readonly />

                @if(!$event->is_public)
                <x-input label="Registration Code" wire:model="form.registration_code" placeholder="Enter registration code" />
                @endif


                <x-button label="Register" rounded="md" class="btn-primary" type="primary" submit="true" />
            </form>


            <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.public_key') }}"></script>
            <script>
                function handleSubmit(event) {
                    event.preventDefault();
                    grecaptcha.ready(function() {
                        grecaptcha.execute('{{ config("services.recaptcha.public_key") }}', {
                                action: 'submit'
                            })
                            .then(function(token) {
                                @this.call('save', token);
                            });
                    })
                }
            </script>
            @endif
            @endif
            </section>
        </div>
    </div>
</div>