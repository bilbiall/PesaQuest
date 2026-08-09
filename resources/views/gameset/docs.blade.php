<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>GameSet Guide — Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.docs-style')
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'docs'])
@include('partials.docs-page', [
    'kicker'   => '📚 CONTENT TEAM MANUAL',
    'title'    => 'GameSet Guide',
    'subtitle' => 'The complete manual for setting up every part of the gameplay — with worked, Kenyan-flavoured samples for every content type.',
    'toc'      => $toc,
    'html'     => $html,
    'siblings' => [
        ['label' => '🛠️ Admin Guide', 'note' => 'platform ops & money', 'href' => \Illuminate\Support\Facades\Route::has('admin.docs') && auth()->user()?->is_admin ? route('admin.docs') : null],
    ],
])
</body>
</html>
