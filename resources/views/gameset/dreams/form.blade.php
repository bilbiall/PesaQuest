<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'edit' ? 'Edit' : 'New' }} Dream — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        label { display:block; font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; margin-bottom:.35rem; }
        input, select, textarea {
            width:100%; padding:.6rem .8rem; border-radius:.7rem; background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.12); color:#fff; font-size:.9rem;
        }
        input:focus, select:focus, textarea:focus { outline:none; border-color:rgba(245,158,11,.5); }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'dreams'])

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-8">
    <a href="{{ route('gameset.dreams.index') }}" class="text-gray-400 hover:text-white text-sm mb-4 inline-flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Back to Dreams
    </a>
    <h1 class="text-2xl font-black mb-6">{{ $mode === 'edit' ? '✏️ Edit Dream' : '✨ New Dream' }}</h1>

    <form method="POST" action="{{ $mode === 'edit' ? route('gameset.dreams.update', $dream) : route('gameset.dreams.store') }}" class="space-y-5">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div>
            <label>Name
                <x-help-tip text="The Dream's title, shown on its Champions' Court catalog card and in the player's Trophy Case once owned." example="Karen Hillside Mansion" />
            </label>
            <input type="text" name="name" value="{{ old('name', $dream->name ?? '') }}" required maxlength="120">
        </div>
        <div>
            <label>Tagline
                <x-help-tip text="A short aspirational line shown under the name on the catalog card — sell the aspiration, this is pure motivation content." example="The house that says you made it." />
            </label>
            <input type="text" name="tagline" value="{{ old('tagline', $dream->tagline ?? '') }}" maxlength="200" placeholder="A short aspirational line">
        </div>
        <div>
            <label>Description
                <x-help-tip text="Longer flavor text shown when a player opens the Dream, reinforcing why it's worth saving for." example="A sprawling hillside estate with a view over the city you built your fortune in." />
            </label>
            <textarea name="description" rows="3" maxlength="1000">{{ old('description', $dream->description ?? '') }}</textarea>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Emoji Icon (fallback)
                    <x-help-tip text="Used as the Dream's visual when no Image URL is set below." example="🏡" />
                </label>
                <input type="text" name="icon" value="{{ old('icon', $dream->icon ?? '🌟') }}" maxlength="10">
            </div>
            <div>
                <label>Image URL (overrides emoji)
                    <x-help-tip text="Overrides the emoji with a custom image — use the in-house SVG trophy set for a consistent look." example="/img/trophies/mansion.svg" />
                </label>
                <input type="text" name="image_url" value="{{ old('image_url', $dream->image_url ?? '') }}" placeholder="/img/trophies/...svg">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Price (KES)
                    <x-help-tip text="The one-time cash cost paid from wallet balance to buy this Dream outright. Price it as a genuine stretch goal — Dreams never count toward net worth and can never be resold, by design." example="45000000" />
                </label>
                <input type="number" name="price" value="{{ old('price', $dream->price ?? 1000000) }}" min="1" required>
            </div>
            <div>
                <label>Category
                    <x-help-tip text="A cosmetic grouping only — it drives the catalog's filter chips and has no gameplay effect." example="property" />
                </label>
                <select name="category" required>
                    @foreach(['property'=>'Property','vehicle'=>'Vehicle','travel'=>'Travel','legacy'=>'Legacy','business'=>'Business','lifestyle'=>'Lifestyle'] as $val => $label)
                        <option value="{{ $val }}" @selected(old('category', $dream->category ?? 'lifestyle') === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label>Min Level (optional gate)
                    <x-help-tip text="Hides the Dream from players below this level; leave blank for no gate. Use it to keep top-tier Dreams aspirational rather than day-one purchases." example="12" />
                </label>
                <input type="number" name="min_level" value="{{ old('min_level', $dream->min_level ?? '') }}" min="1" max="99">
            </div>
            <div>
                <label>Sort Order
                    <x-help-tip text="Manual ordering of Dreams within their category on the catalog — lower numbers show first." example="10" />
                </label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $dream->sort_order ?? 0) }}" min="0">
            </div>
        </div>
        <label class="flex items-center gap-2 !mb-0">
            <input type="checkbox" name="is_active" value="1" class="!w-auto" @checked(old('is_active', $dream->is_active ?? true))>
            <span class="text-white text-sm font-semibold normal-case">Active (visible in Champions' Court)<x-help-tip text="Controls whether this Dream can be purchased in Champions' Court right now; turn off to retire it without deleting it." example="On" /></span>
        </label>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-5 py-2.5 rounded-xl font-black text-white" style="background:linear-gradient(135deg,#f59e0b,#b45309);">
                {{ $mode === 'edit' ? 'Save Changes' : 'Create Dream' }}
            </button>
            <a href="{{ route('gameset.dreams.index') }}" class="px-5 py-2.5 rounded-xl font-bold text-gray-300" style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">Cancel</a>
        </div>
    </form>
</div>
</body>
</html>
