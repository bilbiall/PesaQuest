<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>World Map — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        .btn-mini { font-size:11px; font-weight:800; padding:.4rem .7rem; border-radius:.55rem; cursor:pointer; white-space:nowrap; }

        .cal-district {
            position:absolute; border-radius:.6rem; cursor:grab; user-select:none; touch-action:none;
            background:color-mix(in srgb, var(--dc) 22%, transparent);
            border:1.5px solid var(--dc);
            display:flex; align-items:flex-start; justify-content:flex-start; padding:4px 6px;
            font-size:10px; font-weight:900; color:#fff; text-shadow:0 1px 3px rgba(0,0,0,.8); z-index:5;
        }
        .cal-district:hover { background:color-mix(in srgb, var(--dc) 34%, transparent); z-index:6; }
        .cal-district.dragging { background:color-mix(in srgb, var(--dc) 45%, transparent); cursor:grabbing; z-index:7; }
        .cal-resize {
            position:absolute; right:-6px; bottom:-6px; width:14px; height:14px; border-radius:4px;
            background:#fff; border:2px solid var(--dc); cursor:nwse-resize; touch-action:none; z-index:8;
        }
        .cal-center {
            position:absolute; width:8px; height:8px; margin:-4px 0 0 -4px; border-radius:50%;
            background:#fff; border:1.5px solid #000; pointer-events:none; z-index:9;
        }
    </style>
</head>
<body class="text-white min-h-screen">
@include('gameset.partials.topnav', ['active' => 'world-map'])

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3 text-sm">
        <a href="{{ route('gameset.index') }}" class="text-gray-400 hover:text-white transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            GameSet
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold">🗺️ World Map</span>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">

    @if(session('success'))
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-emerald-300" style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if($errors->any())
    <div class="mb-6 rounded-2xl px-5 py-4 text-sm font-bold text-red-300" style="background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.2);">
        @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
    </div>
    @endif

    <div class="flex items-center justify-between mb-3">
        <h2 class="text-sm font-black text-amber-300">🎯 District Zones — drag to move, drag the white handle to resize</h2>
        <button type="submit" form="map-calibrator" class="btn-mini text-white" style="background:linear-gradient(135deg,#f59e0b,#d97706);">💾 Save Layout</button>
    </div>
    <p class="text-[11px] mb-4" style="color:rgba(255,255,255,.35);">
        Each rectangle is a district's tap-area on /world. The player pin's walking destination (small white dot) is always the exact center of the rectangle — move or resize a district here and both the tap zone and where the pin walks to update together, immediately, no code changes needed.
    </p>

    <form id="map-calibrator" method="POST" action="{{ route('gameset.world.positions.save') }}">
        @csrf
        <div id="mapCalibrator" class="rounded-2xl overflow-hidden mb-2" style="position:relative;">
            <img src="{{ asset('img/game/worldmap.webp') }}" alt="Pesa City world map" class="w-full h-auto block" draggable="false">
            @foreach($districts as $slug => $district)
            @php $pos = $positions[$slug] ?? ['left'=>45,'top'=>45,'width'=>10,'height'=>10]; @endphp
            <div class="cal-district" data-slug="{{ $slug }}"
                 style="left:{{ $pos['left'] }}%; top:{{ $pos['top'] }}%; width:{{ $pos['width'] }}%; height:{{ $pos['height'] }}%; --dc: {{ $district['color'] ?? '#818cf8' }};">
                {{ $district['icon'] ?? '📍' }} {{ $district['name'] ?? $slug }}
                <div class="cal-center" style="left:50%;top:50%;" title="Pin destination"></div>
                <div class="cal-resize"></div>
                <input type="hidden" class="pos-slug-input"   name="positions[{{ $slug }}][slug]"       value="{{ $slug }}">
                <input type="hidden" class="pos-left-input"   name="positions[{{ $slug }}][pos_left]"   value="{{ $pos['left'] }}">
                <input type="hidden" class="pos-top-input"    name="positions[{{ $slug }}][pos_top]"    value="{{ $pos['top'] }}">
                <input type="hidden" class="pos-width-input"  name="positions[{{ $slug }}][pos_width]"  value="{{ $pos['width'] }}">
                <input type="hidden" class="pos-height-input" name="positions[{{ $slug }}][pos_height]" value="{{ $pos['height'] }}">
            </div>
            @endforeach
        </div>
    </form>
    <p class="text-[11px] mb-8" style="color:rgba(255,255,255,.35);">This is the single source of truth for both the tap-area shown on /world and the player pin's destination — the two used to be separate hardcoded arrays that could disagree.</p>

</div>

<script>
    (function () {
        const board = document.getElementById('mapCalibrator');
        if (!board) return;

        function toPercent(clientX, clientY) {
            const rect = board.getBoundingClientRect();
            const left = Math.max(0, Math.min(100, (clientX - rect.left) / rect.width * 100));
            const top  = Math.max(0, Math.min(100, (clientY - rect.top) / rect.height * 100));
            return { left, top };
        }

        board.querySelectorAll('.cal-district').forEach(zone => {
            const leftInput   = zone.querySelector('.pos-left-input');
            const topInput    = zone.querySelector('.pos-top-input');
            const widthInput  = zone.querySelector('.pos-width-input');
            const heightInput = zone.querySelector('.pos-height-input');
            const resizer     = zone.querySelector('.cal-resize');

            let mode = null; // 'move' | 'resize'

            zone.addEventListener('pointerdown', e => {
                if (e.target === resizer) return; // handled by resizer's own listener
                mode = 'move';
                zone.classList.add('dragging');
                try { zone.setPointerCapture(e.pointerId); } catch (_) {}
                e.preventDefault();
            });
            zone.addEventListener('pointermove', e => {
                if (mode !== 'move') return;
                const { left, top } = toPercent(e.clientX, e.clientY);
                const w = parseFloat(widthInput.value);
                const h = parseFloat(heightInput.value);
                const clampedLeft = Math.min(left, 100 - w);
                const clampedTop  = Math.min(top, 100 - h);
                zone.style.left = clampedLeft.toFixed(2) + '%';
                zone.style.top  = clampedTop.toFixed(2) + '%';
                leftInput.value = clampedLeft.toFixed(2);
                topInput.value  = clampedTop.toFixed(2);
            });
            zone.addEventListener('pointerup', () => { if (mode === 'move') { zone.classList.remove('dragging'); mode = null; } });
            zone.addEventListener('pointercancel', () => { if (mode === 'move') { zone.classList.remove('dragging'); mode = null; } });

            resizer.addEventListener('pointerdown', e => {
                mode = 'resize';
                zone.classList.add('dragging');
                try { resizer.setPointerCapture(e.pointerId); } catch (_) {}
                e.preventDefault();
                e.stopPropagation();
            });
            resizer.addEventListener('pointermove', e => {
                if (mode !== 'resize') return;
                const rect = board.getBoundingClientRect();
                const originLeft = parseFloat(leftInput.value);
                const originTop  = parseFloat(topInput.value);
                const w = Math.max(3, Math.min(100 - originLeft, ((e.clientX - rect.left) / rect.width * 100) - originLeft));
                const h = Math.max(3, Math.min(100 - originTop,  ((e.clientY - rect.top) / rect.height * 100) - originTop));
                zone.style.width  = w.toFixed(2) + '%';
                zone.style.height = h.toFixed(2) + '%';
                widthInput.value  = w.toFixed(2);
                heightInput.value = h.toFixed(2);
                e.stopPropagation();
            });
            resizer.addEventListener('pointerup', e => { if (mode === 'resize') { zone.classList.remove('dragging'); mode = null; } e.stopPropagation(); });
            resizer.addEventListener('pointercancel', e => { if (mode === 'resize') { zone.classList.remove('dragging'); mode = null; } e.stopPropagation(); });
        });
    })();
</script>
</body>
</html>
