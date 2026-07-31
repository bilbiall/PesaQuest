{{-- Shared ▲/▼ vote widget styles + JS. Include ONCE per forum page.
     Widget markup contract:
     <span class="fv-wrap" data-type="topic|reply" data-id="N">
        <button class="fv-btn fv-up [fv-on]">▲</button>
        <b class="fv-score">0</b>
        <button class="fv-btn fv-down [fv-dn]">▼</button>
     </span> --}}
<style>
    .fv-wrap { display:inline-flex; align-items:center; gap:2px; border-radius:999px; padding:2px 4px;
               background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.09); }
    .fv-btn  { border:none; background:none; cursor:pointer; font-size:11px; line-height:1; padding:4px 6px;
               border-radius:999px; color:#6b7280; transition:all .12s; }
    .fv-btn:hover { color:#fff; background:rgba(255,255,255,0.08); transform:scale(1.15); }
    .fv-btn.fv-on { color:#34d399; }
    .fv-btn.fv-dn { color:#f87171; }
    .fv-score { font-size:11.5px; font-weight:900; color:#d1d5db; min-width:18px; text-align:center; }
    #fv-toast { position:fixed; bottom:24px; left:50%; transform:translateX(-50%); z-index:9999;
                background:#1a1830; border:1px solid rgba(245,158,11,0.4); color:#fcd34d;
                font-size:12px; font-weight:800; padding:10px 18px; border-radius:12px; display:none; }
</style>
<div id="fv-toast"></div>
<script>
    async function fvVote(btn, dir) {
        const wrap = btn.closest('.fv-wrap');
        try {
            const res = await fetch('{{ route('forums.vote') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                },
                body: JSON.stringify({ type: wrap.dataset.type, id: wrap.dataset.id, dir }),
            });
            const d = await res.json();
            if (!res.ok) { fvToast(d.error || 'Could not vote.'); return; }
            wrap.querySelector('.fv-score').textContent = d.score;
            wrap.querySelector('.fv-up').classList.toggle('fv-on', d.my_vote === 1);
            wrap.querySelector('.fv-down').classList.toggle('fv-dn', d.my_vote === -1);
        } catch (e) { fvToast('Network error — try again.'); }
    }
    function fvToast(msg) {
        const t = document.getElementById('fv-toast');
        t.textContent = msg; t.style.display = 'block';
        clearTimeout(t._h); t._h = setTimeout(() => t.style.display = 'none', 2500);
    }
</script>
