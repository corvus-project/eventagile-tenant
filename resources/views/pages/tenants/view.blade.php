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

new #[Layout('layouts.ea-yoga')]  class extends Component
{
    use WithPagination;

    public Event $event;
    public ?string $name;
    public ?string $email;
    public ?string $captchaToken = null;
    public EventRegistrationForm $form;
    public ?string $errorMessage;

    public bool $showForm = false;

    public function mount(Event $event)
    {
        $this->form->name = auth()?->user()->name ?? '';
        $this->form->email = auth()?->user()->email ?? '';
        $this->form->user_id = auth()?->id() ?? null;
        $this->event = $event;
        $this->form->setEvent($event);
        $this->showForm = $this->getShowForm();
    }

    private function getShowForm()
    {
        if (auth()->user() && $this->event->registrations()->where('user_id', request()->user()->id)->exists()) {
            $this->errorMessage = 'You already registered this event!';
            return false;
        }

        if ($this->event->status !== \App\Enums\EventStatus::SCHEDULED) {
            $this->errorMessage = 'This event is not scheduled!';
            return false;
        }

        if ($this->event->registration_deadline && $this->event->registration_deadline < now()) {
            $this->errorMessage = 'Registration is ended for this event';
            return false;
        }
        $registrations_count = $this->event->registrations()->where('status', RegistrationStatus::CONFIRMED->value)->count();
        if ($registrations_count >= $this->event->capacity) {
            $this->errorMessage = 'This event has reached its capacity.';
            return false;
        }

        if (!auth()->user()) {
            $this->errorMessage = 'Please log in to register for the event';
            return false;
        }

        return true;
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

        $this->form->store();
        Log::info('User registered for event', ['event_id' => $this->event->id]);
        return redirect()->to(route('tenant.event.view', $this->event))->with('register-status', 'You have successfully registered for the event!');
    }
}
?>

<x-slot name="title">
    {{ Setting::get('site_name') }} - Home
</x-slot>

<x-slot name="tenant_name">
    {{ Setting::get('site_name') }}
</x-slot>

<x-slot name="event_hero">
    <!-- Event Hero Section -->
    <section class="gradient-bg text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <span
                        class="inline-block bg-white text-primary px-4 py-1 rounded-full text-sm font-semibold mb-4"></span>
                    <h1 id="eventTitle" class="text-4xl md:text-5xl font-bold mb-4">{{ $event->title ?? '' }}</h1>
                    <p id="eventDescription" class="text-xl mb-6 text-green-50">{{ $event->description ?? '' }}</p>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="flex items-center">

                            <x-heroicon-s-calendar class="text-white mr-2 size-5" />
                            <span>{{ $event->start_time->format('F j, Y H:i') ?? '' }}</span>

                        </div>

                        <div class="flex items-center">

                            <x-heroicon-o-map-pin class="text-white mr-2 size-5" />
                            <span>Location: {{ $event->location ?? '' }}</span>

                        </div>
                        <div class="flex items-center">
                            <x-heroicon-o-users class="text-white mr-2 size-5 " />

                            <span> Capacity: {{ $event->capacity ?? '' }}</span>
                        </div>

                        <div class="flex items-center">
                            <x-heroicon-o-envelope class="text-white mr-2 size-5" /> <span> Organizer: {{ $event->organizer ?? '' }}</span>
                        </div>
                    </div>

                </div>
                <div class="hidden md:block">
                    <img id="eventImage"
                        src="https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&h=400&fit=crop"
                        alt="Yoga Class" class="rounded-2xl shadow-2xl">
                </div>
            </div>

        </div>

    </section>

</x-slot>


<div>


    <!-- Event Details & Booking -->
    <section class="py-3">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-2">

                <!-- Left Column: Event Details -->
                <div class="md:col-span-2 space-y-8">

                    <!-- About This Class -->
                    <div class="bg-white rounded-xl shadow-md p-6">

                        <p id="registration_deadline"><span class="font-bold">Registration Deadline:</span> <br>
                            Please register until {{ $event->registration_deadline ? $event->registration_deadline->format('F j, Y H:i') : 'N/A' }}.
                        </p>

                        {{$event->full_description}}

                    </div>
                </div>

                <!-- Right Column: Booking Form -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-6 sticky top-24">
                        <div class="mb-6">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Book this Class</h3>
                            <p class="text-gray-600">Reserve your spot today</p>
                        </div>

                        @if ($this->form->getErrorBag()->any())
                        <div class="alert alert-danger mb-4">
                            {{ $this->form->getErrorBag()->first() }}
                        </div>
                        @endif
                        @if ($errorMessage)
                        <div class="alert alert-warning mb-4">
                            {{$errorMessage}}
                        </div>
                        @endif
                        @if($showForm)
                        @if (session('register-status'))
                        <div class="alert alert-warning mb-4">
                            {{ session('register-status') }}
                        </div>
                        @endif

                        <form onsubmit="handleSubmit(event)" class="mt-1 space-y-2">
                            <label for="email" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
                                Name
                            </label>

                            <input wire:model="form.name" readonly autofocus class="appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50 " />

                            <label for="email" class="block text-sm font-medium leading-5 text-gray-700 dark:text-gray-300">
                                Email address
                            </label>

                            <input wire:model="form.email" value="{{ $this->user->email ?? '' }}" readonly autofocus class="appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50 " />

                            @if(!$event->is_public)
                            <input label="Registration Code" wire:model="form.registration_code" placeholder="Enter registration code" />
                            @endif

                            <button class="bg-blue-600 text-white hover:bg-blue-600/90 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:bg-blue-700/90 focus:ring-blue-700 px-5 py-3  text-sm font-medium rounded-md" type="submit">
                                Register
                            </button>
                        </form>

                        @endif
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

                    </div>
    </section>

</div>