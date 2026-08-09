@props(['kicker', 'title', 'subtitle', 'toc' => [], 'html' => '', 'siblings' => []])

<div class="docs-wrap">
    <div style="flex:1;min-width:0;">
        <div class="docs-hero">
            <span class="docs-kicker">{{ $kicker }}</span>
            <h1>{{ $title }}</h1>
            <p>{{ $subtitle }}</p>
            @if(collect($siblings)->filter(fn($s) => !empty($s['href']))->isNotEmpty())
            <div class="docs-siblings">
                @foreach($siblings as $s)
                    @if(!empty($s['href']))
                    <a href="{{ $s['href'] }}" class="docs-sib">{{ $s['label'] }} @if(!empty($s['note']))<small>· {{ $s['note'] }}</small>@endif</a>
                    @endif
                @endforeach
            </div>
            @endif
        </div>

        @if(!empty($toc))
        {{-- Mobile TOC (collapsible) --}}
        <div class="docs-toc-mobile" x-data="{ open: false }">
            <button type="button" class="docs-toc-btn" @click="open = !open">
                📑 Jump to section <span style="margin-left:auto;" x-text="open ? '▲' : '▼'"></span>
            </button>
            <div class="docs-toc-panel" x-show="open" x-cloak @click="open = false">
                @foreach($toc as $item)
                    <a href="#{{ $item['slug'] }}" style="display:block;font-size:.8rem;font-weight:700;color:#c7d2fe;text-decoration:none;padding:.5rem .6rem;border-radius:.55rem;">{{ $item['text'] }}</a>
                @endforeach
            </div>
        </div>
        @endif

        <div style="display:flex;gap:2rem;align-items:flex-start;">
            @if(!empty($toc))
            <div class="docs-toc-col">
                <div class="docs-toc">
                    <p class="docs-toc-title">On this page</p>
                    @foreach($toc as $item)
                        <a href="#{{ $item['slug'] }}" data-docs-toc-link data-slug="{{ $item['slug'] }}">{{ $item['text'] }}</a>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="docs-prose">{!! $html !!}</div>
        </div>
    </div>
</div>

<script>
(function () {
    var links = Array.prototype.slice.call(document.querySelectorAll('[data-docs-toc-link]'));
    if (!links.length) return;
    var sections = links.map(function (l) { return document.getElementById(l.dataset.slug); }).filter(Boolean);
    if (!sections.length || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (!entry.isIntersecting) return;
            links.forEach(function (l) { l.classList.remove('docs-toc-active'); });
            var active = links.find(function (l) { return l.dataset.slug === entry.target.id; });
            if (active) active.classList.add('docs-toc-active');
        });
    }, { rootMargin: '-15% 0px -75% 0px' });

    sections.forEach(function (s) { observer.observe(s); });
})();
</script>
