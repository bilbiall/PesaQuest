<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>Marketplace — PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
<style>
*{box-sizing:border-box;}
body{background:#080712;font-family:'Figtree',sans-serif;color:#fff;min-height:100vh;}
[x-cloak]{display:none!important;}

/* Animations */
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
@keyframes shimmer{0%{background-position:200% center}100%{background-position:-200% center}}
@keyframes iconbob{0%,100%{transform:translateY(0) scale(1)}50%{transform:translateY(-5px) scale(1.04)}}
@keyframes gradshift{0%,100%{background-position:0% 50%}50%{background-position:100% 50%}}
@keyframes popIn{from{opacity:0;transform:scale(.94) translateY(10px)}to{opacity:1;transform:scale(1) translateY(0)}}
@keyframes spin-slow{to{transform:rotate(360deg)}}
@keyframes glow-pulse{0%,100%{opacity:.6}50%{opacity:1}}
@keyframes cityFloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
@keyframes twinkle{0%,100%{opacity:0;transform:scale(.4)}40%{opacity:1;transform:scale(1)}70%{opacity:.6;transform:scale(.8)}}
@keyframes twinkle2{0%,100%{opacity:.7;transform:scale(.9)}50%{opacity:.1;transform:scale(.4)}}
@keyframes twinkle3{0%{opacity:.2}35%{opacity:1}65%{opacity:.3}100%{opacity:.8}}

.fade-up{animation:fadeUp .5s ease both;}

/* Stars */
.hero-stars{position:absolute;inset:-40px;pointer-events:none;z-index:0;overflow:hidden;}
.star{position:absolute;border-radius:50%;background:#fff;}
.star-sm{width:1px;height:1px;animation:twinkle ease-in-out infinite;}
.star-md{width:2px;height:2px;animation:twinkle2 ease-in-out infinite;}
.star-lg{width:3px;height:3px;animation:twinkle3 ease-in-out infinite;box-shadow:0 0 4px 1px rgba(180,170,255,.6);}

/* Hero wrap gets relative positioning for stars */
.hero-outer{position:relative;overflow:hidden;}
.fade-up-1{animation:fadeUp .5s .08s ease both;}
.fade-up-2{animation:fadeUp .5s .16s ease both;}
.fade-up-3{animation:fadeUp .5s .24s ease both;}
.fade-up-4{animation:fadeUp .5s .32s ease both;}
.fade-up-5{animation:fadeUp .5s .40s ease both;}

/* Top Nav */
.top-nav{display:flex;align-items:center;justify-content:space-between;padding:1rem 2rem;border-bottom:1px solid rgba(255,255,255,.05);backdrop-filter:blur(12px);background:rgba(8,7,18,.85);position:sticky;top:0;z-index:40;}
.nav-back{display:flex;align-items:center;gap:.5rem;color:rgba(255,255,255,.6);font-size:.875rem;font-weight:600;text-decoration:none;transition:color .2s;}
.nav-back:hover{color:#fff;}
.nav-stats{display:flex;align-items:center;gap:1.5rem;}
.nav-stat{font-size:.8rem;color:rgba(255,255,255,.5);}
.nav-stat span{color:#10b981;font-weight:800;}
.nav-stat.bal span{color:#818cf8;}
.nav-portfolio-btn{display:flex;align-items:center;gap:.5rem;padding:.5rem 1.1rem;border-radius:.75rem;font-size:.8rem;font-weight:800;color:#fff;text-decoration:none;transition:all .2s;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);}
.nav-portfolio-btn:hover{background:rgba(99,102,241,.35);transform:translateY(-1px);}

/* Hero */
.hero-wrap{display:grid;grid-template-columns:1fr auto 1fr;gap:2rem;padding:2.5rem 2rem 1.5rem;max-width:1400px;margin:0 auto;align-items:center;}
.hero-left{max-width:440px;}
.hero-avail{display:inline-flex;align-items:center;gap:.5rem;font-size:.75rem;font-weight:700;color:#818cf8;margin-bottom:1rem;padding:.35rem .75rem;border-radius:9999px;background:rgba(99,102,241,.12);border:1px solid rgba(99,102,241,.25);}
.hero-avail::before{content:'';width:6px;height:6px;border-radius:50%;background:#818cf8;animation:glow-pulse 2s infinite;}
.hero-title{font-size:3.5rem;font-weight:900;line-height:1.05;margin-bottom:1rem;}
.hero-title .gradient-word{background:linear-gradient(135deg,#818cf8,#a78bfa,#38bdf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background-size:200% auto;animation:shimmer 4s linear infinite;}
.hero-subtitle{color:rgba(255,255,255,.5);font-size:.95rem;line-height:1.65;margin-bottom:1.5rem;max-width:360px;}
.faq-btn{width:100%;display:flex;align-items:center;justify-content:space-between;padding:.85rem 1.1rem;border-radius:1rem;font-size:.85rem;font-weight:700;color:rgba(255,255,255,.8);background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);cursor:pointer;transition:all .2s;}
.faq-btn:hover{background:rgba(255,255,255,.07);}
.faq-body{padding:.75rem 1.1rem;border-radius:0 0 1rem 1rem;font-size:.82rem;color:rgba(255,255,255,.55);line-height:1.7;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-top:none;margin-top:-1px;}

/* City illustration */
.hero-center-wrap{display:flex;align-items:center;justify-content:center;width:340px;height:310px;position:relative;}
.city-glow{position:absolute;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(99,102,241,.3) 0%,rgba(99,102,241,.05) 60%,transparent 75%);pointer-events:none;}
.city-art{animation:cityFloat 4s ease-in-out infinite;font-size:10rem;line-height:1;text-align:center;filter:drop-shadow(0 20px 40px rgba(99,102,241,.4));user-select:none;}
.city-img{width:360px;height:auto;object-fit:contain;position:relative;z-index:2;mix-blend-mode:screen;filter:drop-shadow(0 16px 40px rgba(99,102,241,.5)) drop-shadow(0 4px 16px rgba(0,0,0,.3));animation:cityFloat 4s ease-in-out infinite;}

/* Hero stat cards */
.hero-right{display:flex;flex-direction:column;gap:1rem;}
.hero-stat-card{border-radius:1.25rem;padding:1.1rem 1.25rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08);min-width:190px;display:flex;align-items:center;gap:1rem;overflow:hidden;position:relative;}
.hero-stat-card::before{content:'';position:absolute;inset:0;border-radius:inherit;pointer-events:none;}
.hero-stat-card.bal-card::before{background:linear-gradient(135deg,rgba(16,185,129,.06) 0%,transparent 60%);}
.hero-stat-card.nw-card::before{background:linear-gradient(135deg,rgba(99,102,241,.08) 0%,transparent 60%);}
.stat-card-img{width:58px;height:58px;object-fit:contain;flex-shrink:0;filter:drop-shadow(0 4px 12px rgba(0,0,0,.4));position:relative;z-index:1;}
.stat-card-text{flex:1;min-width:0;position:relative;z-index:1;}
.hero-stat-label{font-size:.62rem;font-weight:700;letter-spacing:.1em;color:rgba(255,255,255,.35);text-transform:uppercase;margin-bottom:.3rem;}
.hero-stat-value{font-size:1.35rem;font-weight:900;line-height:1.1;}
.hero-stat-value.green{color:#10b981;}
.hero-stat-value.purple{background:linear-gradient(135deg,#818cf8,#c4b5fd);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.hero-stat-sub{font-size:.68rem;color:rgba(255,255,255,.3);margin-top:.2rem;}

/* Feature highlights — contained row of cards */
.features-wrap{max-width:1400px;margin:0 auto;padding:.25rem 2rem 1.75rem;}
.features-bar{display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;}
.feature-item{padding:.875rem 1.1rem;display:flex;align-items:center;gap:.75rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);border-radius:1rem;transition:all .2s;}
.feature-item:hover{background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.12);transform:translateY(-1px);}
.feature-icon{width:36px;height:36px;border-radius:.875rem;display:flex;align-items:center;justify-content:center;font-size:1.05rem;flex-shrink:0;}
.feature-label{font-size:.78rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;}
.feature-sub{font-size:.68rem;color:rgba(255,255,255,.4);margin-top:.15rem;}

/* Category tabs */
.tabs-wrap{padding:1.25rem 2rem;max-width:1400px;margin:0 auto;display:flex;align-items:center;gap:1rem;}
.tabs-scroller{display:flex;gap:.5rem;overflow-x:auto;flex:1;scrollbar-width:none;-ms-overflow-style:none;padding-bottom:2px;}
.tabs-scroller::-webkit-scrollbar{display:none;}
.cat-tab{display:flex;align-items:center;gap:.4rem;padding:.55rem 1rem;border-radius:.875rem;font-size:.82rem;font-weight:700;cursor:pointer;white-space:nowrap;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);transition:all .2s;user-select:none;}
.cat-tab:hover{color:rgba(255,255,255,.8);background:rgba(255,255,255,.07);}
.cat-tab.active{background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.45);color:#fff;}
.cat-tab .cnt{font-size:.7rem;background:rgba(255,255,255,.1);border-radius:9999px;padding:.1rem .45rem;margin-left:.15rem;}
.cat-tab.active .cnt{background:rgba(99,102,241,.35);}
.sort-select{padding:.55rem .9rem;border-radius:.875rem;font-size:.82rem;font-weight:600;color:rgba(255,255,255,.7);background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);outline:none;cursor:pointer;min-width:140px;}
.sort-select option{background:#1e1b4b;}

/* Sections */
.section-wrap{max-width:1400px;margin:0 auto;padding:1.5rem 2rem;}
.section-header{display:flex;align-items:baseline;gap:.75rem;margin-bottom:1.25rem;}
.section-title{font-size:1.15rem;font-weight:900;color:#fff;}
.section-sub{font-size:.8rem;color:rgba(255,255,255,.35);}

/* Carousel */
.carousel-outer{position:relative;}
.carousel-scroller{display:flex;gap:1rem;overflow-x:auto;padding:4px 0 16px;scrollbar-width:none;-ms-overflow-style:none;scroll-snap-type:x mandatory;scroll-behavior:smooth;}
.carousel-scroller::-webkit-scrollbar{display:none;}
.carousel-nav{position:absolute;top:50%;transform:translateY(-60%);z-index:10;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(15,14,30,.9);border:1px solid rgba(255,255,255,.15);cursor:pointer;transition:all .2s;color:rgba(255,255,255,.7);}
.carousel-nav:hover{background:rgba(99,102,241,.3);border-color:rgba(99,102,241,.5);color:#fff;}
.carousel-nav.prev{left:-18px;}
.carousel-nav.next{right:-18px;}

/* Asset Cards */
.asset-card{flex-shrink:0;width:248px;scroll-snap-align:start;border-radius:1.25rem;overflow:hidden;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.09);cursor:pointer;transition:transform .22s ease,box-shadow .22s ease,border-color .22s ease;display:flex;flex-direction:column;}
.asset-card:hover{transform:translateY(-6px);box-shadow:0 24px 56px rgba(0,0,0,.55),0 0 0 1px rgba(99,102,241,.2);border-color:rgba(99,102,241,.35);}
.asset-card.owned{border-color:rgba(139,92,246,.35);}
.card-img{position:relative;height:170px;overflow:hidden;display:flex;align-items:center;justify-content:center;}
.card-img img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.65;transition:opacity .3s;}
.asset-card:hover .card-img img{opacity:.8;}
.card-img-overlay{position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.05) 0%,rgba(0,0,0,.5) 100%);}
.card-emoji{font-size:3.8rem;line-height:1;animation:iconbob 3.5s ease-in-out infinite;filter:drop-shadow(0 4px 16px rgba(0,0,0,.5));position:relative;z-index:2;}
.card-badge{position:absolute;top:10px;left:10px;font-size:.62rem;font-weight:900;padding:.28rem .65rem;border-radius:9999px;letter-spacing:.05em;z-index:3;text-transform:uppercase;}
.card-heart{position:absolute;top:10px;right:10px;width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,.45);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.12);font-size:.8rem;z-index:3;cursor:pointer;transition:all .2s;}
.card-heart:hover{background:rgba(239,68,68,.25);border-color:rgba(239,68,68,.5);}
.card-owned-badge{position:absolute;bottom:8px;left:10px;font-size:.62rem;font-weight:900;padding:.2rem .6rem;border-radius:9999px;background:rgba(139,92,246,.75);backdrop-filter:blur(6px);z-index:3;color:#fff;}
.card-body{padding:1rem 1rem .85rem;display:flex;flex-direction:column;flex:1;}
.card-chip{display:inline-flex;align-items:center;gap:.3rem;font-size:.65rem;font-weight:800;padding:.22rem .55rem;border-radius:9999px;margin-bottom:.55rem;width:fit-content;letter-spacing:.02em;}
.card-name{font-size:.92rem;font-weight:900;color:#fff;line-height:1.25;margin-bottom:.2rem;}
.card-desc{font-size:.72rem;color:rgba(255,255,255,.42);line-height:1.45;display:-webkit-box;-webkit-line-clamp:1;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:.65rem;}
.card-stats{display:grid;grid-template-columns:1fr 1fr;gap:.5rem;padding:.65rem .7rem;border-radius:.75rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);}
.card-stat-lbl{font-size:.58rem;font-weight:700;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.06em;}
.card-stat-val{font-size:.85rem;font-weight:900;margin-top:.15rem;}
.card-stat-val.pos{color:#10b981;}
.card-stat-val.neg{color:#f87171;}
.card-stat-val.neutral{color:#fff;}
.card-view-btn{margin-top:.7rem;padding:.72rem .9rem;border-radius:.875rem;font-size:.78rem;font-weight:800;text-align:center;display:flex;align-items:center;justify-content:center;gap:.35rem;background:linear-gradient(135deg,rgba(99,102,241,.22),rgba(139,92,246,.16));border:1px solid rgba(99,102,241,.38);color:#a5b4fc;cursor:pointer;transition:all .2s;white-space:nowrap;}
.card-view-btn:hover,.asset-card:hover .card-view-btn{background:linear-gradient(135deg,rgba(99,102,241,.4),rgba(139,92,246,.3));border-color:rgba(99,102,241,.6);color:#fff;transform:none;}

/* Carousel dots */
.carousel-dots{display:flex;gap:.4rem;justify-content:center;margin-top:.5rem;}
.carousel-dot{width:6px;height:6px;border-radius:50%;background:rgba(255,255,255,.15);transition:all .3s;cursor:pointer;}
.carousel-dot.active{width:18px;border-radius:9999px;background:#6366f1;}

/* Growth Plays */
.growth-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;}
.growth-card{display:flex;align-items:center;gap:1rem;padding:1.25rem 1.1rem;border-radius:1.25rem;background:rgba(255,255,255,.025);border:1px solid rgba(255,255,255,.07);text-decoration:none;transition:all .25s;cursor:pointer;}
.growth-card:hover{background:rgba(99,102,241,.08);border-color:rgba(99,102,241,.25);transform:translateY(-2px);}
.growth-icon{width:52px;height:52px;border-radius:1rem;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0;}
.growth-text{flex:1;min-width:0;}
.growth-label{font-size:.88rem;font-weight:900;color:#fff;}
.growth-desc{font-size:.72rem;color:rgba(255,255,255,.4);line-height:1.4;margin-top:.2rem;}
.growth-arrow{color:rgba(255,255,255,.3);font-size:1rem;flex-shrink:0;transition:transform .2s;}
.growth-card:hover .growth-arrow{transform:translateX(3px);color:rgba(255,255,255,.7);}

/* CTA Banner */
.cta-banner{max-width:1400px;margin:1rem auto 2rem;padding:1.75rem 2rem;border-radius:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1.5rem;background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(139,92,246,.1),rgba(59,130,246,.08));border:1px solid rgba(99,102,241,.2);}
.cta-left{display:flex;align-items:center;gap:1.25rem;}
.cta-shield{width:48px;height:48px;border-radius:1rem;display:flex;align-items:center;justify-content:center;font-size:1.5rem;background:rgba(99,102,241,.2);flex-shrink:0;}
.cta-heading{font-size:1.1rem;font-weight:900;color:#fff;}
.cta-sub{font-size:.8rem;color:rgba(255,255,255,.45);margin-top:.2rem;}
.cta-explore-btn{display:flex;align-items:center;gap:.5rem;padding:.875rem 1.75rem;border-radius:1rem;font-size:.9rem;font-weight:900;color:#fff;text-decoration:none;white-space:nowrap;background:linear-gradient(135deg,#6366f1,#8b5cf6);box-shadow:0 4px 24px rgba(99,102,241,.4);transition:all .2s;}
.cta-explore-btn:hover{transform:translateY(-2px);box-shadow:0 8px 32px rgba(99,102,241,.5);}

/* Buy Modal */
@keyframes modal-enter{from{opacity:0;transform:translateY(24px) scale(.97)}to{opacity:1;transform:translateY(0) scale(1)}}
.modal-enter{animation:modal-enter .3s cubic-bezier(.34,1.56,.64,1) both;}
.cat-vehicle   {background:linear-gradient(145deg,#92400e,#b45309,#1c1917);}
.cat-property  {background:linear-gradient(145deg,#064e3b,#065f46,#0f172a);}
.cat-business  {background:linear-gradient(145deg,#3730a3,#4c1d95,#1e1b4b);}
.cat-investment{background:linear-gradient(145deg,#1e3a8a,#1e40af,#0f172a);}
.cat-gadget    {background:linear-gradient(145deg,#831843,#9d174d,#1a1025);}
.icon-bob{animation:iconbob 3.5s ease-in-out infinite;}
.afford-bar{height:6px;border-radius:3px;background:rgba(255,255,255,.08);overflow:hidden;}
.afford-fill{height:100%;border-radius:3px;transition:width .8s cubic-bezier(.4,0,.2,1);}

/* ── SHOW/HIDE HELPERS ── */
.mob-show{display:none!important;}
.desk-show{display:block;}
@media(max-width:767px){
    .mob-show{display:block!important;}
    .desk-show{display:none!important;}
    .mob-flex{display:flex!important;}
    body{padding-bottom:64px;}
}

/* ── MOBILE NAV ── */
.mob-nav{display:none;align-items:center;justify-content:space-between;padding:.875rem 1rem;background:rgba(8,7,18,.95);border-bottom:1px solid rgba(255,255,255,.06);position:sticky;top:0;z-index:40;}
.mob-nav-logo{display:flex;flex-direction:column;line-height:1;}
.mob-nav-logo .brand{font-size:1.1rem;font-weight:900;color:#818cf8;}
.mob-nav-logo .tagline{font-size:.6rem;color:rgba(255,255,255,.3);letter-spacing:.04em;}
@media(max-width:767px){.mob-nav{display:flex!important;}.top-nav{display:none!important;}}

/* ── BOTTOM TAB BAR ── */
.bottom-bar{display:none;position:fixed;bottom:0;left:0;right:0;z-index:50;background:rgba(7,6,15,.98);border-top:1px solid rgba(255,255,255,.07);}
.bottom-bar-inner{display:flex;align-items:stretch;}
.bb-tab{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:.2rem;padding:.6rem .25rem .5rem;text-decoration:none;color:rgba(255,255,255,.35);transition:color .2s;font-size:.58rem;font-weight:700;letter-spacing:.02em;}
.bb-tab svg{width:20px;height:20px;stroke-width:1.8;}
.bb-tab.active,.bb-tab-active{color:#818cf8;}
.bb-tab.active svg,.bb-tab-active svg{stroke:#818cf8;}
.mob-section-view-all{font-size:.72rem;font-weight:700;color:#818cf8;text-decoration:none;}
@media(max-width:767px){.bottom-bar{display:block;}}

/* ── MOBILE HERO ── */
@media(max-width:767px){
    /* Hero outer/wrap become a relative container so city image can be absolute behind text */
    .hero-outer{overflow:visible;}
    .hero-wrap{
        display:flex!important;flex-direction:column!important;align-items:stretch!important;
        padding:0 1rem 1.25rem!important;gap:0!important;max-width:100%!important;
        position:relative!important;min-height:340px!important;
    }
    /* City image floats as an absolute background at the top */
    .hero-center-wrap{
        position:absolute!important;top:-20px!important;left:0!important;right:0!important;
        width:100%!important;height:260px!important;
        display:flex!important;align-items:center!important;justify-content:center!important;
        z-index:0!important;pointer-events:none!important;order:0!important;
    }
    .city-img{width:290px!important;opacity:.75!important;}
    .city-glow{width:220px!important;height:220px!important;opacity:.4!important;}
    /* Text content overlays at the bottom of the image area */
    .hero-left{
        max-width:100%!important;order:1!important;
        position:relative!important;z-index:2!important;
        margin-top:190px!important;
        /* Gradient so text is readable over the image */
        padding-top:.5rem!important;
        background:linear-gradient(to bottom,rgba(8,7,18,0) 0%,rgba(8,7,18,.75) 20%,rgba(8,7,18,1) 55%)!important;
    }
    .hero-faq{display:none!important;}
    /* Stat cards below, normal flow */
    .hero-right{order:2!important;flex-direction:row!important;gap:.625rem!important;position:relative!important;z-index:2!important;margin-top:.875rem!important;}
    .hero-stat-card{flex:1;min-width:0;padding:.875rem .75rem!important;}
    .stat-card-img{width:40px;height:40px;}
    .hero-stat-value{font-size:1.05rem!important;}
    .hero-title{font-size:2.1rem!important;line-height:1.1!important;}
    .hero-subtitle{font-size:.875rem;max-width:100%;margin-bottom:.75rem!important;}
    .hero-avail{font-size:.7rem;margin-bottom:.625rem;}
    .hero-stars{display:none;}
}

/* ── MOBILE FEATURES (Why buy assets) ── */
.mob-why-section{padding:.25rem 1rem 1.25rem;}
.mob-why-title{font-size:.95rem;font-weight:900;color:#fff;margin-bottom:.75rem;}
.mob-features-grid{display:grid;grid-template-columns:1fr 1fr;gap:.625rem;}
.mob-feature-card{padding:.875rem .75rem;border-radius:1rem;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;align-items:center;gap:.4rem;text-align:center;}
.mob-feature-icon{width:40px;height:40px;border-radius:.875rem;display:flex;align-items:center;justify-content:center;font-size:1.15rem;}
.mob-feature-label{font-size:.78rem;font-weight:800;color:rgba(255,255,255,.9);line-height:1.2;}
.mob-feature-sub{font-size:.65rem;color:rgba(255,255,255,.4);line-height:1.3;}

/* ── MOBILE SECTION HEADERS ── */
.mob-section-header{display:flex;align-items:center;justify-content:space-between;padding:.125rem 0 .875rem;}
.mob-section-title{font-size:.95rem;font-weight:900;color:#fff;}
.mob-view-all{font-size:.78rem;font-weight:700;color:#818cf8;text-decoration:none;display:flex;align-items:center;gap:.2rem;}
.mob-view-all:hover{color:#a78bfa;}

/* ── MOBILE CATEGORY CHIPS ── */
.mob-cats-wrap{padding:0 1rem .5rem;}
.mob-cats-scroller{display:flex;gap:.5rem;overflow-x:auto;scrollbar-width:none;padding-bottom:2px;}
.mob-cats-scroller::-webkit-scrollbar{display:none;}
.mob-cat-chip{display:flex;align-items:center;gap:.35rem;padding:.5rem .875rem;border-radius:.875rem;font-size:.78rem;font-weight:700;white-space:nowrap;cursor:pointer;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.04);color:rgba(255,255,255,.5);transition:all .2s;user-select:none;}
.mob-cat-chip.active{background:rgba(99,102,241,.2);border-color:rgba(99,102,241,.45);color:#fff;}
.mob-cat-chip .mob-cnt{font-size:.65rem;background:rgba(255,255,255,.1);border-radius:9999px;padding:.05rem .4rem;}

/* ── MOBILE CTA ── */
@media(max-width:767px){
    .cta-banner{margin:0 1rem 1.25rem;padding:1.1rem 1rem;flex-direction:column;gap:.75rem;text-align:center;border-radius:1.25rem;}
    .cta-left{flex-direction:column;align-items:center;gap:.5rem;}
    .cta-heading{font-size:.95rem;}
    .cta-sub{font-size:.75rem;}
    .cta-explore-btn{width:100%;justify-content:center;padding:.875rem;font-size:.9rem;border-radius:.875rem;}
    /* hide desktop features/tabs, growth section uses 2-col */
    .features-wrap{display:none!important;}
    .tabs-wrap{display:none!important;}
    .section-wrap{padding:1rem 1rem;}
    .growth-grid{grid-template-columns:1fr 1fr;gap:.625rem;}
    .growth-card{padding:.875rem .75rem;}
    .growth-icon{width:38px;height:38px;font-size:1.15rem;}
    .growth-label{font-size:.78rem;}
    .growth-desc{font-size:.65rem;}
    .growth-arrow{display:none;}
    /* Carousel narrower cards on mobile */
    .asset-card{width:200px!important;}
}
</style>
</head>
<body x-data="marketplace()">

{{-- ═══════════════ MOBILE NAV ═══════════════ --}}
<nav class="mob-nav mob-show">
    <div class="mob-nav-logo">
        <span class="brand">moski</span>
        <span class="tagline">it's possible</span>
    </div>
    <a href="{{ route('portfolio') }}" class="nav-portfolio-btn" style="font-size:.78rem;padding:.45rem 1rem;">
        🎒 <span class="nav-portfolio-label">Portfolio</span>
    </a>
</nav>

{{-- ═══════════════ TOP NAV (desktop) ═══════════════ --}}
<nav class="top-nav fade-up desk-show">
    <a href="{{ route('dashboard') }}" class="nav-back">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        Dashboard
    </a>
    <div class="nav-stats">
        <div class="nav-stat">Monthly income: <span>Ksh {{ number_format($totalMonthlyIncome) }}</span></div>
        <div class="nav-stat bal">Balance: <span>Ksh {{ number_format($progress->balance ?? 0) }}</span></div>
        <a href="{{ route('portfolio') }}" class="nav-portfolio-btn">
            🎒 <span class="nav-portfolio-label">Portfolio</span>
        </a>
    </div>
</nav>

{{-- ═══════════════ HERO ═══════════════ --}}
<div class="hero-outer">
<div class="hero-stars" id="heroStars"></div>
<div class="hero-wrap fade-up-1" style="padding-bottom:2rem;">

    {{-- Left: title + FAQ --}}
    <div class="hero-left">
        <div class="hero-avail">{{ $totalCount }} items available for you</div>
        <h1 class="hero-title">PesaQuest<br><span class="gradient-word">Marketplace</span></h1>
        <p class="hero-subtitle">Build your portfolio. Buy assets that work for you while you sleep — or learn what not to buy before it's too late.</p>

        <div x-data="{open:false}" class="mt-4 hero-faq">
            <button class="faq-btn" @click="open=!open">
                <span>💡 How do assets make you money?</span>
                <svg class="w-4 h-4 transition-transform" :class="open?'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div class="faq-body" x-show="open" x-cloak x-transition>
                Assets generate income every game period. Some pay you monthly rent, others earn daily profits from customers. Appreciation assets grow in value over time. The goal: build passive income streams so money flows even when you're offline.
            </div>
        </div>
    </div>

    {{-- Center: City Art --}}
    <div class="hero-center-wrap fade-up-2">
        <div class="city-glow"></div>
        <img src="{{ asset('img/game/market.png') }}" alt="Pesa City" class="city-img" loading="eager">
    </div>

    {{-- Right: Stat Cards --}}
    <div class="hero-right fade-up-3">
        <div class="hero-stat-card bal-card">
            <img src="{{ asset('img/game/market1.png') }}" alt="" class="stat-card-img" loading="eager">
            <div class="stat-card-text">
                <div class="hero-stat-label">Balance</div>
                <div class="hero-stat-value green">Ksh {{ number_format($progress->balance ?? 0) }}</div>
                <div class="hero-stat-sub">Available to invest</div>
            </div>
        </div>
        <div class="hero-stat-card nw-card">
            <img src="{{ asset('img/game/market2.png') }}" alt="" class="stat-card-img" loading="eager">
            <div class="stat-card-text">
                <div class="hero-stat-label">Net Worth</div>
                <div class="hero-stat-value purple">Ksh {{ number_format($progress->net_worth_cache ?? 0) }}</div>
                <div class="hero-stat-sub">Balance + all assets</div>
            </div>
        </div>
    </div>
</div>
</div>{{-- /hero-outer --}}

{{-- ═══════════════ MOBILE FAQ (below stat cards) ═══════════════ --}}
<div class="mob-show" style="padding:.125rem 1rem .5rem;" x-data="{open:false}">
    <button class="faq-btn" @click="open=!open">
        <span>💡 How do assets make you money?</span>
        <svg class="w-4 h-4 transition-transform" :class="open?'rotate-180':''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
    </button>
    <div class="faq-body" x-show="open" x-cloak x-transition>
        Assets generate income every game period. Some pay you monthly rent, others earn daily profits. Appreciation assets grow in value over time. Build passive income so money flows even when you're offline.
    </div>
</div>

{{-- ═══════════════ MOBILE: WHY BUY ASSETS (features) ═══════════════ --}}
<div class="mob-why-section mob-show">
    <div class="mob-why-title">Why buy assets?</div>
    <div class="mob-features-grid">
        <div class="mob-feature-card">
            <div class="mob-feature-icon" style="background:rgba(16,185,129,.15);color:#10b981;">💰</div>
            <div class="mob-feature-label">Earn while you sleep</div>
            <div class="mob-feature-sub">Passive income assets</div>
        </div>
        <div class="mob-feature-card">
            <div class="mob-feature-icon" style="background:rgba(99,102,241,.15);color:#818cf8;">🛡</div>
            <div class="mob-feature-label">Build long-term wealth</div>
            <div class="mob-feature-sub">Appreciate over time</div>
        </div>
        <div class="mob-feature-card">
            <div class="mob-feature-icon" style="background:rgba(139,92,246,.15);color:#a78bfa;">⚡</div>
            <div class="mob-feature-label">Improve your life</div>
            <div class="mob-feature-sub">Tools that level you up</div>
        </div>
        <div class="mob-feature-card">
            <div class="mob-feature-icon" style="background:rgba(245,158,11,.15);color:#fbbf24;">📊</div>
            <div class="mob-feature-label">Diversify &amp; grow</div>
            <div class="mob-feature-sub">Spread risk, build security</div>
        </div>
    </div>
</div>

{{-- ═══════════════ MOBILE: EXPLORE CATEGORIES ═══════════════ --}}
<div class="mob-show" style="padding:.5rem 1rem 0;">
    <div class="mob-section-header">
        <span class="mob-section-title">Explore Categories</span>
        <a href="{{ route('marketplace.all') }}" class="mob-view-all">View all <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke-width:2.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
    </div>
</div>
<div class="mob-cats-wrap mob-show">
    <div class="mob-cats-scroller">
        @foreach($cats ?? [
            'all'        => ['label'=>'All',       'emoji'=>'🏪','count'=>$totalCount],
            'vehicle'    => ['label'=>'Vehicles',  'emoji'=>'🚗','count'=>$categoryCounts['vehicle']    ?? 0],
            'property'   => ['label'=>'Property',  'emoji'=>'🏠','count'=>$categoryCounts['property']   ?? 0],
            'business'   => ['label'=>'Business',  'emoji'=>'💼','count'=>$categoryCounts['business']   ?? 0],
            'investment' => ['label'=>'Investments','emoji'=>'📈','count'=>$categoryCounts['investment'] ?? 0],
            'gadget'     => ['label'=>'Gadgets',   'emoji'=>'📱','count'=>$categoryCounts['gadget']     ?? 0],
        ] as $key => $cat)
        <button class="mob-cat-chip" :class="activeCategory === '{{ $key }}' ? 'active' : ''"
                @click="activeCategory = '{{ $key }}'">
            {{ $cat['emoji'] }} {{ $cat['label'] }}
            <span class="mob-cnt">{{ $cat['count'] }}</span>
        </button>
        @endforeach
    </div>
</div>

{{-- ═══════════════ FEATURE HIGHLIGHTS (desktop only) ═══════════════ --}}
<div class="features-wrap fade-up-2">
    <div class="features-bar">
        <div class="feature-item">
            <div class="feature-icon" style="background:rgba(16,185,129,.15);color:#10b981;">💰</div>
            <div><div class="feature-label">Earn while you sleep</div><div class="feature-sub">Passive income assets</div></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:rgba(99,102,241,.15);color:#818cf8;">🛡</div>
            <div><div class="feature-label">Build long-term wealth</div><div class="feature-sub">Appreciate over time</div></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:rgba(139,92,246,.15);color:#a78bfa;">⚡</div>
            <div><div class="feature-label">Improve your life</div><div class="feature-sub">Tools that level you up</div></div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:rgba(245,158,11,.15);color:#fbbf24;">📊</div>
            <div><div class="feature-label">Diversify &amp; grow</div><div class="feature-sub">Spread risk, build security</div></div>
        </div>
    </div>
</div>

{{-- ═══════════════ CATEGORY TABS + SORT ═══════════════ --}}
<div class="tabs-wrap fade-up-3">
    <div class="tabs-scroller" x-ref="tabsScroller">
        @php
            $cats = [
                'all'        => ['label'=>'All',         'emoji'=>'🏪', 'count'=>$totalCount],
                'vehicle'    => ['label'=>'Vehicles',    'emoji'=>'🚗', 'count'=>$categoryCounts['vehicle']    ?? 0],
                'property'   => ['label'=>'Property',    'emoji'=>'🏠', 'count'=>$categoryCounts['property']   ?? 0],
                'business'   => ['label'=>'Business',    'emoji'=>'💼', 'count'=>$categoryCounts['business']   ?? 0],
                'investment' => ['label'=>'Investments', 'emoji'=>'📈', 'count'=>$categoryCounts['investment'] ?? 0],
                'gadget'     => ['label'=>'Gadgets',     'emoji'=>'📱', 'count'=>$categoryCounts['gadget']     ?? 0],
            ];
        @endphp
        @foreach($cats as $key => $cat)
        <button class="cat-tab" :class="activeCategory === '{{ $key }}' ? 'active' : ''"
                @click="activeCategory = '{{ $key }}'">
            {{ $cat['emoji'] }} {{ $cat['label'] }}
            <span class="cnt">{{ $cat['count'] }}</span>
        </button>
        @endforeach
    </div>
    <select class="sort-select" x-model="sortMode">
        <option value="featured">Sort by: Featured</option>
        <option value="price_asc">Price: Low to High</option>
        <option value="price_desc">Price: High to Low</option>
        <option value="income">Highest Income</option>
    </select>
</div>

{{-- ═══════════════ STARTER MOVES CAROUSEL ═══════════════ --}}
<div class="section-wrap fade-up-4">
    <div class="section-header" style="justify-content:space-between;align-items:flex-end;">
        <div>
            {{-- Desktop label --}}
            <div class="desk-show" style="display:flex;align-items:center;gap:.5rem;">
                <span style="font-size:1.25rem;">🎯</span>
                <span class="section-title">Starter Moves</span>
            </div>
            {{-- Mobile label --}}
            <div class="mob-section-header mob-show" style="padding-bottom:.35rem;">
                <div style="display:flex;align-items:center;gap:.4rem;">
                    <span style="font-size:1.1rem;">⭐</span>
                    <span class="mob-section-title">Featured Assets</span>
                </div>
                <a href="{{ route('marketplace.all') }}" class="mob-view-all">View all assets <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" style="width:14px;height:14px;stroke-width:2.5;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></a>
            </div>
            <p class="section-sub" style="margin-top:.2rem;">Low-cost assets to get your portfolio started</p>
        </div>
    </div>

    <div class="carousel-outer">
        <button class="carousel-nav prev" @click="scrollCarousel('starter', -1)" aria-label="Previous">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        </button>

        <div class="carousel-scroller" id="starterCarousel" x-ref="starterCarousel">
            @php
                $fin = app(\App\Services\AssetFinancingService::class);
                $allStarter = $starterMoves->map(fn($a) => [
                    'financing' => $fin->quote($a),
                    'canFinanceDeposit' => ($q = $fin->quote($a)) ? ($progress->balance ?? 0) >= $q['deposit'] : false,
                    'id' => $a->id, 'name' => $a->name, 'icon' => $a->icon,
                    'brand' => $a->brand ?? '', 'price' => $a->base_price,
                    'net' => $a->monthly_income - $a->monthly_cost,
                    'income' => $a->monthly_income, 'cost' => $a->monthly_cost,
                    'rate' => $a->appreciation_rate, 'desc' => $a->description,
                    'flavor' => $a->flavor_text, 'edu' => $a->educational_note,
                    'bill' => $a->creates_bill_slug, 'risk' => $a->risk_level,
                    'category' => $a->category,
                    'badge' => $a->badge,
                    'image' => $a->image_url ?? '',
                    'canAfford' => ($progress->balance ?? 0) >= $a->base_price,
                    'maxed' => ($ownedCounts[$a->id] ?? 0) >= $a->max_per_player,
                    'owned' => $ownedCounts[$a->id] ?? 0,
                    'max_per_player' => $a->max_per_player,
                    'payback' => ($a->monthly_income - $a->monthly_cost) > 0 ? ceil($a->base_price / ($a->monthly_income - $a->monthly_cost)) : null,
                    'projected' => $a->appreciation_rate != 0 ? (int)round($a->base_price * pow(1 + $a->appreciation_rate/100, 12)) : null,
                    'afford_pct' => $monthlyGross > 0 ? min(100, round($a->monthly_cost / $monthlyGross * 100)) : 0,
                    'afford_label' => $a->monthly_cost === 0 ? 'No monthly cost' : (($monthlyGross > 0 ? min(100,round($a->monthly_cost/$monthlyGross*100)) : 0) .'% of income'),
                    'afford_color' => $a->monthly_cost === 0 ? 'text-emerald-400' : (($monthlyGross > 0 && $a->monthly_cost/$monthlyGross < .1) ? 'text-emerald-400' : (($monthlyGross > 0 && $a->monthly_cost/$monthlyGross < .25) ? 'text-amber-400' : 'text-red-400')),
                    'afford_bar' => $a->monthly_cost === 0 ? '#10b981' : (($monthlyGross > 0 && $a->monthly_cost/$monthlyGross < .1) ? '#10b981' : (($monthlyGross > 0 && $a->monthly_cost/$monthlyGross < .25) ? '#f59e0b' : '#f87171')),
                ])->values();
            @endphp

            <template x-for="(asset, idx) in filteredStarter" :key="asset.id">
                <div class="asset-card" :class="asset.maxed ? 'owned' : ''" @click="openInspect(asset)">
                    {{-- Image area --}}
                    <div class="card-img" :style="'background:' + catGradient(asset.category)">
                        <template x-if="asset.image">
                            <img :src="asset.image" :alt="asset.name" loading="lazy"
                                 :style="asset.icon ? 'opacity:.6' : 'opacity:.92'"
                                 onerror="this.style.display='none'">
                        </template>
                        {{-- Overlay darkens image for emoji contrast; skip when image-only --}}
                        <div class="card-img-overlay" x-show="!!asset.icon"></div>
                        <template x-if="asset.icon">
                            <span class="card-emoji" x-text="asset.icon"></span>
                        </template>
                        {{-- Fallback: no icon AND no image → show category emoji --}}
                        <template x-if="!asset.icon && !asset.image">
                            <span class="card-emoji" x-text="catEmoji(asset.category)" style="opacity:.5;"></span>
                        </template>

                        {{-- Badge --}}
                        <template x-if="asset.badge">
                            <span class="card-badge" :style="'background:' + badgeColor(asset.badge) + '22;color:' + badgeColor(asset.badge) + ';border:1px solid ' + badgeColor(asset.badge) + '44;'" x-text="asset.badge.toUpperCase()"></span>
                        </template>

                        <span class="card-heart">♡</span>

                        {{-- Owned badge --}}
                        <template x-if="asset.maxed">
                            <span class="card-owned-badge">✓ Owned</span>
                        </template>
                    </div>

                    {{-- Body --}}
                    <div class="card-body">
                        <span class="card-chip" :style="'background:' + catChipBg(asset.category) + ';color:' + catChipColor(asset.category)">
                            <span x-text="catEmoji(asset.category)"></span>
                            <span x-text="catLabel(asset.category)"></span>
                        </span>
                        <div class="card-name" x-text="asset.name"></div>
                        <div class="card-desc" x-text="asset.desc"></div>
                        <div class="card-stats">
                            <div>
                                <div class="card-stat-lbl">Net/Mo</div>
                                <div class="card-stat-val" :class="asset.net > 0 ? 'pos' : (asset.net < 0 ? 'neg' : 'neutral')"
                                     x-text="(asset.net >= 0 ? '+' : '') + 'Ksh ' + Math.abs(asset.net).toLocaleString()"></div>
                            </div>
                            <div>
                                <div class="card-stat-lbl">Price</div>
                                <div class="card-stat-val neutral" x-text="'Ksh ' + asset.price.toLocaleString()"></div>
                            </div>
                        </div>
                        <div class="card-view-btn">
                            <span>View Details</span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Empty state --}}
            <template x-if="filteredStarter.length === 0">
                <div style="padding:3rem 1rem;text-align:center;color:rgba(255,255,255,.3);font-size:.85rem;">
                    No starter assets in this category yet.
                </div>
            </template>
        </div>

        <button class="carousel-nav next" @click="scrollCarousel('starter', 1)" aria-label="Next">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>
</div>

{{-- ═══════════════ GROWTH PLAYS ═══════════════ --}}
<div class="section-wrap fade-up-5" style="padding-top:.5rem;">
    <div class="section-header">
        <div style="display:flex;align-items:center;gap:.5rem;">
            <span style="font-size:1.25rem;">📈</span>
            <span class="section-title">Growth Plays</span>
        </div>
        <span class="section-sub desk-show">Assets with higher potential returns</span>
        <a href="{{ route('marketplace.all') }}" class="mob-section-view-all mob-show">View all →</a>
    </div>

    <div class="growth-grid">
        @foreach($growthSections as $key => $section)
        <a href="{{ route('marketplace.all') }}?section={{ $key }}" class="growth-card">
            <div class="growth-icon" style="background:{{ $section['color'] }}22;color:{{ $section['color'] }};">
                {{ $section['icon'] }}
            </div>
            <div class="growth-text">
                <div class="growth-label">{{ $section['label'] }}</div>
                <div class="growth-desc">{{ $section['desc'] }}</div>
                @if(($section['count'] ?? 0) > 0)
                <div style="font-size:.68rem;color:rgba(255,255,255,.3);margin-top:.25rem;">{{ $section['count'] }} asset{{ $section['count'] != 1 ? 's' : '' }}</div>
                @endif
            </div>
            <span class="growth-arrow">→</span>
        </a>
        @endforeach
    </div>
</div>

{{-- ═══════════════ CTA BANNER ═══════════════ --}}
<div class="cta-banner fade-up-5" style="margin-top:.5rem;">
    <div class="cta-left">
        <div class="cta-shield">🛡</div>
        <div>
            <div class="cta-heading">Smart moves today, freedom tomorrow.</div>
            <div class="cta-sub">Every asset you buy is a step toward your dream life in Pesa City.</div>
        </div>
    </div>
    <a href="{{ route('marketplace.all') }}" class="cta-explore-btn">
        Explore All Assets →
    </a>
</div>

{{-- ═══════════════ BUY MODAL ═══════════════ --}}
<div x-show="inspecting" x-cloak x-transition.opacity
     class="fixed inset-0 flex items-center justify-center p-3 sm:p-6"
     style="z-index:9990;background:rgba(0,0,0,.85);backdrop-filter:blur(12px);overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;"
     @click.self="inspecting=null;buyMsg='';">

    {{-- Modal has NO max-height and NO inner scroll region: it grows naturally and
         the OVERLAY scrolls — works on every mobile browser. Footer is sticky so
         the buy/cancel buttons are always on screen. --}}
    <div x-show="inspecting"
         class="modal-enter w-full max-w-lg rounded-3xl"
         style="background:linear-gradient(160deg,#0f172a,#1e1b4b);border:1px solid rgba(139,92,246,.35);margin:auto;">
        <template x-if="inspecting">
            <div>
                <div class="relative h-40 overflow-hidden rounded-t-3xl" :class="'cat-' + inspecting.category">
                    <template x-if="inspecting.image">
                        <img :src="inspecting.image" class="absolute inset-0 w-full h-full object-cover"
                             :style="inspecting.icon ? 'opacity:.45' : 'opacity:.85'"
                             onerror="this.style.display='none'">
                    </template>
                    <div class="absolute inset-0" style="background:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:28px 28px;"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="icon-bob" style="font-size:4rem;"
                              x-text="inspecting.icon || catEmoji(inspecting.category)"></span>
                    </div>
                    <button @click="inspecting=null;buyMsg='';"
                            class="absolute top-4 right-4 w-8 h-8 rounded-xl flex items-center justify-center"
                            style="background:rgba(0,0,0,.4);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.1);color:rgba(255,255,255,.6);">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <div class="absolute bottom-0 left-0 right-0 h-16" style="background:linear-gradient(to top,#0f172a,transparent);"></div>
                </div>

                <div class="px-6 pt-2 pb-4">
                    <div class="mb-5">
                        <h2 class="text-xl font-black text-white" x-text="inspecting.name"></h2>
                        <p class="text-sm text-gray-400" x-text="inspecting.brand || ''"></p>
                        <p class="text-sm text-gray-300 mt-3 leading-relaxed" x-text="inspecting.desc"></p>
                        <p class="text-sm text-indigo-300/80 italic mt-2" x-text="'&quot;' + inspecting.flavor + '&quot;'"></p>
                    </div>

                    <div class="grid grid-cols-3 gap-3 mb-5">
                        <div class="rounded-2xl p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Buy Price</p>
                            <p class="text-sm font-black text-white mt-1" x-text="'Ksh ' + inspecting.price.toLocaleString()"></p>
                        </div>
                        <div class="rounded-2xl p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Net/Month</p>
                            <p class="text-sm font-black mt-1" :class="inspecting.net >= 0 ? 'text-emerald-400' : 'text-red-400'"
                               x-text="(inspecting.net >= 0 ? '+' : '') + 'Ksh ' + Math.abs(inspecting.net).toLocaleString()"></p>
                        </div>
                        <div class="rounded-2xl p-3 text-center" style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">
                            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Value/Month</p>
                            <p class="text-sm font-black mt-1" :class="inspecting.rate >= 0 ? 'text-emerald-400' : 'text-red-400'"
                               x-text="(inspecting.rate >= 0 ? '+' : '') + inspecting.rate + '%'"></p>
                        </div>
                    </div>

                    <div class="mb-5 rounded-2xl overflow-hidden" style="border:1px solid rgba(255,255,255,.08);">
                        <div class="px-4 py-2" style="background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.06);">
                            <p class="text-xs font-black text-white">💰 How this works financially</p>
                        </div>
                        <div class="p-4 space-y-2">
                            <template x-if="inspecting.income > 0">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Monthly revenue</span>
                                    <span class="font-bold text-emerald-400" x-text="'+Ksh ' + inspecting.income.toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.cost > 0">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-400">Monthly costs</span>
                                    <span class="font-bold text-red-400" x-text="'-Ksh ' + inspecting.cost.toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.income > 0 || inspecting.cost > 0">
                                <div class="flex items-center justify-between text-sm border-t border-white/10 pt-2 mt-2">
                                    <span class="font-black text-white">Net cash flow</span>
                                    <span class="font-black text-lg" :class="inspecting.net >= 0 ? 'text-emerald-400' : 'text-red-400'"
                                          x-text="(inspecting.net >= 0 ? '+' : '') + 'Ksh ' + Math.abs(inspecting.net).toLocaleString() + '/mo'"></span>
                                </div>
                            </template>
                            <template x-if="inspecting.payback">
                                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-bold text-emerald-400"
                                     style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.2);">
                                    ⚡ Pays itself off in ~<span x-text="inspecting.payback"></span> game months
                                    (<span x-text="gdApprox(inspecting.payback * 30)"></span>), then pure profit
                                </div>
                            </template>
                            <template x-if="!inspecting.payback && inspecting.projected && inspecting.rate > 0">
                                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-bold text-indigo-300"
                                     style="background:rgba(99,102,241,.1);border:1px solid rgba(99,102,241,.2);">
                                    📈 At <span x-text="inspecting.rate"></span>%/mo, worth ~Ksh <span x-text="(inspecting.projected||0).toLocaleString()"></span> in 12 game months (<span x-text="gdApprox(360)"></span>)
                                </div>
                            </template>
                            <template x-if="inspecting.net < 0 && !inspecting.payback">
                                <div class="mt-3 rounded-xl px-3 py-2 text-xs font-bold text-orange-400"
                                     style="background:rgba(251,146,60,.1);border:1px solid rgba(251,146,60,.2);">
                                    ⚠️ This asset costs more than it earns. Only buy if you can absorb the monthly cost.
                                </div>
                            </template>
                        </div>
                    </div>

                    <template x-if="inspecting.afford_pct > 0">
                        <div class="mb-5 rounded-2xl overflow-hidden" style="border:1px solid rgba(255,255,255,.08);">
                            <div class="px-4 py-2" style="background:rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.06);">
                                <p class="text-xs font-black text-white">📊 Can you really afford this?</p>
                            </div>
                            <div class="p-4">
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-xs text-gray-400">Monthly income strain</span>
                                    <span class="text-xs font-black" :class="inspecting.afford_color" x-text="inspecting.afford_label"></span>
                                </div>
                                <div class="afford-bar mb-3">
                                    <div class="afford-fill" :style="'width:' + Math.min(100,inspecting.afford_pct) + '%;background:' + inspecting.afford_bar"></div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <template x-if="inspecting.bill">
                        <div class="mb-4 rounded-xl px-4 py-3 flex items-start gap-2"
                             style="background:rgba(251,146,60,.08);border:1px solid rgba(251,146,60,.2);">
                            <span class="text-orange-400 text-sm mt-0.5">⚡</span>
                            <p class="text-xs text-orange-300 leading-snug">Buying this will automatically add a recurring bill to your expenses.</p>
                        </div>
                    </template>

                    <div class="mb-4 rounded-xl px-4 py-3 flex items-start gap-2"
                         style="background:rgba(99,102,241,.08);border:1px solid rgba(99,102,241,.2);">
                        <span class="text-indigo-400 text-sm mt-0.5 shrink-0">💡</span>
                        <p class="text-xs text-indigo-300 leading-snug" x-text="inspecting.edu"></p>
                    </div>

                    {{-- Financing option (vehicles & property) --}}
                    <template x-if="inspecting.financing && !inspecting.maxed">
                        <div class="mb-4 rounded-2xl overflow-hidden" style="border:1px solid rgba(245,158,11,.25);">
                            <div class="px-4 py-2" style="background:rgba(245,158,11,.08);border-bottom:1px solid rgba(245,158,11,.15);">
                                <p class="text-xs font-black text-amber-300">🏦 Or finance it — deposit now, pay monthly</p>
                            </div>
                            <div class="p-4 space-y-1.5 text-sm">
                                <div class="flex justify-between"><span class="text-gray-400">Deposit (pay now)</span><span class="font-bold text-white" x-text="'Ksh ' + inspecting.financing.deposit.toLocaleString()"></span></div>
                                <div class="flex justify-between"><span class="text-gray-400">Monthly installment (auto-billed)</span><span class="font-bold text-white" x-text="'Ksh ' + inspecting.financing.monthly.toLocaleString()"></span></div>
                                <div class="flex justify-between"><span class="text-gray-400" x-text="'Term: ' + inspecting.financing.months + ' game months (' + gdApprox(inspecting.financing.months * 30) + ')'"></span><span class="font-bold text-amber-300" x-text="'Total: Ksh ' + inspecting.financing.total_cost.toLocaleString()"></span></div>
                                <p class="text-[11px] text-amber-200/70 pt-1" x-text="'Financing costs Ksh ' + inspecting.financing.interest_cost.toLocaleString() + ' more than paying cash — that\'s the price of credit.'"></p>
                            </div>
                        </div>
                    </template>

                    <div x-show="buyMsg" x-cloak x-transition
                         class="mb-4 rounded-xl px-4 py-3 text-xs font-bold text-center"
                         :class="buyOk ? 'text-emerald-400 bg-emerald-500/10 border border-emerald-500/20' : 'text-red-400 bg-red-500/10 border border-red-500/20'"
                         x-text="buyMsg"></div>
                </div>

                <div class="px-6 py-4 flex gap-3 flex-wrap rounded-b-3xl"
                     style="border-top:1px solid rgba(255,255,255,.08);">
                    <button @click="inspecting=null;buyMsg='';"
                            class="flex-1 py-3 rounded-xl text-sm font-bold text-gray-400 hover:text-white transition-colors"
                            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);min-width:90px;">
                        Cancel
                    </button>
                    <template x-if="inspecting && !inspecting.maxed && inspecting.canAfford">
                        <button @click="confirmBuy(false)" :disabled="buying"
                                class="flex-1 py-3.5 rounded-xl text-sm font-black transition-all hover:scale-[1.02] disabled:opacity-50"
                                style="background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;box-shadow:0 4px 20px rgba(99,102,241,.45);min-width:140px;">
                            <span x-show="!buying">✓ Buy Cash</span>
                            <span x-show="buying" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-linecap="round"/></svg>
                                Buying…
                            </span>
                        </button>
                    </template>
                    <template x-if="inspecting && !inspecting.maxed && inspecting.financing && inspecting.canFinanceDeposit">
                        <button @click="confirmBuy(true)" :disabled="buying"
                                class="flex-1 py-3.5 rounded-xl text-sm font-black transition-all hover:scale-[1.02] disabled:opacity-50"
                                style="background:linear-gradient(135deg,#f59e0b,#f97316);color:#fff;box-shadow:0 4px 20px rgba(245,158,11,.4);min-width:140px;">
                            <span x-show="!buying" x-text="'🏦 Finance — Ksh ' + inspecting.financing.deposit.toLocaleString() + ' Deposit'"></span>
                            <span x-show="buying">Processing…</span>
                        </button>
                    </template>
                    <template x-if="inspecting && inspecting.maxed">
                        <div class="flex-1 py-3 rounded-xl text-sm font-black text-center"
                             style="background:rgba(139,92,246,.15);border:1px solid rgba(139,92,246,.3);color:#c4b5fd;">✓ Already owned</div>
                    </template>
                    <template x-if="inspecting && !inspecting.maxed && !inspecting.canAfford && !(inspecting.financing && inspecting.canFinanceDeposit)">
                        <div class="flex-1 py-3 rounded-xl text-sm font-bold text-center text-gray-500"
                             style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);">Insufficient balance</div>
                    </template>
                </div>
            </div>
        </template>
    </div>
</div>

<script>
const STARTER_ASSETS = @json($allStarter);

// Real seconds per game day (admin clock) → "≈ real time" hints on game-day durations
window.__PESA_SPT__ = {{ (int) round(app(\App\Services\GameClock::class)->secondsPerTick()) }};
window.gdApprox = function (days) {
    const s = Math.max(0, days) * (window.__PESA_SPT__ || 514);
    if (s < 90)    return 'under 2 real min';
    if (s < 3600)  return '≈' + Math.round(s / 60) + ' real min';
    if (s < 86400) return '≈' + (Math.round(s / 360) / 10) + ' real hrs';
    return '≈' + (Math.round(s / 8640) / 10) + ' real days';
};

function marketplace() {
    return {
        init() {
            // Lock page scroll while the buy modal is open so the wheel scrolls the modal, not the page
            this.$watch('inspecting', v => { document.body.style.overflow = v ? 'hidden' : ''; });
        },
        inspecting: null,
        buying: false,
        buyMsg: '',
        buyOk: true,
        activeCategory: 'all',
        sortMode: 'featured',

        get filteredStarter() {
            let assets = STARTER_ASSETS.filter(a =>
                this.activeCategory === 'all' || a.category === this.activeCategory
            );
            switch(this.sortMode) {
                case 'price_asc':  return [...assets].sort((a,b) => a.price - b.price);
                case 'price_desc': return [...assets].sort((a,b) => b.price - a.price);
                case 'income':     return [...assets].sort((a,b) => b.net - a.net);
                default:           return assets;
            }
        },

        scrollCarousel(ref, dir) {
            const el = this.$refs[ref + 'Carousel'] || document.getElementById(ref + 'Carousel');
            if (el) el.scrollBy({ left: dir * 264, behavior: 'smooth' });
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

        catEmoji(cat) {
            const m = {vehicle:'🚗',property:'🏠',business:'🏢',investment:'📈',gadget:'📱'};
            return m[cat] || '📦';
        },
        catLabel(cat) {
            const m = {vehicle:'Use & Earn',property:'Passive Income',business:'Business',investment:'Investment',gadget:'Gadget'};
            return m[cat] || cat;
        },
        catChipBg(cat) {
            const m = {vehicle:'rgba(59,130,246,.15)',property:'rgba(16,185,129,.15)',business:'rgba(249,115,22,.15)',investment:'rgba(6,182,212,.15)',gadget:'rgba(139,92,246,.15)'};
            return m[cat] || 'rgba(107,114,128,.15)';
        },
        catChipColor(cat) {
            const m = {vehicle:'#93c5fd',property:'#6ee7b7',business:'#fdba74',investment:'#67e8f9',gadget:'#c4b5fd'};
            return m[cat] || '#9ca3af';
        },
        catGradient(cat) {
            const m = {
                vehicle:'linear-gradient(145deg,#1e3a8a,#1e40af,#0f172a)',
                property:'linear-gradient(145deg,#064e3b,#065f46,#0f172a)',
                business:'linear-gradient(145deg,#3730a3,#4c1d95,#1e1b4b)',
                investment:'linear-gradient(145deg,#0e7490,#0891b2,#0f172a)',
                gadget:'linear-gradient(145deg,#831843,#9d174d,#1a1025)',
            };
            return m[cat] || 'linear-gradient(145deg,#1f2937,#374151,#111827)';
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

// Stars
(function(){
    const c=document.getElementById('heroStars');
    if(!c) return;
    const cfg=[
        {cls:'star star-sm',count:55},
        {cls:'star star-md',count:25},
        {cls:'star star-lg',count:12},
    ];
    cfg.forEach(({cls,count})=>{
        for(let i=0;i<count;i++){
            const s=document.createElement('span');
            s.className=cls;
            s.style.cssText=`left:${(Math.random()*110-5).toFixed(1)}%;top:${(Math.random()*110-5).toFixed(1)}%;animation-delay:${(Math.random()*6).toFixed(2)}s;animation-duration:${(Math.random()*3+2).toFixed(2)}s;opacity:${(Math.random()*.4).toFixed(2)};`;
            c.appendChild(s);
        }
    });
})();

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
<div class="bottom-bar mob-show">
    <div class="bottom-bar-inner">
        <a href="{{ route('dashboard') }}" class="bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            Home
        </a>
        <a href="{{ route('life.career') }}" class="bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/><line x1="12" y1="12" x2="12" y2="16"/><line x1="10" y1="14" x2="14" y2="14"/></svg>
            Career
        </a>
        <a href="{{ route('marketplace') }}" class="bb-tab bb-tab-active">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Assets
        </a>
        <a href="{{ route('life.timeline') }}" class="bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Timeline
        </a>
        <a href="{{ route('profile.edit') }}" class="bb-tab">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            Profile
        </a>
    </div>
</div>

@include('components.mama-pesa-chat')
<x-mobile-bottom-nav active="city" />
</body>
</html>
