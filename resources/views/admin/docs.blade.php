<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Guide — Documentation</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.docs-style')
</head>
<body class="text-white min-h-screen">

<header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm mr-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Admin Panel
            </a>
            <div>
                <h1 class="text-white font-bold text-lg leading-none">📚 Admin Guide</h1>
                <p class="text-gray-500 text-xs mt-0.5">Platform operations, game logic & security — the full manual</p>
            </div>
        </div>
    </div>
</header>

@include('partials.docs-page', [
    'kicker'   => '🛠️ PLATFORM OPERATIONS MANUAL',
    'title'    => 'Admin Guide',
    'subtitle' => 'How every system works under the hood, how to operate the platform day to day, how it makes money, how it is secured, and how it scales.',
    'toc'      => $toc,
    'html'     => $html,
    'siblings' => [
        ['label' => '🎓 GameSet Guide', 'note' => 'content setup manual', 'href' => \Illuminate\Support\Facades\Route::has('gameset.docs') ? route('gameset.docs') : null],
    ],
])
</body>
</html>
