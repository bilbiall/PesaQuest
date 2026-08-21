@php $isEdit = isset($asset) && $asset->exists; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $isEdit ? 'Edit: '.$asset->name : 'New Asset' }} — Admin</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .form-input {
            width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            color:#fff; border-radius:.75rem; padding:.6rem 1rem; font-size:.875rem;
            outline:none; transition:border-color .2s;
        }
        .form-input:focus { border-color:rgba(99,102,241,.6); box-shadow:0 0 0 3px rgba(99,102,241,.1); }
        .form-label { display:block; font-size:11px; font-weight:700; letter-spacing:.06em; color:#6b7280; text-transform:uppercase; margin-bottom:.35rem; }
        .section-card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1.25rem; padding:1.5rem; margin-bottom:1.25rem; }
        .section-title { font-size:.75rem; font-weight:900; letter-spacing:.1em; color:#6366f1; text-transform:uppercase; margin-bottom:1.25rem; }
        select.form-input option { background:#0f172a; color:#fff; }
        textarea.form-input { min-height:80px; resize:vertical; }
    </style>
</head>
<body class="text-white min-h-screen">

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-4">
        <a href="{{ route('admin.assets') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Assets
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold text-sm">{{ $isEdit ? 'Edit: '.$asset->name : 'New Asset' }}</span>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">

    {{-- Errors --}}
    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-3 text-sm font-bold text-red-300"
         style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.25);">
        <p class="mb-2 font-black">Please fix these errors:</p>
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          action="{{ $isEdit ? route('admin.assets.update', $asset) : route('admin.assets.store') }}">
        @csrf
        @if($isEdit) @method('PUT') @endif

        {{-- Identity --}}
        <div class="section-card">
            <p class="section-title">Identity</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="form-label">Name *</label>
                    <input type="text" name="name" class="form-input" required
                           value="{{ old('name', $asset->name ?? '') }}" placeholder="e.g. Honda Fit 2015">
                </div>
                <div>
                    <label class="form-label">Slug *</label>
                    <input type="text" name="slug" class="form-input" required
                           value="{{ old('slug', $asset->slug ?? '') }}" placeholder="e.g. honda-fit-2015">
                </div>
                <div>
                    <label class="form-label">Icon (name, e.g. car, house, briefcase)</label>
                    <input type="text" name="icon" class="form-input text-center" maxlength="30"
                           value="{{ old('icon', $asset->icon ?? 'store') }}" placeholder="store">
                </div>
                <div>
                    <label class="form-label">Category *</label>
                    <select name="category" class="form-input" required>
                        @foreach(['vehicle'=>'🚗 Vehicle','property'=>'🏠 Property','business'=>'💼 Business','investment'=>'📈 Investment','fixed_income'=>'🏛️ Fixed Income','gadget'=>'📱 Gadget'] as $val => $label)
                        <option value="{{ $val }}" {{ old('category', $asset->category ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Tier (1–5) *</label>
                    <input type="number" name="tier" class="form-input" min="1" max="5" required
                           value="{{ old('tier', $asset->tier ?? 1) }}">
                </div>
                <div>
                    <label class="form-label">Age Group</label>
                    <select name="age_group" class="form-input">
                        @foreach(['all'=>'All Ages','8-12'=>'8–12','13-17'=>'13–17','18-25'=>'18–25','26+'=>'26+'] as $val => $label)
                        <option value="{{ $val }}" {{ old('age_group', $asset->age_group ?? 'all') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Max per Player</label>
                    <input type="number" name="max_per_player" class="form-input" min="1"
                           value="{{ old('max_per_player', $asset->max_per_player ?? 1) }}">
                </div>
            </div>
        </div>

        {{-- Economics --}}
        <div class="section-card">
            <p class="section-title">Economics</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="form-label">Base Price (Ksh) *</label>
                    <input type="number" name="base_price" class="form-input" min="0" required
                           value="{{ old('base_price', $asset->base_price ?? 0) }}">
                </div>
                <div>
                    <label class="form-label">Monthly Income (Ksh)</label>
                    <input type="number" name="monthly_income" class="form-input" min="0"
                           value="{{ old('monthly_income', $asset->monthly_income ?? 0) }}">
                </div>
                <div>
                    <label class="form-label">Monthly Cost (Ksh)</label>
                    <input type="number" name="monthly_cost" class="form-input" min="0"
                           value="{{ old('monthly_cost', $asset->monthly_cost ?? 0) }}">
                </div>
                <div>
                    <label class="form-label">Auto-Create Bill (slug)</label>
                    <input type="text" name="creates_bill_slug" class="form-input"
                           value="{{ old('creates_bill_slug', $asset->creates_bill_slug ?? '') }}"
                           placeholder="e.g. car-insurance">
                    <p class="text-[10px] text-gray-600 mt-1">Leave blank for no auto-bill.</p>
                </div>
            </div>
        </div>

        {{-- Image & Description --}}
        <div class="section-card">
            <p class="section-title">Image & Descriptions</p>
            <div class="space-y-4">
                <div>
                    <label class="form-label">Image URL</label>
                    <input type="url" name="image_url" class="form-input"
                           value="{{ old('image_url', $asset->image_url ?? '') }}"
                           placeholder="https://example.com/image.jpg">
                </div>
                <div>
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" rows="3">{{ old('description', $asset->description ?? '') }}</textarea>
                </div>
                <div>
                    <label class="form-label">Educational Note</label>
                    <textarea name="educational_note" class="form-input" rows="3">{{ old('educational_note', $asset->educational_note ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="section-card">
            <p class="section-title">Status</p>
            <label class="flex items-center gap-4 cursor-pointer select-none">
                <div class="relative">
                    <input type="checkbox" name="is_active" value="1" id="is_active_check" class="sr-only"
                           {{ old('is_active', $asset->is_active ?? true) ? 'checked' : '' }}>
                    <div class="w-11 h-6 rounded-full transition-colors" id="toggle-bg"
                         style="background:{{ old('is_active', $asset->is_active ?? true) ? '#6366f1' : 'rgba(255,255,255,.1)' }}"></div>
                    <div class="absolute top-0.5 left-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform" id="toggle-knob"
                         style="transform:{{ old('is_active', $asset->is_active ?? true) ? 'translateX(20px)' : 'translateX(0)' }}"></div>
                </div>
                <span class="text-sm font-bold text-white" id="toggle-label">
                    {{ old('is_active', $asset->is_active ?? true) ? 'Active — visible in marketplace' : 'Inactive — hidden from players' }}
                </span>
            </label>
        </div>

        {{-- Buttons --}}
        <div class="flex gap-4">
            <a href="{{ route('admin.assets') }}"
               class="flex-1 py-4 rounded-2xl text-sm font-bold text-center text-gray-400 transition-all hover:text-white"
               style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                Cancel
            </a>
            <button type="submit"
                    class="flex-1 py-4 rounded-2xl text-sm font-black text-white transition-all hover:scale-[1.01] hover:brightness-110"
                    style="background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 20px rgba(99,102,241,.4);">
                {{ $isEdit ? 'Save Changes' : 'Create Asset' }}
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('is_active_check').addEventListener('change', function () {
    document.getElementById('toggle-bg').style.background    = this.checked ? '#6366f1' : 'rgba(255,255,255,.1)';
    document.getElementById('toggle-knob').style.transform   = this.checked ? 'translateX(20px)' : 'translateX(0)';
    document.getElementById('toggle-label').textContent      = this.checked ? 'Active — visible in marketplace' : 'Inactive — hidden from players';
});
</script>
</body>
</html>
