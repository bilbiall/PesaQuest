<x-app-layout>
<style>
body{background:#07060f;}
.profile-card{background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);border-radius:1.5rem;padding:1.5rem;}
.dream-card{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);border-radius:1.25rem;padding:1.1rem;transition:all .25s;position:relative;overflow:hidden;}
.dream-card:hover{transform:translateY(-3px);border-color:rgba(245,158,11,.35);}
.dream-icon{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;background:rgba(245,158,11,.1);border:2px solid rgba(245,158,11,.3);margin:0 auto .75rem;}
.dream-icon img{width:70%;height:70%;object-fit:contain;}
.owned-ribbon{position:absolute;top:10px;right:-30px;transform:rotate(35deg);background:linear-gradient(135deg,#10b981,#059669);color:#fff;font-size:.6rem;font-weight:900;padding:.2rem 2rem;letter-spacing:.05em;}
@keyframes fadeUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
</style>

<div class="min-h-screen px-4 py-8 max-w-5xl mx-auto" style="background:#07060f;">
    <a href="{{ route('world') }}" class="text-gray-400 hover:text-white text-sm mb-3 inline-flex items-center gap-2">← Back to Game</a>
    <div class="flex items-center justify-between flex-wrap gap-3 mb-2" style="animation:fadeUp .4s ease both;">
        <div>
            <h1 class="text-2xl sm:text-3xl font-black text-white">🌟 Champions' Dreams</h1>
            <p class="text-sm text-gray-400 mt-1">Expensive. Aspirational. Never resellable. Once you claim one, it's yours forever — a permanent flex on your profile.</p>
        </div>
        <div class="stat-pill" style="background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);border-radius:.875rem;padding:.7rem 1.1rem;text-align:center;">
            <div class="text-lg font-black text-emerald-400">KES {{ number_format($balance) }}</div>
            <div class="text-[.6rem] text-gray-500 uppercase tracking-wide font-bold">Your Balance</div>
        </div>
    </div>

    @if(session('success'))
    <div class="mt-4 px-4 py-3 rounded-xl text-sm font-semibold text-emerald-300 border border-emerald-500/30" style="background:rgba(16,185,129,0.1);">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mt-4 px-4 py-3 rounded-xl text-sm font-semibold text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">⚠ {{ session('error') }}</div>
    @endif

    @if($ownedDreams->count())
    <div class="profile-card mt-6" style="animation:fadeUp .4s .05s ease both;">
        <h2 class="text-sm font-black text-white mb-4">🏆 Your Trophy Case ({{ $ownedDreams->count() }})</h2>
        <div class="flex flex-wrap gap-4">
            @foreach($ownedDreams as $pd)
            <div class="flex flex-col items-center gap-1 w-20 text-center">
                <div class="dream-icon" style="width:52px;height:52px;font-size:1.5rem;">
                    @if($pd->dream?->image_url)<img src="{{ $pd->dream->image_url }}" alt="">@else{{ $pd->dream?->icon ?? '🌟' }}@endif
                </div>
                <span class="text-[.65rem] text-gray-400 font-semibold leading-tight">{{ $pd->dream?->name }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6" style="animation:fadeUp .4s .1s ease both;">
        @foreach($dreams as $dream)
        @php $owned = in_array($dream->id, $ownedIds); @endphp
        <div class="dream-card" @if($dream->level_locked) style="opacity:.55;filter:grayscale(.4);" @endif>
            @if($owned)<div class="owned-ribbon">OWNED</div>@endif
            <div class="dream-icon">
                @if($dream->image_url)<img src="{{ $dream->image_url }}" alt="">@else{{ $dream->icon }}@endif
            </div>
            <h3 class="text-center font-black text-white text-sm">{{ $dream->name }}</h3>
            <p class="text-center text-xs text-gray-500 mt-1 mb-3 min-h-[2rem]">{{ $dream->tagline }}</p>
            <div class="text-center text-lg font-black text-amber-300 mb-3">KES {{ number_format($dream->price) }}</div>
            @if($owned)
                <div class="text-center text-xs font-bold text-emerald-400 py-2">✓ Claimed</div>
            @elseif($dream->level_locked)
                <div class="text-center text-xs font-bold text-gray-400 py-2">🔒 Reach Level {{ $dream->min_level }} to unlock</div>
            @else
                <form method="POST" action="{{ route('dreams.purchase', $dream) }}" onsubmit="return confirm('Claim {{ $dream->name }} for KES {{ number_format($dream->price) }}? This cannot be undone or resold.')">
                    @csrf
                    <button type="submit"
                            @disabled($balance < $dream->price)
                            class="w-full py-2 rounded-xl text-xs font-black text-white transition-all hover:scale-[1.02] disabled:opacity-40 disabled:hover:scale-100"
                            style="background:linear-gradient(135deg,#f59e0b,#b45309);">
                        {{ $balance < $dream->price ? 'Not enough yet' : 'Claim This Dream' }}
                    </button>
                </form>
            @endif
        </div>
        @endforeach
        @if($dreams->isEmpty())
        <p class="text-gray-500 text-sm col-span-full text-center py-10">No dreams available yet — check back soon!</p>
        @endif
    </div>
</div>
</x-app-layout>
