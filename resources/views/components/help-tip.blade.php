{{--
    Inline field-level help. Drop right inside a <label> (or any inline
    context) after the field's text, e.g.:
        <label class="form-label">Base Price (Ksh) *
            <x-help-tip text="What players pay to buy this asset." example="45000" />
        </label>
    `text` is the plain-language explanation of what the field does and how
    it affects gameplay. `example` (optional) is a realistic value — shown
    as a separate "e.g." line so it's visually distinct from the explanation.
    Teleports the popover to <body> so it always escapes clipped/scrolling
    parents (modals, overflow-y cards, etc).
--}}
@props(['text', 'example' => null])

@once
<style>
    .hlp { position:relative; display:inline-flex; vertical-align:middle; margin-left:.4em; }
    .hlp-ic {
        width:15px; height:15px; border-radius:50%; flex-shrink:0;
        display:inline-flex; align-items:center; justify-content:center;
        font-size:9.5px; font-weight:900; line-height:1; font-style:normal;
        color:#a5b4fc; background:rgba(99,102,241,.16); border:1px solid rgba(99,102,241,.4);
        cursor:help; padding:0; transition:all .15s;
    }
    .hlp-ic:hover, .hlp-ic:focus-visible { color:#fff; background:rgba(99,102,241,.4); outline:none; }
    .hlp-pop {
        position:fixed; z-index:99990; background:#14121f; color:#e5e7eb;
        border:1px solid rgba(99,102,241,.35); border-radius:.85rem; padding:.65rem .8rem;
        box-shadow:0 16px 40px rgba(0,0,0,.55); font-family:'Figtree',sans-serif;
        text-transform:none; letter-spacing:normal;
    }
    .hlp-pop-text { font-size:.76rem; font-weight:500; line-height:1.45; color:#d1d5db; margin:0; }
    .hlp-pop-eg { font-size:.72rem; font-weight:600; line-height:1.4; color:#93c5fd; margin:.4rem 0 0; }
    .hlp-pop-eg strong { color:#60a5fa; font-weight:800; text-transform:uppercase; font-size:.62rem; letter-spacing:.05em; margin-right:.3em; }
    @media (max-width:640px) { .hlp-pop { max-width:calc(100vw - 24px) !important; } }
</style>
@endonce

<span class="hlp"
      x-data="{
          open: false,
          top: 0, left: 0, w: 260,
          place() {
              const r = this.$refs.hlpIcon.getBoundingClientRect();
              this.w = Math.min(260, window.innerWidth - 24);
              this.top = r.bottom + 6;
              this.left = Math.min(Math.max(8, r.left - (this.w / 2) + 10), window.innerWidth - this.w - 8);
          }
      }"
      @click.away="open = false">
    <button type="button" x-ref="hlpIcon" class="hlp-ic" aria-label="What is this field for?"
            @click.stop.prevent="open = !open; place()"
            @mouseenter="open = true; place()" @mouseleave="open = false">?</button>
    <template x-teleport="body">
        <div class="hlp-pop" x-show="open" x-cloak x-transition.opacity.duration.100ms
             :style="`top:${top}px;left:${left}px;width:${w}px;`">
            <p class="hlp-pop-text">{{ $text }}</p>
            @if($example)
                <p class="hlp-pop-eg"><strong>e.g.</strong>{{ $example }}</p>
            @endif
        </div>
    </template>
</span>
