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
    <section class="gradient-bg text-white py-10 sm:py-16">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                <div>
                    <span
                        class="inline-block bg-white text-primary px-3 py-1 rounded-full text-xs sm:text-sm font-semibold mb-3 sm:mb-4"></span>
                    <h1 id="eventTitle" class="text-2xl sm:text-4xl md:text-5xl font-bold mb-3 sm:mb-4">{{ $event->title ?? '' }}</h1>
                    <p id="eventDescription" class="text-base sm:text-xl mb-4 sm:mb-6 text-green-50">{{ $event->description ?? '' }}</p>

                    <div class="grid grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div class="flex items-center">
                            <x-heroicon-s-calendar class="text-white mr-1.5 sm:mr-2 size-4 sm:size-5" />
                            <span class="text-xs sm:text-sm">{{ $event->start_time->format('F j, Y H:i') ?? '' }}</span>
                        </div>
                        <div class="flex items-center">
                            <x-heroicon-o-map-pin class="text-white mr-1.5 sm:mr-2 size-4 sm:size-5" />
                            <span class="text-xs sm:text-sm truncate">Location: {{ $event->location ?? '' }}</span>
                        </div>
                        <div class="flex items-center">
                            <x-heroicon-o-users class="text-white mr-1.5 sm:mr-2 size-4 sm:size-5" />
                            <span class="text-xs sm:text-sm">Capacity: {{ $event->capacity ?? '' }}</span>
                        </div>
                        <div class="flex items-center">
                            <x-heroicon-o-envelope class="text-white mr-1.5 sm:mr-2 size-4 sm:size-5" /> <span class="text-xs sm:text-sm truncate">Organizer: {{ $event->organizer ?? '' }}</span>
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
    <section class="py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">

                <!-- Left Column: Event Details -->
                <div class="md:col-span-2 space-y-4 sm:space-y-6">

                    <!-- About This Class -->
                    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6">

                        <p id="registration_deadline" class="text-sm sm:text-base">
                            <span class="font-bold">Registration Deadline:</span>
                            {{ $event->registration_deadline ? $event->registration_deadline->format('F j, Y H:i') : 'N/A' }}.
                        </p>

                        {{$event->full_description}}

                    </div>
                </div>

                <!-- Right Column: Booking Form -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-xl shadow-md p-4 sm:p-6 md:sticky md:top-24">
                        <div class="mb-4 sm:mb-6">
                            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1 sm:mb-2">Book this Class</h3>
                            <p class="text-gray-600 text-sm sm:text-base">Reserve your spot today</p>
                        </div>

                        @if ($this->form->getErrorBag()->any())
                        <div class="alert alert-danger mb-3 sm:mb-4">
                            {{ $this->form->getErrorBag()->first() }}
                        </div>
                        @endif
                        @if ($errorMessage)
                        <div class="alert alert-warning mb-3 sm:mb-4">
                            {{$errorMessage}}
                        </div>
                        @endif
                        @if($showForm)
                        @if (session('register-status'))
                        <div class="alert alert-warning mb-3 sm:mb-4">
                            {{ session('register-status') }}
                        </div>
                        @endif

                        <form onsubmit="handleSubmit(event)" class="mt-2 sm:mt-3 space-y-3 sm:space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Name</label>
                                <input wire:model="form.name" readonly autofocus
                                    class="mt-1 appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50" />
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email address</label>
                                <input wire:model="form.email" value="{{ $this->user->email ?? '' }}" readonly autofocus
                                    class="mt-1 appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50" />
                            </div>

                            @if(!$event->is_public)
                            <div>
                                <label for="registration_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Registration Code</label>
                                <input wire:model="form.registration_code" placeholder="Enter registration code"
                                    class="mt-1 appearance-none flex w-full h-10 px-3 py-2 text-sm bg-white dark:text-gray-300 dark:bg-white/[4%] border rounded-md border-gray-300 dark:border-white/10 ring-offset-background placeholder:text-gray-500 dark:placeholder:text-gray-400 focus:border-gray-300 dark:focus:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200/60 dark:focus:ring-white/20 disabled:cursor-not-allowed disabled:opacity-50" />
                            </div>
                            @endif

                            <button class="w-full sm:w-auto bg-blue-600 text-white hover:bg-blue-600/90 focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900 focus:bg-blue-700/90 focus:ring-blue-700 px-5 py-2.5 sm:py-3 text-sm font-medium rounded-md" type="submit">
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
                </div>
            </div>
        </div>
    </section>

</div>
