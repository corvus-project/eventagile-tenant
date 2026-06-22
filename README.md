## Corvus Event Managemnt

This project is a simple, focused Event Management System (EMS) MVP designed specifically for small organizations and individuals. It provides the fundamental features needed by a small club, community group, independent trainer, or freelancer hosting online sessions. If you're looking for something much simpler and more accessible than comprehensive platforms like Eventbrite or Cvent, this system is for you.
  
The fundemental features list:

Public Event Page:

-   A clean, public-facing page for each event displaying the title, description, date, time, and location.
-   A prominent "Register" button.

Simple Registration Form:
-   Collect Name (first, last).
-   Collect Email Address, Phone number

Attendee List for Organizer:
-   For each event, the organizer can view a list of registered attendees (Name, Email).
-   Ability to Export the list (e.g., CSV or Excel).

Confirmation Email:
-   Automated email sent to the attendee upon successful registration.
-   Contains event details and confirmation (e.g., "You are now registered for [Event Name]").
-   Automated email sent to the attendee upon updating registration.


## Installation

You can use the [Laravel Installer](https://laravel.com/docs#installing-php) to install Corvus.

Clone the reporsitory, sorry this is a personal project, I didn't add a fancy installer.
```bash
git@github.com:kemalyen/corvus.git
```
And then install packages for frontend and backend

```bash
composer install
npm install
npm run build
```
and create database file and populate it:

```bash
php artisan migrate
php artisan db:seed
```

Then, run `composer run dev` to run the asset watcher, and you're good to go!

## Built With

Below is a list of all the technologies that Genesis has been **built with**:

- [TailwindCSS](https://tailwindcss.com)
- [AlpineJS](https://alpinejs.dev)
- [Laravel](https://laravel.com)
- [Livewire](https://livewire.laravel.com)
- [Folio](https://github.com/laravel/folio)
- [Volt](https://github.com/livewire/volt)

 

## Credits

-   [Tony Lea](https://twitter.com/tnylea)
-   [TALL Stack](https://tallstack.dev)
-   [TALL Stack Preset](https://github.com/laravel-frontend-presets/tall)
-   [Laravel Breeze](https://github.com/laravel/breeze)
-   [Laravel Package Boilerplate](https://laravelpackageboilerplate.com)
-   [MaryUI](https://mary-ui.com/)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
