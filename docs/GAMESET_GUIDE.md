# PesaQuest — GameSet Content Manager's Guide

**The complete manual for setting up every part of the gameplay, with worked samples.**

> **Who this is for:** users with the `gameset` (or `admin`) role — the education/content
> team. You shape what players learn; the game engine handles all the math and timing.
> Access the portal at **`/gameset`**.
>
> *This guide replaces the old "Scenario Builder" guide. The node/scenario system has been
> retired — PesaQuest now runs entirely on the living-city systems documented here.*

---

## Table of Contents

1. [The GameSet Hub — Your Home Base](#1-the-gameset-hub)
2. [Golden Rules of PesaQuest Content](#2-golden-rules)
3. [🛒 Marketplace Assets](#3-marketplace-assets) — with sample
4. [🧾 Bills](#4-bills) — with sample
5. [🎓 Courses](#5-courses) — with sample
6. [💼 Jobs (Full-time, Part-time, Freelance)](#6-jobs) — with samples
7. [📜 Quests & Triggers](#7-quests--triggers) — with 3 samples
7b. [🤖 Automation — Contracts, Quest Factory & Blueprints](#7b-automation) — with worked example
8. [📈 Investment Deals](#8-investment-deals) — with sample
9. [🏦 Loan Products](#9-loan-products) — with sample
9b. [🏠 Asset Financing (Estates & Car Yard)](#9b-asset-financing-estates--car-yard) — with worked example
10. [🎲 Life Events](#10-life-events) — with 2 samples
11. [🌪️ Crisis Events](#11-crisis-events) — with worked schedule
12. [🎡 Fun World Activities](#12-fun-world-activities) — with sample
12b. [🐍 Arcade — Pesa Trail](#12b-arcade--pesa-trail) — board layout, settings, XP, flavor text, Rivals Trail
12c. [🌟 Dreams](#12c-dreams) — with sample
12d. [🏆 Champions' Court — Challenges](#12d-champions-court--challenges) — with sample
13. [🏅 Badges](#13-badges)
14. [🌱 Life Chapters](#14-life-chapters)
15. [⚙️ XP Levels, Milestones, Hustle Tips & Career Quiz](#15-config-panels)
16. [📏 Game Rules (pace controls)](#16-game-rules)
17. [✅ Pre-Publish QA Checklist](#17-pre-publish-qa-checklist)
18. [❓ Troubleshooting](#18-troubleshooting)

---

## 1. The GameSet Hub

Open **`/gameset`**. The hub organises everything into three card groups, each card
showing live counts (how many items exist / are active):

- **💰 Economy** — Marketplace Assets, Bills, Investment Deals, Loan Products
- **🎓 Learning & Progress** — Courses, Jobs, Quests, Badges
- **🌍 World & Events** — Life Events, Crisis Events, Fun World

Below the cards sit the **global config panels**: Game Rules, Life Chapters, XP Levels,
Hustle Tips, Journey Milestones, and Career Quiz Questions.

Navigation: the top bar groups the same managers into dropdown menus (hamburger menu on
phones), so you can jump between managers from any page. A crisis snapshot strip at the
top of the hub shows the next scheduled economic event at a glance.

**Two universal conventions:**
- **Active toggle** — every content type has one. Inactive items stay in the database but
  vanish from the game. Draft with inactive, launch by toggling.
- **Ticks = game days.** Wherever a form asks for a duration, it means game days.
  30 ticks = 1 game month. (Admins control how fast game days pass in real time.)

---

## 2. Golden Rules

1. **Every number teaches.** A price, salary or interest rate is a claim about the real
   Kenyan economy. Use believable 2026 values — learners *will* compare with home.
2. **Every item carries its lesson.** Bills have consequence text, courses have financial
   tips, quests have lessons, life events have educational notes. Never leave these
   blank; they are where the teaching happens.
3. **Respect the age bands.** Where a form offers an age group, match obligations to
   life stage. An 8–12 player should never meet a mortgage.
4. **Effort must out-earn luck.** When you tune rewards, keep quest/course/job income
   dominant over windfall events. The economy's balance *is* the values statement.
5. **Small XP numbers.** XP is deliberately scarce (courses ~20–35, simple quests
   ~35–60). Big XP inflates levels and unlocks content too fast.
6. **Preview as a player.** Keep a non-admin test account; check your content on a phone.

---

## 3. Marketplace Assets

**What it is in gameplay:** products players buy to generate passive income — the game's
core "make your money work" system. The engine automatically pays their income, charges
their running costs, decays their condition (~3 points/game month, maintenance repairs
it), and drifts their value over time. Vehicles and property automatically attach
follow-up bills (insurance, fuel, service charge) and can be bought on financing.

**Manager:** GameSet → Marketplace Assets → New Asset.

Key fields:

| Field | Meaning | Guidance |
|---|---|---|
| Name, icon, image, description | What players see | Sell the dream honestly |
| Category | vehicle / property / electronics / business… | Drives contextual bills & market events |
| Price (KES) | Purchase cost | Real-world plausible |
| Monthly income (KES) | Paid every game month | The investment case |
| Monthly cost (KES) | Charged every game month | Never zero for real businesses |
| Luxury flag | Marks zero-income status items | Use it! Luxuries teach by contrast |
| Active | Visible in marketplace | — |

The form shows a live **Payback Preview** — price ÷ (income − cost) — the number players
use to compare investments. Aim for paybacks between ~4 and ~14 game months; under 3 is
free money, over 20 feels pointless.

### 📋 Sample: a well-formed asset

> **Name:** Second-hand Boda Boda · **Icon:** 🛵 · **Category:** vehicle
> **Price:** 85,000 · **Monthly income:** 12,000 · **Monthly cost:** 3,500
> **Description:** "A dependable TVS with 40,000km on the clock. Riders queue at the
> stage every morning — but fuel, repairs and insurance eat into every fare."
> **Payback:** 85,000 ÷ 8,500 = **10 game months**.
> *Because the category is `vehicle`, buying it auto-attaches insurance and fuel bills —
> the ownership lesson arrives by itself.*

---

## 4. Bills

**What it is in gameplay:** recurring obligations on the player's Life HQ board. **Bills
are never auto-paid** — the player must tap *Pay Now* before the due date. Missing it
turns the bill overdue and cuts their credit score every unpaid cycle. Paying early does
not stretch the cycle (due dates are calendar-anchored).

**Manager:** GameSet → Bills → New Bill.

| Field | Meaning | Guidance |
|---|---|---|
| Name, icon | e.g. "Room Rent", 🏠 | — |
| Amount (KES) | Charged per cycle | Scale to the age band's income |
| Frequency (ticks) | Repeat cycle in game days | 30 = monthly, 7 = weekly |
| Age group | Who gets it (or `all`) | Kids get airtime, adults get rent |
| Auto-assign | Given automatically to new/qualifying players | On for life basics |
| Credit impact (pay / miss) | Score change on-time vs missed | Defaults +5 / −15 to −20; essentials should sting more |
| Description / flavor text | The "Why this bill?" panel | Teach, don't just charge |
| Consequence text | Shown when missed | State the real-life parallel |

### 📋 Sample: a well-formed bill

> **Name:** Bedsitter Rent · **Icon:** 🏠 · **Amount:** 6,500 · **Frequency:** 30
> **Age group:** 18–25 · **Auto-assign:** yes · **Credit impact:** +5 / −20
> **Description:** "Your landlord Mama Otis expects rent by the 5th. Housing takes the
> biggest slice of most Kenyan budgets — plan around it first."
> **Consequence:** "Late rent strains the one relationship that keeps a roof over you —
> and landlords talk to each other."

*Asset-triggered bills (insurance for a specific motorbike, service charge for an
apartment) are attached automatically by the engine when a player buys assets in those
categories — you only need to keep sensible category bills in the pool.*

---

## 5. Courses

**What it is in gameplay:** study-then-earn. Courses live at the Opportunity Hub with a
real reading experience: an intro page, an *Enroll* step (fee deducted if the course is
paid), the full lesson content, then *Complete Course* — which awards XP and unlocks any
jobs that require the certificate, with a celebration popup listing them.

**Manager:** GameSet → Courses → New Course.

| Field | Meaning | Guidance |
|---|---|---|
| Title, slug, icon, color | Identity | Slug is referenced by quests — keep it stable |
| Career track | tech / business / finance / creative | Powers "recommended for your path" |
| Intro content | Shown BEFORE enrolling | Sell why this skill earns money |
| Content | The actual lesson | 300–700 words, age-appropriate, concrete Kenyan examples |
| Duration hours, difficulty | Display metadata | — |
| Free / Cost (KES) | Paid courses gate later tracks | Level-1 courses should be free |
| XP reward | Keep low (20–35) | — |
| Outcome / financial tip / jobs intro | Completion screen texts | The tip is the takeaway line |

### 📋 Sample: a well-formed course

> **Title:** M-Pesa Agent Pro · **Slug:** `mpesa-agent-pro` · **Track:** finance · **Free**
> **Intro:** "Every duka with a green sign is a small bank. Learn float management and
> you can run one — or spot when an agent is being conned."
> **Content:** teaches float vs cash, reversal scams, daily reconciliation (with a worked
> float-balancing example).
> **Financial tip:** "Money you can't account for is money you've already lost — reconcile
> daily, even a personal wallet."
> **Unlocks:** the *M-Pesa Float Assistant* job (set on the job side, §6).

---

## 6. Jobs

**What it is in gameplay:** the player's main income. Jobs are gated by course
certificates, pay per game month, and **pay only when the player Reports to Work** —
uncollected paychecks are replaced (lost) at the next payday, so reliability is the
hidden curriculum.

**Manager:** GameSet → Jobs → New Job.

| Field | Meaning |
|---|---|
| Title, employer name, employer logo (emoji) | Identity players see |
| **Career track(s)** | Checkboxes — select one or more (see below) |
| Level (1–3) | Difficulty ladder |
| **Employment Type** | **The big design choice — see below** |
| Monthly salary (KES) / Gig payment (KES, one-off) | The label switches automatically when you pick "Freelance gig" |
| **Gig reopens after (game days)** | Freelance-only — see below |
| Required course (or two) | The certificate gate |
| XP reward | Awarded on hiring (25–60) |

**Career track(s) — multi-select:** a job can now belong to more than one track (e.g. a
bookkeeping role tagged both `finance` and `business`). It shows as "recommended" to a
player whose quiz-assigned track matches *any* one of the ticked tracks. Tick only one for
a narrowly-focused role; tick several for a cross-disciplinary one. Tracks themselves are
still managed in §15's Career Fields & Tracks panel.

**Employment types and their rules (enforced by the engine):**

| Type | Player rule | Design use |
|---|---|---|
| 🏢 Full-time | Their ONLY work — blocks and is blocked by everything else | Highest salaries; the "career" choice |
| ⏰ Part-time | Up to 2 at once | Lower pay each; teaches juggling income |
| ⚡ Freelance gig | ~7 game days of work → paid once → gig closes; the SAME gig reopens for that player after a cooldown (28 game days by default); max 3 gigs running | Lumpy hustle income that cannot be farmed |

**Gig reopens after (game days) — per-job cooldown:** when Employment Type is set to
"Freelance gig", a new field appears letting you override how many game days pass before
that specific gig becomes available to the same player again. Leave it blank to use the
game-wide default (28 days = 4 game weeks). Use this to make quick, low-paying gigs
reopen faster (e.g. 7 days for a small odd job) and prestigious, high-paying gigs stay
scarce for longer (e.g. 60 days for a one-of-a-kind commission) — it only affects how
often *that one gig* can be repeated, not the 7-day delivery time before it pays out.

### 📋 Sample A: full-time job

> **Title:** Bookkeeping Clerk · **Employer:** Jenga Hardware 🧱 · **Track:** finance
> **Level 2 · Full-time · 22,000/mo** · Requires: `bookkeeping-basics`
> **Description:** "Record every sale, reconcile cash daily, flag missing stock. The
> owner sleeps because your ledger is honest."

### 📋 Sample B: freelance gig

> **Title:** Event Photo Assistant · **Employer:** Lulu Studios 📸 · **Track:** creative
> **Level 3 · Freelance gig · 6,000 one-off** · Requires: `photography-basics`
> **Description:** "Second shooter for one wedding weekend — deliver the shots, get paid
> per gig. Reliable freelancers get called back."
> *Player experience: take gig → 7 game days pass → "Gig delivered, Ksh 6,000 ready —
> report to work" → collect → gig reopens after 28 game days.*

**Salary sizing tip:** since gigs pay once for ~a week of game time, price them at
roughly **a quarter to a third** of what a monthly job in that track pays.

---

## 7. Quests & Triggers

**What it is in gameplay:** the lesson plans. A quest is a goal the game *detects
automatically* — the moment the player genuinely does the thing, the quest completes and
celebrates with XP (and optionally KES). Quests are level-gated and subject to an
admin-set daily start cap (see §16), which paces learning across days.

**Manager:** GameSet → Quests → New Quest.

Anatomy of a quest:
- **Title, icon, description** — what the player is asked to do.
- **Instructions** — the how-to shown in the quest popup.
- **Lesson** — displayed at completion; the takeaway.
- **XP / KES rewards** — keep XP low (35–60 simple, 130–260 multi-step); reserve KES for
  chains that genuinely take effort.
- **Level required** — gate by readiness.
- **Triggers + completion mode** — the detection logic (below). A quest with NO triggers
  becomes a *manual* quest that a player submits and an admin approves.

### Trigger type reference

| Trigger type | Value | Fires when |
|---|---|---|
| `buy_item_slug` | asset slug | Player buys that exact asset |
| `buy_item_category` | category | Player buys any asset in category |
| `open_savings` | — | Player opens a savings goal |
| `deposit_savings` / `reach_savings` | minimum KES | Savings balance reaches the amount |
| `reach_balance` | minimum KES | Wallet reaches the amount |
| `reach_level` | level number | Player reaches the level |
| `take_course` | course slug | Player completes that course |
| `get_job` | job id | Player is hired for that job |
| `earn_badge` | badge slug | Player earns that badge |
| `join_chama` | — | Player joins a chama |

**Completion modes** (for multi-trigger quests): **ALL** = every step must fire (a
checklist — step progress shows in the quest panel); **ANY** = first one wins (offer
alternative routes).

### 📋 Sample A: single-trigger quest

> **Title:** Saver's Discipline · **Icon:** 🏦 · **Level 2** · **XP 50**
> **Description:** "Grow your bank savings to Ksh 3,000."
> **Trigger:** `reach_savings` → value `3000` → label "Save Ksh 3,000 at the bank"
> **Lesson:** "The first 3,000 is the hardest. After that, saving is a habit — and
> habits compound faster than interest."

### 📋 Sample B: multi-trigger chain (mode: ALL)

> **Title:** The Hustler's Toolkit · **Icon:** 🗣️ · **Level 2** · **XP 130**
> **Description:** "Learn to sell, then get paid to sell."
> **Trigger 1:** `take_course` → `sales-101` → "Complete Sales 101: The Art of the Close"
> **Trigger 2:** `get_job` → *(pick the Sales Rep job)* → "Get hired as an Airtime & Data Sales Rep"
> **Lesson:** "Skill → job → commission. Income you can grow with effort beats income
> that's capped."
> *Player sees a 2-step checklist; each step ticks the moment it truly happens.*

### 📋 Sample C: manual quest

> **Title:** Teach One Person · **Icon:** 🤝 · **XP 40** · **No triggers**
> **Instructions:** "Explain the 50/30/20 rule to someone at home, then submit this
> quest. Tell us who you taught in the notes!"
> *Appears with a submit button; completions queue under Admin → Quests Pending for
> approval — useful for offline/classroom assignments.*

---

## 7b. Automation

**What it is in gameplay:** three self-driving systems that generate real, drafted game
content from what already exists in your economy — so a small team can keep a big living
city stocked with quests and NPC side-tasks without hand-writing every single one.

**Manager:** GameSet → Quests → 🤖 Automation (also linked from the Quests list). The page
itself has a **"📖 New here?"** disclosure panel at the top — click it for a live,
always-current walkthrough of exactly what's on the page; the summary below is the
field-by-field reference.

### 📜 Contract Rules (personal NPC side-tasks)
Every player automatically holds a small number of live "contracts" — short NPC-voiced
side-tasks generated from *their own* situation (an overdue bill, low mood, uncollected
pay). Nothing to approve; they're ephemeral and re-top-up automatically. You tune the
**recipe**, not individual contracts:

| Field | Meaning | Guidance |
|---|---|---|
| Age group / Level band | Which players this rule applies to | Narrowest matching rule wins; a specific band beats `all` |
| Objectives per contract | How many goals bundle into one contract (3–5) | More objectives = a longer, more demanding contract |
| Completion mode | `all` (every objective) or `any N` (first N of the set) | `any N` is more forgiving — good for younger bands |
| Duration (game days) | How long a contract stays open before expiring unmet | Match to your game clock pace (§2 in ADMIN-GUIDE) |
| Held count | How many contracts a player carries at once | 2–3 keeps it a steady trickle, not a wall of tasks |
| XP / KES reward | Payout on completion | Keep modest — contracts are a side activity, not the main quest economy |

### 🏭 Quest Factory (reactive — one switch)
When **Factory on** is checked, every course or job you create in GameSet auto-drafts its
own matching quest ("Study X", "Get hired at Y") using the NPC voice pool — you never touch
this after flipping it on. **Auto-publish** skips the review queue entirely (only turn this
on once you trust the copy it writes). **⛰️ Quest Gate** is a related but separate switch on
the same card: when on, players cannot level past a level that still has unfinished quests —
XP keeps banking up and applies the instant they clear the backlog; nobody is ever demoted.

### 🎲 The Mixers (one-button batch generation)
Two "press and walk away" composers, both always land as **drafts for review**, never
published directly:
- **Quest Mixer** — fills a level range up to a target quest-count per level, inventing
  combos from your real courses/jobs/assets, with money values sized off your own job
  salaries and a difficulty mix (gentle/balanced/spicy) that sets the XP curve. Idempotent —
  pressing again only tops up levels still below target.
- **Life Events Mixer** — composes a batch of new life events (windfalls, shocks, market
  swings, credit moves, career/story beats) across every age group at once. They land
  switched **off**; approve each one in §10 Life Events.

### 🧬 Quest Blueprints (the ladder printing press)
A blueprint is a **recipe**, not a single quest: one or more triggers (save Ksh X, take any
course + deposit Y, buy an asset in category Z…) repeated up a level ladder with growing
values and rewards, so a themed quest line (e.g. "Saver's Staircase") never has a gap
between levels — a nightly sweep prints any missing rung as a draft automatically.

| Field | Meaning | Guidance |
|---|---|---|
| Name / icon | Identity for the whole ladder | e.g. "Saver's Staircase" 🏦 |
| Trigger recipe | One or more trigger types (same catalogue as §7) with a base value | The "shape" of every rung — only the numbers grow per level |
| Level range (from–to) | Which rungs the sweep should keep filled | e.g. levels 1→9 |
| Value growth | How the trigger's target value scales per rung | e.g. +50% target per level up the ladder |
| Reward growth | How XP/KES scale per rung | Keep pace with the value growth so later rungs still feel worth it |
| **Chain** 🔗 | Makes each rung require the previous rung's quest as a prerequisite | Turns a set of independent quests into a real progression story |
| Active | Pauses the sweep for this blueprint without deleting it | Use to retire a ladder without losing the recipe |

### 📋 Worked example

> **Blueprint:** "Saver's Staircase" 🏦 · **Trigger:** `reach_savings` starting at 1,000 ·
> **Levels:** 1→9 · **Value growth:** +50%/level · **Chain:** on
> *Rung 1 (Lv 1): save Ksh 1,000 → Rung 2 (Lv 2, requires Rung 1): save Ksh 1,500 → … →
> Rung 9 (Lv 9): save Ksh 38,000-ish.* Run the nightly sweep (or trigger it from Admin →
> Artisan) and every missing rung prints as a draft; publish the ladder once, and it stays
> self-healing forever — add a new max level later and the next sweep prints the new rungs.

*Generated output from all three systems (Factory drafts, Mixer output, Blueprint rungs)
lands in one place: GameSet → Quests → 🎲 Generated Quests — filter by source, publish or
discard individually or in bulk.*

---

## 8. Investment Deals

**What it is in gameplay:** Equity Square offers that lock money until a maturity date
with a stated risk level. On maturity, the engine resolves the outcome — good deals pay
the stated return; risky ones can disappoint. Free players hold one active deal at a time
(admin-tunable), which itself teaches choosing carefully.

**Manager:** GameSet → Investment Deals → New Deal. Set the name/story, minimum stake,
expected return, risk tier and maturity (in game days).

### 📋 Sample

> **Name:** Mama Mboga Supply Contract · **Risk:** Medium · **Maturity:** 60 game days
> **Stake:** 10,000 · **Expected return:** +18%
> **Story:** "A vegetable aggregator needs upfront capital for the school-term supply
> contract. Solid client — but one drought away from a thin season."
> *Design note: pair every high-return deal with real failure odds; a deals board where
> everything pays is a slot machine with extra steps.*

---

## 9. Loan Products

**What it is in gameplay:** bank credit lines. Installments auto-deduct monthly (a
standing order — the one deliberate exception to "everything is manual"), interest
compounds, early repayment saves money, and behaviour writes straight into the credit
score (+5 per on-time installment, −20 missed, +30 cleared, −100 default).

**Manager:** GameSet → Loan Products. Define name, icon, annual interest rate, limits
and term. Keep 2–3 clearly differentiated products.

### 📋 Sample

> **Name:** Hustler Boost Loan · **Icon:** ⚡ · **Rate:** 14% p.a. · **Max:** 50,000 ·
> **Term:** 6 game months
> **Pitch:** "Quick capital for a working asset. Borrow for things that EARN — if the
> asset's monthly profit beats the installment, debt is a tool; if not, it's a trap."

*Note: this "Loan Products" manager is for the Bank's general-purpose loans — the ones a
player browses and applies for directly. Vehicle and property financing (Car Yard and
Estates) is a separate, asset-triggered system with its own config panel — see §9b below.*

---

## 9b. Asset Financing (Estates & Car Yard)

**What it is in gameplay:** the "buy now, pay monthly" path for big-ticket purchases.
Instead of a player needing the full cash price of a car or a plot up front, they pay a
**deposit** and the rest becomes an automatic loan — installments auto-deduct every game
month via the same engine that runs Loan Products (`LifeSimulator::settleLoans`), interest
compounds, and payment behaviour hits the credit score exactly like a bank loan does
(+5 per on-time installment, −20 missed, +30 fully cleared, −100 defaulted). The whole
point is to make the true cost of credit visible: the notification shown at purchase time
tells the player the deposit, the monthly installment, and — crucially — the **total cost
with interest vs. the plain cash price**, so they can see exactly how much "buying on
credit" cost them.

This only applies to assets whose **Category** (set in GameSet → Marketplace Assets) is
exactly `vehicle` (offered in the Car Yard district) or `property` (offered in the
Estates district). There is no per-asset "allow financing" toggle — every vehicle/property
asset is automatically financeable at the SAME terms; you tune the terms per category, not
per item.

**Manager:** GameSet Hub → scroll to **🏦 Asset Financing (Estates & Car Yard)**, right
below Career Quiz Questions. It's a config panel, not a list — one card for `🚗 Vehicle
Financing` and one for `🏠 Property Mortgage`.

| Field | Meaning | How it affects the player |
|---|---|---|
| **Deposit (% of price)** | Share of the asset's listed price the player must pay in cash up front | Higher deposit = smaller loan = lower monthly installment, but the player needs more cash saved before they can buy at all. Lower deposit = easier to get in, but a bigger loan (and more total interest) over the term. |
| **Annual Interest Rate (%)** | The yearly rate charged on whatever's still owed | Compounds every game month on the *outstanding balance* — the classic "cost of credit" lesson. A higher rate makes the installment bigger and the total interest paid over the loan much bigger; a small rate difference compounds into a large gap over a 24–36 month term. |
| **Loan Term (game months)** | How many game months the player has to pay it off | A longer term spreads the same principal into smaller monthly installments (easier to afford day-to-day) but the player pays interest for longer, so the *total* interest cost is higher. A shorter term means bigger installments but less total interest. |

**Defaults** (used until you save custom values): Vehicle — 20% deposit, 14% p.a., 24
game months. Property — 10% deposit, 12% p.a., 36 game months. Property gets a lower
deposit and rate because it's framed as the "long game" asset class, while vehicles (which
depreciate) intentionally carry a steeper rate to discourage over-financing a wasting
asset — keep that contrast if you retune the numbers.

**What is NOT configurable here:** which specific assets are financeable (that's the
`vehicle`/`property` category, set per-asset in Marketplace Assets), and the exact listings
shown in the Car Yard/Estates districts (those are curated separately in the World
content) — this panel only sets the deposit/rate/term math applied whenever *any*
vehicle or property asset is bought on financing.

**Important — rate changes are NOT retroactive.** Each player's loan snapshots its
interest rate and monthly installment the moment they finance the asset. Changing the
rate here only affects purchases made *after* you save — it never changes what an
existing player already owes. This is deliberate (real mortgages/car loans don't
retroactively reprice either), but it means if you're correcting a mistake, existing
affected players keep the old terms.

### 📋 Worked example

> Setting: **Vehicle** — 20% deposit, 14% p.a., 24 months.
> Asset: a car listed at **KES 800,000**.
> - Deposit due immediately: **KES 160,000** (20% of 800,000)
> - Loan principal: **KES 640,000** (the remaining 80%)
> - Monthly installment: calculated automatically from the principal, rate and term
>   (an amortized payment, like a real car loan) — shown to the player before they see the
>   final total.
> - Over 24 months the player pays back more than the 640,000 principal because of the
>   14% interest — the exact "total cost with interest" and "extra vs. cash price" figures
>   are shown in the purchase notification. That gap IS the lesson.
>
> *Design note: if you want financing to feel like a meaningfully worse deal than saving up
> cash (the intended lesson), keep the rate high enough that the "extra paid" figure is
> impossible to miss — anything under ~8% p.a. stops feeling like a real cost to players.*

---

## 10. Life Events

**What it is in gameplay:** randomized life happening to players as game days pass — the
"nobody's budget survives contact with reality" system. Each rolls with a probability per
game day, filtered by the player's life chapter and (optionally) by asset ownership.
Fired events hit the wallet/credit/assets, appear in the While-You-Were-Away summary, and
are recorded forever on the player's Life Story timeline.

**Manager:** GameSet → Life Events → New Event.

| Field | Meaning | Guidance |
|---|---|---|
| Slug, title, icon | Identity | Slug must be unique |
| **Chapter** | Despite the name, this scopes by **age group** (`8-12`, `13-17`, `18-25`, `26+`) or `all` — NOT the six net-worth Life Chapters from §14. An 8–12 event should never be a mortgage story. | Match hardship to age band |
| Asset category (optional) | Only fires for owners of that category (`vehicle`, `property`, `business`, `investment`, `gadget`) | Ownership consequences |
| Effect type + effect data | What happens (below) | — |
| Probability | Chance per game day (e.g. 0.010 = 1%) | 0.005–0.02 typical; players see ~⅓ of your pool monthly |
| Flavor text | A quoted voice line | Kenyan, human, short |
| Educational note | THE LESSON | Never skip |
| Positive flag | Colours the event green/red | — |

**Effect types and their exact data shape** (the form gives you structured sub-fields per
type, not raw JSON — but this is what actually gets stored):
- `balance_delta` — wallet change, picked randomly within a range. Data:
  `{"balance_min": -3000, "balance_max": -800}` (both sides negative = pure loss; both
  positive = pure windfall; straddling zero = a mixed-luck event).
- `market_event` — one asset category's value shifts by a percentage. Data:
  `{"market_categories": [{"category": "investment", "pct": -8}]}` (category is one of
  the same five asset categories: vehicle/property/business/investment/gadget).
- `credit_adjust` — credit score change, picked randomly within a range. Data:
  `{"credit_min": -15, "credit_max": -5}`.
- `bill_assign` — attaches an existing bill (by slug) to the player, as if they'd taken on
  a new obligation. Data: `{"bill_slug": "car-insurance"}` — the slug must match a real
  bill in GameSet → Bills.
- `career_change` — a one-off income swing framed as a job/career event (a raise, a pay
  cut, a bonus). Data: `{"income_delta_min": 2000, "income_delta_max": 6000}`.

### 📋 Sample A: negative event

> **Slug:** `matatu-fare-hike` · **Chapter:** all · **Icon:** 🚌 · **Probability:** 0.012
> **Effect:** `balance_delta` → `{"balance_min": -900, "balance_max": -300}`
> **Title:** "Matatu Fares Doubled Overnight" · **Positive:** no
> **Flavor:** *"Beba beba! Mafuta imepanda, si mimi."* — every conductor this week
> **Educational note:** "Transport is a budget's most volatile line. Keep a small buffer
> so a fare hike never touches your savings."

### 📋 Sample B: positive, age-scoped event

> **Slug:** `chama-dividend` · **Chapter:** 18-25 · **Icon:** 🎉 · **Probability:** 0.008
> **Effect:** `balance_delta` → `{"balance_min": 3000, "balance_max": 5000}` · **Positive:** yes
> **Flavor:** *"Mwisho wa mwaka, mgao!"* — your chama treasurer, beaming
> **Educational note:** "Group saving pays twice: the dividend, and the discipline you
> practised all year."

### 📋 Sample C: bill_assign event

> **Slug:** `new-phone-plan` · **Chapter:** all · **Icon:** 📱 · **Probability:** 0.006
> **Effect:** `bill_assign` → `{"bill_slug": "airtime-data"}` · **Positive:** no
> **Educational note:** "A new habit is a new recurring cost — check what it does to your
> budget before you commit to it, not after."

---

## 11. Crisis Events

**What it is in gameplay:** scheduled, server-wide economic shocks — the shared-storm
system. Unlike random life events, crises hit *every player at once*, with an advance
warning, and are scheduled by you. They appear in notifications AND on every affected
player's timeline with a lesson.

**Manager:** GameSet → Crisis Events (admins have the same tool in the admin panel).

**The three moments of a crisis:**
1. **📢 Warning time** — every player gets a "crisis incoming" notification. Classic: 48
   hours before the hit (one-click presets exist for "warn now, hit in 48h/24h").
2. **💥 Hit time** — the effect applies once to everyone when the window opens.
3. **🕊️ End time** — closes the window. *Salary-cut crises keep reducing every salary
   collected during the whole window* — the one continuous effect.

**Effect types:** Investment Drop (pending deals lose value) · Asset Crash (owned asset
values fall) · Balance Drain (wallets lose a %) · Salary Cut (pay reduced while active).
Severity is a percentage: 5–10% mild, 20%+ painful.

**Presets** (one click fills the form): NSE Market Crash, Property Market Slump, Drought
Food Inflation, Fuel Price Shock, Economic Recession, Currency Devaluation.

### 📋 Worked example: running a classroom crisis week

> **Monday:** open Crisis Events, click preset **"Drought Food Inflation"** (Balance
> Drain 10%), click **"⏱️ Classic: warn now, hit in 48h"**, Schedule.
> **Monday–Wednesday:** every player carries the warning — the prepared ones stock
> savings (savings balances are NOT drained — only wallets; the protection is the lesson).
> **Wednesday:** the drain hits; each player's timeline gains a 🌪️ CRISIS entry showing
> their personal loss + the emergency-fund lesson.
> **Thursday class discussion:** "Who lost the least? What did they do differently?"

**Status flow** you'll see in the list: 🗓️ Scheduled → 📢 Warning sent → 🔥 Active now →
✅ Completed. Cancel anything unhit with one click. Admins can also force immediate
processing via Admin → Artisan → *Process Crises Now* (useful for testing).

---

## 12. Fun World Activities

**What it is in gameplay:** the leisure economy. Mood decays 1 point per game day; below
40, work income drops 10%. Fun World activities trade money for mood — teaching that
recreation is a budget line with a return.

**Manager:** GameSet → Fun World → New Activity.

| Field | Guidance |
|---|---|
| Name, icon, description | Make it vivid and local |
| Price (KES) | Spread your catalogue across price points (200 → 5,000) |
| Mood boost | Roughly proportional to price (5–25) |
| XP reward | Small (10–35) |

### 📋 Sample

> **Name:** Karura Forest Walk · **Icon:** 🌳 · **Price:** 300 · **Mood:** +6 · **XP:** 12
> **Description:** "Nature walk and picnic in the forest. Cheap therapy."
> *Catalogue design tip: always keep at least one near-free option — teaching that mood
> management doesn't require money is itself a lesson.*

---

## 12b. Arcade — Pesa Trail

**What it is in gameplay:** a Snakes & Ladders-style board game reachable from the Fun World
district. A player stakes savings, rolls a die around an 81-tile board, and either reaches
the finish (bonus payout), busts out (pot falls below the floor), or banks it early. It can
be played solo against "Robo" (a bot), with friends in a normal race, or head-to-head for
real stakes in **Rivals Trail** (see below). Every setting on this page is genuinely
game-affecting — there's no filler.

**Manager:** GameSet → Arcade.

### Board Layout (drag-to-place)

This is **not** cosmetic — it's the literal x/y position each player's token snaps to when
they land on that tile number. If it's wrong, tokens visually land in the wrong spot even
though the underlying tile number/effect is correct.

- **Desktop Layout tab** — calibrates the board as shown on a normal wide screen.
- **Mobile Landscape Layout tab** — calibrates it **separately** for phones, where the
  board is auto-rotated 90° to fill the screen (a portrait phone playing a landscape board).
  The same physical tile can end up needing a nudged position once rotated, which is why
  this is a genuinely separate calibration pass, not just the desktop one scaled down.
  Leave a tile uncalibrated here and it falls back to its desktop position.
- Drag any numbered square onto where that tile actually sits on the board art, then hit
  **Save** for whichever tab you're on. Saving one tab never touches the other.

### ⚙️ Game Settings

| Field | Guidance |
|---|---|
| Tile count | Fixed (board art is built for exactly this many tiles) — not editable |
| **Floor %** | If a session's pot drops to this % of the entry stake (or below), the session busts and ends immediately. Higher = players bust out faster/more often; lower = more forgiving, longer games |
| **Finish bonus %** | A house-funded top-up added to the pot the instant a player reaches the last tile — the reward for actually finishing rather than cashing out early |
| **XP per play** | A flat XP amount every session earns just for playing, win or lose — added on top of the existing position/outcome-based XP, not a replacement for it |
| **XP per win** | Extra flat XP awarded only on a genuine win (reaching the finish, or winning a Rivals Trail round) |
| **Active** | Unchecking hides Pesa Trail from the arcade lobby entirely (e.g. for maintenance) without deleting any configuration |

### ❓ Mystery Pool (gift / curse)

Mystery tiles (marked `?` on the board) don't have a fixed effect — landing on one rolls a
weighted random outcome from this pool instead. Each row is one possible outcome.

| Field | Guidance |
|---|---|
| Label | The line shown to the player (e.g. "Found cash on the street!") |
| Effect | 🎁 Gift (adds to the pot) or 💀 Curse (removes from the pot) |
| Percent | How much of the *current pot* this outcome moves, not a flat KES amount |
| Weight | Relative odds — a weight-20 outcome is twice as likely as a weight-10 one, regardless of how many other outcomes exist |

### 💬 Flavor Text (reward / expense lessons)

Every plain reward or expense tile shows a short one-line "lesson" alongside the +/- amount
(e.g. *"Consistent saving compounds — small wins add up."*). This pool is what that line is
picked from — **genuinely at random, every landing** — so a returning player doesn't see the
exact same sentence every single time they land on tile #23.

- Add as many lines as you want per category (reward / expense) — a bigger pool means more
  variety before anything repeats.
- Uncheck **On** to keep a line in the list without it being picked (useful for retiring a
  line without losing your work).
- A specific tile's own **Label** field (set in the tile editor below) always overrides this
  pool for that one tile — use the pool for the generic "plain reward/expense tile" case,
  and a tile's own Label for something that should always say the same specific thing (e.g.
  a sponsor-branded tile).

### 💵 Stake Tiers (level → deposit amount)

How much of a player's own wallet balance becomes their starting in-round savings, based on
their level — a level-1 player and a level-15 player shouldn't be risking the same amount.
This tier system is used for **solo play and normal (non-Rivals-Trail) matches only** —
Rivals Trail stakes are chosen by the round's creator instead (see below), not by level.

| Field | Guidance |
|---|---|
| Label | Just a readable name for the tier (e.g. "Starter (Lv 1–5)") |
| Level min / max | The level range this tier applies to |
| Stake amount | KES deducted from the wallet and turned into starting pot when a player in this range starts a game |

### Tile Editor (money effect, movement, sponsor)

The big table further down is the per-tile registry — for each of the 81 tiles: whether it's
a plain tile, a reward/expense tile (and by how much, as a % of the *current pot*), a ladder
bottom or snake head (and which tile it sends the player to), a mystery tile, or a golden
tile (first landing reveals it; every landing after adds +25% of the *original stake* to the
pot, automatically). A tile's own **Label** overrides the random flavor-text pool above for
that tile specifically.

### ⚔️ Rivals Trail (head-to-head wager mode)

A separate mode from everything above — two or more real players each put in the **same
agreed entry amount** (chosen by whoever creates the round, not by level tier) and play for
keeps: the winner takes 60% of what's left in each other player's pot, and a player who
misses 8 turns in a row is withdrawn (keeping 70% of their pot; the other 30% joins a bonus
pool paid to whoever wins). There's currently no separate GameSet panel for this — it reuses
every setting above (floor %, finish bonus %, tile effects, flavor text, XP) exactly as
configured; the only thing unique to a Rivals Trail round is the entry amount, which players
set for themselves per round, not something GameSet configures ahead of time.

---

## 12c. Dreams

**What it is in gameplay:** the "someday" wishlist at **Champions' Court** — expensive,
purely cosmetic trophies (mansions, supercars, a private jet) that a player buys outright
with wallet cash once they've genuinely accumulated it. Dreams **never count toward net
worth** and can never be resold — that's a deliberate anti-loophole decision, so a Dream
can't be used to launder wealth through a chapter-advancement or challenge metric. Owned
Dreams show in the player's Trophy Case on their profile.

**Manager:** GameSet → Dreams → New Dream.

| Field | Meaning | Guidance |
|---|---|---|
| Name, tagline, description | What players see on the catalog card | Sell the aspiration — this is pure motivation content |
| Emoji icon / Image URL | Visual | Image overrides the emoji; use the in-house SVG trophy set (`/img/trophies/...svg`) for a consistent look |
| Price (KES) | Cash cost, paid once from wallet balance | Should feel like a genuine stretch goal — 1,000,000+ is typical |
| Category | property / vehicle / travel / legacy / business / lifestyle | Cosmetic grouping only — drives the catalog's filter chips |
| Min level (optional) | Hides the Dream from players below this level | Leave blank for no gate; use to keep top-tier Dreams aspirational rather than day-one purchases |
| Sort order | Manual ordering within its category | Lower numbers show first |
| Active | Visible in Champions' Court | — |

### 📋 Sample

> **Name:** Karen Hillside Mansion · **Icon:** 🏡 · **Category:** property ·
> **Price:** 45,000,000 · **Min level:** 12
> **Tagline:** "The house that says you made it."
> *Design note: price Dreams meaningfully above what any single asset or job pays out —
> the lesson is patient accumulation across the whole economy, not one lucky windfall.*

---

## 12d. Champions' Court — Challenges

**What it is in gameplay:** fair head-to-head and broadcast competitions between players,
scored on **baseline + delta** (how much you *improved* during the challenge window, not
your raw starting balance) so a rich player and a poor player can compete on equal footing.
Two modes: **duel** (1-on-1 or team, invite → accept, both sides' baselines snapshot the
moment everyone has accepted — a fair start line) and **broadcast** (join-anytime
leaderboard, used for official Pesa City events, school Class Challenges, and chama-vs-chama
battles).

**Manager:** GameSet → Challenges → New Template. You define reusable **templates**; players
(or teachers, or the "official" broadcast tool) spin up actual challenges from a template.

| Field | Meaning | Guidance |
|---|---|---|
| Key | Stable machine identifier | Set once, cannot be changed after creation |
| Name, description, icon/image | What players see | — |
| **Metric** | What progress is measured (net worth, savings balance, wallet balance, XP, courses completed, assets owned, jobs started, gigs completed, chama contributions, friends, forum posts, bills paid, Pesa Trail wins/winnings) | Pick something every eligible player can move — a "courses completed" challenge is unfair to a player who's already finished them all |
| **Style** | `percent` growth (fairest — rewards improvement relative to your own start), `amount` (absolute KES/points gained), or `count` (raw event count) | Percent is the safest default for money metrics; count suits activity metrics like bills paid |
| Default duration (days) | How long a challenge from this template runs | 7 is typical — long enough to matter, short enough to stay urgent |
| Level min / max | Eligibility band | Keep bands narrow enough that the metric stays a fair contest |
| Players can create duels | Lets players challenge friends directly using this template | Turn off for templates meant only for official/teacher use |
| Usable for broadcasts | Available to the admin/teacher "start an official challenge" tool | — |
| Active | Selectable at all | — |

**Stakes** (set by whoever creates an actual challenge, not the template): an optional
entry fee, pooled and paid winner-take-all — always framed as an "entry fee / prize," never
a "bet," matching the game's no-gambling stance.

### 📋 Sample

> **Key:** `savings-sprint` · **Name:** Savings Sprint · **Icon:** 🏦
> **Metric:** Savings Balance · **Style:** percent · **Duration:** 7 days ·
> **Level:** 3–99 · **Player duels:** on · **Broadcasts:** on
> **Description:** "Whoever grows their savings balance the most (by %) in a week wins."
> *A level-30 player with Ksh 500,000 saved and a level-4 player with Ksh 2,000 saved can
> both win this — percent style measures who improved the most, not who started richest.*

---

## 12e. World Map Calibration

**What it is in gameplay:** the invisible tap-zones over the Pesa City map art — where a
player actually needs to click to enter each district (Marketplace, Fun World, Champions'
Court, Estates, Car Yard, etc.), and where each district's info panel/pin visually sits.
There is no text form here; it's a **drag-and-resize** tool laid directly over the map
image.

**Manager:** GameSet → World Map.

- Drag any district's coloured box to reposition its tap-zone; drag its bottom-right handle
  to resize it. The small dot marks the district's exact anchor point (used for the info
  panel's pin).
- **Two independent calibration passes are required**, same reasoning as Arcade's board
  layout (§12b): a **Desktop** pass for wide screens, and a separate **Mobile** pass for
  phones (different aspect ratio, different crop of the same art). Save each tab
  separately — saving one never touches the other.
- Get this wrong and the *game* still works (district names/content are unaffected) but
  players tap empty air trying to enter a district, or the info panel points at the wrong
  spot on the map.
- *Tip:* after changing the map artwork itself, always redo both calibration passes —
  positions are pixel/percent coordinates tied to that specific image.

---

## 13. Badges

**What it is in gameplay:** permanent achievement markers on the player profile.

**Manager:** GameSet → Badges. Define name, slug, icon, description and (where
supported) the trigger type that auto-awards it; badges can also be awarded or revoked
manually per player from the same screen — useful for classroom recognitions. Badge
slugs can be quest triggers (`earn_badge`), letting you chain systems.

---

## 14. Life Chapters

**What it is in gameplay:** the six net-worth life stages every player climbs — the
macro narrative (Student → Graduate → Hustler → Settler → Builder → Elder). Entering a
chapter awards +15 credit score, fires a milestone celebration, and changes which life
events can occur.

**Manager:** GameSet Hub → 🌱 Life Chapters panel.

- Editable per stage: **name, icon, tagline, and the net-worth trigger** (the Ksh amount
  that unlocks it). Stage 1 always starts at 0; triggers must ascend.
- The six stage *keys* are fixed (life events and timelines are wired to them) — think
  of them as slots you can rebrand and retune, not delete.
- **Threshold design:** the defaults (50k / 200k / 1M / 5M / 20M) suit the default
  economy. If you raise salaries and asset yields, raise thresholds proportionally, or
  players will race through chapters and the arc loses meaning.
- Chapters mirror wealth *honestly* — a net-worth collapse can drop a player back a
  chapter. That is intentional.

---

## 15. Config Panels

All on the GameSet Hub:

- **⚙️ XP Levels** — the level ladder: names + XP thresholds (ascending; level 1 fixed
  at 0). Level names are aspirational identities ("Saver", "Investor") — pick words you
  want learners calling themselves.
- **🗺️ Journey Milestones** — the goal checklist on the Life Timeline. Each row: icon,
  title, type + threshold. Types: `level`, `balance`, `net_worth`, `job` (times hired),
  `course`, `quest`, `asset` (counts), or `manual` (always shown). *Sample:* 🏘️
  "Property Owner — buy your first asset" → type `asset`, threshold 1.
- **💡 Hustle Tips** — the rotating one-liner tips in the Pesa City sidebar. Keep 8–15
  live; one idea per tip; Kenyan and concrete.
- **🧭 Career Fields & Tracks** — the two linked lists everything career-related is built
  from; nothing about careers is hardcoded. **Tracks** (📚) are the coarse groupings
  Courses and Jobs are filed under (e.g. `finance`, `tech`) — each has an icon, label,
  key, and colour. **Fields** (🎯) are the *finer* interest categories shown in the career
  quiz (e.g. "Finance & Banking") — each field picks one recommended track from the list
  above, plus its own icon/label/key/colour and an optional result-screen description.
  Rename or add either list here and it updates the quiz, Courses and Jobs everywhere at
  once. *Gotcha:* renaming a field's key here does **not** retroactively rename it inside
  quiz options already saved below — re-pick the field in each affected option after
  saving.
- **🎯 Career Quiz Questions** — the onboarding quiz. Each option maps to career fields
  (picked from the Fields list above) with weights, e.g. `{"finance": 3, "business": 1}`.
  Keep 4–6 questions; the result recommends (never restricts) courses/jobs.
- **🧭 Onboarding Wizard** — the first-time "how to play" tutorial. Shown once, right
  after a new player lands on the dashboard (separate from the Career Quiz Questions
  gate above — a player sees the wizard first, then the career quiz if they haven't
  picked a field yet). Each step has an **icon**, a **category** label (a short tag like
  "Earn" or "Grow & Borrow" shown above the title), a **title**, and a **body** paragraph.
  There's no fixed step count — add, remove, or reorder freely; the wizard's progress
  dots and "Step X of N" counter adapt automatically. Players can click **Next** through
  every step or **Close wizard** at any point — either way it's marked as seen and never
  reappears for that account. *Design tip: one system per step (courses/jobs, marketplace,
  savings/loans, bills/credit, quests, community) keeps each step skimmable — resist
  cramming two lessons into one step.*

---

## 16. Game Rules

GameSet Hub → 📏 Game Rules — pace controls applying to every player:

| Rule | Effect | Guidance |
|---|---|---|
| **Max quests started per day** | Caps new quest starts per real day (0 = unlimited) | 2–3 keeps quests a daily ritual instead of a binge |
| **WYWA minimum game days** | The catch-up popup only appears after this many game days passed | Default 7; urgent news (overdue bills, payday, crises, chapters) ALWAYS shows |
| **WYWA cooldown (real minutes)** | After showing once, the popup stays quiet this long | Default 45; stops popup fatigue on quick revisits |

---

## 17. Pre-Publish QA Checklist

Before toggling anything active:

- [ ] Is the **lesson field** written (consequence/tip/educational note/lesson)?
- [ ] Are the **numbers believable** for Kenya 2026 — and balanced against existing
      content (does this job out-earn everything at its level)?
- [ ] Right **age group / chapter / level gate**?
- [ ] Slugs stable and unique (quests reference course/asset slugs)?
- [ ] For quests: did you test the trigger on a test account — does it actually fire?
- [ ] For paid courses/assets: can a player at the target level actually afford it?
- [ ] Phone check: does the description fit a small screen without walls of text?
- [ ] Spelling of names players will see repeatedly (employers, landlords, chamas)?

---

## 18. Troubleshooting

| Symptom | Likely cause / fix |
|---|---|
| New content invisible in game | Active toggle off, or age/level gate excludes your test account |
| Quest never completes | Trigger value mismatch — course *slug* vs title, job *id* vs name. Re-pick from the dropdown |
| Player says salary never arrives | It has arrived as PENDING — they must Report to Work; check their Career page |
| Bill hits the wrong ages | Check the bill's age group and auto-assign flags |
| Life event never fires | Probability too low, wrong chapter scope, or asset-category scoping excludes non-owners |
| Crisis warned but "nothing happened" | Effects apply when the ACTIVE window opens — check the hit time; status should read 🔥 then ✅ |
| Numbers changed but players see old values | Ask an admin to run *Clear All Caches* in the Artisan tab |

*Companion documents: **GAMEPLAY.md** (how the game plays and its educational rationale —
give this to reviewers), **ADMIN-GUIDE.md** (platform operations), **BILLS_GUIDE.md**
(player-facing — how bills work and grow by chapter) and **SMART_TOOLS_GUIDE.md**
(player-facing — the 6 in-game calculators plus the Premium real-life bill/savings tools).*
