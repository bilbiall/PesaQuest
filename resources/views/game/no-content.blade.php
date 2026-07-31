<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PesaQuest – Coming Soon</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body { background: #0f0e17; }</style>
</head>
<body class="min-h-screen text-white font-sans antialiased flex items-center justify-center p-4">
    <div class="text-center max-w-sm">
        <div class="text-7xl mb-6">🚧</div>
        <h1 class="text-2xl font-black mb-3">Content Coming Soon</h1>
        <p class="text-gray-400 mb-8">Our game masters are busy crafting new scenarios for your age group. Check back soon!</p>
        <a href="{{ route('dashboard') }}" class="inline-block bg-indigo-500 hover:bg-indigo-400 text-white font-bold px-6 py-3 rounded-xl transition-colors">
            Back to Dashboard
        </a>
    </div>
</body>
</html>
