# PesaQuest — Platform Administration & Game Logic Guide

*How every system works under the hood, how to operate the platform day to day, how it
makes money, how it is secured, and how it scales.*
*Version: August 2026 · Audience: administrators, developers, Moski leadership*

**Companion documents:** `GAMEPLAY.md` (the game explained for reviewers/educators — the
document to hand to KICD) · `GAMESET_GUIDE.md` (the content team's setup manual with
samples) · `BILLS_GUIDE.md` and `SMART_TOOLS_GUIDE.md` (player-facing guides — hand these
to support/community teams fielding "how do bills/tools work" questions).

---

## Table of Contents
1. [Architecture Overview](#1-architecture-overview)
2. [The Tick System — deep dive](#2-the-tick-system)
3. [Engine-by-Engine Game Logic](#3-engine-by-engine-game-logic)
4. [Admin Panel Manual — tab by tab](#4-admin-panel-manual)
5. [Day-1 Setup & Weekly Operating Routine](#5-day-1-setup--weekly-routine)
6. [💰 How the Game Makes Money](#6-how-the-game-makes-money)
7. [🔐 Security](#7-security)
8. [📈 Scaling & Capacity](#8-scaling--capacity)
9. [Deploys, Golden Rules & Troubleshooting](#9-deploys-golden-rules--troubleshooting)

---

## 1. Architecture Overview

- **Stack:** Laravel 12 (PHP 8.2), Blade templates + Alpine.js + Tailwind (Vite-built),
  MySQL. Single monolithic app — deliberately simple to host and reason about.
- **No cron required.** The simulation is **login-driven**: each player's world catches
  up when they arrive. Scheduled commands exist for hosts with cron (`game:process-crises`
  hourly), but every scheduled job also runs opportunistically on player logins with
  idempotent guards, so shared hosting works fully.
- **Key services** (`app/Services/`):
  | Service | Responsibility |
  |---|---|
  | `GameClock` | Real time ⇄ game ticks conversion (§2) |
  | `LifeSimulator` | The master catch-up pipeline (§3.1) — the game's heartbeat |
  | `CrisisService` | Server-wide economic events: warnings, effects, timeline entries |
  | `PlanGate` | Every freemium limit; reads admin-set values on each check (§6) |
  | `BillService` | Attaches contextual bills when assets are bought |
  | `CareerService` | Payslip math: PAYE, NHIF, NSSF, net pay |
  | `AssetFinancingService` | Deposit + installment quotes for vehicles/property |
  | `QuestTriggerService` | Detects quest completion from real gameplay |
  | `EventEngine` | Contextual world-event popups in Pesa City |
- **The Settings table is the control room.** Game clock speed, plan limits, trial
  length, quest caps, WYWA pacing, savings interest rate, XP levels, chapters, quiz,
  hustle tips — all live in `settings` and are edited from the admin/gameset UIs.
  **No gameplay tuning ever requires a deploy.**
- **Roles:** `is_admin` (admin panel + everything), `is_gameset` (content portal).
  Both are treated as premium in-game. Everyone else is a player.

---

## 2. The Tick System

**One tick = one game day** — the unit every timed mechanic is written in.

### The mechanics, precisely
1. Admin sets pace in **Settings → Game Clock**: *"real hours per game week."*
   - Example: `24` → one real day = 7 game days → **1 tick ≈ 3.4 real hours**.
   - Example: `56` → one real day = 3 game days → 1 tick = 8 real hours.
2. Each player's `user_progress` row stores `last_tick_at` (when we last simulated them)
   and `tick_count` (their personal game-day counter — players are NOT synchronized).
3. Any page that calls `LifeSimulator::processLogin()` (dashboard, world, Life HQ,
   check-in):
   - `GameClock::ticksSince(last_tick_at)` → whole game days elapsed. (Implementation
     note: raw timestamp subtraction — Carbon 3's signed diffs once froze the clock;
     see §9 golden rules.)
   - The simulator processes those ticks in order inside one DB transaction (§3.1).
   - `last_tick_at` resets; leftover partial-day seconds carry over.
4. **Caps:** hard ceiling 60 ticks (2 game months) per catch-up; free accounts are
   further capped by the `catchup_ticks` gate (default 7).

### Tick-based vs real-time — know which is which

| Tick-based (game days) | Real-time (calendar) |
|---|---|
| Bills cycles, salary months, savings interest, loan installments, asset decay/appreciation, gig durations & cooldowns, deal maturities, life-event rolls, quest deadlines | Daily login bonus, daily quest cap, WYWA cooldown, crisis warning/hit datetimes, trial days, upsell nudge cadence |

### Choosing a clock speed
- **Faster** (e.g. 12 real h/game week): a school term contains years of financial life;
  more paydays and bills per week of real time; more urgency. Risk: players who visit
  daily face many obligations per visit.
- **Slower** (e.g. 72 real h/game week): calmer pace, better for younger cohorts.
- Change anytime; each player's next visit recalculates from their own `last_tick_at`.
  Big speed-ups mean big first catch-ups (capped at 60 ticks, so bounded).

---

## 3. Engine-by-Engine Game Logic

### 3.1 The LifeSimulator pipeline (every login, in this exact order)
1. **Crisis engine** — send due warnings, apply due effects (idempotent, server-wide)
2. **Bill initialization** — new/qualifying players receive age-band auto-assign bills
3. **Zero-balance credit ding** — −10, at most once per 30 ticks
4. **Daily login bonus** — real-day gated streak reward
5. **Subscription nudge** — free players, every N real days (admin-set, §6)
6. **Per-tick loop** — mood decays 1/day (floor 30; first crossing below 50 notifies)
7. **settleBills** — overdue detection + credit penalties (never auto-pays; max 2 credit
   hits per bill per catch-up)
8. **settleAssets** — income, running costs, condition −3/game-month, value drift
9. **settleDeals** — matured investments resolve by risk model
10. **settleLoans** — compounding + auto-deducted installments + credit effects
11. **settleFriendLoans** — overdue P2P loans: garnish available cash to the lender;
    shortfall → default (borrower −30 credit, lender notified)
12. **settleSavingsInterest** — compounds monthly at Setting `savings_interest_annual`
13. **settleJobSalaries** — salary **accrual** (pending pay *stacks*, never forfeited)
    + freelance gig completion + attendance strikes: 3 consecutive missed paydays →
    final notice (`job_warning`); a further game month of silence → dismissal
    (`job_dismissed`, mood −15, credit −15, pay stays collectible)
13. **Credit behaviour signals** — savings habit +10, asset collector +5, loyal worker +10
14. **Life events roll** — probability compounds with elapsed ticks; max 3 per session
15. **Net worth recalculation → chapter advancement** (+15 credit on upward milestone)
16. **WYWA assembly** — events list + `show_wywa` gating (min game days + cooldown;
    urgent events bypass both)

### 3.2 Bills — manual-only, calendar-anchored
No autopay. Each cycle passed unpaid → status `overdue`, `missed_count`++, credit
penalty. Manual payment: on-time = full credit reward; clearing overdue = half. Next due
= *old due + frequency* (early payment lengthens the visible gap; late shortens it) —
cycles never drift.

### 3.3 Salaries — the check-in economy
Monthly jobs accrue `unpaid_ticks`; each completed 30 **adds** a paycheck to
`pending_salary` (pay stacks — nothing is forfeited). A payday landing on uncollected
pay increments `missed_paydays`; at 3 the row gets `removal_warned_at_tick` and the
player a final notice; ≥4 misses **and** 30+ ticks after the warning → `status =
'dismissed'` (pay stays collectible as a final settlement). Any successful
`POST /life/work/checkin` banks all pending pay (city jobs + completed gigs + dismissed
final settlements), applies mood (−10% under 40) and active crisis salary cuts, and
resets `missed_paydays`/`removal_warned_at_tick`. Job types enforced at application
time: full-time is exclusive; part-time max 2; freelance max 3 concurrent, one-off pay,
28-tick per-gig cooldown.

### 3.3b Friends & P2P loans
`friendships` (requester/addressee/status) gate everything social; `users.friend_code`
is generated lazily. `users.username` (unique, `^[a-z][a-z0-9_]{2,19}$`, backfilled by
migration, auto-derived at registration, editable in Profile) is the public handle:
`User::getRouteKey()` emits it in URLs and `resolveRouteBinding()` accepts username OR
numeric id, so old links keep working; `Chama` does the same with `slug` (new chamas get
readable name-based slugs). Usernames must start with a letter so they can never
collide with numeric ids. `friend_loans` negotiation states: requested → offered → (countered
once) → active → repaid/defaulted; declined/expired close it. Hard rules live as
constants on `App\Models\FriendLoan` (RATE_PRESETS 5–20%, TERM_PRESETS 10/20/30 ticks,
AMOUNT_PRESETS, MAX_LEND_SHARE 20%, MAX_OPEN_PER_SIDE 3, MIN_LEVEL 3). Money moves only
at acceptance (lender must still cover ≤20% of cash); due dates run on the borrower's
clock and appear on their calendar. Unfriending is blocked while a loan is open.
Chamas: `visibility` public/private, private ones carry a unique `join_code`
(POST `/chama/join-code`), public ones may set `min_level`/`min_credit_score`/
`min_savings`; membership capped at 3 chamas (invites bypass requirements, never the
cap). Forum voting: `forum_votes` + cached `score` + `users.forum_karma` (badge trigger
`forum_karma`); one vote per player per post, no self-votes.

### 3.3c City Contracts & the Quest Factory (automation)
**Contracts** (`ContractService`, tuned in GameSet → Quests → 🤖 Automation): on each
dashboard load, active contracts settle by re-measuring each objective's metric against
its issue-time **baseline snapshot** (no event hooks; can't desync; works for offline
catch-up), then the player is topped up to their rule's contract count. `contract_rules`
rows (per age group + level band; seeded defaults ship in the migration) control
objectives per contract (3–5), completion mode **all** vs **any N**, duration in game
days, held count, and XP/Ksh rewards — narrowest matching rule wins, specific age beats
'all', no matching rule = no new contracts. Objective archetypes + all NPC copy live in
`config/pesa_voice.php` (15 archetypes × 5 NPCs × 3 age-band voice packs); urgent
archetypes (clean_slate, payday_pro) always make the cut when relevant. Completion pays
via `points_total`/`balance` + a `contract_completed` notification (push category:
achievements).

**Quest Factory** (`QuestFactory`): creating a course or job in GameSet auto-drafts its
quest — copy from the voice packs, age/level/career targeting inherited from the content
row, triggers wired to the existing engine (`take_course` slug / `get_job` id; jobs with
required courses become a two-step study→hired multi-trigger chain). Drafts land with
`is_active=false, source='factory'` and queue behind the amber banner in GameSet →
Quests (filter `?drafts=1`; publish = the normal activate toggle). Settings:
`quest_factory_enabled` (default on) and `quest_factory_autopublish` (default off), both
on the Automation page. Factory calls are try/catch-wrapped — they can never block
content creation.

### 3.3d Dreams & Challenges (Champions' Court)
**Dreams** are a flat purchase (`DreamService`) — wallet debit, `PlayerDream` row created,
never counted in net worth and never resellable (deliberate: closes a "buy cosmetic →
resell → inflate net worth" loophole). **Challenges** (`ChallengeService`) share a
progress engine, `GameMetrics`, with City Contracts (`GameMetrics::METRIC_STYLES` —
percent-style metrics use a floor divisor to avoid divide-by-zero/absurd swings for
near-zero starting balances). Two shapes: `duel` (baselines snapshot at full acceptance,
so both sides start from a fair line) and `broadcast` (join-anytime; used for official
Pesa City events, teacher Class Challenges, and chama-vs-chama Battles, which rank by
*average* member progress rather than any one member's score). Stakes, if any, are
charged symmetrically at each side's own commitment moment (creator at creation, others
at accept) and paid out winner-take-all on settlement. Duels settle instantly the moment
someone hits the goal; everything else settles via `game:settle-challenges`
(`SettleChallenges`, scheduled `dailyAt('02:45')`, same login-driven-or-cron pattern as
every other engine here).

### 3.4 Credit score (300–850, start 500)
Single write path `UserProgress::adjustCreditScoreWithLog()` — every change writes a
`credit_change` notification with delta + reason (powers the player-visible history and
de-duplicates behaviour signals). Values table in `GAMEPLAY.md` §12.

### 3.5 Crises
`financial_crises` rows carry two independent flags: `warning_sent_at` (warning pass)
and `is_processed` (effect pass) — a warned crisis still hits. Effects: investment_drop /
asset_drop / balance_drain are one-shot at window open; **salary_cut is continuous** for
the whole active window (applied at every pay collection). Every impact also writes a
timeline entry via a hidden LifeEvent template. Processing: hourly scheduler *and*
login-driven; *Process Crises Now* button in Artisan for immediate/testing.

### 3.6 Quests
Triggers fire from real gameplay via `QuestTriggerService` (types table in
`GAMESET_GUIDE.md` §7). Multi-trigger quests track per-step progress; `trigger_mode`
ALL/ANY. Daily start cap via Setting `max_quests_per_day` (0 = unlimited; re-opening an
already-started quest never counts). Trigger-less quests are manual → admin approval
queue.

### 3.7 One-liners
- **Savings:** typed rows (deposit/withdrawal/interest); monthly compounding; rate
  admin-set (default 8% p.a.).
- **Assets:** payback preview = price ÷ (income − cost); vehicles/property auto-attach
  bills; financing = deposit + auto-deducted installment loan.
- **Life events:** filtered by chapter + owned asset categories; fire chance
  `1-(1-p)^ticks`; effects balance/credit/market/compound/narrative.
- **Chapters:** Settings-backed config (names/icons/taglines/thresholds editable in
  GameSet; six keys fixed).
- **Fun World / Badges / XP / Milestones / Quiz / Hustle tips:** all DB/Settings-driven,
  gameset-editable.

---

## 4. Admin Panel Manual

`/admin` — tabs across the top. What each does and the procedures you'll actually run:

### 👥 Users
- Roster with search; create users manually (name/email/password/age group/role).
- **Grant roles:** toggle `gameset` (content portal) or `admin` per user.
- **Grant a subscription manually** (e.g. a partner, a tester): pick user → grant → the
  account is premium until the chosen end date, no payment involved.
- Player deep-dive lives in **Player Search** (`/players`) — progress, portfolio,
  credit history per player; use it for support questions ("my salary disappeared").

### 💳 Plans & Pricing
- **Individual plans:** create/edit monthly plans (name, price, months). These are what
  the subscribe page sells via M-Pesa.
- **School plans:** seat-package products for institutions — name, price, seats, **max
  classes** (how many `SchoolClass` cohorts the school can create — see 🏫 Schools below),
  and duration in months.
- **Coupons:** code, percent-or-fixed value, max redemptions, plan scope, expiry — for
  campaigns and classroom promos.
- **🚧 Free Plan Gates panel** — the monetization control room; see §6.3 for
  field-by-field.

### 📋 Subscriptions & 💰 Payments
Read-only ledgers: every subscription with status/dates, every M-Pesa transaction with
result codes. First stop for "I paid but I'm not premium" tickets (check the
transaction's status + reference).

### 🏫 Schools
School subscriptions with seat counts and member lists; add/remove members. A school
member plays with full features while their membership is active.

- **Classes** — each school can split its roster into `SchoolClass` cohorts (e.g. "Form 2
  Blue"), one teacher per class, up to the school plan's **max classes** limit. A class
  scopes two things: the roster a **Class Challenge** auto-enrolls (below), and which
  students a given teacher's dashboard shows.
- **Teacher portal** (`/school/teacher`, own login — not `/admin`) — school owners can
  invite additional teacher accounts; each teacher manages their own class(es): roster,
  Class Challenges, and the **teacher evaluation dashboard** (per-student progress,
  engagement and at-risk flags, so a teacher can spot a student who's stalled without
  reading every profile individually).
- **Class Challenges & Chama Battles** — a teacher can launch a broadcast Challenge (see
  `GAMESET_GUIDE.md` §12d) scoped to their class, auto-enrolling the active roster
  (assigned, not opt-in — it's the teacher's own class). **Chama Battles** are the
  chama-vs-chama equivalent for the general playerbase: two chamas face off, ranked by
  average member progress on the challenge's metric rather than any single member's
  score, so a small dedicated chama can beat a large, less-engaged one.

### 📊 Analytics
`/admin/analytics` — an operational dashboard (Chart.js), read-only: active/live player
counts, signup and retention trends, economy health (money in circulation, top
assets/jobs), and content engagement (quests/courses completed). Deliberately answers
"what's happening inside the game," not "who's visiting the marketing site" — pair with
PostHog/GA4 (below) for the latter.

### 📣 Broadcast
A composer for one-shot announcements, sent as both an in-game notification and a push
(where the recipient has push enabled): **title**, **body**, and an **audience** picker —
everyone, one age group, one school's active roster, free-tier players only (a
conversion nudge), or a single player by email (support follow-ups). Delivery still
respects each recipient's own quiet-hours/daily-cap/category preferences, so "sent
successfully" does not guarantee every recipient's device buzzed — that's expected, not a
bug.

### 🌪 Crises
Same scheduler the gameset portal has (presets, quick "warn now / hit in 48h" buttons,
status badges Scheduled → Warning sent → Active → Completed, cancel). Full field
reference and a worked classroom example: `GAMESET_GUIDE.md` §11.

### ✅ Quests Pending
Approval queue for manual (trigger-less) quest submissions. Approve → rewards granted.

### ⚙️ Settings
Grouped forms; each saves independently:
- **SMTP** — outgoing mail (host/port/credentials/from) + *send test* button.
- **M-Pesa** — environment (sandbox/live), consumer key/secret, shortcode, passkey,
  account reference. Fill before selling subscriptions.
- **Game Clock** — *real hours per game week* (§2). The most powerful knob in the game.
- **AI (pesAI/Mama Pesa)** — OpenRouter key, model, per-day free limit, agent icon +
  *test* button.
- **General** — `free_for_all` switch: everyone premium (school events, demos, launch
  weeks). Overrides every gate while on.
- **🔔 Push Notifications (VAPID)** — one-time setup for the web-push/PWA pipeline: hit
  *Generate VAPID Keys* once (this also invalidates every existing player push
  subscription — expected the first time, disruptive if repeated later) and set the
  *subject* (a `mailto:` contact address required by the push spec). *Send Test Push*
  fires a real push to **your own** logged-in device/browser, bypassing quiet hours and
  the daily cap (it's a diagnostic, not a real send) — it requires you to have already
  enabled push in your own Profile → Notification Settings first. Real pushes to players
  respect quiet hours (9:30pm–6am), a daily cap (4/day), and each player's per-category
  preferences — see the Broadcast composer above for actually sending one.
- **📈 Trackers** — third-party analytics wiring: **PostHog** key + host (self-serve
  product analytics — funnels, session replay), plus GA4 and Microsoft Clarity IDs if
  used. These IDs only get injected into pages once saved here; nothing is hardcoded into
  the frontend. See `POSTHOG-GUIDE.md` for what's already instrumented (Pesa Trail funnel
  events) and how to add more.
- **Hustle Tips** — also editable from the GameSet hub.

### 🛠 Artisan — the deploy console (no SSH needed)
| Button | What it runs | When |
|---|---|---|
| Run Migrations | `migrate --force` | After every code deploy that includes migrations |
| Migration Status | `migrate:status` | Verify what's applied |
| Clear All Caches | `optimize:clear` | After ANY deploy; first move on weird behaviour |
| Optimize (Cache All) | config/route/view cache | After clearing, on production |
| Storage Link / Fix Image URLs | housekeeping | Once per server / when uploads 404 |
| Process Crises Now | `game:process-crises` | Testing a crisis without waiting |
| Seeders (Level 1-3 Content, Life Events, Fun World, Market Events, Missions, NPCs…) | `db:seed --class=…` | Loading/refreshing content packs |
| **SEED ALL** | full `DatabaseSeeder` | Fresh installs — ⚠️ some seeders reset content tables (never player data) — confirm prompt explains |
Output appears in the terminal box below the buttons; non-zero exit codes highlight red.

---

## 5. Day-1 Setup & Weekly Routine

### Fresh-install checklist (in order)
1. Artisan tab → **Run Migrations** → **SEED ALL** → **Clear All Caches** → Optimize.
2. Settings → **Game Clock**: choose pace (24 real h/game week is the balanced default).
3. Settings → **SMTP**: configure + send test (password resets depend on it).
4. Settings → **M-Pesa**: credentials (sandbox first; switch env to live at launch).
5. Plans & Pricing: create at least one individual plan; review **Free Plan Gates**
   (defaults are sane) and **trial days**.
6. Users: create your gameset users; hand them `GAMESET_GUIDE.md`.
7. Make a personal **non-admin test account** and play the first hour like a student.

### Weekly operating routine (~15 minutes)
- Payments tab: scan failed M-Pesa transactions.
- Quests Pending: clear the manual approval queue.
- Crises: schedule next week's economic event (shared storms drive retention & lessons).
- Player Search: spot-check two random new players' timelines for anything odd.
- After any deploy: Clear All Caches; smoke-test dashboard → world → Life HQ → pay one
  bill → check in at work.

---

## 6. 💰 How the Game Makes Money

### 6.1 Revenue streams
1. **Individual subscriptions** — monthly plans via M-Pesa STK push. Plans/prices are DB
   rows; nothing is hardcoded.
2. **School subscriptions** — seat packages managed by a teacher account. The B2B/NGO
   channel: one champion teacher = a classroom of seats.
3. **Coupons** — discount instruments for campaigns and partners.

### 6.2 The conversion funnel (deliberate design)
1. **Taste first:** every account starts fully premium for `trial_days` (default 7).
2. **The wall is pace, not content:** free play keeps the entire learning loop; limits
   apply to scale (asset/deal/savings counts) and speed (catch-up days per visit).
3. **Friction sells politely:** every gate denial explains the benefit + links to
   subscribe.
4. **Periodic nudges:** free players get one rotating benefit-focused notification every
   `upsell_nag_days` real days (toggleable).
5. **Catch-up envy:** WYWA shows free players how many game days Premium would have
   simulated.

### 6.3 The Gates panel (Plans & Pricing → 🚧 Free Plan Gates)
Every value live-edits the `plan_limits` Setting read by `PlanGate` on each check
(0 = unlimited):

| Field | Free default | Meaning |
|---|---|---|
| Max assets owned | 3 | Distinct active assets |
| Max active deals | 1 | Pending investments at once |
| Max savings goals | 1 | Open schemes |
| Max loans | 1 | Active loans |
| Catch-up game days/visit | 7 | The strongest pace lever — never set below ~5 |
| pesAI questions/day | 3 | AI coach usage |
| Fun World per game month | 2 | Leisure actions |
| Forum topics min level | 5 | Anti-spam gate (replying is always free) |
| Can create Chama | No | Joining is always free |
| **Send money to friends** | Locked | P2P cash gifts between friends — borrowing/lending (Friend Loans) stays free either way; this gate only covers no-strings-attached transfers |
| **Pesa Trail games/day** | 3 | Arcade sessions started per real day (0 = unlimited); inviting others to a match counts the same as starting one |
| Trial (real days) | 7 | Full-premium window for new accounts |
| Nudges on/off + cadence | On / 3 days | Subscription reminders |
| Max quests started/day | 0 | Game-wide pacing (also in GameSet Game Rules) |

### 6.4 Levers worth experimenting with
Shorter trial (7→3) vs conversion; catch-up ticks 7→5 for stronger pace pressure;
`free_for_all` during school events then coupon the attendees; seasonal school-plan
pricing aligned to terms.

---

## 7. 🔐 Security

**Authentication & sessions** — Laravel Breeze: bcrypt password hashing, HttpOnly
session cookies, email password reset. Enforce HTTPS at the host.

**Authorization — three layers**
1. Route middleware: `auth` (players), `gameset` (portal; `is_gameset` or `is_admin`),
   `admin` (panel).
2. Object-level ownership checks in controllers — every bill/asset/loan/savings action
   verifies `user_id === auth()->id()` (403 otherwise).
3. All freemium limits enforced **server-side** in `PlanGate` — client-side hiding is
   never the only guard.

**Input & injection** — every write endpoint uses `$request->validate()` with
whitelisted fields/enums; all DB access via Eloquent/query builder (parameterized —
SQLi-safe); Blade `{{ }}` output escaping (XSS-safe); CSRF token on every POST.

**The Artisan runner is a whitelist** — UI keys map to a fixed command+argument list;
anything else is rejected with 422. It cannot execute arbitrary commands.

**Payments** — M-Pesa credentials in Settings; every transaction logged; callbacks
validate reference before crediting.

**Operational must-dos**
1. `APP_DEBUG=false` in production — debug pages leak configuration.
2. Remove/rotate the legacy `GET /run/{secret}` URL runner (`ARTISAN_SECRET`) — the
   Artisan tab replaced it.
3. Nightly automated DB backup (cPanel/`mysqldump`) + weekly off-site copy. Test a
   restore once a term.
4. Rate-limit login/payment endpoints (`throttle` middleware) if abuse appears.
5. Never commit `.env`; restrict hosting-panel access to named individuals.

**Data & minors** — stored: name, email, DOB-derived age band, gameplay stats. No real
financial data, no location, no advertising, no data sale (non-profit). Under-13s:
school-managed accounts recommended. No player-to-player private messaging exists.

---

## 8. 📈 Scaling & Capacity

### 8.1 Why the game is cheap to run
Login-driven ticks mean **zero background load** — an idle player costs nothing. Views/
config/routes cached; hot settings memoized; 13 purpose-built DB indexes from the
performance pass; hero/world images pre-compressed to WebP.

### 8.2 Today: shared cPanel hosting — honest numbers
Typical shared host: 10–25 PHP workers, shared MySQL, 1–2 GB RAM slice. Heaviest request
= login catch-up (~50–200 ms transaction).

| Metric | Comfortable | Strained |
|---|---|---|
| Simultaneous players | **100–200** | 300+ (queueing) |
| Daily actives | **1,000–3,000** | 5,000+ |
| Registered accounts | **10,000+** (storage-bound) | — |

Early warning signs: >3s page loads at peak, MySQL connection errors, cPanel CPU
throttle notices. Squeeze more: run *Optimize* after deploys; prune `game_notifications`
older than ~90 days (the fastest-growing table); keep images WebP.

### 8.3 Next step: VPS (~KES 3,000–8,000/month)
**Trigger:** sustained 200+ concurrent, 3,000+ DAU, or revenue that justifies it.
**Spec:** 4 vCPU / 8 GB / NVMe (Hetzner, DigitalOcean, Contabo, or Kenyan provider for
latency). **Stack:** Nginx + tuned PHP-FPM, MySQL 8 (sized buffer pool), **Redis**
(cache/sessions/queues), real cron, opcache, HTTP/2, Cloudflare free tier in front.

| Stage | Concurrent | DAU |
|---|---|---|
| Tuned 4c/8GB VPS | 500–1,000 | 10,000–30,000 |
| + Laravel Octane | 2–3× above | — |

Migration is low-drama: rsync + `mysqldump` restore + DNS switch; the app has no
host-specific dependencies. Budget half a day.

### 8.4 National scale
Split web/DB → second app server behind a load balancer (sessions already Redis-ready)
→ managed MySQL + read replica → object storage/CDN for uploads → queue workers for
notification fan-out (a crisis to 100k players becomes a queued job). The tick
architecture shards naturally — each player's simulation is independent; there is no
global lock to outgrow.

---

## 9. Deploys, Golden Rules & Troubleshooting

### Deploy checklist (shared hosting)
1. Upload changed files — **including `public/build/` when JS/CSS changed** (built with
   `npm run build`).
2. Artisan tab: **Run Migrations** → **Clear All Caches** → *(content packs?)* seeders.
3. Smoke-test: dashboard, world, Life HQ, pay a bill, report to work.

### Golden rules learned in production (violate at your peril)
- **Carbon 3:** never `now()->diffInX($past)` — signed diffs once froze the entire game
  clock. Use timestamp subtraction.
- **Blade:** never inline `@php(...)`; never span `@json(...)` across lines — both
  compile silently into broken PHP. `view:cache` succeeding proves nothing; verify by
  `php -l` on `storage/framework/views/*`.
- **Tailwind:** arbitrary classes (`z-[9990]`) are dead until `npm run build` runs — use
  inline styles for critical layout.
- **MySQL:** never non-nullable `timestamp()` in migrations — strict-mode hosts then
  reject every future ALTER on that table ("Invalid default value"). Use `dateTime()`.
- **Modals:** overlay-scroll pattern only — fixed full-screen overlay scrolls, box uses
  `margin:auto`, inline `z-index ≥ 9990`; bottom navs stay ≤ 9000.

### Troubleshooting quick table

| Symptom | First move |
|---|---|
| ParseError after deploy | Clear All Caches (stale compiled views) |
| "Due in N days" frozen | Game clock setting; verify player's `last_tick_at` advances |
| Migration "Invalid default value" | Legacy TIMESTAMP column — golden rules above |
| "No salary" reports | Pay is PENDING by design — player must Report to Work |
| Salary notification but no job | Legacy phantom (auto-heals on login since Phase 25) |
| Crisis warned but never hit | Check `active_from` passed; run Process Crises Now |
| Player limits wrong | Gates panel values + `free_for_all` switch + trial window |
| Slow at peak | Prune notifications, Optimize, then §8.3 |
| Content edits not visible | Cache clear; then check Active toggles and gates |
