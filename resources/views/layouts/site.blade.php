@props(['title' => null, 'description' => null, 'liturgicalColor' => null])
@php($liturgicalColor ??= app(\App\Support\LiturgicalCalendar::class)->colorAt(now()))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-liturgical="{{ $liturgicalColor->value }}">
    <head>
        @include('partials.head', ['title' => $title ?? 'St Catherine Orthodox Church'])
        @if ($description)
            <meta name="description" content="{{ $description }}">
        @endif
    </head>
    <body class="min-h-screen bg-[var(--liturgical-surface)] antialiased">
        <header class="border-b border-[var(--liturgical-border)]">
            <flux:container class="flex flex-col items-center gap-4 py-6 text-center lg:flex-row lg:gap-6 lg:text-left">
                <img src="/st-catherine-icon.jpg" alt="St Catherine of Alexandria Icon" class="h-24 w-auto shrink-0 rounded">
                <div>
                    <flux:heading size="xl" level="1">St Catherine Orthodox Church</flux:heading>
                    <flux:text size="lg">Aiken, SC</flux:text>
                    <flux:text size="sm">Antiochian Orthodox Christian Archdiocese of North America</flux:text>
                </div>

                <flux:spacer class="max-lg:hidden" />

                <flux:navbar aria-label="Main" class="flex-wrap justify-center lg:justify-end">
                    <flux:navbar.item :href="route('home')" :current="request()->routeIs('home')" class="text-base font-semibold">Home</flux:navbar.item>
                    <flux:navbar.item href="https://payv3.xpress-pay.com/org/5CAEADE8445E434" target="_blank" rel="noopener noreferrer" class="text-base font-semibold">Online Giving</flux:navbar.item>
                    <flux:navbar.item href="#" class="text-base font-semibold">Ministries</flux:navbar.item>
                    <flux:navbar.item href="#" class="text-base font-semibold">About</flux:navbar.item>
                    <flux:dropdown>
                        <flux:navbar.item icon:trailing="chevron-down" class="text-base font-semibold">Choir</flux:navbar.item>
                        <flux:navmenu>
                            <flux:navmenu.item :href="route('music-library')">Music Library</flux:navmenu.item>
                        </flux:navmenu>
                    </flux:dropdown>
                    <flux:navbar.item href="#" class="text-base font-semibold">Contact Form</flux:navbar.item>
                </flux:navbar>
            </flux:container>
        </header>

        <main>
            {{ $slot }}
        </main>

        <flux:separator />

        <footer>
            <flux:container class="grid gap-8 py-10 md:grid-cols-3">
                <div>
                    <flux:heading size="lg" level="2" class="mb-2">St Catherine Orthodox Church</flux:heading>
                    <address class="not-italic">
                        <flux:text>1470 Edgefield Hwy</flux:text>
                        <flux:text>Aiken, SC 29801</flux:text>
                        <flux:text>Phone: (803) 439-1909</flux:text>
                        <flux:text>Email: fgrogers@aol.com</flux:text>
                    </address>
                </div>
                <div class="flex flex-col gap-1">
                    <flux:heading size="lg" level="2" class="mb-2">Quick Links</flux:heading>
                    <flux:link :href="route('home')">Home</flux:link>
                    <flux:link href="#">About</flux:link>
                    <flux:link href="#">Calendar</flux:link>
                    <flux:link href="#">ACTS Ministry</flux:link>
                    <flux:link href="https://payv3.xpress-pay.com/org/5CAEADE8445E434" target="_blank" rel="noopener noreferrer">Online Giving</flux:link>
                </div>
                <div class="flex flex-col gap-1">
                    <flux:heading size="lg" level="2" class="mb-2">Connect With Us</flux:heading>
                    <flux:link href="https://www.youtube.com/@StCatherineOrthodoxChurch" class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0" aria-hidden="true"><path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/></svg>
                        Subscribe on YouTube
                    </flux:link>
                    <flux:link href="https://www.facebook.com/profile.php?id=100064377501969" class="inline-flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0" aria-hidden="true"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        Follow us on Facebook
                    </flux:link>
                </div>
            </flux:container>
            <flux:separator />
            <flux:container class="py-4 text-center">
                <flux:text>&copy; {{ date('Y') }} St Catherine Orthodox Church</flux:text>
                <flux:text size="sm">Antiochian Orthodox Christian Archdiocese of North America</flux:text>
            </flux:container>
        </footer>

        @fluxScripts
    </body>
</html>
