<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesa City — PesaQuest</title>
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/world.css') }}?v={{ filemtime(public_path('css/world.css')) }}">
</head>
<body class="font-sans antialiased" style="background:#08111A; overflow:hidden;">
    <style>[x-cloak]{display:none!important}</style>
    <script>
        // Real seconds per game day (admin clock setting) — used to show "≈ real time" hints
        window.__PESA_SPT__ = {{ (int) round(app(\App\Services\GameClock::class)->secondsPerTick()) }};
    </script>
    {{ $slot }}
    {{-- Howler.js — Phase 7 sound scaffold. Audio hooks are wired; add files to public/sounds/ to activate. --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.4/howler.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="{{ asset('js/world.js') }}?v={{ filemtime(public_path('js/world.js')) }}"></script>
</body>
</html>
