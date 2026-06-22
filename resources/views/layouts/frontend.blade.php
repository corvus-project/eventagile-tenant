<x-layouts.main>

    <x-slot name="title">
        {{ $title ?? 'CorvusApp' }}
    </x-slot>

    <x-ui.frontend.header />

    <!-- Page Heading -->
    @if (isset($header))
    <div class="mb-5 bg-white border-b border-gray-200/80 dark:border-gray-200/10 dark:bg-gray-900/40">
        <div class="py-6 mx-auto max-w-6xl sm:px-6 lg:px-12">
            {{ $header }}
        </div>
    </div>
    @endif

    <div class="w-full max-w-6xl mx-auto px-4 sm:px-6">
        {{ $slot }}
    </div>
    <footer class="pt-8 pb-8 bg-gray-100 mt-8">
        <nav class="flex justify-center space-x-6">
            <a href="/" class="text-gray-600 hover:text-primary">Home</a>
            <a href="/features" class="text-gray-600 hover:text-primary">Features</a>
            <a href="/examples" class="text-gray-600 hover:text-primary">Usage Examples</a>
            <a href="/pricing" class="text-gray-600 hover:text-primary">Pricing</a>
            <a href="/contact" class="text-gray-600 hover:text-primary">Contact</a>
            <a href="/privacy" class="text-gray-600 hover:text-primary">Privacy Policy</a>
        </nav>
        <p class="mt-4 text-center text-xs text-gray-400">&copy; {{now()->year}} Event Agile. All rights reserved.</p>
    </footer>
</x-layouts.main>