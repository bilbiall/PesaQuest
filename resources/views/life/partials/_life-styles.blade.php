{{-- Consolidated CSS for all 4 Life tabs (board/career/timeline/finances).
     Included ONCE in the shared shell's <head> — every tab's styling must be
     present regardless of which tab is currently active, since switching
     tabs only swaps #life-panel's content, never re-fetches <head>. --}}
<style>
    body { background: #07060f; font-family: 'Figtree', sans-serif; }
    [x-cloak] { display: none !important; }

    /* ═══ Shared ═══ */
    .glass {
        background: linear-gradient(135deg, rgba(255,255,255,0.045), rgba(255,255,255,0.015));
        border: 1px solid rgba(255,255,255,0.08);
        backdrop-filter: blur(12px);
    }
    .nav-pill {
        display: inline-flex; align-items: center; gap: 0.35rem;
        font-size: 0.7rem; font-weight: 600;
        padding: 0.3rem 0.7rem; border-radius: 0.5rem;
        border: 1px solid; transition: all .2s;
    }

    /* ═══ Life HQ (board) ═══ */
    .life-bg {
        background:
            radial-gradient(ellipse at top left,    rgba(16,185,129,0.09) 0%, transparent 50%),
            radial-gradient(ellipse at bottom right, rgba(99,102,241,0.09) 0%, transparent 50%),
            radial-gradient(ellipse at center,       rgba(245,158,11,0.04) 0%, transparent 70%),
            #07060f;
    }
    .char-banner {
        background:
            radial-gradient(ellipse at 70% 50%, rgba(99,102,241,0.15) 0%, transparent 60%),
            linear-gradient(135deg, rgba(16,185,129,0.06), rgba(99,102,241,0.06));
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .wallet-card {
        background: linear-gradient(135deg, rgba(16,185,129,0.2), rgba(5,150,105,0.08));
        border: 1px solid rgba(16,185,129,0.3);
        box-shadow: 0 0 40px rgba(16,185,129,0.07);
    }
    .wallet-card.deficit {
        background: linear-gradient(135deg, rgba(239,68,68,0.18), rgba(220,38,38,0.06));
        border-color: rgba(239,68,68,0.3);
        box-shadow: 0 0 40px rgba(239,68,68,0.07);
    }
    .asset-card { transition: all 0.25s ease; }
    .asset-card:hover { transform: translateY(-4px); box-shadow: 0 20px 48px -12px rgba(0,0,0,0.45); }
    .cat-vehicle    { background: linear-gradient(135deg,rgba(245,158,11,.13),rgba(234,88,12,.04));  border-color: rgba(245,158,11,.22); }
    .cat-vehicle:hover  { box-shadow: 0 20px 48px -12px rgba(245,158,11,.15); }
    .cat-property   { background: linear-gradient(135deg,rgba(59,130,246,.13),rgba(6,182,212,.04));   border-color: rgba(59,130,246,.22); }
    .cat-property:hover { box-shadow: 0 20px 48px -12px rgba(59,130,246,.15); }
    .cat-business   { background: linear-gradient(135deg,rgba(139,92,246,.13),rgba(167,139,250,.04)); border-color: rgba(139,92,246,.22); }
    .cat-business:hover { box-shadow: 0 20px 48px -12px rgba(139,92,246,.15); }
    .cat-investment { background: linear-gradient(135deg,rgba(16,185,129,.13),rgba(5,150,105,.04));   border-color: rgba(16,185,129,.22); }
    .cat-investment:hover { box-shadow: 0 20px 48px -12px rgba(16,185,129,.15); }
    .cat-gadget     { background: linear-gradient(135deg,rgba(100,116,139,.13),rgba(71,85,105,.04));  border-color: rgba(100,116,139,.22); }
    .bill-overdue { background: rgba(239,68,68,.08); border-color: rgba(239,68,68,.3); }
    .bill-urgent  { background: rgba(245,158,11,.07); border-color: rgba(245,158,11,.3); }
    .bill-soon    { background: rgba(251,191,36,.05); border-color: rgba(251,191,36,.2); }
    .bill-ok      { background: rgba(255,255,255,.02); border-color: rgba(255,255,255,.07); }
    .dot-earning { animation: earn-pulse 2s ease-in-out infinite; }
    @keyframes earn-pulse { 0%,100% { box-shadow: 0 0 0 0 rgba(16,185,129,.6); } 50% { box-shadow: 0 0 0 5px rgba(16,185,129,0); } }
    .toast-wrap { backdrop-filter: blur(12px); animation: slideIn .4s ease, fadeOut .5s ease 2.5s forwards; }
    @keyframes slideIn { from { transform: translateX(120%); opacity:0; } to { transform: translateX(0); opacity:1; } }
    @keyframes fadeOut { to { opacity:0; transform: translateX(120%); } }
    .milestone-bar { background: linear-gradient(90deg, #6366f1, #a78bfa, #818cf8); background-size: 200% 100%; animation: shimmer 2.5s linear infinite; }
    @keyframes shimmer { 0% { background-position:200% 0; } 100% { background-position:-200% 0; } }
    .chapter-bar { background: linear-gradient(90deg, #10b981, #6366f1); }

    /* ═══ Career ═══ */
    .career-bg {
        background:
            radial-gradient(ellipse at top left,    rgba(245,158,11,0.10) 0%, transparent 50%),
            radial-gradient(ellipse at bottom right, rgba(99,102,241,0.09) 0%, transparent 50%),
            #07060f;
    }
    .payslip-row { border-bottom: 1px solid rgba(255,255,255,0.05); }
    .ladder-rung { transition: all 0.2s; }
    .ladder-rung.current {
        background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(251,191,36,0.08));
        border-color: rgba(245,158,11,0.5);
        box-shadow: 0 0 20px rgba(245,158,11,0.1);
    }
    .ladder-rung.done { background: rgba(16,185,129,0.08); border-color: rgba(16,185,129,0.25); }
    .ladder-rung.future { border-color: rgba(255,255,255,0.06); background: rgba(255,255,255,0.02); }
    .field-card { transition: all 0.2s; border: 1px solid rgba(255,255,255,0.07); }
    .field-card:hover { border-color: rgba(255,255,255,0.15); transform: translateY(-2px); }

    /* ═══ Timeline (Life Story) ═══ */
    .timeline-line { width: 2px; background: linear-gradient(to bottom, rgba(139,92,246,0.6), rgba(99,102,241,0.1)); }
    .tl-extra { display: none; }
    .tl-chapter.tl-expanded .tl-extra { display: block; }
    .tl-more-btn {
        display: flex; align-items: center; gap: .4rem; margin-top: .75rem;
        padding: .55rem 1rem; border-radius: .75rem; font-size: .78rem; font-weight: 800;
        color: #a5b4fc; background: rgba(99,102,241,.1); border: 1px solid rgba(99,102,241,.25);
        cursor: pointer; transition: all .15s;
    }
    .tl-more-btn:hover { background: rgba(99,102,241,.18); color: #fff; }

    /* ═══ Finances ═══ */
    .fin-bg {
        background:
            radial-gradient(ellipse at top left,    rgba(16,185,129,0.10) 0%, transparent 50%),
            radial-gradient(ellipse at bottom right, rgba(56,189,248,0.08) 0%, transparent 50%),
            #07060f;
    }
    .fin-cta { transition: all .15s; }
    .fin-cta:hover { transform: translateY(-2px); }
</style>
