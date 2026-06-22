<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Used to add dark mode right away, adding here prevents any flicker -->
    <script>
        if (typeof(Storage) !== "undefined") {
            if (localStorage.getItem('dark_mode') && localStorage.getItem('dark_mode') == 'true') {
                document.documentElement.classList.add('dark');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <title>{{ $title ?? config('app.name')}}</title>

</head>

<body class="min-h-screen font-sans antialiased bg-blue-900 dark:bg-gray-900">
    {{ $slot }}
    <x-toast />

</body>

</html>