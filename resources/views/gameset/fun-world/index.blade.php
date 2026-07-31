<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Fun World Activities — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        @keyframes popIn { from{opacity:0;transform:translateY(12px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }
        .card-in { animation:popIn .3s cubic-bezier(.34,1.56,.64,1) both; }
        .fw-input { width:100%; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.12); border-radius:.75rem; padding:.6rem .85rem; font-size:.85rem; color:#fff; }
        .fw-input:focus { outline:none; border-color:rgba(255,107,53,.5); }
        .fw-label { display:block; font-size:.68rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:rgba(255,255,255,.45); margin-bottom:.3rem; }
    </style>
</head>
<body class="text-white min-h-screen"
      x-data="{
          showForm: false,
          editing: null,
          form: { name:'', icon:'🎉', description:'', price:500, mood_boost_base:8, xp_reward:15, sort_order:0, is_active:true },
          openCreate() {
              this.editing = null;
              this.form = { name:'', icon:'🎉', description:'', price:500, mood_boost_base:8, xp_reward:15, sort_order:0, is_active:true };
              this.showForm = true;
          },
          openEdit(a) {
              this.editing = a.id;
              this.form = { name:a.name, icon:a.icon, description:a.description ?? '', price:a.price, mood_boost_base:a.mood_boost_base, xp_reward:a.xp_reward, sort_order:a.sort_order, is_active:!!a.is_active };
              this.showForm = true;
          },
      }">
@include('gameset.partials.topnav', ['active' => 'fun-world'])

{{-- Nav --}}
<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                GameSet
            </a>
            <span class="text-white/20">/</span>
            <span class="text-white font-bold text-sm">🎡 Fun World</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('gameset.bills.index') }}"
               class="hidden sm:flex items-center gap-1.5 text-xs text-emerald-400 border border-emerald-500/25 hover:border-emerald-500/50 px-3 py-1.5 rounded-lg transition-colors">
                🗓 Bills
            </a>
            <button @click="openCreate()"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-black text-white transition-all hover:scale-[1.02]"
                    style="background:linear-gradient(135deg,#FF6B35,#f59e0b);box-shadow:0 4px 14px rgba(255,107,53,.35);">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Activity
            </button>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 flex items-center gap-3 text-sm font-bold text-emerald-300"
         style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        <span>✅</span>{{ session('success') }}
    </div>
    @endif

    {{-- Header + Stats --}}
    <div class="mb-8">
        <h1 class="text-3xl font-black text-white mb-1">Fun World Activities</h1>
        <p class="text-gray-400 text-sm mb-6">Entertainment experiences players can buy to boost their character's mood. Actual mood boost = min(25, max(base, price ÷ 200)).</p>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach([
                ['label'=>'Total',        'val'=>$stats['total'],                              'color'=>'text-white'],
                ['label'=>'Active',       'val'=>$stats['active'],                             'color'=>'text-emerald-400'],
                ['label'=>'Cheapest',     'val'=>'KES '.number_format($stats['cheapest']),     'color'=>'text-amber-400'],
                ['label'=>'Priciest',     'val'=>'KES '.number_format($stats['priciest']),     'color'=>'text-orange-400'],
            ] as $s)
            <div class="rounded-2xl p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);">
                <p class="text-lg font-black {{ $s['color'] }}">{{ $s['val'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">{{ $s['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Activities grid --}}
    @if($activities->isEmpty())
    <div class="text-center py-16 rounded-3xl" style="background:rgba(255,255,255,.02);border:1px dashed rgba(255,255,255,.1);">
        <p class="text-4xl mb-3">🎡</p>
        <p class="text-gray-300 font-black">No activities yet</p>
        <p class="text-gray-500 text-sm mt-1">Run the FunWorldActivitySeeder or add one manually.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($activities as $a)
        <div class="card-in rounded-2xl p-5 flex flex-col gap-3 {{ $a->is_active ? '' : 'opacity-50' }}"
             style="background:linear-gradient(135deg,#2d1608,#0f172a);border:1px solid rgba(255,107,53,.25);">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">{{ $a->icon }}</span>
                    <div>
                        <p class="font-black text-white leading-tight">{{ $a->name }}</p>
                        <p class="text-xs text-orange-300 font-bold mt-0.5">KES {{ number_format($a->price) }}</p>
                    </div>
                </div>
                <button class="text-[10px] font-black px-2 py-1 rounded-lg {{ $a->is_active ? 'text-emerald-400' : 'text-gray-500' }}"
                        style="border:1px solid {{ $a->is_active ? 'rgba(16,185,129,.3)' : 'rgba(255,255,255,.1)' }};"
                        @click="fetch('{{ route('gameset.fun-world.toggle-active', $a) }}', {method:'POST', headers:{'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content,'Accept':'application/json'}}).then(()=>location.reload())">
                    {{ $a->is_active ? 'ACTIVE' : 'OFF' }}
                </button>
            </div>
            @if($a->description)
            <p class="text-xs text-gray-400 leading-snug">{{ $a->description }}</p>
            @endif
            <div class="flex items-center gap-2 text-[11px] font-bold">
                <span class="px-2 py-1 rounded-lg text-orange-300" style="background:rgba(255,107,53,.1);border:1px solid rgba(255,107,53,.2);">+{{ $a->moodBoost() }} mood</span>
                <span class="px-2 py-1 rounded-lg text-violet-300" style="background:rgba(139,92,246,.1);border:1px solid rgba(139,92,246,.2);">+{{ $a->xp_reward }} XP</span>
                <span class="px-2 py-1 rounded-lg text-gray-400" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">#{{ $a->sort_order }}</span>
            </div>
            <div class="flex gap-2 mt-auto pt-2">
                <button @click='openEdit(@json($a))'
                        class="flex-1 py-2 rounded-xl text-xs font-black text-white transition-colors"
                        style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);">
                    ✏️ Edit
                </button>
                <form method="POST" action="{{ route('gameset.fun-world.destroy', $a) }}" class="flex-1"
                      onsubmit="return confirm('Delete “{{ $a->name }}”?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 rounded-xl text-xs font-black text-red-400 transition-colors"
                            style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);">
                        🗑 Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Create / Edit modal --}}
<div x-show="showForm" x-cloak x-transition.opacity
     class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-3 sm:p-6"
     style="background:rgba(0,0,0,.85);backdrop-filter:blur(12px);"
     @click.self="showForm=false">
    <div x-show="showForm" class="w-full max-w-lg rounded-3xl overflow-hidden"
         style="background:linear-gradient(160deg,#0f172a,#2d1608);border:1px solid rgba(255,107,53,.35);max-height:92vh;display:flex;flex-direction:column;">
        <div class="px-6 py-4 flex items-center justify-between flex-shrink-0" style="border-bottom:1px solid rgba(255,255,255,.06);">
            <h2 class="font-black text-white" x-text="editing ? '✏️ Edit Activity' : '🎡 New Activity'"></h2>
            <button @click="showForm=false" class="text-gray-500 hover:text-white">✕</button>
        </div>
        <form :action="editing ? '{{ url('gameset/fun-world') }}/' + editing : '{{ route('gameset.fun-world.store') }}'" method="POST"
              class="overflow-y-auto flex-1 px-6 py-5 space-y-4" style="min-height:0;">
            @csrf
            <template x-if="editing"><input type="hidden" name="_method" value="PUT"></template>
            <div class="grid grid-cols-3 gap-3">
                <div class="col-span-2">
                    <label class="fw-label">Name</label>
                    <input name="name" x-model="form.name" required maxlength="80" class="fw-input" placeholder="Nyama Choma Hangout">
                </div>
                <div>
                    <label class="fw-label">Icon (emoji)</label>
                    <input name="icon" x-model="form.icon" required maxlength="8" class="fw-input text-center">
                </div>
            </div>
            <div>
                <label class="fw-label">Description</label>
                <input name="description" x-model="form.description" maxlength="200" class="fw-input" placeholder="Grilled meat and good vibes with friends.">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="fw-label">Price (KES)</label>
                    <input name="price" type="number" min="1" x-model.number="form.price" required class="fw-input">
                </div>
                <div>
                    <label class="fw-label">Base Mood Boost (1–25)</label>
                    <input name="mood_boost_base" type="number" min="1" max="25" x-model.number="form.mood_boost_base" required class="fw-input">
                </div>
                <div>
                    <label class="fw-label">XP Reward</label>
                    <input name="xp_reward" type="number" min="0" x-model.number="form.xp_reward" required class="fw-input">
                </div>
                <div>
                    <label class="fw-label">Sort Order</label>
                    <input name="sort_order" type="number" min="0" x-model.number="form.sort_order" class="fw-input">
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-gray-300 font-bold">
                <input type="hidden" name="is_active" :value="form.is_active ? 1 : 0">
                <input type="checkbox" x-model="form.is_active" class="rounded">
                Active (visible to players)
            </label>
            <div class="rounded-xl px-4 py-3 text-xs text-orange-300 leading-snug" style="background:rgba(255,107,53,.06);border:1px solid rgba(255,107,53,.15);">
                💡 Effective mood boost = min(25, max(base boost, price ÷ 200)). Higher-priced experiences give bigger boosts automatically.
            </div>
            <div class="flex gap-3 pt-1 pb-2">
                <button type="button" @click="showForm=false"
                        class="flex-1 py-3 rounded-xl text-sm font-bold text-gray-400"
                        style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);">Cancel</button>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl text-sm font-black text-white"
                        style="background:linear-gradient(135deg,#FF6B35,#f59e0b);box-shadow:0 4px 14px rgba(255,107,53,.35);">
                    <span x-text="editing ? 'Save Changes' : 'Create Activity'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
