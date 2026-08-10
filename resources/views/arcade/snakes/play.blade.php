<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesa Trail — Playing</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @include('partials.trackers')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            background:
                radial-gradient(ellipse 900px 500px at 20% 0%, rgba(245,158,11,.10), transparent 60%),
                radial-gradient(ellipse 900px 600px at 85% 100%, rgba(99,102,241,.14), transparent 60%),
                #0b0a16;
            font-family:'Figtree',sans-serif; color:#fff; overscroll-behavior:none; min-height:100vh;
        }
        .atmo-prop { position:fixed; font-size:2.4rem; opacity:.09; pointer-events:none; z-index:0; filter:blur(.3px); }
        .topbar { display:flex; align-items:center; gap:.6rem; padding:.75rem 1.1rem; background:rgba(8,7,16,.75); backdrop-filter:blur(14px); border-bottom:1px solid rgba(255,255,255,.07); position:sticky; top:0; z-index:40; box-shadow:0 4px 20px rgba(0,0,0,.25); flex-wrap:wrap; row-gap:.4rem; }
        .chip { display:flex; align-items:center; gap:.35rem; padding:.4rem .75rem; border-radius:.7rem; font-size:.78rem; font-weight:800; background:rgba(255,255,255,.06); border:1px solid rgba(255,255,255,.1); white-space:nowrap; }
        @media (max-width:640px) {
            .topbar { padding:.6rem .7rem; gap:.4rem; }
            .topbar .chip { padding:.32rem .55rem; font-size:.7rem; }
            .topbar .title-text { display:none; }
            .invite-chip .invite-label { display:none; }
        }

        /* Notification bell */
        .notif-wrap { position:relative; margin-left:auto; }
        .notif-bell { cursor:pointer; position:relative; }
        .notif-badge { position:absolute; top:-5px; right:-5px; background:#ef4444; color:#fff; font-size:.6rem; font-weight:900; border-radius:999px; min-width:16px; height:16px; display:flex; align-items:center; justify-content:center; padding:0 3px; box-shadow:0 0 0 2px #0b0a16; }
        .notif-panel { position:absolute; right:0; top:calc(100% + .5rem); width:320px; max-width:88vw; max-height:420px; overflow-y:auto; background:#14121f; border:1px solid rgba(255,255,255,.14); border-radius:1rem; box-shadow:0 20px 50px rgba(0,0,0,.5); z-index:50; display:none; }
        .notif-panel.show { display:block; }
        .notif-panel-head { padding:.75rem .9rem; font-size:.75rem; font-weight:900; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; border-bottom:1px solid rgba(255,255,255,.08); }
        .notif-row { display:flex; gap:.6rem; padding:.7rem .9rem; border-bottom:1px solid rgba(255,255,255,.05); text-decoration:none; color:inherit; }
        .notif-row:hover { background:rgba(255,255,255,.03); }
        .notif-row.unread { background:rgba(99,102,241,.07); }
        .notif-row .notif-icon { font-size:1.2rem; flex-shrink:0; }
        .notif-row .notif-title { font-weight:800; font-size:.78rem; margin-bottom:.15rem; }
        .notif-row .notif-body { font-size:.7rem; color:#9ca3af; line-height:1.4; }
        .notif-row .notif-time { font-size:.62rem; color:#6b7280; margin-top:.2rem; }
        .notif-empty { padding:1.5rem; text-align:center; font-size:.75rem; color:#6b7280; }
        .layout { position:relative; z-index:1; display:grid; grid-template-columns: 1fr; gap:1rem; max-width:1760px; margin:0 auto; padding:1rem; }
        .mobile-drawer { display:contents; } /* desktop: invisible — child stays a direct grid item */
        @media (min-width:1024px) {
            /* One sidebar, board gets the rest — matches the world map's layout
               instead of the old two-sidebar split that left the board squeezed
               and the space either side of it unused. */
            .layout { grid-template-columns: 1fr 320px; align-items:start; }
            .panel-board { grid-column:1; }
            .panel-sidebar {
                grid-column:2;
                /* The board fills the full available width (letting its height
                   follow the real aspect ratio, even past one screen's height) —
                   rather than making the whole page that tall to see it, the
                   sidebar stays pinned in the viewport and scrolls internally. */
                position:sticky; top:1rem; max-height:calc(100vh - 2rem); overflow-y:auto;
            }
        }
        .menu-toggle { display:none; cursor:pointer; }
        @media (max-width:1023px) {
            /* The board art is landscape (1264x848); sized to the full mobile
               width it's naturally shorter than one screen. Forcing the page to
               a full extra viewport height (the desktop min-height) just turned
               that leftover into a big dead gap below the board — let the page
               be exactly as tall as its real content instead. */
            body { min-height:auto; }
            .layout { padding:.5rem; gap:.5rem; }
            .menu-toggle { display:flex; }
            .mobile-drawer {
                display:flex; flex-direction:column; gap:.6rem;
                position:fixed; top:0; bottom:0; right:-100vw; width:min(230px,58vw); z-index:60;
                overflow-y:auto; padding:4.4rem .6rem 1.5rem; background:#0d0b1a;
                box-shadow:-20px 0 50px rgba(0,0,0,.55);
                transition:right .3s cubic-bezier(.4,0,.2,1);
            }
            .mobile-drawer.drawer-open { right:0; }
            .mobile-drawer .panel { box-shadow:none; padding:.7rem; }
            .mobile-drawer .avatar { width:2rem; height:2rem; font-size:.9rem; }
            .mobile-drawer .profile-head { gap:.5rem; margin-bottom:.7rem; }
            .mobile-drawer .reaction-btn { font-size:.9rem; padding:.2rem .3rem; }

            /* .mobile-drawer's z-index:60 only competes against OTHER elements
               inside .layout — .layout itself (position:relative; z-index:1) is a
               SIBLING of .drawer-backdrop (z-index:55) at the outer level, and it's
               THAT z-index:1 vs 55 comparison that actually decides paint/click
               order between them. 1 < 55 meant the backdrop sat on top of the
               entire .layout subtree — drawer included — so every tap on a drawer
               button was actually landing on the backdrop's close handler behind
               the scenes, even though the drawer visually looked like it was on
               top. Only needs to clear the backdrop's 55, not the topbar's 80. */
            body.drawer-open .layout { z-index:56; }

            /* The die/turn-banner/event-toast float as fixed-position elements
               above the board so they stay visible while the drawer is closed —
               left merely visible-but-inert (z-index dropped, pointer-events
               off) while open still showed the die floating over the drawer,
               reading as clutter/still-broken. Fully hiding them while the
               drawer is open is cleaner — they're all still reachable the
               instant it closes. #turnBanner is scoped to .die-panel
               specifically (not a bare body.drawer-open #turnBanner) because
               that selector only matches when the banner is actually nested
               inside the die panel — true in rotated (forced-landscape) mode,
               but on a mobile device already held landscape (no forced
               rotation), the banner instead lives naturally inside the drawer's
               own sidebar content and should stay visible there. */
            body.drawer-open .die-panel,
            body.drawer-open .die-panel #turnBanner,
            body.drawer-open .event-toast { display:none; }

            /* Fills the space below the board with the same turn banner + opponent
               stats the desktop sidebar shows — real content instead of dead space,
               and visible without opening the drawer. */
            .mobile-hud { margin-top:.75rem; }
            .mobile-hud:empty { display:none; margin:0; padding:0; border:none; }
        }
        @media (min-width:1024px) { .mobile-hud { display:none; } }
        .drawer-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.55); z-index:55; }
        .drawer-backdrop.show { display:block; }

        /* The board art is landscape — on a portrait phone that means playing it
           small and narrow no matter how it's sized. Auto-rotating the board itself
           (not asking the player to physically turn their phone) lets it fill the
           whole screen at full landscape size instead. Standard CSS-only "force
           landscape" trick: a box sized to the viewport's SWAPPED dimensions
           (100vh × 100vw), rotated 90° around its top-left corner, then shifted back
           into view by its own (now-vertical) height. getBoundingClientRect() already
           accounts for the rotation, so the die's existing dockOverBoard() positioning
           keeps landing correctly on top of it with no extra math needed. */
        @media (max-width:1023px) and (orientation:portrait) {
            /* The board fills the ENTIRE screen — including the strip behind the
               topbar. Earlier this reserved space by measuring the topbar's real
               height via JS (--topbar-h) and starting the board below it, but that
               measured value kept coming out larger than the topbar's true visible
               height (root cause never pinned down despite several passes), which
               always left a dead gap between them no matter how the measurement
               was refined. This sidesteps needing that measurement at all — the
               topbar already has its own translucent, blurred background
               (rgba(8,7,16,.75) + backdrop-filter:blur), designed to sit as a
               floating HUD over content rather than push it down, so letting the
               board render underneath it (z-index below the topbar) looks correct
               by construction, with zero risk of a gap. Position offsets (`top`/
               `left`) are NOT swapped by the rotation — `top` still shifts the
               final screen's Y position, `left` still shifts screen X, exactly as
               normal. Only the box's OWN `width`/`height` dimensions swap which
               final screen axis they extend along (width → screen-Y extent,
               height → screen-X extent). */
            .panel-board {
                position:fixed; z-index:30; top:0; left:0; margin:0; padding:0;
                /* vh/vw is a fallback for older browsers — it's a STATIC size that
                   doesn't shrink when the browser's address bar is actually showing,
                   so the rotated board rendered slightly larger than the real
                   visible viewport, cropping off the edges (the "START"/"FINISH"
                   corners not visible" bug). dvh/dvw track the ACTUAL current
                   viewport dynamically and override the line above wherever
                   supported (current mobile Chrome/Safari). */
                width:100vh; height:100vw;
                width:100dvh; height:100dvw;
                transform-origin:top left; transform:rotate(90deg) translateY(-100%);
                display:flex; align-items:center; justify-content:center; background:#0b0a16;
            }
            /* The board image's fixed 1264:848 ratio doesn't match a typical
               phone's rotated proportions. Forcing board-wrap to exactly
               100%x100% of the rotated screen box (dropping the ratio lock)
               used to close that gap, but stretched the art on any device
               whose rotated ratio diverges from 1264:848 — circles became
               ovals, tiles looked squashed. Tokens are plotted as left_m/top_m
               PERCENTAGES OF board-wrap ITSELF (see tokenPos()), calibrated
               against board-wrap exactly filling the image with no internal
               dead space — so simply switching the <img> to object-fit:contain
               would keep the art undistorted but shift every token off its
               tile, since the visible image would then be smaller than and
               centered within board-wrap rather than equal to it.
               This min()/calc() pair instead sizes board-wrap ITSELF to the
               largest 1264:848 rectangle that fits inside the rotated screen
               box (100dvh x 100dvw), so board-wrap == the image's true bounds
               again (fill stays undistorted, tokens stay correctly calibrated)
               — any leftover space becomes slim letterbox/pillarbox bars
               OUTSIDE board-wrap, filled by .panel-board's own centered flex
               layout and dark background rather than stretched pixels. */
            .panel-board .board-wrap {
                /* vh/vw fallback, same reasoning as .panel-board above — dvh/dvw
                   override wherever supported. */
                width: min(100vh, calc(100vw * 1264 / 848));
                height: min(100vw, calc(100vh * 848 / 1264));
                width: min(100dvh, calc(100dvw * 1264 / 848));
                height: min(100dvw, calc(100dvh * 848 / 1264));
                aspect-ratio: auto;
            }
            .panel-board .board-wrap img { object-fit: fill; }
            /* Pushed below the fixed, full-viewport-height board and topbar (both
               position:fixed here, so neither reserves any normal-flow space —
               mobile-hud would otherwise start flowing from the very top of the
               page, underneath both). The board's visual height is 100vh. */
            .mobile-hud { margin-top:calc(100vh + 1rem); }

            /* The topbar (back button, hamburger, notification bell) has to stay
               reachable above the now-fixed, full-viewport board, not get covered
               by it — pinned to a real screen-fixed strip rather than its normal
               sticky-in-flow position. */
            .topbar { position:fixed; top:0; left:0; right:0; z-index:80; }
        }

        /* Mobile: the die escapes the drawer as an always-visible floating,
           draggable widget — position:fixed on a drawer descendant still
           resolves against the viewport (not the drawer) as long as the
           drawer itself only animates via `right`, never `transform`. */
        @media (max-width:1023px) {
            .die-panel {
                position:fixed; z-index:70; top:0; left:0; /* JS sets real left/top, over the board, on load */
                margin:0; padding:.3rem; gap:.3rem; width:80px;
            }
            .die-panel .die-badge { width:78px; height:78px; touch-action:none; cursor:grab; }
            .die-panel .die-badge.dragging { cursor:grabbing; }
            .die-panel .die-scene { transform:scale(.72); }
            /* Base .die-badge .die-label keeps a wide letter-spacing (.1em) meant
               for the roomy 150px desktop circle — on the shrunk 78px mobile
               circle that same tracking pushed "ROLL DICE" just past one line's
               width and it wrapped to two. Tightening the tracking (rather than
               shrinking the font further) frees up the room instead, so the
               label stays a legible size and fits on one row. */
            .die-panel .die-label { font-size:.58rem; letter-spacing:.02em; white-space:nowrap; }
            .die-panel .die-caption { display:none; }
            .die-panel .cashout-btn { font-size:.6rem; padding:.35rem .2rem; }
            .die-panel.thrown .die-badge { animation:dieThrow .45s ease; }

            /* These three float above the board and were sized for a roomy
               desktop screen — on a phone they covered so much of the board
               that the game underneath became hard to see while they were up. */
            .event-toast { max-width:230px; padding:.6rem .75rem; font-size:.68rem; border-radius:.8rem; line-height:1.4; }
            .notif-panel { width:250px; max-height:280px; }
            .notif-row { padding:.6rem .7rem; }
            .overlay-card { padding:1.3rem; max-width:250px; border-radius:1.1rem; }
            .overlay-card .text-3xl { font-size:1.6rem; }
            .overlay-card .text-xl { font-size:1rem; }
            .overlay-card .text-sm { font-size:.72rem; }
        }
        /* Still too large on small phones even after the shrink above — the
         * board itself needs the screen space more than the die does there. */
        @media (max-width:480px) {
            .die-panel { width:64px; }
            .die-panel .die-badge { width:54px; height:54px; }
            .die-panel .die-scene { transform:scale(.56); }
            .die-panel .die-label { font-size:.46rem; letter-spacing:0; }
            .die-panel .cashout-btn { font-size:.52rem; padding:.28rem .15rem; border-radius:.55rem; }
        }
        @keyframes dieThrow { 0% { transform:scale(1); } 35% { transform:scale(1.22) rotate(10deg); } 65% { transform:scale(.92) rotate(-6deg); } 100% { transform:scale(1) rotate(0); } }
        .die-value-badge {
            position:absolute; top:-2px; right:-2px; width:26px; height:26px; border-radius:50%;
            background:#1a1a2e; border:2px solid #fbbf24; color:#fbbf24; font-weight:900; font-size:.8rem;
            display:none; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(0,0,0,.5); z-index:2;
        }
        .die-value-badge.show { display:flex; }
        .panel { background:rgba(255,255,255,.035); border:1px solid rgba(255,255,255,.09); border-radius:1.25rem; padding:1.1rem; box-shadow:0 8px 26px rgba(0,0,0,.22); }
        .avatar { width:2.6rem; height:2.6rem; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem; font-weight:900; flex-shrink:0; background:linear-gradient(135deg,#f59e0b,#d97706); box-shadow:0 3px 10px rgba(245,158,11,.35); }
        .avatar-op { background:linear-gradient(135deg,#6366f1,#4f46e5); box-shadow:0 3px 10px rgba(99,102,241,.35); }
        .avatar-bot { background:linear-gradient(135deg,#64748b,#475569); box-shadow:0 3px 10px rgba(100,116,139,.35); }
        .profile-head { display:flex; align-items:center; gap:.7rem; margin-bottom:1rem; }
        /* Sized by aspect-ratio (matches the real board art, 1264x848) rather than a
           plain width:100% — on a very wide container, scaling purely by width would
           make the height balloon to match (no cap), pushing the board far below the
           fold. aspect-ratio keeps the wrapper's box pixel-exact with the image so
           the tokens' percentage-based left/top positions stay correctly aligned
           (no letterboxing, unlike object-fit centering inside a mismatched box). */
        .board-wrap {
            position:relative; margin:0 auto; border-radius:1.4rem; overflow:hidden;
            box-shadow:0 0 0 1px rgba(245,158,11,.18), 0 20px 50px rgba(0,0,0,.4);
            width:100%; aspect-ratio:1264/848;
        }
        .board-wrap img { width:100%; height:100%; display:block; object-fit:contain; }
        /* The board is the game, not a card containing the game — strip the
           generic .panel framing (background/border/shadow/padding) so it isn't
           sitting inside a visibly boxed, gutter-padded rectangle. */
        .panel-board {
            background:none; border:none; box-shadow:none; padding:0;
            overflow:auto; /* safety net: scroll rather than break if a screen ever can't fit it */
            /* Plain block, not flex — board-wrap centers itself via its own
               margin:0 auto. Avoids a flaky flex + percentage-width + aspect-ratio
               combination that can miscompute height in some browsers. */
        }
        .htp-row { display:flex; align-items:center; gap:.55rem; padding:.5rem .6rem; border-radius:.7rem; background:rgba(255,255,255,.025); margin-bottom:.4rem; font-size:.75rem; font-weight:600; color:#d1d5db; }
        .htp-row span.htp-icon { font-size:1rem; }
        .htp-toggle {
            width:100%; display:flex; align-items:center; justify-content:space-between;
            background:none; border:none; cursor:pointer; padding:.6rem 0 .3rem; margin-top:.75rem;
            border-top:1px solid rgba(255,255,255,.08);
            font-size:.75rem; font-weight:800; color:#9ca3af; text-transform:uppercase; letter-spacing:.06em;
        }
        .htp-chevron { font-size:.65rem; transition:transform .2s ease; }
        .htp-toggle.expanded .htp-chevron { transform:rotate(180deg); }
        .htp-body { max-height:0; overflow:hidden; transition:max-height .3s ease; }
        .htp-body.expanded { max-height:900px; padding-top:.6rem; }
        .token { position:absolute; width:26px; height:26px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; transform:translate(-50%,-50%); transition:left 1s cubic-bezier(.34,1.2,.64,1), top 1s cubic-bezier(.34,1.2,.64,1); box-shadow:0 2px 8px rgba(0,0,0,.5); z-index:20; }
        /* While hopping tile-by-tile (animateHopPath()), each step needs a much
           quicker transition than the one long glide above — otherwise a 6-tile
           roll looks like slow motion repeated six times. Removed once the hop
           sequence finishes so any later single-jump resync uses the smooth one. */
        .token.hopping { transition:left .22s linear, top .22s linear; }
        .token-me { background:#f59e0b; border:2px solid #fff; z-index:21; font-size:16px; }
        .token.landed { animation: tokenPop .6s ease; }
        @keyframes tokenPop { 0% { box-shadow:0 0 0 0 rgba(245,158,11,.6); } 60% { box-shadow:0 0 0 16px rgba(245,158,11,0); } 100% { box-shadow:0 2px 8px rgba(0,0,0,.5); } }
        .token.glow-gold { animation: tokenGlowGold 1.4s ease; }
        @keyframes tokenGlowGold { 0%,100% { box-shadow:0 2px 8px rgba(0,0,0,.5); } 40% { box-shadow:0 0 0 10px rgba(251,191,36,.55), 0 0 26px 8px rgba(251,191,36,.6); } }
        .token-op { background:#6366f1; border:2px solid #fff; opacity:.9; }
        /* A token showing a real profile picture fills the same circle the emoji
           fallback sits inside of, rather than the photo floating as a separate
           square badge next to it. */
        /* Absolutely positioned over the always-rendered emoji fallback text
           (see the Blade/JS that render tokens) — a broken/stale photo URL's
           onerror handler removes just the <img>, revealing that emoji instead
           of a broken-image icon. */
        .token img { position:absolute; inset:0; width:100%; height:100%; border-radius:50%; object-fit:cover; }

        /* The board itself scales with the viewport (width:100%), but tokens
           were a flat 26px everywhere — proportionate on a narrow phone board,
           but tiny and hard to spot on a large desktop board that can render
           several times wider. Scales up only at the desktop breakpoint
           already used for the board's own two-column layout above. */
        @media (min-width:1024px) {
            .token { width:40px; height:40px; font-size:22px; }
            .token-me { font-size:24px; }
        }

        .reaction-bar { display:flex; flex-wrap:wrap; gap:.3rem; justify-content:center; margin:.6rem 0; }
        .reaction-btn {
            font-size:1.15rem; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
            border-radius:.6rem; padding:.25rem .4rem; cursor:pointer; line-height:1; transition:transform .1s, background .15s;
        }
        .reaction-btn:hover { background:rgba(255,255,255,.1); }
        .reaction-btn:active { transform:scale(.88); }
        .reaction-btn:disabled { opacity:.4; cursor:not-allowed; }
        .floating-emoji {
            position:fixed; font-size:2.2rem; z-index:9995; pointer-events:none;
            animation: floatEmojiUp 1.6s ease-out forwards;
        }
        /* Flies from where the money was won/lost (the token) to the KES chip
           in the topbar, landing right as the displayed number updates — a
           gain/loss reads as a single connected event instead of a number just
           silently changing in the corner. */
        .money-float {
            position:fixed; font-size:.95rem; font-weight:900; z-index:9996; pointer-events:none;
            transition:left .85s cubic-bezier(.2,.7,.3,1), top .85s cubic-bezier(.2,.7,.3,1), opacity .85s ease, transform .85s cubic-bezier(.2,.7,.3,1);
            text-shadow:0 2px 8px rgba(0,0,0,.6); transform:scale(1);
        }
        .money-float.mf-gain { color:#6ee7b7; }
        .money-float.mf-loss { color:#fca5a5; }
        .money-float.mf-arrived { opacity:0; transform:scale(.4); }
        @keyframes hudPotPulse { 0%,100% { transform:scale(1); } 40% { transform:scale(1.18); } }
        #hudPot.pulse { display:inline-block; animation:hudPotPulse .4s ease; }
        @keyframes floatEmojiUp {
            0% { transform:translate(-50%,-50%) scale(.4) rotate(-8deg); opacity:0; }
            15% { transform:translate(-50%,-70%) scale(1.15) rotate(6deg); opacity:1; }
            100% { transform:translate(-50%,-160%) scale(1.9) rotate(-4deg); opacity:0; }
        }
        /* Same motion/scale as floatEmojiUp, +90° on every rotate value — used in
           forced-landscape mode (see the .floating-emoji override) so the emoji
           glyph itself reads consistently with the rotated board/die/banner. */
        @keyframes floatEmojiUpRotated {
            0% { transform:translate(-50%,-50%) scale(.4) rotate(82deg); opacity:0; }
            15% { transform:translate(-50%,-70%) scale(1.15) rotate(96deg); opacity:1; }
            100% { transform:translate(-50%,-160%) scale(1.9) rotate(86deg); opacity:0; }
        }

        .stat-mini { text-align:center; }
        .stat-mini b { display:block; font-size:1.1rem; font-weight:900; }
        .stat-mini span { font-size:.62rem; color:#9ca3af; font-weight:700; text-transform:uppercase; letter-spacing:.08em; }

        /* 3D Die — a circular gradient badge housing a rotating 3D cube */
        .die-panel { display:flex; flex-direction:column; align-items:center; gap:.7rem; padding:1rem .5rem 1.1rem; margin-bottom:1rem; border-radius:1rem; }
        .die-badge {
            position:relative;
            width:150px; height:150px; border-radius:50%; border:none; cursor:pointer; padding:0;
            display:flex; flex-direction:column; align-items:center; justify-content:center; gap:.35rem;
            background:rgba(255,255,255,.04);
            box-shadow:0 0 0 1px rgba(255,255,255,.1), 0 10px 26px rgba(0,0,0,.4), inset 0 1px 3px rgba(255,255,255,.06);
            transition:transform .15s ease, filter .2s ease, background .15s ease;
        }
        .die-badge:hover:not(:disabled) { background:rgba(255,255,255,.07); }
        .die-badge:hover:not(:disabled) { transform:scale(1.05); }
        .die-badge:active:not(:disabled) { transform:scale(.95); }
        .die-badge:disabled { filter:grayscale(.85) brightness(.6); cursor:not-allowed; }
        .die-badge .die-label { font-size:.62rem; font-weight:900; letter-spacing:.1em; color:#e5e7eb; text-transform:uppercase; }
        .die-badge .die-caption { font-size:.6rem; font-weight:700; color:#9ca3af; opacity:.9; }
        .die-scene { width:64px; height:64px; perspective:420px; }
        .die-scene.spinning { cursor:default; }
        .die-cube { width:64px; height:64px; position:relative; transform-style:preserve-3d; transform:rotateX(-18deg) rotateY(22deg); }
        /* backface-visibility:hidden is the load-bearing fix here — without it every
           face renders even when pointed away from the camera, and they all show
           through each other at once as a garbled flat smear instead of a cube. */
        .die-face { position:absolute; width:64px; height:64px; border-radius:12px; display:grid; grid-template-columns:repeat(3,1fr); grid-template-rows:repeat(3,1fr); padding:10px; backface-visibility:hidden; box-shadow:inset 0 0 0 1px rgba(0,0,0,.12); }
        .pip { background:radial-gradient(circle at 35% 30%, #4b4b66, #14121f); border-radius:50%; width:9px; height:9px; align-self:center; justify-self:center; box-shadow:0 1px 1px rgba(255,255,255,.2) inset; }
        /* Each face gets its own light/shadow tone (top brightest, bottom darkest,
           sides in between) so the cube reads as one lit solid object rather than
           six identical flat cards. */
        .face-1 { transform:translateZ(32px); background:linear-gradient(155deg,#ffffff,#eef0f4 60%,#dde1e8); box-shadow:inset 0 2px 4px rgba(255,255,255,.95), inset 0 -5px 9px rgba(0,0,0,.1), inset 0 0 0 1px rgba(0,0,0,.08); }
        .face-6 { transform:rotateY(180deg) translateZ(32px); background:linear-gradient(155deg,#c7cbd3,#aeb3bd 60%,#969ba7); box-shadow:inset 0 2px 4px rgba(255,255,255,.35), inset 0 -5px 9px rgba(0,0,0,.28), inset 0 0 0 1px rgba(0,0,0,.15); }
        .face-2 { transform:rotateX(90deg) translateZ(32px); background:linear-gradient(155deg,#fdfdfe,#e6e9ee 60%,#d3d7de); box-shadow:inset 0 2px 4px rgba(255,255,255,.9), inset 0 -5px 9px rgba(0,0,0,.12), inset 0 0 0 1px rgba(0,0,0,.08); }
        .face-5 { transform:rotateX(-90deg) translateZ(32px); background:linear-gradient(155deg,#d6d9df,#bfc3cc 60%,#a8adb8); box-shadow:inset 0 2px 4px rgba(255,255,255,.4), inset 0 -5px 9px rgba(0,0,0,.22), inset 0 0 0 1px rgba(0,0,0,.12); }
        .face-3 { transform:rotateY(90deg) translateZ(32px); background:linear-gradient(155deg,#f2f4f7,#dde1e8 60%,#c9ced7); box-shadow:inset 0 2px 4px rgba(255,255,255,.7), inset 0 -5px 9px rgba(0,0,0,.16), inset 0 0 0 1px rgba(0,0,0,.1); }
        .face-4 { transform:rotateY(-90deg) translateZ(32px); background:linear-gradient(155deg,#e2e5eb,#cbd0da 60%,#b7bcc7); box-shadow:inset 0 2px 4px rgba(255,255,255,.5), inset 0 -5px 9px rgba(0,0,0,.2), inset 0 0 0 1px rgba(0,0,0,.11); }
        .roll-btn { font-weight:900; border-radius:.9rem; padding:.6rem 1.2rem; font-size:.82rem; cursor:pointer; color:#fff; background:linear-gradient(135deg,#f59e0b,#d97706); border:none; width:100%; }
        .roll-btn:disabled { opacity:.4; cursor:not-allowed; }
        .cashout-btn { margin-top:.15rem; background:rgba(16,185,129,.2); border:1px solid rgba(16,185,129,.4); color:#6ee7b7; }

        /* Event toast — slides up with a slight spring overshoot instead of a
           flat fade, and a shrinking accent bar along the bottom edge shows at
           a glance how long is left before it auto-clears (see JS: the bar's
           animation duration is set to match toastTimer's actual delay). */
        .event-toast { position:fixed; left:50%; bottom:170px; transform:translateX(-50%) translateY(18px) scale(.94); max-width:340px; background:rgba(10,9,20,.96); border:1px solid rgba(255,255,255,.18); border-radius:1.1rem; padding:.85rem 1.1rem .75rem; font-size:.78rem; z-index:9998; opacity:0; pointer-events:none; transition:opacity .4s cubic-bezier(.34,1.56,.64,1), transform .4s cubic-bezier(.34,1.56,.64,1); line-height:1.5; box-shadow:0 12px 34px rgba(0,0,0,.5); overflow:hidden; }
        .event-toast.show { opacity:1; transform:translateX(-50%) translateY(0) scale(1); }
        .event-toast .toast-headline { font-weight:900; font-size:.92rem; margin-top:.35rem; animation:toastPop .35s ease .05s both; }
        .event-toast .toast-headline:first-child { margin-top:0; }
        .event-toast .toast-lesson { font-size:.7rem; color:#9ca3af; margin-bottom:.3rem; font-weight:600; }
        .event-toast::after { content:''; position:absolute; left:0; bottom:0; height:3px; width:100%; background:linear-gradient(90deg,#6366f1,#a78bfa); transform-origin:left; transform:scaleX(0); }
        .event-toast.show::after { animation:toastShrink linear forwards; animation-duration:var(--toast-ms, 7000ms); }
        @keyframes toastShrink { from { transform:scaleX(1); } to { transform:scaleX(0); } }
        @keyframes toastPop { from { opacity:0; transform:translateY(3px); } to { opacity:1; transform:translateY(0); } }
        @media (min-width:1024px) { .event-toast { bottom:40px; } }

        .opp-row { border-radius:.7rem; background:rgba(255,255,255,.02); margin-bottom:.4rem; font-size:.75rem; transition:opacity .3s, background .15s; overflow:hidden; }
        .opp-row.opp-ended { opacity:.45; }
        .opp-row-head { display:flex; align-items:center; gap:.5rem; padding:.5rem; cursor:pointer; }
        .opp-row-head:hover { background:rgba(255,255,255,.04); }
        .opp-chevron { font-size:.6rem; color:#6b7280; transition:transform .2s ease; flex-shrink:0; }
        .opp-row.expanded .opp-chevron { transform:rotate(180deg); }
        .opp-details { max-height:0; overflow:hidden; transition:max-height .25s ease; padding:0 .6rem; }
        .opp-row.expanded .opp-details { max-height:120px; padding:0 .6rem .6rem; }
        .opp-detail-line { display:flex; justify-content:space-between; font-size:.68rem; color:#9ca3af; padding:.15rem 0; }
        .opp-detail-line b { color:#e5e7eb; font-weight:800; }
        .opp-bar { flex:1; height:5px; border-radius:3px; background:rgba(255,255,255,.08); overflow:hidden; }
        .opp-bar i { display:block; height:100%; background:#6366f1; transition:width .5s ease; }

        /* Seating order — who plays after whom, for matches with 3+ players
           where "them or you" isn't the whole story. */
        .turn-order-strip { display:flex; align-items:center; gap:.35rem; margin-bottom:.7rem; overflow-x:auto; padding-bottom:.15rem; }
        .tos-seat {
            display:flex; align-items:center; gap:.3rem; flex-shrink:0; padding:.25rem .55rem .25rem .3rem;
            border-radius:999px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08);
            font-size:.66rem; font-weight:800; color:#9ca3af; transition:background .2s,border-color .2s,color .2s;
        }
        .tos-seat .tos-avatar {
            width:1.35rem; height:1.35rem; border-radius:50%; display:flex; align-items:center; justify-content:center;
            font-size:.62rem; font-weight:900; background:rgba(255,255,255,.08); flex-shrink:0;
        }
        .tos-seat.tos-current { background:rgba(16,185,129,.16); border-color:rgba(16,185,129,.5); color:#6ee7b7; }
        .tos-seat.tos-current .tos-avatar { background:#15C77E; color:#04140c; }
        .tos-seat.tos-ended { opacity:.4; text-decoration:line-through; }
        .tos-arrow { color:#4b5563; font-size:.6rem; flex-shrink:0; }

        .turn-banner { margin-bottom:.85rem; padding:.55rem .7rem; border-radius:.8rem; font-size:.78rem; font-weight:800; display:flex; align-items:center; justify-content:center; gap:.5rem; }
        .turn-banner.my-turn { background:rgba(16,185,129,.14); border:1px solid rgba(16,185,129,.35); color:#6ee7b7; }
        .turn-banner.waiting { background:rgba(245,158,11,.12); border:1px solid rgba(245,158,11,.3); color:#fbbf24; }
        .turn-secs-badge {
            display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;
            width:26px; height:26px; border-radius:50%; font-weight:900; font-size:.76rem;
            color:#0b0a16; font-variant-numeric:tabular-nums; transition:background .3s ease;
        }
        .turn-secs-badge.secs-high { background:#34d399; box-shadow:0 0 10px rgba(52,211,153,.6); }
        .turn-secs-badge.secs-mid { background:#fbbf24; box-shadow:0 0 10px rgba(251,191,36,.6); }
        .turn-secs-badge.secs-low { background:#f87171; box-shadow:0 0 10px rgba(248,113,113,.7); animation:turnSecsPulse .8s infinite; }
        @keyframes turnSecsPulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.18); } }

        .overlay { position:fixed; inset:0; background:rgba(0,0,0,.7); display:none; align-items:center; justify-content:center; z-index:10000; }
        .overlay-card { background:#14121f; border:1px solid rgba(255,255,255,.15); border-radius:1.5rem; padding:2rem; text-align:center; max-width:320px; }
        .confetti-piece { position:fixed; top:-10px; width:8px; height:14px; z-index:9999; pointer-events:none; }

        .sparkle { position:fixed; font-size:1rem; z-index:9990; pointer-events:none; animation: sparkleUp 1.1s ease-out forwards; }
        @keyframes sparkleUp { 0% { transform:translate(0,0) scale(.4) rotate(0deg); opacity:1; } 100% { transform:translate(var(--sx,0),-46px) scale(1.1) rotate(90deg); opacity:0; } }

        /* The board is CSS-rotated 90° into landscape on narrow portrait phones
           (see .panel-board above) — everything else that floats on top of it
           needs to rotate the same way, or it reads upright/sideways against a
           sideways board: the die, the whose-turn/countdown banner, the event
           toast, and the win/bust/cash-out modals. */
        @media (max-width:1023px) and (orientation:portrait) {
            .die-panel { transform:rotate(90deg); }
            /* Unlike the die/banner/toast/overlay above (all position:fixed
               against the viewport, so each needs its OWN rotate(90deg) to match
               the board), a token is a plain descendant of the already-rotated
               .panel-board and inherits that rotation automatically — the piece
               itself (its emoji glyph, or a real profile photo) ends up sideways
               on top of a board that otherwise reads correctly landscape.
               Counter-rotating the token itself (not just its <img>, which used
               to be the only thing rotated here and left the plain-emoji case
               still sideways) cancels the inherited rotation for BOTH cases —
               same trick as the GameSet mobile calibrator's tile-number labels.
               Keeps the existing translate(-50%,-50%) centering, just adds the
               counter-rotation alongside it; the <img> itself needs no rotation
               of its own anymore since its now-upright parent already covers it. */
            .token { transform: translate(-50%,-50%) rotate(-90deg); }
            /* The wobble keyframes set their OWN transform (including rotate) at
               every step, so a plain static rotate(90deg) here would just get
               overridden the instant the animation starts — swapping to a
               dedicated keyframe set (same rotate values +90°) is what's actually
               needed to make the emoji glyph itself match the rotated board,
               same as the die/banner/toast above. */
            .floating-emoji { animation-name: floatEmojiUpRotated; }
            /* relocateGameHud() moves #turnBanner to be the FIRST CHILD of
               .die-panel in this mode specifically — a plain flow element that
               rides along with the die panel's own position/drag/rotation
               instead of a separately fixed corner badge unrelated to where the
               (draggable) die actually is. Sized relative to the compact die
               panel it now lives inside, not the old ~70vw fixed-corner width. */
            .die-panel #turnBanner {
                margin:0 0 .35rem; padding:.3rem .5rem; font-size:.6rem; gap:.3rem;
                width:max-content; max-width:100%; white-space:nowrap;
            }
            .die-panel #turnBanner .turn-secs-badge { width:18px; height:18px; font-size:.58rem; }
            /* left/top are set from the board's real rotated center by
               positionToastOverBoard() (JS) — a plain left:50% here would center
               against the screen's un-rotated X axis, which is NOT the rotated
               board's visual horizontal, exactly the "toast sits too far over"
               bug. translate(-50%,-50%) anchors on that computed center point. */
            .event-toast { left:var(--toast-left, 50%); top:var(--toast-top, 50%); bottom:auto; transform:translate(-50%,-50%) scale(.9) rotate(90deg); }
            .event-toast.show { transform:translate(-50%,-50%) scale(1) rotate(90deg); }
            .overlay-card { transform:rotate(90deg); }
            /* The drawer itself is a full-height edge panel — rotating it like the
               small die/banner/toast above would need the same dimension-swapping
               corner-pivot .panel-board uses (a naive center-rotate would blow its
               footprint far wider than the screen). Left upright deliberately: the
               physical viewport is still portrait-shaped underneath the visually-
               rotated board, so a plain right-edge drawer at full (portrait) height
               already covers the real screen correctly. What WAS actually broken —
               the hamburger trigger becoming unreachable/hidden behind the
               full-viewport fixed board — is fixed by promoting .topbar to fixed
               with a z-index above the board (see the .panel-board rule above);
               this panel already sits at z-index:60, above the board's 30 too. */
        }
    </style>
</head>
<body>
    <div class="topbar">
        <img src="{{ asset('moski-logo.png') }}" class="w-7 h-7 rounded-lg">
        <span class="font-black text-sm title-text">🐍 Pesa Trail</span>
        <div class="chip">💰 KES <span id="hudPot">{{ number_format($session->pot_amount) }}</span></div>
        <div class="chip">⭐ Lv {{ $progress->level }}</div>
        @if($session->match && $session->match->join_code)
        <button type="button" class="chip invite-chip" style="cursor:pointer;background:rgba(99,102,241,.18);border-color:rgba(99,102,241,.35);color:#a5b4fc;" onclick="shareInvite('{{ $session->match->join_code }}')">📨 <span class="invite-label">Invite ({{ $session->match->join_code }})</span></button>
        @endif

        <div class="notif-wrap">
            <div class="chip notif-bell" id="notifBell" onclick="toggleNotifPanel()">
                🔔<span id="notifBadge" class="notif-badge" style="display:none;">0</span>
            </div>
            <div class="notif-panel" id="notifPanel">
                <div class="notif-panel-head">Notifications</div>
                <div id="notifList"><div class="notif-empty">Loading…</div></div>
            </div>
        </div>

        <button type="button" class="chip menu-toggle" id="menuToggle" onclick="toggleDrawer()" title="Menu">☰</button>
        <a href="{{ route('arcade.snakes.lobby') }}" class="chip">← Lobby</a>
    </div>

    <span class="atmo-prop" style="left:2%; top:20%;">🐷</span>
    <span class="atmo-prop" style="left:4%; top:70%;">💼</span>
    <span class="atmo-prop" style="right:3%; top:16%;">🌱</span>
    <span class="atmo-prop" style="right:5%; top:66%;">🪙</span>

    <div class="drawer-backdrop" id="drawerBackdrop" onclick="toggleDrawer(false)"></div>

    <div class="layout">
        {{-- Board (as large as the layout allows) --}}
        <div class="panel panel-board">
            <div class="board-wrap" id="boardWrap">
                <img src="{{ asset('img/game/arcade/pesatrail.webp') }}" alt="Pesa Trail board">
                <div id="tokenMe" class="token token-me" style="left:{{ $positions[$session->position]['left'] ?? $positions[1]['left'] }}%; top:{{ $positions[$session->position]['top'] ?? $positions[1]['top'] }}%;">
                    🧑
                    @if(auth()->user()->avatar_url)
                        {{-- Sits on top of the always-rendered emoji above; a
                             broken/stale photo URL removes itself on error,
                             revealing that emoji instead of a broken-image icon. --}}
                        <img src="{{ auth()->user()->avatar_url }}" alt="" onerror="this.remove();">
                    @endif
                </div>
                @foreach($opponents as $opp)
                <div id="token-opp-{{ $opp->id }}" class="token token-op" style="left:{{ $positions[$opp->position]['left'] ?? $positions[1]['left'] }}%; top:{{ $positions[$opp->position]['top'] ?? $positions[1]['top'] }}%;">
                    {{ $opp->is_bot ? '🤖' : '👤' }}
                    @if(!$opp->is_bot && $opp->user?->avatar_url)
                        <img src="{{ $opp->user->avatar_url }}" alt="" onerror="this.remove();">
                    @endif
                </div>
                @endforeach
            </div>
        </div>

        {{-- Mobile only — the turn banner and opponents get relocated here by JS
             (relocateGameHud) so they're always visible instead of hidden inside
             the collapsed drawer. Empty/hidden on desktop, filled in by JS either way. --}}
        <div class="panel mobile-hud" id="mobileHud"></div>

        {{-- On mobile this wrapper becomes the slide-in drawer (display:contents on
             desktop keeps panel-sidebar a normal direct grid item) --}}
        <div class="mobile-drawer" id="mobileDrawer">
        {{-- One sidebar: profile, roll controls, opponents, then collapsible how-to-play --}}
        <div class="panel panel-sidebar" style="height:fit-content;">
            <div class="profile-head">
                <div class="avatar">{{ strtoupper(substr(auth()->user()->name ?? 'P', 0, 1)) }}</div>
                <div>
                    <p class="font-black text-sm">{{ auth()->user()->name ?? 'Player' }}</p>
                    <p class="text-[10px] text-gray-500 font-bold uppercase tracking-wider">Level {{ $progress->level }}</p>
                </div>
            </div>
            <div id="yourProgress">
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Your Progress</p>
                <div class="flex items-center justify-between mb-3">
                    <div class="stat-mini"><b id="hudPosition">{{ $session->position }}</b><span>Tile</span></div>
                    <div class="stat-mini"><b>{{ $game->tile_count }}</b><span>Finish</span></div>
                    <div class="stat-mini"><b>{{ number_format($session->stake_amount) }}</b><span>Savings</span></div>
                </div>
            </div>

            {{-- Waiting room state — a Rivals Trail (wager) match with nobody
                 else in it yet isn't "solo", it's a challenge sitting unanswered.
                 Hidden the instant polling picks up an opponent (same JS hook
                 that reveals #oppHeading), so this page IS the waiting area:
                 leave and come back anytime via "Resume your game" in the
                 Arcade lobby, or just leave this tab open — it updates live. --}}
            @if(($match->mode ?? null) === 'wager' && $opponents->isEmpty())
            <div class="turn-banner waiting" id="waitingForOpponentBanner" style="display:flex;">
                ⏳ Waiting for your opponent to accept the challenge — this updates the moment they join.
            </div>
            @endif

            {{-- Only meaningful with 3+ total players — a 1v1 already has an
                 obvious "them or you" via the turn banner alone. --}}
            <div class="turn-order-strip" id="turnOrderStrip" style="display:none;"></div>

            <div class="turn-banner" id="turnBanner" style="display:none;"></div>

            <div class="die-panel">
                <button type="button" class="die-badge" id="rollBtn" title="Click to roll" {{ ($session->status !== 'active' || !$isMyTurn) ? 'disabled' : '' }}>
                    <span class="die-label">Roll Dice</span>
                    <div class="die-scene" id="dieScene">
                        <div class="die-cube" id="dieCube">
                            <div class="die-face face-1"><i class="pip" style="grid-area:2/2"></i></div>
                            <div class="die-face face-2"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                            <div class="die-face face-3"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:2/2"></i><i class="pip" style="grid-area:3/3"></i></div>
                            <div class="die-face face-4"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                            <div class="die-face face-5"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:2/2"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                            <div class="die-face face-6"><i class="pip" style="grid-area:1/1"></i><i class="pip" style="grid-area:1/3"></i><i class="pip" style="grid-area:2/1"></i><i class="pip" style="grid-area:2/3"></i><i class="pip" style="grid-area:3/1"></i><i class="pip" style="grid-area:3/3"></i></div>
                        </div>
                    </div>
                    <span class="die-value-badge" id="dieValueBadge"></span>
                    <span class="die-caption">Click to roll</span>
                </button>
                @if(($match->mode ?? 'standard') !== 'wager')
                <button type="button" class="roll-btn cashout-btn" id="cashOutBtn" onclick="cashOut()" {{ $session->status !== 'active' ? 'disabled' : '' }}>🚪 Quit</button>
                @endif
                {{-- Rivals Trail rounds settle automatically the instant they're decided
                     (see ArcadeSnakesService::settleMatchIfDecided()) — no manual cash-out
                     to protect a pot from the eventual cut. Explained once in How To Play
                     instead of as a permanent line of text crowding the die controls. --}}
            </div>

            @if($opponents->isNotEmpty())
            <div class="reaction-bar" id="reactionBar">
                @foreach(['😂', '😮', '😤', '🔥', '👏', '😅', '💪', '😬'] as $emoji)
                <button type="button" class="reaction-btn" onclick="sendReaction('{{ $emoji }}')">{{ $emoji }}</button>
                @endforeach
            </div>
            @endif

            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4" id="oppHeading" style="{{ $opponents->isEmpty() ? 'display:none;' : '' }}">{{ $opponents->contains('is_bot', true) && $opponents->count() === 1 ? 'Racing' : 'Opponents' }}</p>
            <div id="opponentsList">
            @foreach($opponents as $opp)
            <div class="opp-row" id="opp-row-{{ $opp->id }}">
                <div class="opp-row-head" onclick="toggleOppRow({{ $opp->id }})">
                    <div class="avatar {{ $opp->is_bot ? 'avatar-bot' : 'avatar-op' }}" style="width:1.9rem;height:1.9rem;font-size:.85rem;position:relative;">
                        {{ $opp->is_bot ? '🤖' : strtoupper(substr($opp->user->name ?? 'P', 0, 1)) }}
                        @if(!$opp->is_bot && $opp->user?->avatar_url)
                            {{-- Sits on top of the always-rendered initial above; a
                                 broken/stale photo URL removes itself on error,
                                 revealing that initial instead of a broken-image icon. --}}
                            <img src="{{ $opp->user->avatar_url }}" alt="" style="position:absolute;inset:0;width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.remove();">
                        @endif
                    </div>
                    <div style="flex:1;">
                        <p class="font-bold">{{ $opp->is_bot ? 'Robo' : ($opp->user->name ?? 'Player') }}</p>
                        <div class="opp-bar"><i id="opp-bar-{{ $opp->id }}" style="width:{{ round($opp->position / $game->tile_count * 100) }}%;"></i></div>
                    </div>
                    <span class="text-[10px] text-gray-500" id="opp-pos-{{ $opp->id }}">{{ $opp->position }}/{{ $game->tile_count }}</span>
                    <span class="opp-chevron">▼</span>
                </div>
                <div class="opp-details">
                    <div class="opp-detail-line"><span>Status</span><b id="opp-status-{{ $opp->id }}">{{ ucfirst($opp->status) }}</b></div>
                    <div class="opp-detail-line"><span>Savings</span><b id="opp-pot-{{ $opp->id }}">KES {{ number_format($opp->pot_amount) }}</b></div>
                    <div class="opp-detail-line"><span>Started with</span><b>KES {{ number_format($opp->stake_amount) }}</b></div>
                    @if(($match->mode ?? 'standard') === 'wager')
                    <div class="opp-detail-line" id="opp-missed-wrap-{{ $opp->id }}" style="{{ $opp->missed_turns > 0 ? '' : 'display:none;' }}">
                        <span>Missed turns</span><b id="opp-missed-{{ $opp->id }}" style="color:#fbbf24;">{{ $opp->missed_turns }}/{{ \App\Services\ArcadeSnakesService::FORFEIT_MISSED_TURNS }}</b>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
            </div>
            <p class="text-xs text-gray-500 italic mt-4" id="noOpponentsMsg" style="{{ $opponents->isNotEmpty() ? 'display:none;' : '' }}">{{ ($match->mode ?? null) === 'wager' ? 'Waiting for your opponent to join…' : 'Solo game — no opponents in this session.' }}</p>

            {{-- How To Play — collapsible so it doesn't compete with the game itself for space --}}
            <button type="button" class="htp-toggle" id="htpToggle" onclick="toggleHowToPlay()">
                <span>How To Play</span><span class="htp-chevron" id="htpChevron">▼</span>
            </button>
            <div class="htp-body" id="htpBody">
                <div class="htp-row"><span class="htp-icon">🎲</span> Roll to move along the trail</div>
                <div class="htp-row"><span class="htp-icon">🪜</span> Ladders boost you forward</div>
                <div class="htp-row"><span class="htp-icon">🐍</span> Snakes slide you back</div>
                <div class="htp-row"><span class="htp-icon">💰</span> Reward tiles grow your savings</div>
                <div class="htp-row"><span class="htp-icon">💸</span> Expense tiles shrink it</div>
                <div class="htp-row"><span class="htp-icon">❓</span> Mystery tiles are a gift or a curse</div>
                <div class="htp-row"><span class="htp-icon">🏆</span> Reach tile {{ $game->tile_count }} to win!</div>

                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">Golden Tiles</p>
                <div class="htp-row" style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.25);">
                    <span class="htp-icon">🏆</span>
                    <span>First landing reveals the golden tile. Every landing after that instantly adds +25% of your starting savings — automatic, no buying.</span>
                </div>

                @if(($match->mode ?? 'standard') === 'wager')
                <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">Rivals Trail</p>
                <div class="htp-row" style="background:rgba(236,72,153,.08);border:1px solid rgba(236,72,153,.25);">
                    <span class="htp-icon">⚔️</span>
                    <span>This round settles automatically the instant it ends — no manual cash-out.</span>
                </div>
                @endif
            </div>
        </div>
        </div>{{-- /mobile-drawer --}}
    </div>

    <div id="eventToast" class="event-toast"></div>

    <div id="winOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">🏁🎉</p>
        <p class="text-xl font-black text-amber-300 mb-1">You Won!</p>
        <p class="text-sm font-bold text-emerald-300 mb-1" id="winGainLine" style="display:none;"></p>
        <p class="text-sm text-gray-300 mb-4">Total saved: <b id="winPayout">KES 0</b></p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    {{-- Standard (non-wager) matches: shown to whoever DIDN'T reach the finish
         tile once another player did — pollState() is the only path that can
         ever detect this for the non-winning side. --}}
    <div id="raceOverOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">🏁</p>
        <p class="text-xl font-black text-amber-300 mb-1"><span id="raceWinnerName">A player</span> won the race!</p>
        <p class="text-sm text-gray-300 mb-4">Total saved: <b id="raceOverPayout">KES 0</b></p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    <div id="bustOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">💥</p>
        <p class="text-xl font-black text-red-300 mb-1">Out of Savings!</p>
        <p class="text-sm text-gray-300 mb-4">Your savings ran out this round — but every game teaches something.</p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    {{-- Rivals Trail — shown to a player who DIDN'T roll the decisive move
         themselves (another player's win or forfeit decided it); pollState()
         is the only path that can ever detect this for the non-active side. --}}
    <div id="lostOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">📉</p>
        <p class="text-xl font-black text-red-300 mb-1">Lost a Rivals Trail round</p>
        <p class="text-sm font-bold text-red-300 mb-1" id="lostAmountLine" style="display:none;"></p>
        <p class="text-sm text-gray-300 mb-4">You kept <b id="lostPayout">KES 0</b> of your in-round savings.</p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    <div id="forfeitedOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">🚪</p>
        <p class="text-xl font-black text-amber-300 mb-1">Withdrawn from the round</p>
        <p class="text-sm text-gray-300 mb-4">You missed too many turns in a row — you kept <b id="forfeitedPayout">KES 0</b> of your in-round savings.</p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    <div id="cashOutOverlay" class="overlay"><div class="overlay-card">
        <p class="text-3xl mb-2">🏦</p>
        <p class="text-xl font-black text-emerald-300 mb-1">Saved!</p>
        <p class="text-sm text-gray-300 mb-4">Banked <b id="cashOutPayout">KES 0</b> — new wallet balance: <b id="cashOutBalance">KES 0</b></p>
        @include('arcade.snakes.partials.overlay-actions')
    </div></div>

    <script src="{{ asset('js/arcade-sounds.js') }}"></script>
    <script>
        const TILE_POSITIONS = {!! json_encode($positions) !!};
        const TILE_COUNT = {{ $game->tile_count }};
        const ROLL_URL = "{{ route('arcade.snakes.roll', $session) }}";
        const CASH_OUT_URL = "{{ route('arcade.snakes.cash-out', $session) }}";
        const REACT_URL = "{{ route('arcade.snakes.react', $session) }}";
        const MY_SESSION_ID = {{ $session->id }};
        const MY_NAME = {!! json_encode(auth()->user()->name ?? 'You') !!};
        const MY_TURN_ORDER = {{ $session->turn_order }};
        const STATE_URL = "{{ route('arcade.snakes.state', $session) }}";
        const TURN_MODE = "{{ $turnMode }}";
        const MATCH_MODE = "{{ $match->mode ?? 'standard' }}";
        // No real opponent's experience depends on this game continuing while the
        // player isn't even looking — unlike a real multiplayer match, there's no
        // reason for Robo to keep "waiting" (or for the turn timer to keep
        // burning down) in the background. Gates the auto-pause-on-hidden-tab
        // behavior below to solo/bot sessions only.
        const IS_SOLO_BOT = {{ ($opponents->isEmpty() || $opponents->every(fn($o) => $o->is_bot)) ? 'true' : 'false' }};
        const FORFEIT_MISSED_TURNS = {{ \App\Services\ArcadeSnakesService::FORFEIT_MISSED_TURNS }};
        const HEADERS = { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' };

        let status = "{{ $session->status }}";
        let rolling = false;
        let myTurn = {{ $isMyTurn ? 'true' : 'false' }};
        let turnSecondsLeft = {{ $turnSecondsRemaining ?? 'null' }};
        let lastTurnUser = null;

        const DIE_ROTATIONS = { 1:{x:0,y:0}, 6:{x:0,y:180}, 2:{x:-90,y:0}, 5:{x:90,y:0}, 3:{x:0,y:-90}, 4:{x:0,y:90} };
        // Resting tilt (matches .die-cube's default CSS transform) — enough of an
        // angle to read as a real 3D object; the separate digit badge (showDieValue)
        // covers legibility so the tilt itself doesn't have to be timid about it.
        const DIE_TILT_X = -18, DIE_TILT_Y = 22;

        // Same forced-landscape condition the CSS media query uses — mirrors the
        // pattern relocateGameHud()/dockOverBoard() already use elsewhere in this
        // file for picking behavior based on the rotated mobile layout.
        function isRotatedMobile() {
            return window.innerWidth < 1024 && window.matchMedia('(orientation: portrait)').matches;
        }
        function tokenPos(n) {
            const p = TILE_POSITIONS[n] || TILE_POSITIONS[1];
            if (!p) return { left: 50, top: 50 };
            return isRotatedMobile() ? { left: p.left_m, top: p.top_m } : { left: p.left, top: p.top };
        }
        function placeToken(el, n) {
            const p = tokenPos(n);
            el.style.left = p.left + '%'; el.style.top = p.top + '%';
            setTimeout(() => {
                el.classList.remove('landed');
                void el.offsetWidth; // restart the animation
                el.classList.add('landed');
            }, 850);
        }

        /** Hops a token through every real tile in `path` (one placeToken() call
         *  per tile, at a quick fixed cadence) instead of one long diagonal glide
         *  straight to the destination — reads as a real board-game piece moving
         *  space by space. Only used for the FIRST segment of a roll (real,
         *  sequential tile numbers); a snake/ladder jump afterward has no
         *  meaningful intermediate tiles, so that stays a plain two-point glide. */
        function animateHopPath(el, path, onDone) {
            // Mirrors ArcadeSnakesService::HOP_MS (PHP) — that constant estimates
            // this exact duration to delay the next player's turn timer until this
            // animation is actually done, so the two must stay in step. A previous
            // 170ms felt hurried/broken for multi-tile rolls; slowed down to read
            // as a real piece moving space by space.
            const HOP_MS = 260;
            el.classList.add('hopping');
            let i = 0;
            (function step() {
                if (i >= path.length) {
                    el.classList.remove('hopping');
                    onDone();
                    return;
                }
                placeToken(el, path[i]);
                i++;
                setTimeout(step, HOP_MS);
            })();
        }

        function glowGold(el) {
            el.classList.remove('glow-gold');
            void el.offsetWidth;
            el.classList.add('glow-gold');
        }

        let dieValueTimer = null;
        // Pips can be hard to read at a glance (especially at the small mobile size),
        // so the actual rolled number also shows as a plain digit badge for a moment.
        function showDieValue(n) {
            const badge = document.getElementById('dieValueBadge');
            if (!badge) return;
            badge.textContent = n;
            badge.classList.add('show');
            clearTimeout(dieValueTimer);
            dieValueTimer = setTimeout(() => badge.classList.remove('show'), 2500);
        }

        // Emoji reactions/taunts — ephemeral (no history), a 6s client-side
        // cooldown mirrors the server's own cooldown so a double-tap doesn't even
        // round-trip. lastReactionId dedupes so the same reaction never plays
        // twice across repeated polls.
        let reactionCooldownUntil = 0;
        let lastReactionId = null;
        // Set the instant sendReaction() plays its own optimistic animation —
        // handleIncomingReaction() checks this so the SAME reaction doesn't also
        // play a second time once the poll that actually stored it comes back
        // around (polling can be up to 3.5s on my own turn, which read as a
        // laggy/unreliable reaction before this).
        let myRecentReactions = new Set();
        async function sendReaction(emoji) {
            if (Date.now() < reactionCooldownUntil) return;
            reactionCooldownUntil = Date.now() + 6000;
            const bar = document.getElementById('reactionBar');
            if (bar) {
                bar.querySelectorAll('.reaction-btn').forEach(b => b.disabled = true);
                setTimeout(() => bar.querySelectorAll('.reaction-btn').forEach(b => b.disabled = false), 6000);
            }
            // Instant local feedback — don't wait for a poll to reflect this back.
            myRecentReactions.add(emoji);
            setTimeout(() => myRecentReactions.delete(emoji), 8000);
            floatEmoji(emoji, document.getElementById('tokenMe'));
            ArcadeSound.play('reaction');
            try {
                await fetch(REACT_URL, { method: 'POST', headers: { ...HEADERS, 'Content-Type': 'application/json' }, body: JSON.stringify({ emoji }) });
            } catch (e) { /* silent — a missed taunt isn't worth interrupting the game over */ }
        }

        /** Floats a big emoji up from near the given element (or screen center as
         *  a fallback) and self-removes — reuses the same disposable-DOM-node
         *  pattern as sparkleBurst() below. */
        function floatEmoji(emoji, nearEl) {
            const rect = nearEl ? nearEl.getBoundingClientRect() : { left: window.innerWidth / 2, top: window.innerHeight / 2, width: 0, height: 0 };
            const el = document.createElement('span');
            el.className = 'floating-emoji';
            el.textContent = emoji;
            el.style.left = (rect.left + rect.width / 2) + 'px';
            el.style.top = (rect.top + rect.height / 2) + 'px';
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 1700);
        }

        function handleIncomingReaction(reaction) {
            if (!reaction || reaction.id === lastReactionId) return;
            lastReactionId = reaction.id;
            // Already played instantly by sendReaction() the moment I tapped it —
            // this poll is just the server confirming it, not a new event.
            if (reaction.from_session_id === MY_SESSION_ID && myRecentReactions.has(reaction.emoji)) return;
            const nearEl = reaction.from_session_id === MY_SESSION_ID
                ? document.getElementById('tokenMe')
                : document.getElementById('token-opp-' + reaction.from_session_id);
            floatEmoji(reaction.emoji, nearEl);
            ArcadeSound.play('reaction');
        }

        /** Spawns a "+250"/"-100" span at fromEl's position and flies it to the
         *  KES chip in the topbar, updating the displayed number (with a quick
         *  pulse) right as it arrives — a gain/loss reads as one connected
         *  event instead of the corner number just silently changing. */
        function floatMoneyToBalance(amount, fromEl, newTotal) {
            const target = document.getElementById('hudPot');
            if (!target || !amount) { if (target && newTotal !== undefined) target.textContent = Number(newTotal).toLocaleString(); return; }

            const startRect = (fromEl || document.getElementById('tokenMe')).getBoundingClientRect();
            const el = document.createElement('span');
            el.className = 'money-float ' + (amount > 0 ? 'mf-gain' : 'mf-loss');
            el.textContent = (amount > 0 ? '+' : '−') + 'KES ' + Math.abs(amount).toLocaleString();
            el.style.left = (startRect.left + startRect.width / 2) + 'px';
            el.style.top = (startRect.top + startRect.height / 2) + 'px';
            document.body.appendChild(el);

            requestAnimationFrame(() => {
                const endRect = target.getBoundingClientRect();
                el.style.left = (endRect.left + endRect.width / 2) + 'px';
                el.style.top = (endRect.top + endRect.height / 2) + 'px';
                el.classList.add('mf-arrived');
            });

            setTimeout(() => {
                if (newTotal !== undefined) {
                    target.textContent = Number(newTotal).toLocaleString();
                    target.classList.remove('pulse');
                    void target.offsetWidth;
                    target.classList.add('pulse');
                }
                el.remove();
            }, 820);
        }

        function sparkleBurst(el) {
            if (!el) return;
            const rect = el.getBoundingClientRect();
            const icons = ['✨', '⭐', '🌟'];
            for (let i = 0; i < 6; i++) {
                const s = document.createElement('span');
                s.className = 'sparkle';
                s.textContent = icons[Math.floor(Math.random() * icons.length)];
                s.style.left = (rect.left + rect.width / 2) + 'px';
                s.style.top = (rect.top + rect.height / 2) + 'px';
                s.style.setProperty('--sx', (Math.random() * 60 - 30) + 'px');
                document.body.appendChild(s);
                setTimeout(() => s.remove(), 1200);
            }
        }

        let toastTimer = null;
        function showToast(lines, ms = 7000) {
            if (typeof positionToastOverBoard === 'function') positionToastOverBoard();
            const el = document.getElementById('eventToast');
            el.innerHTML = lines.map(l => `<div>${l}</div>`).join('');
            el.style.setProperty('--toast-ms', ms + 'ms');
            // Force a reflow between remove/add so the shrinking progress bar
            // restarts from full even if a toast is already showing — without
            // this, re-adding the same class mid-animation wouldn't reset it.
            el.classList.remove('show');
            void el.offsetWidth;
            el.classList.add('show');
            clearTimeout(toastTimer);
            toastTimer = setTimeout(() => el.classList.remove('show'), ms);
        }

        function eventLine(ev) {
            switch (ev.type) {
                case 'reward': return `<div class="toast-headline" style="color:#6ee7b7;">💰 +KES ${ev.amount}</div><div class="toast-lesson">${ev.label || ''}</div>`;
                case 'expense': return `<div class="toast-headline" style="color:#fca5a5;">💸 -KES ${ev.amount}</div><div class="toast-lesson">${ev.label || ''}</div>`;
                case 'mystery': return `<div class="toast-headline" style="color:${ev.effect === 'gift' ? '#6ee7b7' : '#fca5a5'};">${ev.effect === 'gift' ? '🎁' : '💀'} ${ev.effect === 'gift' ? '+' : '-'}KES ${ev.amount}</div><div class="toast-lesson">${ev.label}</div>`;
                case 'move': return `<div class="toast-headline">${ev.via === 'snake_head' ? '🐍 Snake! Sliding to tile ' + ev.to : '🪜 Ladder! Climbing to tile ' + ev.to}</div>`;
                case 'golden_first': return `<div class="toast-headline" style="color:#fbbf24;">🏆 Golden tile discovered! Land here again for a boost.</div>`;
                case 'golden_boost': return `<div class="toast-headline" style="color:#fbbf24;">🏆 Golden boost! +KES ${ev.amount}</div><div class="toast-lesson">25% of your starting savings, added automatically.</div>`;
                case 'overshoot': return `<div class="toast-headline">Rolled ${ev.roll}, needed ${ev.needed} exactly — bounced back to tile ${ev.bounced_to}</div>`;
                case 'bust': return `<div class="toast-headline" style="color:#fca5a5;">💥 Out of savings!</div>`;
                case 'win': return `<div class="toast-headline" style="color:#fbbf24;">🏁 You reached the finish! +KES ${ev.bonus} bonus</div>`;
                default: return '';
            }
        }

        function updateHud(pot, position) {
            document.getElementById('hudPot').textContent = Number(pot).toLocaleString();
            document.getElementById('hudPosition').textContent = position;
        }

        function updateRollButtonState() {
            // Turn-gate on animation: even once the server says it's my turn, don't
            // let me roll while an opponent's token is still visibly hopping across
            // the board on MY screen — animatingSessions is cleared by
            // animateOpponent()/spinDieForBot() the instant that finishes.
            document.getElementById('rollBtn').disabled = rolling || status !== 'active' || (TURN_MODE === 'turns' && !myTurn) || animatingSessions.size > 0;
        }

        function turnSecsBadgeClass(secs) {
            if (secs <= 3) return 'secs-low';
            if (secs <= 6) return 'secs-mid';
            return 'secs-high';
        }

        function updateTurnBanner(currentTurnUser) {
            if (currentTurnUser !== undefined) lastTurnUser = currentTurnUser;
            const banner = document.getElementById('turnBanner');
            if (TURN_MODE !== 'turns' || status !== 'active') { banner.style.display = 'none'; return; }
            banner.style.display = 'flex';
            const secs = turnSecondsLeft !== null
                ? `<span class="turn-secs-badge ${turnSecsBadgeClass(turnSecondsLeft)}">${turnSecondsLeft}</span>`
                : '';
            // The server can only ESTIMATE how long a roll's animation will visibly
            // take (it has no idea a bot roll adds its own ~1s die-spin flourish
            // before the token even starts hopping) — so however good that estimate
            // is, myTurn can still flip true a moment before the previous move is
            // actually done playing out on THIS screen. Gating the display (not just
            // the roll button) on animatingSessions is what actually guarantees the
            // banner/counter never claims "your turn" while a piece is still moving.
            const effectivelyMyTurn = myTurn && animatingSessions.size === 0;
            if (effectivelyMyTurn) {
                banner.className = 'turn-banner my-turn';
                banner.innerHTML = `🎲 Your turn — roll!${secs}`;
            } else {
                banner.className = 'turn-banner waiting';
                banner.innerHTML = `⏳ Waiting for ${escapeHtml(lastTurnUser || 'the other player')} to play${secs}`;
            }
            updateRollButtonState();
        }

        // Seating order — only shown for 3+ total players, where "them or you"
        // (the turn banner alone) doesn't tell you how long until your turn
        // comes back around. Rebuilt from scratch each time rather than
        // diffed — it's a handful of small pills, cheap either way.
        function updateTurnOrderStrip(seats, currentTurnSessionId) {
            const strip = document.getElementById('turnOrderStrip');
            if (!strip) return;
            if (seats.length < 3) { strip.style.display = 'none'; strip.innerHTML = ''; return; }

            const sorted = [...seats].sort((a, b) => a.turn_order - b.turn_order);
            strip.innerHTML = sorted.map((s, i) => {
                const isCurrent = s.session_id === currentTurnSessionId;
                const ended = s.status !== 'active';
                const initial = escapeHtml((s.name || 'P').charAt(0).toUpperCase());
                const arrow = i < sorted.length - 1 ? '<span class="tos-arrow">→</span>' : '';
                return `<div class="tos-seat ${isCurrent ? 'tos-current' : ''} ${ended ? 'tos-ended' : ''}" title="${escapeHtml(s.name)}">
                    <span class="tos-avatar">${s.is_bot ? '🤖' : initial}</span>${escapeHtml(s.is_me ? 'You' : s.name)}
                </div>${arrow}`;
            }).join('');
            strip.style.display = 'flex';
        }

        // Ticks the displayed countdown down every second between polls; pollState()
        // resyncs the real value every 3s so client-side drift never accumulates.
        // The server already delays the real countdown start until a roll's token
        // animation should be done (see ArcadeSnakesService::advanceTurn()) — this
        // animatingSessions guard is a client-side backstop for the moments in
        // between polls, so the visible number never ticks down while a piece is
        // still visibly hopping across the board on this screen.
        setInterval(() => {
            if (turnSecondsLeft === null || turnSecondsLeft <= 0) return;
            if (animatingSessions.size > 0) return;
            turnSecondsLeft -= 1;
            updateTurnBanner();
        }, 1000);

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function toggleOppRow(sessionId) {
            document.getElementById('opp-row-' + sessionId)?.classList.toggle('expanded');
            ArcadeSound.play('toggle');
        }

        function toggleDrawer(open) {
            const drawer = document.getElementById('mobileDrawer');
            const backdrop = document.getElementById('drawerBackdrop');
            const isOpen = open === undefined ? !drawer.classList.contains('drawer-open') : open;
            drawer.classList.toggle('drawer-open', isOpen);
            backdrop.classList.toggle('show', isOpen);
            // The die/turn-banner float as position:fixed descendants of the drawer
            // with a HIGHER z-index than it (so they stay visible over the board
            // while the drawer is closed) — that same z-index otherwise floats them
            // on top of the drawer's own content once it opens, making it look like
            // the drawer won't respond to taps. This class drops them behind it.
            document.body.classList.toggle('drawer-open', isOpen);
            ArcadeSound.play('toggle');
        }

        // Click-outside-to-close, as a capture-phase listener rather than
        // relying solely on the backdrop's own onclick — while the drawer is
        // open, body.drawer-open bumps .layout's z-index ABOVE the backdrop
        // (see the CSS comment above .drawer-backdrop) so the backdrop alone
        // doesn't reliably catch taps that land on the game board underneath.
        // Checking the click target's DOM ancestry directly sidesteps that.
        document.addEventListener('click', function (e) {
            const drawer = document.getElementById('mobileDrawer');
            const toggle = document.getElementById('menuToggle');
            if (!drawer || !drawer.classList.contains('drawer-open')) return;
            if (drawer.contains(e.target) || (toggle && toggle.contains(e.target))) return;
            toggleDrawer(false);
        }, true);

        function toggleHowToPlay() {
            document.getElementById('htpToggle').classList.toggle('expanded');
            document.getElementById('htpBody').classList.toggle('expanded');
            ArcadeSound.play('toggle');
        }

        // Moves (not duplicates) the turn banner specifically to the die panel in
        // rotated (forced-landscape) mode — a plain flow child that inherits the
        // die panel's own position/rotation/drag, so it visibly moves and scales
        // WITH the die instead of being a separately fixed corner element
        // unrelated to where the (draggable) die actually sits. Everything else
        // (progress, seating order, opponents) stays in its natural sidebar
        // position inside the drawer on mobile — this used to ALSO relocate to a
        // separate scrollable area below the board, which just read as a
        // confusing duplicate of the drawer's own content.
        function relocateGameHud() {
            const isMobile = window.innerWidth < 1024;
            const rotated = isMobile && isRotatedMobile();
            const turnBanner = document.getElementById('turnBanner');
            const diePanel = document.querySelector('.die-panel');
            if (!turnBanner || !diePanel) return;

            if (rotated) {
                if (turnBanner.parentElement !== diePanel) {
                    diePanel.insertBefore(turnBanner, diePanel.firstChild);
                }
            } else if (turnBanner.nextElementSibling !== diePanel) {
                diePanel.parentElement.insertBefore(turnBanner, diePanel);
            }
        }
        relocateGameHud();
        let hudResizeTimer = null;
        window.addEventListener('resize', () => {
            clearTimeout(hudResizeTimer);
            hudResizeTimer = setTimeout(relocateGameHud, 150);
        });
        window.addEventListener('orientationchange', () => setTimeout(relocateGameHud, 250));

        // Centers the event toast on the ROTATED board's true visual center — see
        // the .event-toast rule's comment for why a plain CSS left:50% can't do
        // this once the board (and the toast's own rotate(90deg)) are involved.
        // No-ops outside the forced-landscape case, clearing any inline override
        // so the plain (already-centered) CSS rule takes over normally.
        function positionToastOverBoard() {
            const toast = document.getElementById('eventToast');
            const board = document.getElementById('boardWrap');
            if (!toast || !board) return;
            const isRotated = window.innerWidth < 1024 && window.matchMedia('(orientation: portrait)').matches;
            if (!isRotated) {
                toast.style.removeProperty('--toast-left');
                toast.style.removeProperty('--toast-top');
                return;
            }
            const r = board.getBoundingClientRect();
            toast.style.setProperty('--toast-left', (r.left + r.width / 2) + 'px');
            toast.style.setProperty('--toast-top', (r.top + r.height / 2) + 'px');
        }
        positionToastOverBoard();
        window.addEventListener('load', positionToastOverBoard);
        window.addEventListener('resize', positionToastOverBoard);
        window.addEventListener('orientationchange', () => setTimeout(positionToastOverBoard, 200));

        // Re-snaps every visible token to the correct (desktop vs mobile-landscape)
        // position set the instant the phone is physically rotated mid-game —
        // tokenPos() already picks the right set, this just re-applies it.
        function repositionAllTokens() {
            const tokenMe = document.getElementById('tokenMe');
            const myPos = parseInt(document.getElementById('hudPosition').textContent, 10);
            if (tokenMe && !isNaN(myPos)) placeToken(tokenMe, myPos);
            document.querySelectorAll('.token-op').forEach(el => {
                const m = el.id.match(/^token-opp-(\d+)$/);
                if (!m) return;
                const posLabel = document.getElementById('opp-pos-' + m[1]);
                if (!posLabel) return;
                const currentPos = parseInt(posLabel.textContent, 10);
                if (!isNaN(currentPos)) placeToken(el, currentPos);
            });
        }
        window.addEventListener('orientationchange', () => setTimeout(repositionAllTokens, 250));

        // Mobile: the die floats free of the drawer (see .die-panel media query),
        // starts docked over the board itself (not wherever empty page space happens
        // to be), and can be dragged anywhere on screen Ludo-style. A real drag just
        // repositions it — only a plain tap (movement under DRAG_THRESHOLD) rolls.
        (function () {
            const diePanel = document.querySelector('.die-panel');
            const dieBadge = document.getElementById('rollBtn');
            const board = document.getElementById('boardWrap');
            if (!diePanel || !dieBadge) return;

            const DRAG_THRESHOLD = 10; // px
            let dragging = false, moved = false, downX = 0, downY = 0, offX = 0, offY = 0;

            function dockOverBoard() {
                if (window.innerWidth >= 1024 || !board) return;
                const r = board.getBoundingClientRect();
                const w = diePanel.offsetWidth || 88, h = diePanel.offsetHeight || 100;
                const x = Math.max(4, Math.min(window.innerWidth - w - 4, r.left + r.width * 0.78));
                const y = Math.max(4, Math.min(window.innerHeight - h - 4, r.top + r.height * 0.68));
                diePanel.style.left = x + 'px'; diePanel.style.top = y + 'px';
                diePanel.style.right = 'auto'; diePanel.style.bottom = 'auto';
            }
            // Board image loads async — dock once now (in case it's cached/instant)
            // and again shortly after, once the real board dimensions are known.
            dockOverBoard();
            window.addEventListener('load', dockOverBoard);
            setTimeout(dockOverBoard, 300);
            window.addEventListener('resize', dockOverBoard);

            dieBadge.addEventListener('pointerdown', e => {
                downX = e.clientX; downY = e.clientY; moved = false;
                if (window.innerWidth >= 1024) return;
                dragging = true;
                const r = diePanel.getBoundingClientRect();
                offX = e.clientX - r.left; offY = e.clientY - r.top;
                diePanel.style.transition = 'none';
                dieBadge.classList.add('dragging');
                try { dieBadge.setPointerCapture(e.pointerId); } catch (_) {}
            });
            dieBadge.addEventListener('pointermove', e => {
                if (Math.abs(e.clientX - downX) > DRAG_THRESHOLD || Math.abs(e.clientY - downY) > DRAG_THRESHOLD) moved = true;
                if (!dragging) return;
                let x = e.clientX - offX, y = e.clientY - offY;
                x = Math.max(4, Math.min(window.innerWidth - diePanel.offsetWidth - 4, x));
                y = Math.max(4, Math.min(window.innerHeight - diePanel.offsetHeight - 4, y));
                diePanel.style.left = x + 'px'; diePanel.style.top = y + 'px';
                diePanel.style.right = 'auto'; diePanel.style.bottom = 'auto';
            });
            const endGesture = () => {
                const wasDragging = dragging;
                if (wasDragging) {
                    dragging = false;
                    dieBadge.classList.remove('dragging');
                    diePanel.style.transition = '';
                }
                if (window.innerWidth >= 1024 || !moved) {
                    doRoll(); // plain tap (desktop click, or mobile tap under the threshold)
                } else if (wasDragging) {
                    diePanel.classList.add('thrown'); // real drag — just a landing flourish, no roll
                    setTimeout(() => diePanel.classList.remove('thrown'), 450);
                }
            };
            dieBadge.addEventListener('pointerup', endGesture);
            dieBadge.addEventListener('pointercancel', () => {
                dragging = false;
                dieBadge.classList.remove('dragging');
                diePanel.style.transition = '';
            });
        })();

        // Sessions currently mid-animation via animateOpponent() — applyOpponentState()
        // skips repositioning their token so the slow multi-step animation isn't
        // interrupted by an instant snap to the already-resolved DB position.
        const animatingSessions = new Set();
        // Every entry gets its own force-clear safety net — animatingSessions
        // gates the roll button (see updateRollButtonState()), so a stuck
        // opponent/bot animation (an exception mid-chain, a backgrounded tab
        // throttling timers, anything) must never leave it permanently blocked.
        function trackAnimating(sessionId, ms = 6000) {
            animatingSessions.add(sessionId);
            setTimeout(() => {
                if (animatingSessions.delete(sessionId)) updateRollButtonState();
            }, ms);
        }

        function applyOpponentState(opp) {
            let row = document.getElementById('opp-row-' + opp.session_id);
            let token = document.getElementById('token-opp-' + opp.session_id);
            const isNew = !row;

            if (isNew) {
                document.getElementById('noOpponentsMsg').style.display = 'none';
                document.getElementById('oppHeading').style.display = 'block';
                document.getElementById('waitingForOpponentBanner')?.style.setProperty('display', 'none');

                const safeName = escapeHtml(opp.name || 'Player');
                const initial = escapeHtml((opp.name || 'P').charAt(0).toUpperCase());
                // The fallback initial/emoji is always in the DOM; a broken/stale
                // photo URL sits on top and removes itself on error, revealing it.
                const avatarInner = (opp.is_bot ? '🤖' : initial)
                    + ((!opp.is_bot && opp.avatar) ? `<img src="${escapeHtml(opp.avatar)}" alt="" style="position:absolute;inset:0;width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.remove();">` : '');
                row = document.createElement('div');
                row.className = 'opp-row';
                row.id = 'opp-row-' + opp.session_id;
                row.innerHTML = `<div class="opp-row-head" onclick="toggleOppRow(${opp.session_id})">
                    <div class="avatar ${opp.is_bot ? 'avatar-bot' : 'avatar-op'}" style="width:1.9rem;height:1.9rem;font-size:.85rem;position:relative;">${avatarInner}</div>
                    <div style="flex:1;"><p class="font-bold">${opp.is_bot ? 'Robo' : safeName}</p>
                    <div class="opp-bar"><i id="opp-bar-${opp.session_id}" style="width:0%;"></i></div></div>
                    <span class="text-[10px] text-gray-500" id="opp-pos-${opp.session_id}">0/${TILE_COUNT}</span>
                    <span class="opp-chevron">▼</span>
                    </div>
                    <div class="opp-details">
                        <div class="opp-detail-line"><span>Status</span><b id="opp-status-${opp.session_id}">Active</b></div>
                        <div class="opp-detail-line"><span>Savings</span><b id="opp-pot-${opp.session_id}">KES 0</b></div>
                        <div class="opp-detail-line"><span>Started with</span><b>KES ${Number(opp.stake || 0).toLocaleString()}</b></div>
                        ${MATCH_MODE === 'wager' ? `<div class="opp-detail-line" id="opp-missed-wrap-${opp.session_id}" style="display:none;"><span>Missed turns</span><b id="opp-missed-${opp.session_id}" style="color:#fbbf24;">0/${FORFEIT_MISSED_TURNS}</b></div>` : ''}
                    </div>`;
                document.getElementById('opponentsList').appendChild(row);

                token = document.createElement('div');
                token.className = 'token token-op';
                token.id = 'token-opp-' + opp.session_id;
                token.textContent = opp.is_bot ? '🤖' : '👤';
                if (!opp.is_bot && opp.avatar) {
                    const img = document.createElement('img');
                    img.src = opp.avatar;
                    img.alt = '';
                    img.onerror = () => img.remove();
                    token.appendChild(img);
                }
                document.getElementById('boardWrap').appendChild(token);
                placeToken(token, opp.position || 1);
                ArcadeSound.play('notify');
                showToast([`👋 ${opp.is_bot ? 'Robo' : safeName} joined the match!`]);
            }

            const bar = document.getElementById('opp-bar-' + opp.session_id);
            const posLabel = document.getElementById('opp-pos-' + opp.session_id);
            const statusLabel = document.getElementById('opp-status-' + opp.session_id);
            const potLabel = document.getElementById('opp-pot-' + opp.session_id);
            if (bar) bar.style.width = Math.round(opp.position / TILE_COUNT * 100) + '%';
            if (posLabel) posLabel.textContent = opp.position + '/' + TILE_COUNT;
            if (statusLabel) statusLabel.textContent = opp.status.charAt(0).toUpperCase() + opp.status.slice(1);
            if (potLabel && opp.pot !== undefined) potLabel.textContent = 'KES ' + Number(opp.pot).toLocaleString();
            if (MATCH_MODE === 'wager' && opp.missed_turns !== undefined) {
                const missedWrap = document.getElementById('opp-missed-wrap-' + opp.session_id);
                const missedLabel = document.getElementById('opp-missed-' + opp.session_id);
                if (missedWrap) missedWrap.style.display = opp.missed_turns > 0 ? '' : 'none';
                if (missedLabel) missedLabel.textContent = opp.missed_turns + '/' + FORFEIT_MISSED_TURNS;
            }
            // Real opponents don't return a hop_path/roll event the way bot_roll
            // does (state() only ever reports their CURRENT position) — so this
            // can't animate tile-by-tile, but it can still clearly announce the
            // move and briefly gate the roll button while it plays, closing the
            // gap where only bot moves used to get this treatment.
            if (!isNew && token && !animatingSessions.has(opp.session_id)) {
                const p = tokenPos(opp.position);
                const moved = parseFloat(token.style.left) !== p.left || parseFloat(token.style.top) !== p.top;
                if (moved) {
                    if (!opp.is_bot) {
                        trackAnimating(opp.session_id, 1100);
                        const lines = [`🎲 ${escapeHtml(opp.name || 'Player')} rolled — moving to tile ${opp.position}...`];
                        // Same eventLine() wording used for your OWN reward/expense/
                        // mystery/golden tiles — money_event is only ever present
                        // once per new position (see state()'s $s->last_event pick),
                        // so this rides the same one-shot `moved` gate above and
                        // won't repeat on later polls.
                        if (opp.money_event) {
                            lines.push(eventLine(opp.money_event));
                            const lost = opp.money_event.type === 'expense'
                                || (opp.money_event.type === 'mystery' && opp.money_event.effect !== 'gift');
                            ArcadeSound.play(lost ? 'coinLoss' : 'coinGain');
                        }
                        showToast(lines);
                        ArcadeSound.play('move');
                    }
                    placeToken(token, opp.position);
                }
            }
            if (row) row.classList.toggle('opp-ended', opp.status !== 'active');
        }

        async function pollState() {
            let res;
            try {
                const r = await fetch(STATE_URL, { headers: HEADERS });
                res = await r.json();
            } catch (e) { return; }
            if (!res.success) return;

            myTurn = res.my_turn;

            // Registered BEFORE updateTurnBanner()/the countdown resync below (not
            // after, as this used to be ordered) — a bot's roll arrives in this same
            // poll, but its die-spin flourish + hop haven't started animating yet.
            // Marking it as animating first is what makes the "hold the display
            // while animating" logic below actually see it in THIS same tick,
            // instead of the banner/counter flipping to "your turn" for one poll
            // before the animation is even known about.
            if (res.bot_roll && !animatingSessions.has(res.bot_roll.session_id)) {
                trackAnimating(res.bot_roll.session_id);
                showToast(['🤖 Robo is rolling...']);
                spinDieForBot(res.bot_roll.roll).then(() => {
                    animatingSessions.delete(res.bot_roll.session_id);
                    animateOpponent(res.bot_roll, 'Robo');
                });
            }

            // Only resync the displayed countdown from the server while nothing is
            // visibly animating on THIS screen — the server can only estimate how
            // long a move's animation takes (and can't know about this specific
            // client's exact render timing), so blindly trusting it mid-animation
            // is what caused the countdown to visibly jump (e.g. 6 straight to 4).
            if (animatingSessions.size === 0) {
                turnSecondsLeft = res.turn_seconds_remaining ?? null;
            }
            updateTurnBanner(res.current_turn_user);
            if (!rolling) updateRollButtonState();
            (res.opponents || []).forEach(applyOpponentState);
            handleIncomingReaction(res.reaction);

            const mySeat = { session_id: MY_SESSION_ID, name: MY_NAME, is_me: true, is_bot: false, turn_order: MY_TURN_ORDER, status: res.session ? res.session.status : status };
            updateTurnOrderStrip([mySeat, ...(res.opponents || [])], res.current_turn_session_id);

            // A player who didn't roll the decisive move themselves (someone
            // else's win, or in Rivals Trail an opponent's forfeit, settled the
            // round) only ever learns the outcome here — processRollResult()'s
            // win/bust overlays only ever cover the roller's OWN turn. This used
            // to be gated to `res.mode === 'wager'` only, which meant standard
            // (non-wager) opponents never found out the match was over at all —
            // their own session stays 'active' server-side until this same block
            // flips it, so without this firing they could keep right on rolling
            // against a match that had already been won.
            if (res.session && status === 'active' && res.session.status !== 'active' && !rolling) {
                status = res.session.status;
                updateHud(res.session.pot, res.session.position);
                updateRollButtonState();
                if (res.mode === 'wager') {
                    if (res.session.status === 'lost') { ArcadeSound.play('bust'); showLostOverlay(res.session.pot, res.session.amount_lost, res.session.winner_name); }
                    else if (res.session.status === 'forfeited') { showForfeitedOverlay(res.session.pot); }
                    else if (res.session.status === 'won') { ArcadeSound.play('win'); showWinOverlay(res.session); }
                } else {
                    if (res.session.status === 'won') { ArcadeSound.play('win'); showWinOverlay(res.session); }
                    else if (res.session.status === 'lost') {
                        const winnerOpp = (res.opponents || []).find(o => o.status === 'won');
                        ArcadeSound.play('bust');
                        showRaceOverOverlay(winnerOpp ? winnerOpp.name : 'A player', res.session.pot);
                    }
                }
            }
        }

        /** Spins the shared die (landing on a known value) to make the bot's turn
         *  visibly happen — otherwise its move is just a silent token hop somewhere
         *  on the board, easy to miss entirely. */
        function spinDieForBot(rollValue) {
            return new Promise(resolve => {
                const cube = document.getElementById('dieCube');
                const scene = document.getElementById('dieScene');
                scene.classList.add('spinning');
                ArcadeSound.play('roll');
                cube.style.transition = 'transform 0.12s linear';
                let spin = 0;
                const spinTimer = setInterval(() => {
                    spin += 50;
                    cube.style.transform = `rotateX(${DIE_TILT_X + spin * 1.3}deg) rotateY(${DIE_TILT_Y + spin}deg)`;
                }, 60);

                setTimeout(() => {
                    clearInterval(spinTimer);
                    const target = DIE_ROTATIONS[rollValue];
                    cube.style.transition = 'transform 0.55s cubic-bezier(.25,1,.5,1)';
                    cube.style.transform = `rotateX(${720 + DIE_TILT_X + target.x}deg) rotateY(${1080 + DIE_TILT_Y + target.y}deg)`;
                    showDieValue(rollValue);
                    setTimeout(() => { scene.classList.remove('spinning'); resolve(); }, 580);
                }, 480);
            });
        }

        // Safety net: whatever goes wrong client-side after a roll starts —
        // an unforeseen exception mid-animation, a browser throttling timers
        // in the background, anything — the game must never stay stuck longer
        // than this. Cleared normally the instant a roll finishes; if it ever
        // actually fires, something upstream broke and this forces recovery.
        let rollWatchdog = null;
        function armRollWatchdog() {
            clearTimeout(rollWatchdog);
            rollWatchdog = setTimeout(() => {
                if (!rolling) return;
                console.warn('[PesaTrail] roll animation watchdog fired — forcing recovery.');
                rolling = false;
                animatingSessions.clear();
                document.getElementById('dieScene')?.classList.remove('spinning');
                updateRollButtonState();
                pollState();
            }, 8000);
        }
        function disarmRollWatchdog() { clearTimeout(rollWatchdog); }

        async function doRoll() {
            if (rolling || status !== 'active') return;
            if (TURN_MODE === 'turns' && !myTurn) { showToast(['⏳ Wait for your turn.']); return; }
            rolling = true;
            armRollWatchdog();
            document.getElementById('rollBtn').disabled = true;
            document.getElementById('dieScene').classList.add('spinning');
            ArcadeSound.play('roll');

            const cube = document.getElementById('dieCube');
            cube.style.transition = 'transform 0.12s linear';
            let spin = 0;
            const spinTimer = setInterval(() => {
                spin += 50;
                cube.style.transform = `rotateX(${DIE_TILT_X + spin * 1.3}deg) rotateY(${DIE_TILT_Y + spin}deg)`;
            }, 60);

            let res;
            try {
                const r = await fetch(ROLL_URL, { method: 'POST', headers: HEADERS });
                res = await r.json();
            } catch (e) {
                clearInterval(spinTimer);
                disarmRollWatchdog();
                rolling = false;
                document.getElementById('dieScene').classList.remove('spinning');
                updateRollButtonState();
                showToast(['Network error — try again.']);
                return;
            }
            clearInterval(spinTimer);

            if (!res.success) {
                disarmRollWatchdog();
                rolling = false;
                document.getElementById('dieScene').classList.remove('spinning');
                updateRollButtonState();
                showToast([res.message || 'Could not roll.']);
                return;
            }

            if (TURN_MODE === 'turns') myTurn = false; // server already advanced the turn; poll will confirm who's next

            const target = DIE_ROTATIONS[res.roll];
            cube.style.transition = 'transform 0.55s cubic-bezier(.25,1,.5,1)';
            cube.style.transform = `rotateX(${720 + DIE_TILT_X + target.x}deg) rotateY(${1080 + DIE_TILT_Y + target.y}deg)`;
            showDieValue(res.roll);

            setTimeout(() => processRollResult(res), 580);
        }

        function processRollResult(res) {
            document.getElementById('dieScene').classList.remove('spinning');
            const tokenMe = document.getElementById('tokenMe');
            ArcadeSound.play('move');

            let moveEvent = null;
            let overshootEvent = null;
            res.events.forEach(ev => {
                if (ev.type === 'move') moveEvent = ev;
                if (ev.type === 'overshoot') overshootEvent = ev;
            });

            const finish = () => {
                disarmRollWatchdog();
                status = res.status;
                updateHud(res.pot, res.position);
                if (status === 'won') {
                    rolling = false;
                    setTimeout(() => { ArcadeSound.play('win'); showWinOverlay(res); }, 600);
                } else if (status === 'busted') {
                    rolling = false;
                    setTimeout(() => { ArcadeSound.play('bust'); showBustOverlay(); }, 600);
                } else {
                    const cashOutBtn = document.getElementById('cashOutBtn'); // absent for Rivals Trail sessions — see below
                    if (cashOutBtn) cashOutBtn.disabled = false;
                    rolling = false;
                    updateRollButtonState();
                    updateTurnBanner();
                    // Pick up the turn change (and, for Rivals Trail, a possible
                    // instant settlement) right away instead of waiting for the
                    // next scheduled poll tick.
                    pollState();
                }
            };

            // Once the token has visibly hopped to where the FIRST tile's effect
            // actually applies, fire that tile's sound/toast/sparkle — not before,
            // which is what used to make a multi-tile roll's coin chime/toast land
            // audibly before the token had even finished moving there.
            const afterFirstLanding = () => {
                const lines = [];
                let netChange = 0;
                res.events.forEach(ev => {
                    lines.push(eventLine(ev));
                    if (ev.type === 'reward') { ArcadeSound.play('coinGain'); sparkleBurst(tokenMe); netChange += ev.amount; }
                    if (ev.type === 'expense') { ArcadeSound.play('coinLoss'); netChange -= ev.amount; }
                    if (ev.type === 'mystery') { ArcadeSound.play('mystery'); netChange += ev.effect === 'gift' ? ev.amount : -ev.amount; }
                    if (ev.type === 'golden_first') glowGold(tokenMe);
                    if (ev.type === 'golden_boost') { ArcadeSound.play('coinGain'); glowGold(tokenMe); sparkleBurst(tokenMe); netChange += ev.amount; }
                });
                if (lines.length) showToast(lines);
                // One combined floating +/-KES number for the whole roll (not
                // one per event) — a roll landing on multiple effects at once
                // reads as a single net result, not a pile-up of separate flights.
                if (netChange !== 0) floatMoneyToBalance(netChange, tokenMe, res.pot);

                if (moveEvent) {
                    setTimeout(() => {
                        placeToken(tokenMe, moveEvent.to);
                        ArcadeSound.play(moveEvent.via === 'snake_head' ? 'snake' : 'ladder');
                        setTimeout(finish, 900);
                    }, 400);
                } else {
                    // Overshoot's bounce-back is already the tail end of hop_path
                    // (see ArcadeSnakesService::roll()) — the token is already
                    // sitting on bounced_to, no extra placeToken needed here.
                    setTimeout(finish, 500);
                }
            };

            const path = (res.hop_path && res.hop_path.length) ? res.hop_path : [res.first_landing];
            animateHopPath(tokenMe, path, afterFirstLanding);
        }

        function animateOpponent(botResult, label) {
            const el = document.getElementById('token-opp-' + botResult.session_id);
            const bar = document.getElementById('opp-bar-' + botResult.session_id);
            const posLabel = document.getElementById('opp-pos-' + botResult.session_id);
            const statusLabel = document.getElementById('opp-status-' + botResult.session_id);
            const potLabel = document.getElementById('opp-pot-' + botResult.session_id);
            if (!el || animatingSessions.has(botResult.session_id)) return;

            trackAnimating(botResult.session_id);
            ArcadeSound.play('move');
            const moveEvent = botResult.events.find(ev => ev.type === 'move');
            const moneyEvent = botResult.events.find(ev => ev.type === 'reward' || ev.type === 'expense' || ev.type === 'golden_boost');

            const settle = () => {
                if (bar) bar.style.width = Math.round(botResult.position / TILE_COUNT * 100) + '%';
                if (posLabel) posLabel.textContent = botResult.position + '/' + TILE_COUNT;
                if (statusLabel) statusLabel.textContent = botResult.status.charAt(0).toUpperCase() + botResult.status.slice(1);
                if (potLabel) potLabel.textContent = 'KES ' + Number(botResult.pot).toLocaleString();
                const finalEv = botResult.events[botResult.events.length - 1];
                if (finalEv && finalEv.type === 'win') showToast([`🤖 ${label} reached the finish first!`]);
                else if (finalEv && finalEv.type === 'bust') showToast([`🤖 ${label} ran out of savings!`]);
                animatingSessions.delete(botResult.session_id);
                // The banner/counter were held at "their turn" for the whole
                // animation (see pollState()) — refresh immediately now that it's
                // actually done, rather than leaving a stale display up to ~4s
                // (the adaptive poll interval) until the next natural tick.
                updateTurnBanner();
                pollState();
            };

            const afterFirstLanding = () => {
                if (moneyEvent) {
                    const gain = moneyEvent.type !== 'expense';
                    showToast([`🤖 ${label} ${gain ? 'gained' : 'lost'} KES ${moneyEvent.amount} on tile ${moneyEvent.tile}`]);
                    if (gain) { ArcadeSound.play('coinGain'); sparkleBurst(el); } else { ArcadeSound.play('coinLoss'); }
                }
                if (moveEvent) {
                    setTimeout(() => { placeToken(el, moveEvent.to); ArcadeSound.play(moveEvent.via === 'snake_head' ? 'snake' : 'ladder'); setTimeout(settle, 900); }, 400);
                } else {
                    // Overshoot's bounce-back is already the tail end of hop_path — see processRollResult()'s comment.
                    setTimeout(settle, 500);
                }
            };

            const path = (botResult.hop_path && botResult.hop_path.length) ? botResult.hop_path : [botResult.first_landing];
            animateHopPath(el, path, afterFirstLanding);
        }

        async function cashOut() {
            if (status !== 'active') return;
            if (!confirm('Bank your KES ' + document.getElementById('hudPot').textContent + ' savings now?')) return;
            document.getElementById('rollBtn').disabled = true;
            document.getElementById('cashOutBtn').disabled = true;
            let res;
            try {
                const r = await fetch(CASH_OUT_URL, { method: 'POST', headers: HEADERS });
                res = await r.json();
            } catch (e) { showToast(['Network error — try again.']); document.getElementById('cashOutBtn').disabled = false; return; }

            if (!res.success) { showToast([res.message]); document.getElementById('cashOutBtn').disabled = false; return; }
            ArcadeSound.play('cashout');
            document.getElementById('cashOutPayout').textContent = 'KES ' + Number(res.payout).toLocaleString();
            document.getElementById('cashOutBalance').textContent = 'KES ' + Number(res.balance).toLocaleString();
            document.getElementById('cashOutOverlay').style.display = 'flex';
        }

        // No-op if PostHog isn't configured (Admin → Analytics → Tracker Setup) —
        // see resources/views/partials/trackers.blade.php.
        function phTrack(event, props) {
            if (window.posthog) posthog.capture(event, Object.assign({ mode: MATCH_MODE }, props || {}));
        }

        function showWinOverlay(res) {
            const gain = (res.winner_gain || 0) + (res.forfeit_bonus || 0);
            const gainLine = document.getElementById('winGainLine');
            if (gain > 0) {
                gainLine.textContent = '💰 Won KES ' + Number(gain).toLocaleString() + ' from your opponent' + (res.forfeit_bonus ? ' (incl. forfeit bonus)' : '') + '!';
                gainLine.style.display = '';
            } else {
                gainLine.style.display = 'none';
            }
            document.getElementById('winPayout').textContent = 'KES ' + Number(res.pot).toLocaleString();
            document.getElementById('winOverlay').style.display = 'flex';
            spawnConfetti();
            phTrack('pesatrail_round_won', { pot: res.pot, gain: gain });
        }
        function showBustOverlay() {
            document.getElementById('bustOverlay').style.display = 'flex';
            phTrack('pesatrail_round_busted', {});
        }
        function showLostOverlay(pot, amountLost, winnerName) {
            const amountLine = document.getElementById('lostAmountLine');
            if (amountLost) {
                amountLine.textContent = '📤 Lost KES ' + Number(amountLost).toLocaleString() + ' to ' + (winnerName || 'the other player') + '.';
                amountLine.style.display = '';
            } else {
                amountLine.style.display = 'none';
            }
            document.getElementById('lostPayout').textContent = 'KES ' + Number(pot).toLocaleString();
            document.getElementById('lostOverlay').style.display = 'flex';
            phTrack('pesatrail_round_lost', { pot: pot, amount_lost: amountLost || 0 });
        }
        function showForfeitedOverlay(pot) {
            document.getElementById('forfeitedPayout').textContent = 'KES ' + Number(pot).toLocaleString();
            document.getElementById('forfeitedOverlay').style.display = 'flex';
            phTrack('pesatrail_round_forfeited', { pot: pot });
        }
        function showRaceOverOverlay(winnerName, pot) {
            document.getElementById('raceWinnerName').textContent = winnerName || 'A player';
            document.getElementById('raceOverPayout').textContent = 'KES ' + Number(pot).toLocaleString();
            document.getElementById('raceOverOverlay').style.display = 'flex';
            phTrack('pesatrail_round_lost', { pot: pot });
        }

        function spawnConfetti() {
            const colors = ['#f59e0b', '#6366f1', '#10b981', '#ec4899', '#3b82f6'];
            for (let i = 0; i < 50; i++) {
                const el = document.createElement('div');
                el.className = 'confetti-piece';
                el.style.left = Math.random() * 100 + 'vw';
                el.style.background = colors[Math.floor(Math.random() * colors.length)];
                el.style.transform = `rotate(${Math.random() * 360}deg)`;
                el.style.animation = `confettiFall ${1.8 + Math.random() * 1.4}s ease-in forwards`;
                el.style.animationDelay = (Math.random() * 0.4) + 's';
                document.body.appendChild(el);
                setTimeout(() => el.remove(), 4000);
            }
        }

        const styleTag = document.createElement('style');
        styleTag.textContent = `@keyframes confettiFall { to { transform: translateY(100vh) rotate(720deg); opacity: 0.2; } }`;
        document.head.appendChild(styleTag);

        // Unlock the WebAudio context on the very first tap/click anywhere, so the
        // roll sound isn't the browser's first-ever audio interaction attempt —
        // and start the soft background ambience at the same time (autoplay
        // policies require a real user gesture before any audio can start).
        document.addEventListener('pointerdown', function primeAudio() {
            ArcadeSound.unlock();
            ArcadeSound.startAmbient();
            document.removeEventListener('pointerdown', primeAudio);
        }, { once: true });
        window.addEventListener('pagehide', () => ArcadeSound.stopAmbient());
        // Same tab-visibility pause/resume pattern world.js already uses for the
        // city map's ambient audio — stop the music the instant the player tabs
        // away, resume it the instant they come back. For a solo/bot session,
        // this ALSO fully pauses polling — with no real opponent waiting on the
        // other end, there's no reason Robo should keep rolling (or the turn
        // timer keep burning down) while nobody's even looking at the screen.
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                ArcadeSound.stopAmbient();
                if (IS_SOLO_BOT) clearTimeout(pollTimer);
            } else {
                ArcadeSound.startAmbient();
                if (IS_SOLO_BOT && status === 'active') pollState().finally(schedulePoll);
            }
        });

        updateTurnBanner();
        updateRollButtonState();
        // Adaptive polling cadence: tight (1.2s) while I'm waiting on an opponent —
        // that's exactly when latency is felt — and relaxed (3.5s) on my own turn
        // or in free (non-turn-based) matches, where there's nothing to wait for.
        // Re-arms itself after every poll rather than a flat setInterval so the
        // cadence can change the instant myTurn flips (see pollState()).
        let pollTimer = null;
        function schedulePoll() {
            clearTimeout(pollTimer);
            if (status !== 'active') return;
            if (IS_SOLO_BOT && document.hidden) return; // paused — visibilitychange resumes it
            const delay = (TURN_MODE === 'turns' && !myTurn) ? 1200 : 3500;
            pollTimer = setTimeout(() => { pollState().finally(schedulePoll); }, delay);
        }
        if (status === 'active') {
            pollState().finally(schedulePoll);
        }

        // ── Notifications — same GameNotification feed used sitewide (e.g. "salary
        // ready for pickup"), so nothing gets missed while inside a game session. ──
        const NOTIFICATIONS_URL = "{{ route('game.notifications') }}";
        const NOTIFICATIONS_READ_URL = "{{ route('game.notifications.read') }}";
        let notifPanelOpen = false;
        let notifBaselineSet = false;
        const lastNotifIds = new Set();

        function timeAgo(iso) {
            const secs = Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 1000));
            if (secs < 60) return 'just now';
            if (secs < 3600) return Math.floor(secs / 60) + 'm ago';
            if (secs < 86400) return Math.floor(secs / 3600) + 'h ago';
            return Math.floor(secs / 86400) + 'd ago';
        }

        function renderNotifications(list) {
            const box = document.getElementById('notifList');
            if (!list.length) { box.innerHTML = '<div class="notif-empty">You\'re all caught up 🎉</div>'; return; }
            box.innerHTML = list.map(n => `
                <a class="notif-row ${n.is_read ? '' : 'unread'}" ${n.url ? `href="${n.url}"` : ''}>
                    <span class="notif-icon">${n.icon || '💡'}</span>
                    <div style="flex:1;min-width:0;">
                        <div class="notif-title">${escapeHtml(n.title || '')}</div>
                        <div class="notif-body">${escapeHtml(n.body || '')}</div>
                        <div class="notif-time">${timeAgo(n.created_at)}</div>
                    </div>
                </a>`).join('');
        }

        async function fetchNotifications() {
            let list;
            try {
                const r = await fetch(NOTIFICATIONS_URL, { headers: HEADERS });
                list = await r.json();
            } catch (e) { return; }
            if (!Array.isArray(list)) return;

            const unread = list.filter(n => !n.is_read);
            const badge = document.getElementById('notifBadge');
            if (unread.length) { badge.style.display = 'flex'; badge.textContent = unread.length > 9 ? '9+' : unread.length; }
            else { badge.style.display = 'none'; }

            // Surface brand-new unread notifications as an in-game toast too — this is
            // what makes "salary ready" etc. visible without leaving the board. The
            // first fetch just records the existing backlog as a baseline (no toast
            // spam for things that were already waiting before this session opened).
            if (!notifBaselineSet) {
                unread.forEach(n => lastNotifIds.add(n.id));
                notifBaselineSet = true;
            } else {
                unread.forEach(n => {
                    if (!lastNotifIds.has(n.id)) {
                        lastNotifIds.add(n.id);
                        ArcadeSound.play('notify');
                        showToast([`<div class="toast-headline">${n.icon || '💡'} ${escapeHtml(n.title || '')}</div><div class="toast-lesson">${escapeHtml(n.body || '')}</div>`]);
                    }
                });
            }

            if (notifPanelOpen) renderNotifications(list);
            window._notifCache = list;
        }

        function toggleNotifPanel() {
            notifPanelOpen = !notifPanelOpen;
            document.getElementById('notifPanel').classList.toggle('show', notifPanelOpen);
            ArcadeSound.play('toggle');
            if (notifPanelOpen) {
                renderNotifications(window._notifCache || []);
                fetch(NOTIFICATIONS_READ_URL, { method: 'POST', headers: HEADERS }).then(() => {
                    document.getElementById('notifBadge').style.display = 'none';
                });
            }
        }

        document.addEventListener('click', (e) => {
            const wrap = document.querySelector('.notif-wrap');
            if (notifPanelOpen && wrap && !wrap.contains(e.target)) toggleNotifPanel();
        });

        fetchNotifications();
        setInterval(fetchNotifications, 20000);

        function shareInvite(code) {
            const url = "{{ route('arcade.snakes.lobby') }}?join=" + code;
            const text = `Join my Pesa Trail match! Code: ${code}`;
            if (navigator.share) {
                navigator.share({ title: 'Pesa Trail', text, url }).catch(() => {});
            } else {
                navigator.clipboard.writeText(`${text} ${url}`).then(() => showToast(['📋 Invite link copied!']));
            }
        }
    </script>
</body>
</html>
