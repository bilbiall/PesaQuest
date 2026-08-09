<style>
    body { background:#07060f; font-family:'Figtree',sans-serif; }
    [x-cloak] { display:none !important; }

    .docs-wrap { max-width:78rem; margin:0 auto; padding:1.75rem 1rem 5rem; display:flex; gap:2rem; align-items:flex-start; }
    .docs-hero { border-radius:1.5rem; padding:1.75rem 1.5rem; margin-bottom:1.75rem; border:1px solid rgba(99,102,241,.22);
                 background:linear-gradient(135deg, rgba(99,102,241,.12), rgba(139,92,246,.05) 60%, transparent); }
    .docs-kicker { display:inline-block; font-size:.62rem; font-weight:900; letter-spacing:.16em; text-transform:uppercase;
                   color:#34d399; background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.3);
                   padding:.3rem .8rem; border-radius:999px; margin-bottom:.75rem; }
    .docs-hero h1 { font-size:1.65rem; font-weight:900; color:#fff; letter-spacing:-.02em; }
    .docs-hero p { color:#9ca3af; font-size:.9rem; margin-top:.4rem; max-width:42rem; line-height:1.5; }
    .docs-siblings { display:flex; flex-wrap:wrap; gap:.5rem; margin-top:1.1rem; }
    .docs-sib { display:inline-flex; align-items:center; gap:.4rem; font-size:.75rem; font-weight:800; color:#c7d2fe;
                background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.1); border-radius:999px;
                padding:.4rem .9rem; text-decoration:none; transition:all .15s; }
    .docs-sib:hover { background:rgba(99,102,241,.14); border-color:rgba(99,102,241,.4); color:#fff; }
    .docs-sib small { color:#6b7280; font-weight:600; }

    /* ── Sidebar TOC ── */
    .docs-toc-col { flex-shrink:0; width:15.5rem; display:none; }
    .docs-toc { position:sticky; top:5.5rem; max-height:calc(100vh - 7rem); overflow-y:auto; padding-right:.5rem; }
    .docs-toc-title { font-size:.65rem; font-weight:900; text-transform:uppercase; letter-spacing:.12em; color:#6b7280; margin-bottom:.6rem; }
    .docs-toc a { display:block; font-size:.78rem; font-weight:700; color:#9ca3af; text-decoration:none; padding:.4rem .65rem;
                  border-radius:.6rem; line-height:1.35; transition:all .12s; }
    .docs-toc a:hover { color:#fff; background:rgba(255,255,255,.05); }
    .docs-toc a.docs-toc-active { color:#a5b4fc; background:rgba(99,102,241,.14); }
    @media (min-width:1024px) { .docs-toc-col { display:block; } }

    /* Mobile TOC drawer trigger */
    .docs-toc-mobile { display:block; margin-bottom:1.25rem; }
    @media (min-width:1024px) { .docs-toc-mobile { display:none; } }
    .docs-toc-btn { display:flex; align-items:center; gap:.5rem; width:100%; text-align:left; padding:.75rem 1rem; border-radius:1rem;
                    background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); color:#c7d2fe; font-size:.82rem; font-weight:800; cursor:pointer; }
    .docs-toc-panel { margin-top:.5rem; padding:.5rem; border-radius:1rem; background:#12101f; border:1px solid rgba(99,102,241,.25); max-height:22rem; overflow-y:auto; }

    /* ── Prose (rendered markdown) ── */
    .docs-prose { flex:1; min-width:0; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.07); border-radius:1.5rem; padding:2rem 2.25rem; }
    @media (max-width:640px) { .docs-prose { padding:1.5rem 1.25rem; } }
    .docs-prose h1 { font-size:1.5rem; font-weight:900; color:#fff; margin:0 0 .5rem; letter-spacing:-.01em; }
    .docs-prose h2 { font-size:1.2rem; font-weight:900; color:#fff; margin:2.2rem 0 .9rem; padding-top:1.4rem; border-top:1px solid rgba(255,255,255,.08); letter-spacing:-.01em; }
    .docs-prose h1 + h2, .docs-prose > h2:first-child { border-top:none; padding-top:0; margin-top:0; }
    .docs-prose h3 { font-size:.98rem; font-weight:800; color:#c7d2fe; margin:1.6rem 0 .6rem; }
    .docs-prose p { font-size:.87rem; line-height:1.7; color:#d1d5db; margin:0 0 1rem; }
    .docs-prose ul, .docs-prose ol { margin:0 0 1rem 1.3rem; color:#d1d5db; font-size:.87rem; line-height:1.7; }
    .docs-prose li { margin-bottom:.3rem; }
    .docs-prose li > ul, .docs-prose li > ol { margin-top:.3rem; }
    .docs-prose strong { color:#fff; font-weight:800; }
    .docs-prose em { color:#c7d2fe; }
    .docs-prose a { color:#818cf8; font-weight:700; text-decoration:underline; text-decoration-color:rgba(129,140,248,.35); }
    .docs-prose a:hover { color:#a5b4fc; }
    .docs-prose hr { border:none; border-top:1px solid rgba(255,255,255,.08); margin:2rem 0; }
    .docs-prose code { background:rgba(99,102,241,.14); color:#c7d2fe; border-radius:.35rem; padding:.1rem .4rem; font-size:.82em; font-family:ui-monospace,SFMono-Regular,Menlo,monospace; }
    .docs-prose pre { background:#0d0b1a; border:1px solid rgba(255,255,255,.08); border-radius:.9rem; padding:1rem 1.1rem; overflow-x:auto; margin:0 0 1rem; }
    .docs-prose pre code { background:none; padding:0; }
    .docs-prose blockquote { border-left:3px solid rgba(99,102,241,.5); background:rgba(99,102,241,.06); border-radius:0 .8rem .8rem 0;
                              padding:.75rem 1.1rem; margin:0 0 1.2rem; color:#c7d2fe; font-size:.85rem; line-height:1.65; }
    .docs-prose blockquote p:last-child { margin-bottom:0; }
    .docs-prose blockquote > blockquote { margin-top:.6rem; }
    .docs-prose table { width:100%; border-collapse:collapse; margin:0 0 1.3rem; font-size:.82rem; display:block; overflow-x:auto; white-space:nowrap; }
    .docs-prose table thead { background:rgba(255,255,255,.04); }
    .docs-prose th, .docs-prose td { border:1px solid rgba(255,255,255,.08); padding:.55rem .75rem; text-align:left; white-space:normal; }
    .docs-prose th { color:#a5b4fc; font-weight:800; font-size:.72rem; text-transform:uppercase; letter-spacing:.04em; }
    .docs-prose td { color:#d1d5db; }
</style>
