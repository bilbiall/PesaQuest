<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Deal' : 'Edit Deal' }} — GameSet</title>
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>body{background:#07060f;font-family:'Figtree',sans-serif;}[x-cloak]{display:none!important;}
    .field-label{font-size:12px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;display:block;}
    .field-input{width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:10px;padding:10px 14px;color:#fff;font-size:14px;}
    .field-input:focus{outline:none;border-color:rgba(5,150,105,0.5);}
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
        <a href="{{ route('gameset.deals.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Deals
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold text-sm">{{ $mode === 'create' ? '+ New Deal' : 'Edit: '.$deal->title }}</span>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    @if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-xl text-sm text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <form method="POST" action="{{ $mode === 'create' ? route('gameset.deals.store') : route('gameset.deals.update', $deal) }}">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Left column --}}
            <div class="space-y-5">
                <div>
                    <label class="field-label">Icon (emoji)</label>
                    <input type="text" name="icon" value="{{ old('icon', $deal->icon ?? '💼') }}" class="field-input" maxlength="8">
                </div>
                <div>
                    <label class="field-label">Title *</label>
                    <input type="text" name="title" value="{{ old('title', $deal->title ?? '') }}" required class="field-input" placeholder="e.g. Nairobi Property Flip">
                </div>
                <div>
                    <label class="field-label">Category</label>
                    <select name="category" class="field-input">
                        @foreach(['stocks','crypto','property_flip','side_hustle','forex','nse','bonds','general'] as $cat)
                        <option value="{{ $cat }}" {{ old('category', $deal->category ?? 'general') === $cat ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $cat)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="field-label">Description</label>
                    <textarea name="description" class="field-input" rows="3" placeholder="What is this deal about?">{{ old('description', $deal->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="field-label">Lesson (financial insight)</label>
                    <textarea name="lesson" class="field-input" rows="3" placeholder="What should the player learn from this deal?">{{ old('lesson', $deal->lesson ?? '') }}</textarea>
                </div>
            </div>

            {{-- Right column --}}
            <div class="space-y-5">
                <div>
                    <label class="field-label">Cost to Enter (KES) *</label>
                    <input type="number" name="cost" value="{{ old('cost', $deal->cost ?? 5000) }}" required min="100" class="field-input">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Min Return % (success)</label>
                        <input type="number" name="min_return_pct" value="{{ old('min_return_pct', $deal->min_return_pct ?? 20) }}" min="0" max="500" step="0.5" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Max Return % (success)</label>
                        <input type="number" name="max_return_pct" value="{{ old('max_return_pct', $deal->max_return_pct ?? 80) }}" min="0" max="500" step="0.5" class="field-input">
                    </div>
                </div>
                <div>
                    <label class="field-label">Loss % on Failure (0–100)</label>
                    <input type="number" name="loss_pct" value="{{ old('loss_pct', $deal->loss_pct ?? 100) }}" min="0" max="100" step="1" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">100 = lose entire investment. 50 = lose half.</p>
                </div>
                <div>
                    <label class="field-label">Success Probability (0.01–0.99)</label>
                    <input type="number" name="success_probability" value="{{ old('success_probability', $deal->success_probability ?? 0.5) }}" min="0.01" max="0.99" step="0.01" class="field-input">
                    <p class="text-xs text-gray-500 mt-1">0.5 = 50% chance of winning</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Maturity (ticks/game days)</label>
                        <input type="number" name="maturity_ticks" value="{{ old('maturity_ticks', $deal->maturity_ticks ?? 7) }}" min="1" max="365" class="field-input">
                        <p class="text-xs text-gray-500 mt-1">7 ticks ≈ 1 game week</p>
                    </div>
                    <div>
                        <label class="field-label">Risk Level (1–5)</label>
                        <select name="risk_level" class="field-input">
                            @foreach([1=>'Very Low',2=>'Low',3=>'Medium',4=>'High',5=>'Very High'] as $v => $l)
                            <option value="{{ $v }}" {{ old('risk_level', $deal->risk_level ?? 3) == $v ? 'selected' : '' }}>{{ $v }} — {{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="field-label">Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $deal->sort_order ?? 0) }}" min="0" class="field-input">
                    </div>
                    <div class="flex items-end pb-1">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $deal->is_active ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border-white/20 bg-white/5 text-emerald-500 focus:ring-emerald-500/20">
                            <span class="text-sm font-semibold text-gray-300">Active</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 flex items-center gap-4">
            <button type="submit"
                    class="px-8 py-3 rounded-xl font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#059669,#047857);box-shadow:0 4px 14px rgba(5,150,105,.35);">
                {{ $mode === 'create' ? 'Create Deal' : 'Save Changes' }}
            </button>
            <a href="{{ route('gameset.deals.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5">
                Cancel
            </a>
        </div>
    </form>
</div>
</body>
</html>
