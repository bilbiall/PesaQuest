@if(request()->header('X-Fragment'))
@include('life.partials._career')
@else
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Career — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('life.partials._life-styles')
</head>
<body class="text-white min-h-screen">
    <x-life-tabs active="career" />
    <div id="life-panel">
        @include('life.partials._career')
    </div>
    @include('partials.life-spa')
    <x-mobile-bottom-nav active="life" />
</body>
</html>
@endif
