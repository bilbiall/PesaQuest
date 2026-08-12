<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>All Assets — PesaQuest Marketplace</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="{{ asset('js/icons.js') }}"></script>
<style>
*{box-sizing:border-box;}
body{background:#080712;font-family:'Figtree',sans-serif;color:#fff;min-height:100vh;}
[x-cloak]{display:none!important;}

@keyframes popIn{from{opacity:0;transform:scale(.96) translateY(8px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes iconbob{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-4px) scale(1.03)}}
@keyframes shimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}

/* Top Nav */
.top-nav{display:flex;align-items:center;justify-content:space-between;padding:.875rem 2rem;border-bottom:1px solid rgba(255,255,255,.05);backdrop-filter:blur(12px);background:rgba(8,7,18,.9);position:sticky;top:0;z-index:40;}
.nav-left{display:flex;align-items:center;gap:1.5rem;}
.nav-back{display:flex;align-items:center;gap:.4rem;color:rgba(255,255,255,.5);font-size:.82rem;font-weight:600;text-decoration:none;transition:color .2s;}
.nav-back:hover{color:#fff;}
.nav-breadcrumb{display:flex;align-items:center;gap:.5rem;font-size:.8rem;color:rgba(255,255,255,.3);}
.nav-breadcrumb a{color:rgba(255,255,255,.4);text-decoration:none;transition:color .2s;}
.nav-breadcrumb a:hover{color:rgba(255,255,255,.7);}
.nav-stats{display:flex;align-items:center;gap:1.25rem;}
.nav-stat{font-size:.78rem;color:rgba(255,255,255,.45);}
.nav-stat span{color:#10b981;font-weight:800;}
.nav-stat.bal span{color:#818cf8;}
.nav-portfolio-btn{display:flex;align-items:center;gap:.5rem;padding:.45rem 1rem;border-radius:.75rem;font-size:.78rem;font-weight:800;color:#fff;text-decoration:none;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);transition:all .2s;}
.nav-portfolio-btn:hover{background:rgba(99,102,241,.35);}

/* Page Header */
.page-header{padding:1.5rem 2rem 1rem;border-bottom:1px solid rgba(255,255,255,.04);}
.ph-inner{max-width:1400px;margin:0 auto;display:flex;align-items:flex-start;gap:1.5rem;}
.ph-icon{width:56px;height:56px;border-radius:1.25rem;display:flex;align-items:center;justify-content:center;font-size:1.75rem;background:rgba(99,102,241,.15);flex-shrink:0;}
.ph-title{font-size:2rem;font-weight:900;color:#fff;line-height:1;}
.ph-count{font-size:.82rem;color:rgba(255,255,255,.35);margin-top:.3rem;}
.ph-search-wrap{flex:1;position:relative;max-width:460px;}
.ph-search{width:100%;padding:.75rem 1rem .75rem 2.75rem;border-radius:1rem;font-size:.875rem;font-weight:500;color:#fff;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);outline:none;transition:all .2s;font-family:inherit;}
.ph-search:focus{background:rgba(255,255,255,.08);border-color:rgba(99,102,241,.45);box-shadow:0 0 0 3px rgba(99,102,241,.12);}
.ph-search::placeholder{color:rgba(255,255,255,.3);}
.ph-search-icon{position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;}
.search-suggestions{position:absolute;top:calc(100% + 6px);left:0;right:0;border-radius:1rem;overflow:hidden;z-index:50;background:#0f0e1a;border:1px solid rgba(255,255,255,.1);box-shadow:0 20px 60px rgba(0,0,0,.5);animation:slideDown .15s ease both;}
.suggestion-item{display:flex;align-items:center;gap:.75rem;padding:.75rem 1rem;cursor:pointer;transition:background .15s;border-bottom:1px solid rgba(255,255,255,.04);}
.suggestion-item:last-child{border-bottom:none;}
.suggestion-item:hover{background:rgba(99,102,241,.12);}
.suggestion-icon{width:32px;height:32px;border-radius:.625rem;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;background:rgba(255,255,255,.06);}
.suggestion-name{font-size:.82rem;font-weight:700;color:#fff;}
.suggestion-meta{font-size:.7rem;color:rgba(255,255,255,.35);margin-top:.1rem;}
.suggestion-badge{font-size:.6rem;font-weight:900;padding:.1rem .45rem;border-radius:9999px;margin-left:.5rem;text-transform:uppercase;}
.ph-controls{display:flex;align-items:center;gap:.75rem;margin-left:auto;}
.ph-sort{padding:.65rem .9rem;border-radius:.875rem;font-size:.8rem;font-weight:600;color:rgba(255,255,255,.7);background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);outline:none;cursor:pointer;font-family:inherit;}
.ph-sort option{background:#0f0e1a;}
.view-toggle{display:flex;border-radius:.75rem;overflow:hidden;border:1px solid rgba(255,255,255,.1);}
.view-btn{width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;background:rgba(255,255,255,.04);color:rgba(255,255,255,.4);}
.view-btn.active{background:rgba(99,102,241,.2);color:#818cf8;}

/* Section tabs */
.section-tabs{padding:.875rem 2rem;border-bottom:1px solid rgba(255,255,255,.04);overflow-x:auto;scrollbar-width:none;}
.section-tabs::-webkit-scrollbar{display:none;}
.section-tabs-inner{display:flex;gap:.5rem;max-width:1400px;margin:0 auto;}
.sec-tab{display:flex;align-items:center;gap:.4rem;padding:.5rem .9rem;border-radius:.75rem;font-size:.78rem;font-weight:700;white-space:nowrap;cursor:pointer;text-decoration:none;transition:all .2s;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);color:rgba(255,255,255,.45);}
.sec-tab:hover{background:rgba(255,255,255,.07);color:rgba(255,255,255,.7);}
.sec-tab.active{background:rgba(99,102,241,.15);border-color:rgba(99,102,241,.4);color:#fff;}

/* Main layout */
.main-layout{display:grid;grid-template-columns:260px 1fr;gap:0;max-width:1400px;margin:0 auto;}

/* Sidebar */
.sidebar{padding:1.25rem 1.5rem;border-right:1px solid rgba(255,255,255,.05);min-height:calc(100vh - 160px);position:sticky;top:56px;height:fit-content;}
.sb-section{margin-bottom:1.75rem;}
.sb-label{font-size:.68rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:.75rem;}

/* Sidebar Category List */
.sb-cat-item{display:flex;align-items:center;justify-content:space-between;padding:.55rem .65rem;border-radius:.75rem;cursor:pointer;transition:all .2s;text-decoration:none;}
.sb-cat-item:hover{background:rgba(255,255,255,.05);}
.sb-cat-item.active{background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);}
.sb-cat-item.active .sb-cat-name{color:#fff;}
.sb-cat-item.active .sb-cat-count{background:rgba(99,102,241,.3);color:#c4b5fd;}
.sb-cat-left{display:flex;align-items:center;gap:.6rem;}
.sb-cat-name{font-size:.82rem;font-weight:700;color:rgba(255,255,255,.6);transition:color .2s;}
.sb-cat-count{font-size:.68rem;font-weight:800;padding:.1rem .45rem;border-radius:9999px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.4);}

/* Price range */
.price-inputs{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;}
.price-input{padding:.5rem .65rem;border-radius:.625rem;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.7);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);outline:none;width:100%;font-family:inherit;}
.price-input:focus{border-color:rgba(99,102,241,.45);}
.price-input::placeholder{color:rgba(255,255,255,.25);}

/* Checkboxes */
.sb-checkbox{display:flex;align-items:center;gap:.65rem;padding:.4rem 0;cursor:pointer;user-select:none;}
.sb-checkbox input[type=checkbox]{width:16px;height:16px;border-radius:.25rem;accent-color:#6366f1;cursor:pointer;}
.sb-checkbox-label{font-size:.8rem;font-weight:600;color:rgba(255,255,255,.6);}

/* Income range chips */
.income-chips{display:flex;flex-wrap:wrap;gap:.4rem;}
.income-chip{padding:.35rem .7rem;border-radius:9999px;font-size:.72rem;font-weight:700;cursor:pointer;transition:all .2s;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.5);}
.income-chip:hover{background:rgba(255,255,255,.09);}
.income-chip.active{background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.4);color:#c4b5fd;}

/* Clear filters */
.clear-filters-btn{display:flex;align-items:center;justify-content:center;gap:.5rem;padding:.65rem;border-radius:.875rem;font-size:.78rem;font-weight:700;cursor:pointer;color:rgba(255,255,255,.4);background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);width:100%;transition:all .2s;font-family:inherit;}
.clear-filters-btn:hover{background:rgba(255,255,255,.08);color:rgba(255,255,255,.7);}

/* Asset grid */
.assets-main{padding:1.25rem 1.5rem;}
.assets-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
.assets-grid.list-view{grid-template-columns:1fr;}

/* Asset Cards */
.asset-card{border-radius:1.25rem;overflow:hidden;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);cursor:pointer;transition:transform .22s,box-shadow .22s,border-color .22s;animation:popIn .3s ease both;display:flex;flex-direction:column;}
.asset-card:hover{transform:translateY(-4px);box-shadow:0 16px 48px rgba(0,0,0,.45);border-color:rgba(99,102,241,.28);}
.asset-card.owned{border-color:rgba(139,92,246,.28);}

/* List view card */
.list-card{flex-direction:row;border-radius:1rem;}
.list-card .card-img{width:120px;min-width:120px;height:auto;min-height:110px;flex-shrink:0;border-radius:0;}
/* Two-row body: name/description on top, amounts + button beneath */
.list-card .card-body{flex-direction:column;align-items:stretch;gap:.6rem;padding:.75rem 1rem;}
.list-card .card-body-left{flex:none;}
.list-card .card-bottom{display:flex;align-items:center;justify-content:space-between;gap:1rem;border-top:1px solid rgba(255,255,255,.06);padding-top:.6rem;}
.list-card .card-stats{display:flex;gap:1.75rem;border-top:none;padding-top:0;margin-top:0;}
.list-card .card-view-btn{margin-top:0;min-width:120px;}
/* In grid mode the wrapper is invisible — original column layout untouched */
.card-bottom{display:contents;}

.card-img{position:relative;height:148px;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.card-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.6;}
.card-img-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1),rgba(0,0,0,.5));}
.card-emoji{font-size:3.2rem;line-height:1;animation:iconbob 3.5s ease-in-out infinite;position:relative;z-index:2;filter:drop-shadow(0 4px 10px rgba(0,0,0,.4));}
.card-badge{position:absolute;top:10px;left:10px;font-size:.62rem;font-weight:900;padding:.25rem .6rem;border-radius:9999px;letter-spacing:.05em;z-index:3;text-transform:uppercase;}
.card-heart{position:absolute;top:10px;right:10px;width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.4);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.1);font-size:.75rem;z-index:3;cursor:pointer;}
.card-owned-badge{position:absolute;bottom:8px;left:10px;font-size:.6rem;font-weight:900;padding:.2rem .55rem;border-radius:9999px;background:rgba(139,92,246,.7);z-index:3;}

.card-body{padding:.875rem .9rem 1rem;display:flex;flex-direction:column;flex:1;}
.card-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.66rem;font-weight:700;padding:.2rem .55rem;border-radius:9999px;margin-bottom:.5rem;width:fit-content;}
.card-name{font-size:.875rem;font-weight:900;color:#fff;line-height:1.25;margin-bottom:.25rem;}
.card-desc{font-size:.71rem;color:rgba(255,255,255,.4);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;}
.card-stats{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;margin-top:.65rem;padding-top:.65rem;border-top:1px solid rgba(255,255,255,.06);}
.card-stat-lbl{font-size:.57rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em;}
.card-stat-val{font-size:.8rem;font-weight:900;margin-top:.1rem;}
.card-stat-val.pos{color:#10b981;}
.card-stat-val.neg{color:#f87171;}
.card-stat-val.neutral{color:#fff;}
.card-view-btn{margin-top:.65rem;padding:.5rem;border-radius:.75rem;font-size:.73rem;font-weight:800;text-align:center;background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.18);color:#818cf8;transition:all .2s;}
.card-view-btn:hover{background:rgba(99,102,241,.22);border-color:rgba(99,102,241,.38);color:#fff;}

/* Pagination */
.pagination-wrap{padding:1.5rem;display:flex;align-items:center;justify-content:space-between;border-top:1px solid rgba(255,255,255,.05);}
.page-info{font-size:.8rem;color:rgba(255,255,255,.35);}
.page-btns{display:flex;gap:.35rem;}
.page-btn{width:36px;height:36px;border-radius:.625rem;display:flex;align-items:center;justify-content:center;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.5);text-decoration:none;}
.page-btn:hover:not(.disabled){background:rgba(255,255,255,.08);color:#fff;}
.page-btn.current{background:rgba(99,102,241,.25);border-color:rgba(99,102,241,.45);color:#fff;}
.page-btn.disabled{opacity:.3;cursor:default;}

/* Empty state */
.empty-state{grid-column:1/-1;padding:5rem 2rem;text-align:center;}
.empty-icon{font-size:4rem;margin-bottom:1rem;opacity:.5;}
.empty-title{font-size:1rem;font-weight:800;color:rgba(255,255,255,.4);margin-bottom:.5rem;}
.empty-sub{font-size:.82rem;color:rgba(255,255,255,.25);}

/* Modal */
@keyframes modal-enter{from{opacity:0;transform:translateY(20px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.modal-enter{animation:modal-enter .28s cubic-bezier(.34,1.56,.64,1) both;}
.cat-vehicle   {background:linear-gradient(145deg,#92400e,#b45309,#1c1917);}
.cat-property  {background:linear-gradient(145deg,#064e3b,#065f46,#0f172a);}
.cat-business  {background:linear-gradient(145deg,#3730a3,#4c1d95,#1e1b4b);}
.cat-investment{background:linear-gradient(145deg,#1e3a8a,#1e40af,#0f172a);}
.cat-gadget    {background:linear-gradient(145deg,#831843,#9d174d,#1a1025);}
.icon-bob-sm{animation:iconbob 3.5s ease-in-out infinite;}
.afford-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden;}
.afford-fill{height:100%;border-radius:3px;transition:width .8s ease;}

/* ── MOBILE RESPONSIVE ────────────────────────── */
.mob-show{display:none!important;}
.desk-show{display:flex;}

/* Mobile nav */
.mob-nav-all{display:none;align-items:center;justify-content:space-between;padding:.75rem 1rem;border-bottom:1px solid rgba(255,255,255,.06);background:rgba(8,7,18,.97);position:sticky;top:0;z-index:40;}
.mob-nav-left{display:flex;align-items:center;gap:.6rem;}
.mob-nav-back{display:flex;align-items:center;gap:.3rem;color:rgba(255,255,255,.6);font-size:.82rem;font-weight:700;text-decoration:none;}
.mob-nav-back svg{width:18px;height:18px;}
.mob-nav-title{font-size:.92rem;font-weight:900;color:#fff;}
.mob-nav-portfolio{display:flex;align-items:center;gap:.3rem;padding:.4rem .85rem;border-radius:.625rem;font-size:.75rem;font-weight:800;color:#fff;text-decoration:none;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);}

/* Mobile chips */
.mob-filter-wrap{display:none;flex-direction:column;gap:0;border-bottom:1px solid rgba(255,255,255,.05);}
.mob-chip-row{display:flex;gap:.45rem;padding:.6rem 1rem;overflow-x:auto;scrollbar-width:none;}
.mob-chip-row::-webkit-scrollbar{display:none;}
.mob-chip{display:flex;align-items:center;gap:.3rem;padding:.4rem .75rem;border-radius:9999px;font-size:.72rem;font-weight:700;white-space:nowrap;text-decoration:none;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.45);flex-shrink:0;}
.mob-chip.active{background:rgba(99,102,241,.18);border-color:rgba(99,102,241,.4);color:#fff;}
.mob-chip-divider{width:1px;background:rgba(255,255,255,.07);flex-shrink:0;align-self:stretch;margin:.2rem 0;}
.mob-search-wrap{padding:.5rem 1rem .6rem;position:relative;}
.mob-search{width:100%;padding:.65rem 1rem .65rem 2.5rem;border-radius:.875rem;font-size:.85rem;font-weight:500;color:#fff;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);outline:none;font-family:inherit;}
.mob-search::placeholder{color:rgba(255,255,255,.3);}
.mob-search:focus{border-color:rgba(99,102,241,.4);}
.mob-search-icon{position:absolute;left:1.6rem;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;}

/* Mobile sort bar */
.mob-sort-bar{display:none;align-items:center;justify-content:space-between;padding:.5rem 1rem;border-bottom:1px solid rgba(255,255,255,.05);}
.mob-sort-select{padding:.4rem .65rem;border-radius:.625rem;font-size:.75rem;font-weight:600;color:rgba(255,255,255,.7);background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);outline:none;font-family:inherit;}
.mob-sort-select option{background:#0f0e1a;}
.mob-view-toggle{display:flex;border-radius:.625rem;overflow:hidden;border:1px solid rgba(255,255,255,.1);}
.mob-view-btn{width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .2s;background:rgba(255,255,255,.04);color:rgba(255,255,255,.4);}
.mob-view-btn.active{background:rgba(99,102,241,.2);color:#818cf8;}

/* Bottom bar */
.all-bottom-bar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:50;background:rgba(7,6,15,.98);border-top:1px solid rgba(255,255,255,.07);}
.all-bb-inner{display:flex;align-items:stretch;}
.all-bb-tab{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;padding:.6rem .25rem .5rem;text-decoration:none;color:rgba(255,255,255,.35);transition:color .2s;font-size:.58rem;font-weight:700;letter-spacing:.02em;}
.all-bb-tab svg{width:20px;height:20px;stroke-width:1.8;}
.all-bb-tab.active,.all-bb-tab-active{color:#818cf8;}
.all-bb-tab.active svg,.all-bb-tab-active svg{stroke:#818cf8;}

@media(max-width:1024px){
    .main-layout{grid-template-columns:220px 1fr;}
    .assets-grid{grid-template-columns:repeat(3,1fr);}
}
@media(max-width:767px){
    body{padding-bottom:64px;}
    .mob-show{display:flex!important;}
    .desk-show{display:none!important;}

    /* Hide desktop nav + page-header + section-tabs + sidebar on mobile */
    .top-nav{display:none!important;}
    .page-header{display:none!important;}
    .section-tabs{display:none!important;}
    .sidebar{display:none!important;}

    /* Show mobile nav + chips */
    .mob-nav-all{display:flex;}
    .mob-filter-wrap{display:flex;}
    .mob-sort-bar{display:flex;}
    .all-bottom-bar{display:block;}

    /* Full-width, 2 cards per row */
    .main-layout{grid-template-columns:1fr;padding:0!important;}
    .assets-main{padding:.5rem .75rem;}
    .assets-grid{grid-template-columns:repeat(2,1fr);gap:.6rem;}
    .pagination-wrap{flex-direction:column;gap:.75rem;text-align:center;padding:1rem .875rem;}

    /* Compact vertical card — image on top, name, then stats, then the
       View Details button last so everything reads top-to-bottom cleanly
       instead of being squeezed into one row. */
    .asset-card{border-radius:.875rem;}
    .asset-card .card-img{height:88px;}
    .asset-card .card-emoji{font-size:1.9rem;}
    .asset-card .card-badge{font-size:.5rem;padding:.1rem .3rem;top:6px;left:6px;}
    .asset-card .card-heart{width:22px;height:22px;font-size:.62rem;top:6px;right:6px;}
    .asset-card .card-owned-badge{font-size:.5rem;padding:.1rem .3rem;bottom:6px;}
    .asset-card .card-body{padding:.6rem .65rem .7rem;}
    .asset-card .card-chip{margin-bottom:.3rem;font-size:.56rem;padding:.12rem .4rem;}
    .asset-card .card-name{font-size:.76rem;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:0;}
    .asset-card .card-desc{display:none;}
    .asset-card .card-stats{gap:.35rem;margin-top:.5rem;padding-top:.5rem;}
    .asset-card .card-stat-lbl{font-size:.5rem;}
    .asset-card .card-stat-val{font-size:.72rem;}
    .asset-card .card-view-btn{margin-top:.5rem;padding:.45rem;font-size:.68rem;}

    /* Card left info section */
    .mob-card-info{flex:1;min-width:0;}
}
</style>
</head>
<body x-data="allMarketplace()">

{{-- ═══════════════ MOBILE NAV ═══════════════ --}}
<nav class="mob-nav-all">
    <div class="mob-nav-left">
        <a href="{{ route('marketplace') }}" class="mob-nav-back">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        </a>
        <span class="mob-nav-title">All Assets</span>
    </div>
    <a href="{{ route('portfolio') }}" class="mob-nav-portfolio"><x-icon name="bar-chart" class="w-3.5 h-3.5 inline-block" /> Portfolio</a>
</nav>

{{-- ═══════════════ MOBILE SEARCH + FILTER CHIPS ═══════════════ --}}
<div class="mob-filter-wrap">
    {{-- Search --}}
    <div class="mob-search-wrap" x-data="searchBox()">
        <span class="mob-search-icon">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
        </span>
        <input type="text" class="mob-search" placeholder="Search assets…"
               x-model="query" @input.debounce.300ms="fetchSuggestions()"
               @keydown.enter.prevent="if(suggestions.length) goToFirst()" autocomplete="off">
        <div class="search-suggestions" x-show="suggestions.length > 0 && query.length >= 3" x-cloak @click.outside="suggestions=[]">
            <template x-for="s in suggestions" :key="s.id">
                <div class="suggestion-item" @click="selectSuggestion(s)">
                    <div class="suggestion-icon"><span class="w-4 h-4" x-html="pqIcon(s.icon, 'w-4 h-4')"></span></div>
                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:.4rem;">
                            <span class="suggestion-name" x-text="s.name"></span>
                        </div>
                        <div class="suggestion-meta">
                            <span x-text="s.category"></span> · <span x-text="s.price"></span>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Category chips --}}
    <div class="mob-chip-row">
        @php $currentCat = request('cat','all'); @endphp
        <a href="{{ route('marketplace.all', request()->except('cat','page')) }}"
           class="mob-chip {{ $currentCat === 'all' ? 'active' : '' }}"><x-icon name="store" class="w-3 h-3 inline-block" /> All</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), ['cat'=>'vehicle'])) }}"
           class="mob-chip {{ $currentCat === 'vehicle' ? 'active' : '' }}"><x-icon name="car" class="w-3 h-3 inline-block" /> Vehicles</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), ['cat'=>'property'])) }}"
           class="mob-chip {{ $currentCat === 'property' ? 'active' : '' }}"><x-icon name="house" class="w-3 h-3 inline-block" /> Property</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), ['cat'=>'business'])) }}"
           class="mob-chip {{ $currentCat === 'business' ? 'active' : '' }}"><x-icon name="briefcase" class="w-3 h-3 inline-block" /> Business</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), ['cat'=>'investment'])) }}"
           class="mob-chip {{ $currentCat === 'investment' ? 'active' : '' }}"><x-icon name="trend-up" class="w-3 h-3 inline-block" /> Investments</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), ['cat'=>'gadget'])) }}"
           class="mob-chip {{ $currentCat === 'gadget' ? 'active' : '' }}"><x-icon name="phone" class="w-3 h-3 inline-block" /> Gadgets</a>
    </div>

    {{-- Section chips --}}
    <div class="mob-chip-row" style="padding-top:0;">
        @php $currentSection = request('section',''); @endphp
        <a href="{{ route('marketplace.all', request()->except('section','page')) }}"
           class="mob-chip {{ $currentSection === '' ? 'active' : '' }}">All Types</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), ['section'=>'starter_moves'])) }}"
           class="mob-chip {{ $currentSection === 'starter_moves' ? 'active' : '' }}"><x-icon name="target" class="w-3 h-3 inline-block" /> Starter</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), ['section'=>'high_growth'])) }}"
           class="mob-chip {{ $currentSection === 'high_growth' ? 'active' : '' }}"><x-icon name="rocket" class="w-3 h-3 inline-block" /> High Growth</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), ['section'=>'serious_money'])) }}"
           class="mob-chip {{ $currentSection === 'serious_money' ? 'active' : '' }}"><x-icon name="diamond" class="w-3 h-3 inline-block" /> Serious</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), ['section'=>'dividend_builders'])) }}"
           class="mob-chip {{ $currentSection === 'dividend_builders' ? 'active' : '' }}"><x-icon name="coin" class="w-3 h-3 inline-block" /> Dividend</a>
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), ['section'=>'lifestyle_upgrades'])) }}"
           class="mob-chip {{ $currentSection === 'lifestyle_upgrades' ? 'active' : '' }}"><x-icon name="headphones" class="w-3 h-3 inline-block" /> Lifestyle</a>
    </div>
</div>

{{-- ═══════════════ MOBILE SORT BAR ═══════════════ --}}
<div class="mob-sort-bar">
    <div style="font-size:.72rem;color:rgba(255,255,255,.35);font-weight:600;">{{ $totalCount }} assets</div>
    <div style="display:flex;align-items:center;gap:.5rem;">
        <select class="mob-sort-select" x-model="sort" @change="applyFilters()">
            <option value="featured">Featured</option>
            <option value="price_asc">Price ↑</option>
            <option value="price_desc">Price ↓</option>
            <option value="income">Highest Income</option>
            <option value="newest">Newest</option>
        </select>
        <div class="mob-view-toggle">
            <div class="mob-view-btn" :class="viewMode==='list'?'active':''" @click="viewMode='list'">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="3" rx="1"/><rect x="1" y="7" width="14" height="3" rx="1"/><rect x="1" y="12" width="14" height="3" rx="1"/></svg>
            </div>
            <div class="mob-view-btn" :class="viewMode==='grid'?'active':''" @click="viewMode='grid'">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ TOP NAV ═══════════════ --}}
<nav class="top-nav desk-show">
    <div class="nav-left">
        <a href="{{ route('marketplace') }}" class="nav-back">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Marketplace
        </a>
        <div class="nav-breadcrumb">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <span>›</span>
            <a href="{{ route('marketplace') }}">Marketplace</a>
            <span>›</span>
            <span style="color:rgba(255,255,255,.7);">All Assets</span>
        </div>
    </div>
    <div class="nav-stats">
        <div class="nav-stat">Monthly income: <span>Ksh {{ number_format($monthlyGross) }}</span></div>
        <div class="nav-stat bal">Balance: <span>Ksh {{ number_format($progress->balance ?? 0) }}</span></div>
        <a href="{{ route('portfolio') }}" class="nav-portfolio-btn">🎒 Portfolio</a>
    </div>
</nav>

{{-- ═══════════════ PAGE HEADER ═══════════════ --}}
<div class="page-header">
    <div class="ph-inner">
        <div class="ph-icon"><x-icon name="store" class="w-6 h-6" /></div>
        <div>
            <div class="ph-title">All Assets</div>
            <div class="ph-count">{{ $totalCount }} items available</div>
        </div>

        {{-- Search Bar --}}
        <div class="ph-search-wrap" x-data="searchBox()">
            <span class="ph-search-icon">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
            </span>
            <input type="text" class="ph-search" placeholder='Search assets, e.g. "apartment", "car", "kiosk"...'
                   x-model="query" @input.debounce.300ms="fetchSuggestions()"
                   @keydown.escape="suggestions=[];query='';"
                   @keydown.enter.prevent="if(suggestions.length) goToFirst()"
                   autocomplete="off">

            <div class="search-suggestions" x-show="suggestions.length > 0 && query.length >= 3" x-cloak @click.outside="suggestions=[]">
                <template x-for="s in suggestions" :key="s.id">
                    <div class="suggestion-item" @click="selectSuggestion(s)">
                        <div class="suggestion-icon"><span class="w-4 h-4" x-html="pqIcon(s.icon, 'w-4 h-4')"></span></div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;align-items:center;gap:.4rem;">
                                <span class="suggestion-name" x-text="s.name"></span>
                                <template x-if="s.badge">
                                    <span class="suggestion-badge" :style="'background:' + badgeColor(s.badge) + '22;color:' + badgeColor(s.badge)" x-text="s.badge.toUpperCase()"></span>
                                </template>
                            </div>
                            <div class="suggestion-meta">
                                <span x-text="s.category"></span>
                                <template x-if="s.net">
                                    <span> · <span style="color:#10b981;" x-text="s.net"></span></span>
                                </template>
                                <span> · <span x-text="s.price"></span></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Sort + View toggle --}}
        <div class="ph-controls">
            <select class="ph-sort" x-model="sort" @change="applyFilters()">
                <option value="featured">Featured</option>
                <option value="price_asc">Price ↑</option>
                <option value="price_desc">Price ↓</option>
                <option value="income">Highest Income</option>
                <option value="newest">Newest</option>
            </select>
            <div class="view-toggle">
                <div class="view-btn" :class="viewMode==='grid'?'active':''" @click="viewMode='grid'" title="Grid view">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="1" width="6" height="6" rx="1"/><rect x="9" y="1" width="6" height="6" rx="1"/><rect x="1" y="9" width="6" height="6" rx="1"/><rect x="9" y="9" width="6" height="6" rx="1"/></svg>
                </div>
                <div class="view-btn" :class="viewMode==='list'?'active':''" @click="viewMode='list'" title="List view">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 16 16"><rect x="1" y="2" width="14" height="3" rx="1"/><rect x="1" y="7" width="14" height="3" rx="1"/><rect x="1" y="12" width="14" height="3" rx="1"/></svg>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ SECTION TABS ═══════════════ --}}
<div class="section-tabs">
    <div class="section-tabs-inner">
        @php
            $currentSection = request('section', '');
            $sections = [
                '' => ['icon'=>'store','label'=>'All Assets'],
                'starter_moves'      => ['icon'=>'target','label'=>'Starter Moves'],
                'high_growth'        => ['icon'=>'rocket','label'=>'High Growth'],
                'serious_money'      => ['icon'=>'diamond','label'=>'Serious Money'],
                'dividend_builders'  => ['icon'=>'coin','label'=>'Dividend Builders'],
                'lifestyle_upgrades' => ['icon'=>'headphones','label'=>'Lifestyle Upgrades'],
            ];
        @endphp
        @foreach($sections as $sKey => $sec)
        <a href="{{ route('marketplace.all', array_merge(request()->except('section','page'), $sKey ? ['section'=>$sKey] : [])) }}"
           class="sec-tab {{ $currentSection === $sKey ? 'active' : '' }}">
            <x-icon :name="$sec['icon']" class="w-3.5 h-3.5 inline-block" /> {{ $sec['label'] }}
        </a>
        @endforeach
    </div>
</div>

{{-- ═══════════════ MAIN LAYOUT ═══════════════ --}}
<div class="main-layout" style="padding:0 1rem 0 1rem;">

    {{-- ── SIDEBAR ── --}}
    <aside class="sidebar">

        {{-- Categories --}}
        <div class="sb-section">
            <div class="sb-label">Categories</div>
            @php
                $currentCat = request('cat', 'all');
                $sbCats = [
                    'all'        => ['icon'=>'store','label'=>'All Assets',    'count'=>$totalCount],
                    'vehicle'    => ['icon'=>'car','label'=>'Vehicles',      'count'=>$categoryCounts['vehicle']    ?? 0],
                    'property'   => ['icon'=>'house','label'=>'Property',      'count'=>$categoryCounts['property']   ?? 0],
                    'business'   => ['icon'=>'briefcase','label'=>'Business',      'count'=>$categoryCounts['business']   ?? 0],
                    'investment' => ['icon'=>'trend-up','label'=>'Investments',   'count'=>$categoryCounts['investment'] ?? 0],
                    'gadget'     => ['icon'=>'phone','label'=>'Gadgets',       'count'=>$categoryCounts['gadget']     ?? 0],
                ];
            @endphp
            @foreach($sbCats as $cKey => $c)
            <a href="{{ route('marketplace.all', array_merge(request()->except('cat','page'), $cKey !== 'all' ? ['cat'=>$cKey] : [])) }}"
               class="sb-cat-item {{ $currentCat === $cKey ? 'active' : '' }}">
                <div class="sb-cat-left">
                    <x-icon :name="$c['icon']" class="w-4 h-4" />
                    <span class="sb-cat-name">{{ $c['label'] }}</span>
                </div>
                <span class="sb-cat-count">{{ $c['count'] }}</span>
            </a>
            @endforeach
        </div>

        {{-- Price Range --}}
        <div class="sb-section">
            <div class="sb-label">Price Range</div>
            <form method="GET" action="{{ route('marketplace.all') }}" id="filterForm">
                @foreach(request()->except('min_price','max_price','page') as $k => $v)
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <div class="price-inputs">
                    <input type="number" name="min_price" class="price-input" placeholder="Min price"
                           value="{{ request('min_price') }}" min="0">
                    <input type="number" name="max_price" class="price-input" placeholder="Max price"
                           value="{{ request('max_price') }}" max="{{ $maxPrice }}">
                </div>
                <button type="submit" style="display:none;"></button>
            </form>
            <div style="margin-top:.5rem;display:flex;justify-content:flex-end;">
                <button onclick="document.getElementById('filterForm').submit()"
                        style="font-size:.72rem;font-weight:700;color:#818cf8;background:none;border:none;cursor:pointer;padding:.2rem .5rem;border-radius:.4rem;transition:background .15s;"
                        onmouseover="this.style.background='rgba(99,102,241,.12)'" onmouseout="this.style.background='none'">
                    Apply →
                </button>
            </div>
        </div>

        {{-- Income Type --}}
        <div class="sb-section">
            <div class="sb-label">Income Type</div>
            @php
                $activeIncome = (array) request('income', []);
                $incomeTypes = [
                    'passive'  => ['icon'=>'heart','label'=>'Passive Income'],
                    'use_earn' => ['icon'=>'car','label'=>'Use & Earn'],
                    'business' => ['icon'=>'building','label'=>'Business Income'],
                    'capital'  => ['icon'=>'trend-up','label'=>'Capital Growth'],
                ];
            @endphp
            @foreach($incomeTypes as $iKey => $iType)
            <a href="{{ route('marketplace.all', array_merge(
                request()->except('income','page'),
                ['income' => in_array($iKey,$activeIncome) ? array_diff($activeIncome,[$iKey]) : array_merge($activeIncome,[$iKey])]
            )) }}" style="text-decoration:none;">
                <label class="sb-checkbox" style="cursor:pointer;">
                    <input type="checkbox" {{ in_array($iKey,$activeIncome) ? 'checked' : '' }} style="pointer-events:none;">
                    <span class="sb-checkbox-label inline-flex items-center gap-1"><x-icon :name="$iType['icon']" class="w-3.5 h-3.5" /> {{ $iType['label'] }}</span>
                </label>
            </a>
            @endforeach
        </div>

        {{-- Income per Month --}}
        <div class="sb-section">
            <div class="sb-label">Income Per Month</div>
            @php $activeRange = request('income_range',''); @endphp
            <div class="income-chips">
                <a href="{{ route('marketplace.all', array_merge(request()->except('income_range','page'), $activeRange!=='' ? [] : ['income_range'=>''])) }}"
                   class="income-chip {{ $activeRange === '' ? 'active' : '' }}" style="text-decoration:none;">Any</a>
                @foreach(['0-5k'=>'Ksh 0–5K','5k-50k'=>'Ksh 5K–50K','50k+'=>'Ksh 50K+'] as $rKey => $rLabel)
                <a href="{{ route('marketplace.all', array_merge(request()->except('income_range','page'), ['income_range'=>$activeRange===$rKey?'':$rKey])) }}"
                   class="income-chip {{ $activeRange === $rKey ? 'active' : '' }}" style="text-decoration:none;">{{ $rLabel }}</a>
                @endforeach
            </div>
        </div>

        {{-- Clear Filters --}}
        @if(request()->except('page'))
        <a href="{{ route('marketplace.all') }}" class="clear-filters-btn" style="text-decoration:none;">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4l16 16M20 4L4 20"/></svg>
            Clear Filters
        </a>
        @endif
    </aside>

    {{-- ── ASSETS MAIN ── --}}
    <div class="assets-main">
        <div class="assets-grid" :class="viewMode === 'list' ? 'list-view' : ''">
            @forelse($assets as $asset)
            @php
                $owned     = $ownedCounts[$asset->id] ?? 0;
                $canAfford = ($progress->balance ?? 0) >= $asset->base_price;
                $maxed     = $owned >= $asset->max_per_player;
                $net       = $asset->monthly_income - $asset->monthly_cost;
                $payback   = $net > 0 ? ceil($asset->base_price / $net) : null;
                $projected = $asset->appreciation_rate != 0 ? (int)round($asset->base_price * pow(1 + $asset->appreciation_rate/100, 12)) : null;
                $affordPct = $monthlyGross > 0 ? min(100, round($asset->monthly_cost / $monthlyGross * 100)) : 0;
                $affordLabel = $asset->monthly_cost === 0 ? 'No monthly cost' : ($affordPct . '% of income');
                $affordColor = $asset->monthly_cost === 0 ? 'text-emerald-400' : ($affordPct <= 10 ? 'text-emerald-400' : ($affordPct <= 25 ? 'text-amber-400' : 'text-red-400'));
                $affordBar   = $asset->monthly_cost === 0 ? '#10b981' : ($affordPct <= 10 ? '#10b981' : ($affordPct <= 25 ? '#f59e0b' : '#f87171'));
            @endphp

            <div class="asset-card {{ $maxed ? 'owned' : '' }}"
                 :class="viewMode==='list' ? 'list-card' : ''"
                 @click="openInspect({{ json_encode([
                     'id' => $asset->id, 'name' => $asset->name, 'icon' => $asset->icon,
                     'brand' => $asset->brand ?? '', 'price' => $asset->base_price,
                     'net' => $net, 'income' => $asset->monthly_income, 'cost' => $asset->monthly_cost,
                     'rate' => $asset->appreciation_rate, 'desc' => $asset->description,
                     'flavor' => $asset->flavor_text, 'edu' => $asset->educational_note,
                     'bill' => $asset->creates_bill_slug, 'risk' => $asset->risk_level,
                     'category' => $asset->category, 'image' => $asset->image_url ?? '',
                     'badge' => $asset->badge,
                     'canAfford' => $canAfford, 'maxed' => $maxed,
                     'owned' => $owned, 'max_per_player' => $asset->max_per_player,
                     'payback' => $payback, 'projected' => $projected,
                     'afford_pct' => $affordPct, 'afford_label' => $affordLabel,
                     'afford_color' => $affordColor, 'afford_bar' => $affordBar,
                     'financing' => ($finQuote = app(\App\Services\AssetFinancingService::class)->quote($asset)),
                     'canFinanceDeposit' => $finQuote ? ($progress->balance ?? 0) >= $finQuote['deposit'] : false,
                 ]) }})">

                {{-- Image --}}
                <div class="card-img" style="background:{{ $asset->categoryGradient() }}">
                    @if($asset->image_url)
                    <img src="{{ $asset->image_url }}" alt="{{ $asset->name }}" loading="lazy"
                         style="opacity:.92"
                         onerror="this.style.display='none'">
                    @else
                    {{-- No image? Show the icon in its place — never over an actual item photo. --}}
                    <span class="card-emoji" style="opacity:.75;"><x-icon :name="$asset->icon ?? 'store'" class="w-8 h-8" /></span>
                    @endif

                    @if($asset->badge)
                    <span class="card-badge"
                          style="background:{{ $asset->badgeColor() }}22;color:{{ $asset->badgeColor() }};border:1px solid {{ $asset->badgeColor() }}44;">
                        {{ $asset->badgeLabel() }}
                    </span>
                    @endif

                    <span class="card-heart">♡</span>

                    @if($maxed)
                    <span class="card-owned-badge">✓ Owned</span>
                    @endif
                </div>

                {{-- Body --}}
                <div class="card-body">
                    <div :class="viewMode==='list' ? 'card-body-left' : ''" style="flex:1;">
                        <span class="card-chip"
                              style="background:{{ $asset->categoryChipColor() }};color:{{ $asset->categoryTextColor() }}">
                            {{ $asset->categoryEmoji() }} {{ $asset->categoryLabel() }}
                        </span>
                        <div class="card-name">{{ $asset->name }}</div>
                        <div class="card-desc">{{ $asset->description }}</div>
                    </div>
                    <div class="card-bottom">
                        <div class="card-stats">
                            <div>
                                <div class="card-stat-lbl">Net/Mo</div>
                                <div class="card-stat-val {{ $net > 0 ? 'pos' : ($net < 0 ? 'neg' : 'neutral') }}">
                                    {{ $net >= 0 ? '+' : '' }}Ksh {{ number_format(abs($net)) }}
                                </div>
                            </div>
                            <div>
                                <div class="card-stat-lbl">Price</div>
                                <div class="card-stat-val neutral">Ksh {{ number_format($asset->base_price) }}</div>
                            </div>
                        </div>
                        <div class="card-view-btn">View Details →</div>
                    </div>
                </div>
            </div>

            @empty
            <div class="empty-state">
                <div class="empty-icon">🔍</div>
                <div class="empty-title">No assets found</div>
                <div class="empty-sub">Try adjusting your filters or <a href="{{ route('marketplace.all') }}" style="color:#818cf8;">clear all filters</a></div>
            </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($assets->hasPages())
        <div class="pagination-wrap">
            <div class="page-info">Showing {{ $assets->firstItem() }} to {{ $assets->lastItem() }} of {{ $assets->total() }} items</div>
            <div class="page-btns">
                @if($assets->onFirstPage())
                    <span class="page-btn disabled">‹</span>
                @else
                    <a href="{{ $assets->previousPageUrl() }}" class="page-btn">‹</a>
                @endif

                @foreach($assets->getUrlRange(max(1, $assets->currentPage()-2), min($assets->lastPage(), $assets->currentPage()+2)) as $page => $url)
                    @if($page == $assets->currentPage())
                        <span class="page-btn current">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                    @endif
                @endforeach

                @if($assets->currentPage() < $assets->lastPage() - 3)
                    <span class="page-btn disabled">…</span>
                @endif

                @if($assets->hasMorePages())
                    <a href="{{ $assets->nextPageUrl() }}" class="page-btn">›</a>
                @else
                    <span class="page-btn disabled">›</span>
                @endif
            </div>
        </div>
        @else
        <div class="page-info" style="padding:1rem 1.5rem;font-size:.8rem;color:rgba(255,255,255,.25);">
            Showing {{ $assets->count() }} of {{ $assets->total() }} items
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════ BUY MODAL ═══════════════ --}}
<div x-show="inspecting" x-cloak x-transition.opacity
     class="fixed inset-0 flex items-center justify-center p-3 sm:p-6"
     style="z-index:9990;background:rgba(0,0,0,.85);backdrop-filter:blur(12px);overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;"
     @click.self="inspecting=null;buyMsg='';">

    {{-- No max-height / inner scroll: modal grows naturally, the OVERLAY scrolls,
         and the action footer is sticky so buttons are always visible. --}}
    <div x-show="inspecting"
         class="modal-enter w-full max-w-lg rounded-3xl"
         style="background:linear-gradient(160deg,#0f172a,#1e1b4b);border:1px solid rgba(139,92,246,.35);margin:auto;">
        <template x-if="inspecting">
            <div>
                <div class="relative h-32 sm:h-40 overflow-hidden rounded-t-3xl" :class="'cat-' + inspecting.category">
                    <template x-if="inspecting.image">
                        <img :src="inspecting.image" class="absolute inset-0 w-full h-full object-cover" style="opacity:.85;">
                    </template>
                    <div class="absolute inset-0" style="background:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:28px 28px;"></div>
                    {{-- No image? Show the icon in its place — never over an actual item photo. --}}
                    <template x-if="!inspecting.image">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="icon-bob-sm w-12 h-12 sm:w-16 sm:h-16" x-html="pqIcon(inspecting.icon, 'w-12 h-12 sm:w-16 sm:h-16')"></span>
                        </div>
                    </template>
                    <button @click="inspecting=null;buyMsg='';" class="absolute top-3 right-3 sm:top-4 sm:right-4 w-7 h-7 sm:w-8 sm:h-8 rounded-xl flex items-center justify-center"
                            style="background:rgba(0,0,0,.4);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.6);">
                        <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="absolute bottom-0 left-0 right-0 h-16" style="background:linear-gradient(to top,#0f172a,transparent);"></div>
                </div>

                <div class="px-4 pt-2 pb-3 sm:px-6 sm:pt-2 sm:pb-4">
                    <div class="mb-4 sm:mb-5">
                        <h2 class="text-base sm:text-xl font-black text-white" x-text="inspecting.name"></h2>
                        <p class="text-xs sm:text-sm text-gray-400" x-text="inspecting.brand || ''"></p>
                        <p class="text-xs sm:text-sm text-gray-300 mt-2 sm:mt-3 leading-relaxed" x-text="inspecting.desc"></p>
                        <p class="text-xs sm:text-sm text-indigo-300/80 italic mt-2" x-text="'&quot;' + inspecting.flavor + '&quot;'"></p>
                    </div>

                    <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-4 sm:mb-5">
                        <div class="rounded-2xl p-2 sm:p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[9px] sm:text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Buy Price</p>
                            <p class="text-xs sm:text-sm font-black text-white mt-1 truncate" x-text="'Ksh ' + inspecting.price.toLocaleString()"></p>
                        </div>
                        <div class="rounded-2xl p-2 sm:p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[9px] sm:text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Net/Month</p>
                            <p class="text-xs sm:text-sm font-black mt-1 truncate" :class="inspecting.net >= 0 ? 'text-emerald-400' : 'text-red-400'"
                               x-text="(inspecting.net >= 0 ? '+' : '') + 'Ksh ' + Math.abs(inspecting.net).toLocaleString()"></p>
                        </div>
                        <div class="rounded-2xl p-2 sm:p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[9px] sm:text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Value/Month</p>
                            <p class="text-xs sm:text-sm font-black mt-1 truncate" :class="inspecting.rate >= 0 ? 'text-emerald-400' : 'text-red-400'"
                               x-text="(inspecting.rate >= 0 ? '+' : '') + inspecting.rate + '%'"></p>
                        </div>
                    </div>

                    <div class="mb-4 sm:mb-5 rounded-2xl overflow-hidden" style="border:1px solid rgba(255,255,255,.08);">
                        <div class="px-3 py-1.5 sm:px-4 sm:py-2" style="background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.06);">
                            <p class="text-[11px] sm:text-xs font-black text-white inline-flex items-center gap-1"><x-icon name="coin" class="w-3 h-3 sm:w-3.5 sm:h-3.5" /> How this works financially</p>
                        </div>
                        <div class="p-3 sm:p-4 space-y-1.5 sm:space-y-2">
                            <template x-if="inspecting.income > 0">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-gray-400">Monthly revenue</span>
                                    <span class="font-bold text-emerald-400 truncate" x-text="'+Ksh ' + inspecting.income.toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.cost > 0">
                                <div class="flex items-center justify-between text-xs sm:text-sm">
                                    <span class="text-gray-400">Monthly costs</span>
                                    <span class="font-bold text-red-400 truncate" x-text="'-Ksh ' + inspecting.cost.toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.income > 0 || inspecting.cost > 0">
                                <div class="flex items-center justify-between text-xs sm:text-sm border-t border-white/10 pt-2 mt-2">
                                    <span class="font-black text-white">Net cash flow</span>
                                    <span class="font-black text-sm sm:text-lg truncate" :class="inspecting.net >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                          x-text="(inspecting.net >= 0 ? '+' : '') + 'Ksh ' + Math.abs(inspecting.net).toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.payback">
                                <div class="mt-2 sm:mt-3 rounded-xl px-2.5 py-1.5 sm:px-3 sm:py-2 text-[11px] sm:text-xs font-bold text-emerald-400"
                                     style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);">
                                    ⚡ Pays itself off in ~<span x-text="inspecting.payback"></span> game months
                                    (<span x-text="gdApprox(inspecting.payback * 30)"></span>)
                                </div>
                            </template>
                            <template x-if="!inspecting.payback && inspecting.projected && inspecting.rate > 0">
                                <div class="mt-2 sm:mt-3 rounded-xl px-2.5 py-1.5 sm:px-3 sm:py-2 text-[11px] sm:text-xs font-bold text-indigo-300"
                                     style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);">
                                    📈 Worth ~Ksh <span x-text="(inspecting.projected||0).toLocaleString()"></span> in 12 game months (<span x-text="gdApprox(360)"></span>)
                                </div>
                            </template>
                            <template x-if="inspecting.net < 0 && !inspecting.payback">
                                <div class="mt-2 sm:mt-3 rounded-xl px-2.5 py-1.5 sm:px-3 sm:py-2 text-[11px] sm:text-xs font-bold text-orange-400"
                                     style="background:rgba(251,146,60,.1);border:1px solid rgba(251,146,60,.2);">
                                    ⚠️ This asset costs more than it earns.
                                </div>
                            </template>
                        </div>
                    </div>

                    <template x-if="inspecting.afford_pct > 0">
                        <div class="mb-4 sm:mb-5 rounded-2xl overflow-hidden" style="border:1px solid rgba(255,255,255,.08);">
                            <div class="px-3 py-1.5 sm:px-4 sm:py-2" style="background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.06);">
                                <p class="text-[11px] sm:text-xs font-black text-white inline-flex items-center gap-1"><x-icon name="bar-chart" class="w-3 h-3 sm:w-3.5 sm:h-3.5" /> Affordability check</p>
                            </div>
                            <div class="p-3 sm:p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-[11px] sm:text-xs text-gray-400">Monthly income strain</span>
                                    <span class="text-[11px] sm:text-xs font-black" :class="inspecting.afford_color" x-text="inspecting.afford_label"></span>
                                </div>
                                <div class="afford-bar">
                                    <div class="afford-fill" :style="'width:' + Math.min(100,inspecting.afford_pct) + '%;background:' + inspecting.afford_bar"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="inspecting.bill">
                        <div class="mb-3 sm:mb-4 rounded-xl px-3 py-2.5 sm:px-4 sm:py-3 flex items-start gap-2"
                             style="background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);">
                            <span class="text-orange-400 text-xs sm:text-sm">⚡</span>
                            <p class="text-[11px] sm:text-xs text-orange-300 leading-snug">Buying this will add a recurring bill to your expenses.</p>
                        </div>
                    </template>

                    <div class="mb-3 sm:mb-4 rounded-xl px-3 py-2.5 sm:px-4 sm:py-3 flex items-start gap-2"
                         style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);">
                        <span class="text-indigo-400 text-xs sm:text-sm shrink-0">💡</span>
                        <p class="text-[11px] sm:text-xs text-indigo-300 leading-snug" x-text="inspecting.edu"></p>
                    </div>

                    {{-- Financing option (vehicles & property) --}}
                    <template x-if="inspecting.financing && !inspecting.maxed">
                        <div class="mb-3 sm:mb-4 rounded-2xl overflow-hidden" style="border:1px solid rgba(245,158,11,.25);">
                            <div class="px-3 py-1.5 sm:px-4 sm:py-2" style="background:rgba(245,158,11,.08);border-bottom:1px solid rgba(245,158,11,.15);">
                                <p class="text-[11px] sm:text-xs font-black text-amber-300 inline-flex items-center gap-1"><x-icon name="bank" class="w-3 h-3 sm:w-3.5 sm:h-3.5" /> Or finance it — deposit now, pay monthly</p>
                            </div>
                            <div class="p-3 sm:p-4 space-y-1 sm:space-y-1.5 text-xs sm:text-sm">
                                <div class="flex justify-between gap-2"><span class="text-gray-400">Deposit (pay now)</span><span class="font-bold text-white truncate" x-text="'Ksh ' + inspecting.financing.deposit.toLocaleString()"></span></div>
                                <div class="flex justify-between gap-2"><span class="text-gray-400">Monthly installment (auto-billed)</span><span class="font-bold text-white truncate" x-text="'Ksh ' + inspecting.financing.monthly.toLocaleString()"></span></div>
                                <div class="flex justify-between gap-2"><span class="text-gray-400" x-text="'Term: ' + inspecting.financing.months + ' game months (' + gdApprox(inspecting.financing.months * 30) + ')'"></span><span class="font-bold text-amber-300 truncate" x-text="'Total: Ksh ' + inspecting.financing.total_cost.toLocaleString()"></span></div>
                                <p class="text-[10px] sm:text-[11px] text-amber-200/70 pt-1" x-text="'Financing costs Ksh ' + inspecting.financing.interest_cost.toLocaleString() + ' more than paying cash — that\'s the price of credit.'"></p>
                            </div>
                        </div>
                    </template>

                    <div x-show="buyMsg" x-cloak x-transition
                         class="mb-3 sm:mb-4 rounded-xl px-3 py-2.5 sm:px-4 sm:py-3 text-[11px] sm:text-xs font-bold text-center"
                         :class="buyOk ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-red-400 bg-red-500/10 border border-red-500/20'"
                         x-text="buyMsg"></div>
                </div>

                <div class="px-4 py-3 sm:px-6 sm:py-4 flex gap-2 sm:gap-3 flex-wrap rounded-b-3xl"
                     style="border-top:1px solid rgba(255,255,255,.08);">
                    <button @click="inspecting=null;buyMsg='';" class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold text-gray-400 hover:text-white transition-colors"
                            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);min-width:90px;">Cancel</button>
                    <template x-if="inspecting && !inspecting.maxed && inspecting.canAfford">
                        <button @click="confirmBuy(false)" :disabled="buying"
                                class="flex-1 py-3 sm:py-3.5 rounded-xl text-xs sm:text-sm font-black transition-all hover:scale-[1.02] disabled:opacity-50"
                                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 20px rgba(99,102,241,.45);min-width:140px;">
                            <span x-show="!buying">✓ Buy Cash</span>
                            <span x-show="buying" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-3.5 h-3.5 sm:w-4 sm:h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-linecap="round"/></svg>
                                Buying…
                            </span>
                        </button>
                    </template>
                    <template x-if="inspecting && !inspecting.maxed && inspecting.financing && inspecting.canFinanceDeposit">
                        <button @click="confirmBuy(true)" :disabled="buying"
                                class="flex-1 py-3 sm:py-3.5 rounded-xl text-xs sm:text-sm font-black transition-all hover:scale-[1.02] disabled:opacity-50"
                                style="background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;box-shadow:0 4px 20px rgba(245,158,11,.4);min-width:140px;">
                            <span x-show="!buying" x-text="'🏦 Finance — Ksh ' + inspecting.financing.deposit.toLocaleString() + ' Deposit'"></span>
                            <span x-show="buying">Processing…</span>
                        </button>
                    </template>
                    <template x-if="inspecting && inspecting.maxed">
                        <div class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-black text-center"
                             style="background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.3);color:#c4b5fd;">✓ Already owned</div>
                    </template>
                    <template x-if="inspecting && !inspecting.maxed && !inspecting.canAfford && !(inspecting.financing && inspecting.canFinanceDeposit)">
                        <div class="flex-1 py-2.5 sm:py-3 rounded-xl text-xs sm:text-sm font-bold text-center text-gray-500"
                             style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">Insufficient balance</div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
// Real seconds per game day (admin clock) → "≈ real time" hints on game-day durations
window.__PESA_SPT__ = {{ (int) round(app(\App\Services\GameClock::class)->secondsPerTick()) }};
window.gdApprox = function (days) {
    const s = Math.max(0, days) * (window.__PESA_SPT__ || 514);
    if (s < 90)    return 'under 2 real min';
    if (s < 3600)  return '≈' + Math.round(s / 60) + ' real min';
    if (s < 86400) return '≈' + (Math.round(s / 360) / 10) + ' real hrs';
    return '≈' + (Math.round(s / 8640) / 10) + ' real days';
};

function allMarketplace() {
    return {
        inspecting: null,
        buying: false,
        buyMsg: '',
        buyOk: true,
        sort: '{{ request('sort','featured') }}',
        viewMode: 'grid',

        init() {
            if (window.innerWidth <= 767) this.viewMode = 'list';
            // Lock page scroll while the buy modal is open so the wheel scrolls the modal, not the page
            this.$watch('inspecting', v => { document.body.style.overflow = v ? 'hidden' : ''; });
        },

        openInspect(asset) {
            this.inspecting = asset;
            this.buyMsg = '';
            this.buying = false;
        },

        badgeColor(badge) {
            const c = {popular:'#f97316',trending:'#10b981',new:'#8b5cf6',stable:'#0ea5e9',risky:'#ef4444'};
            return c[badge] || '#6b7280';
        },

        applyFilters() {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', this.sort);
            params.delete('page');
            window.location.href = '{{ route('marketplace.all') }}?' + params.toString();
        },

        async confirmBuy(financed = false) {
            if (!this.inspecting || this.buying) return;
            this.buying = true;
            this.buyMsg = '';
            try {
                const res = await fetch(`/marketplace/${this.inspecting.id}/buy`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ financing: financed }),
                });
                const data = await res.json();
                if (res.ok) {
                    this.buyOk  = true;
                    this.buyMsg = '🎉 ' + data.message + (data.bill_added ? ' · New bill: ' + data.bill_name : '');
                    pesaMarketSound('purchase');
                    setTimeout(() => window.location.reload(), 1800);
                } else {
                    this.buyOk  = false;
                    this.buyMsg = data.error || 'Purchase failed.';
                    this.buying = false;
                }
            } catch {
                this.buyOk  = false;
                this.buyMsg = 'Network error. Try again.';
                this.buying = false;
            }
        }
    };
}

function searchBox() {
    return {
        query: '',
        suggestions: [],

        badgeColor(badge) {
            const c = {popular:'#f97316',trending:'#10b981',new:'#8b5cf6',stable:'#0ea5e9',risky:'#ef4444'};
            return c[badge] || '#6b7280';
        },

        async fetchSuggestions() {
            if (this.query.length < 3) { this.suggestions = []; return; }
            try {
                const res = await fetch(`/marketplace/search?q=${encodeURIComponent(this.query)}`, {
                    headers: {'Accept':'application/json'}
                });
                this.suggestions = await res.json();
            } catch { this.suggestions = []; }
        },

        selectSuggestion(s) {
            this.query = s.name;
            this.suggestions = [];
            // Trigger search via query param
            const params = new URLSearchParams(window.location.search);
            params.set('q', s.name);
            params.delete('page');
            window.location.href = '{{ route('marketplace.all') }}?' + params.toString();
        },

        goToFirst() {
            if (this.suggestions[0]) this.selectSuggestion(this.suggestions[0]);
        },
    };
}

function pesaMarketSound(type) {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        if (type === 'purchase') {
            [880,1047,1319,880,1319].forEach((freq,i) => {
                const o=ctx.createOscillator(),g=ctx.createGain();
                o.connect(g);g.connect(ctx.destination);
                o.type='triangle';
                o.frequency.setValueAtTime(freq,ctx.currentTime+i*.07);
                g.gain.setValueAtTime(.18,ctx.currentTime+i*.07);
                g.gain.exponentialRampToValueAtTime(.001,ctx.currentTime+i*.07+.16);
                o.start(ctx.currentTime+i*.07);o.stop(ctx.currentTime+i*.07+.16);
            });
        }
    } catch(e) {}
}
</script>

{{-- ═══════════════ BOTTOM TAB BAR (mobile) ═══════════════ --}}
<div class="all-bottom-bar">
    <div class="all-bb-inner">
        <a href="{{ route('dashboard') }}" class="all-bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <a href="{{ route('life.career') }}" class="all-bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            Career
        </a>
        <a href="{{ route('marketplace.all') }}" class="all-bb-tab all-bb-tab-active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Assets
        </a>
        <a href="{{ route('life.timeline') }}" class="all-bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Timeline
        </a>
        <a href="{{ route('profile.edit') }}" class="all-bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile
        </a>
    </div>
</div>

@include('components.mama-pesa-chat')
<x-mobile-bottom-nav active="city" />
</body>
</html>
