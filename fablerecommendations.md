# Pesa City — Extension Recommendations

## How to use this document

Each section below is a self-contained feature proposal for PesaQuest / Pesa City. Review them independently — none blocks another unless listed under *Dependencies*. Mark the ones you want with ✅ and hand the list back to a development session; each entry includes enough implementation direction to start immediately. Complexity is an estimate of engineering effort (Low ≈ hours, Medium ≈ 1–2 sessions, High ≈ multi-session). Everything is designed around what already exists: the tick-based game clock, `QuestTriggerService`, `GameNotification`, the mood system, credit score, chapters, and the GameSet admin.

---

## Retention mechanics

### Savings Streaks & Compound Bonus
- **What it is:** A visible streak counter for consecutive game-weeks with at least one savings deposit. At streak milestones (4, 8, 12 weeks) the "bank" pays a small interest bonus on the player's savings scheme balances — the longer the streak, the higher the rate.
- **Why:** Turns the abstract idea of compound interest into a felt mechanic; gives a daily/weekly reason to return. Streaks are the single most proven retention loop in habit apps, and here the streak *is* the financial lesson.
- **Complexity:** Low
- **Dependencies:** Savings schemes (exists), `UserStreak` model (exists), GameClock.
- **Approach:** Extend `UserStreak` with a `savings_streak` counter updated in `SavingsController::deposit()`; pay the bonus in `LifeSimulator::processLogin()` with a `GameNotification` explaining the effective interest rate earned.

### Comeback Package ("Karibu Tena")
- **What it is:** Players who've been away 7+ real days get a one-time "Karibu tena!" package on return: a small cash cushion, mood reset to 70, and a 3-quest "get back on track" checklist instead of a wall of missed-bill penalties.
- **Why:** The tick catch-up system currently punishes lapsed players (missed bills, decayed mood, credit damage) exactly at the moment they're most likely to churn permanently. Softening re-entry converts lapsed players back into active ones.
- **Complexity:** Low
- **Dependencies:** LifeSimulator catch-up flow (exists).
- **Approach:** In `processLogin()`, if `last_tick_at` is 7+ real days old, cap bill misses at 1 cycle, floor the credit hit, and emit the package via the existing WYWA modal.

### Seasonal Events Calendar
- **What it is:** Admin-schedulable, time-limited city events: "Nairobi Trade Fair" (marketplace discounts), "Rate Hike Week" (loans cost more), "Harvest Season" (agriculture assets earn double). Shown as a banner on the world map with a countdown.
- **Why:** Time-limited content is the strongest "log in today, not someday" driver, and each event doubles as a macroeconomics lesson (inflation, seasonality, interest rate cycles).
- **Complexity:** Medium
- **Dependencies:** `MarketEvent`/`FinancialCrisis` models (partially exist), GameSet admin.
- **Approach:** A `city_events` table (name, modifier JSON, starts_at, ends_at) + a GameSet CRUD; `LifeSimulator` and `MarketplaceController` read the active event's modifiers. Reuse the world-map event banner pattern.

---

## Social features

### Chama Leaderboards & Merry-Go-Round Payouts
- **What it is:** (a) A city-wide leaderboard of chamas ranked by pool balance and contribution consistency. (b) A true Kenyan *merry-go-round* mode: each game month the whole pool pays out to one member in rotation — the classic chama structure every Kenyan household knows.
- **Why:** Chamas are the most culturally resonant feature in the game but currently lack a competitive/urgency layer. Rotation payouts create real anticipation ("my turn is Game Month 4") and teach commitment mechanics — if you skip a contribution, you hurt someone specific, not an abstract pool.
- **Complexity:** Medium
- **Dependencies:** Chama system (exists, now on game-day cycles), game-month keys shipped this session.
- **Approach:** Add `payout_mode` enum (`shares`/`rotation`) + `rotation_index` to chamas; on each full contribution cycle, `distribute()` pays the pool to the next member in `joined_at` order. Leaderboard is a simple query + page reusing the existing leaderboard view style.

### Friend Challenges ("Bet You Can't Save 5k")
- **What it is:** A player challenges a friend: "Save KES 5,000 in 30 game days" or "Reach credit 650 first." Both stake a small KES amount; winner takes the pot plus XP. Challenges appear in the notification bell and on profiles.
- **Why:** Peer accountability is the strongest documented driver of real savings behaviour among Kenyan youth (it's why chamas work). This translates that instinct into a game loop and drives invites.
- **Complexity:** Medium
- **Dependencies:** Player search/profiles (exist), QuestTriggerService (exists — reuse threshold checking).
- **Approach:** `player_challenges` table (challenger, challengee, metric, target, deadline_tick, stake); check resolution inside `LifeSimulator::processLogin()` for both parties; notify via `GameNotification`.

### Milestone Sharing Cards
- **What it is:** When a player hits a milestone (chapter unlock, first asset, loan cleared), generate a stylish shareable image card ("I just became a Hustler in Pesa City 💪") with a referral link, downloadable or shareable to WhatsApp.
- **Why:** WhatsApp is the distribution channel in Kenya. Every milestone becomes zero-cost marketing for Moski, and public commitment reinforces the player's own progress.
- **Complexity:** Medium
- **Dependencies:** GD library (already used for image processing), chapter/badge events (exist).
- **Approach:** A `ShareCardController` that renders a PNG via GD from a template per milestone type; add a "Share 📤" button on the chapter-unlock and quest-complete celebration modals with `https://wa.me/?text=` links.

---

## Narrative depth

### Story Arcs: The Long Game
- **What it is:** Multi-part life-event chains that unfold over game-months instead of single random events. Example arc: a cousin asks for a loan → if you agree, three ticks later they either repay with thanks (+credit story) or dodge you (choice: forgive, confront, involve the chama) → each branch teaches a different lesson about lending to family.
- **Why:** Single random events are forgettable; arcs create "what happens next?" retention and let the game teach nuanced, culturally real dilemmas (fare-well obligations, black tax, harambee requests) that one-shot events can't.
- **Complexity:** High
- **Dependencies:** LifeEvent engine + PlayerLifeEvent history (exist), Life Inbox (exists — good delivery surface).
- **Approach:** Add `arc_key` + `arc_step` + `next_event_conditions` JSON to `life_events`; `rollLifeEvents()` prioritises pending arc continuations before random rolls. Author 3–4 arcs in a seeder; expose arc authoring in the GameSet life-events CRUD.

### Market Shock Fridays
- **What it is:** Once per real week, a city-wide market shock hits with 24-hour warning via the notification bell: fuel price spike (vehicle costs +30% for 5 game days), shilling slide (imported gadget prices up), NSE rally (investment assets +8%). A "City News" ticker on the world map narrates it.
- **Why:** Teaches that markets move for reasons outside your control and rewards diversified players — the core insight of risk management — while creating a shared "did you see Friday's shock?" moment across the player base.
- **Complexity:** Medium
- **Dependencies:** MarketEvent model + `applyEventEffect()` market logic (exist).
- **Approach:** A scheduled artisan command (whitelisted in the admin runner) or lazy check in `LifeSimulator` picks the week's shock from a `market_shocks` pool; apply through the existing market_event effect path; render the ticker with the existing world-map event banner component.

### NPC Mentors with Personality
- **What it is:** 3–4 recurring characters — e.g. Mama Pesa (already the AI chat brand), Baba Duka the kiosk owner, Wanjiku the SACCO manager — who appear in life events, quest briefs, and district panels with consistent voices and occasionally conflicting advice (Baba Duka says "stock more inventory!", Wanjiku says "clear your loan first").
- **Why:** Players remember characters, not tips. Conflicting advice forces actual decision-making — the real skill being taught — rather than following the one "correct" answer.
- **Complexity:** Medium
- **Dependencies:** Npc model + relationship table (exist!), Life Inbox (exists).
- **Approach:** Assign an `npc_id` to life events/quests; render the NPC avatar + name on event cards and quest popups; track affinity in the existing `PlayerNpcRelationship` and let high affinity unlock NPC-specific deals.

---

## Mini-games

### Matatu Budget Run
- **What it is:** A 60-second arcade round: you're a matatu conductor for one route day. Passengers pay fares (tap to collect), expenses pop up (fuel, county fee, tout commission, police checkpoint) and you choose instantly: pay, negotiate, or skip. End screen shows profit/loss and a "what ate your margin?" breakdown. Daily play cap; profit converts to a small KES reward.
- **Why:** Cashflow management under pressure is *the* informal-sector survival skill. The matatu setting makes unit economics visceral for Kenyan teens in a way spreadsheets never will. A capped daily mini-game is also a strong daily-login anchor.
- **Complexity:** High
- **Dependencies:** None hard; Alpine.js + the existing WebAudio SoundMgr for feel.
- **Approach:** Pure front-end game state in Alpine with a single settle endpoint (`POST /minigames/matatu/settle`) that validates plausibility server-side (cap max payout), awards KES/XP, and fires a `play_minigame` quest trigger.

### The Haggle (Negotiation Duel)
- **What it is:** When buying selected marketplace assets, an optional "Haggle" button opens a 3-round offer/counter-offer duel against the seller NPC. Good haggling (anchoring low, walking away once) earns 3–10% off; overplaying it loses the deal for a game-day.
- **Why:** Negotiation is a real, teachable money skill omnipresent in Kenyan markets, and it adds decision texture to what is currently a single "Buy" click.
- **Complexity:** Medium
- **Dependencies:** Marketplace buy flow (exists), buy modal (fixed this session).
- **Approach:** Server-side haggle session (asset_id, hidden reserve price, round count) with two endpoints (offer, accept); discount applied via a signed one-time token consumed by `MarketplaceController::buy()`.

### Budget Blocks (50/30/20 Puzzle)
- **What it is:** A drag-and-drop monthly puzzle: given a salary and a shuffled pile of expense tiles (rent, fare, DSTV, chama, airtime, nyama choma), sort them into Needs / Wants / Savings so the ratios hit 50/30/20. Scored on accuracy; a new salary scenario each game-month.
- **Why:** Fun World already preaches 50/30/20 — this makes players *practice* it. Classification arguments ("is DSTV a need?") are exactly the reflection the lesson wants to provoke.
- **Complexity:** Low–Medium
- **Dependencies:** None.
- **Approach:** Alpine drag-and-drop (or tap-to-assign on mobile) with scenarios stored in a `Setting` JSON editable from GameSet; award XP through the existing quest trigger `play_scenario`.

---

## Real-world tie-ins

### M-Pesa Statement Literacy Module
- **What it is:** A safe, simulated M-Pesa experience inside the game: the player's game transactions render as an authentic-looking M-Pesa statement (Paybill, Till, Send Money, withdrawal charges), and quests ask them to answer questions from it — "How much did you lose to withdrawal charges this month?"
- **Why:** Every Kenyan teen will live inside M-Pesa. Reading a statement, spotting charges, and understanding Paybill vs Till vs Send Money costs is arguably the single most practical lesson the game can teach. (Simulated only — no real Daraja money movement for minors; the MpesaService integration stays for subscriptions.)
- **Complexity:** Medium
- **Dependencies:** GameNotification statement data (exists — the /life statement is halfway there).
- **Approach:** A "My M-Pesa" view that re-renders the player's `GameNotification` money events in M-Pesa statement format with realistic simulated charges; 3–4 quests keyed to `statement_quiz` triggers.

### SACCO Membership Tier
- **What it is:** A SACCO in the Bank district as a mid-game unlock: buy shares monthly, earn annual dividends, and unlock loans at ~half the bank's interest rate but capped at 3× your share balance — exactly how real Kenyan SACCOs work.
- **Why:** SACCOs are where most employed Kenyans actually build wealth, and the "borrow against your savings multiple" rule is a brilliant mechanic: it makes saving *unlock* borrowing power, inverting the game's current loan logic in an educational way.
- **Complexity:** Medium–High
- **Dependencies:** Loans + credit system (exist), Bank district panel (exists).
- **Approach:** `player_sacco_shares` table + a SACCO section in the bank district panel; extend `LoanController::take()` to check share-multiple eligibility and apply the discounted rate; dividends paid annually (every 365 ticks) in `LifeSimulator`.

### Harambee Events
- **What it is:** Periodic community fundraiser events on the world map: a school roof, a neighbour's hospital bill, a mtaa clean-up. Players choose to contribute (KES), volunteer (costs a game-day, boosts mood), or skip. Community-wide progress bar; contributors get a badge and a small credit/mood boost.
- **Why:** Harambee is a cornerstone of Kenyan financial culture and the game currently treats money as purely individual. Teaching *planned* generosity — giving as a budget line, not a crisis — is a genuinely Kenyan financial literacy lesson no imported curriculum covers.
- **Complexity:** Medium
- **Dependencies:** GameNotification, world map event surface (exists).
- **Approach:** `harambee_events` table (goal, ends_at, cause text) + contributions table; admin-created via GameSet; render with the existing world event card; fire a `join_harambee` quest trigger.

---

## Progression systems

### Prestige: The Legacy Reset
- **What it is:** Elder-chapter players (20M+ net worth) can voluntarily "retire": the character's story ends, their wealth converts to a *Legacy Score*, and they restart as a new young character with permanent small perks (e.g. +2% savings interest, a family plot inherited) and a "Generation II" badge.
- **Why:** Gives endgame players a reason to keep playing and teaches the most advanced concept in the curriculum — intergenerational wealth transfer. Restarting with perks also demonstrates compound advantage across generations viscerally.
- **Complexity:** High
- **Dependencies:** Chapters (exist), badge system (exists).
- **Approach:** A `legacies` table snapshotting final net worth/level/badges; a guarded `POST /life/retire` that archives progress and reseeds `UserProgress` with perk flags read by `LifeSimulator`.

### Mentor Role for Veterans
- **What it is:** Level 15+ players can register as mentors; new players may pick one during onboarding. Mentors see mentees' milestone feed (not balances), can send one nudge per game-week from preset messages, and earn XP + a "Mwalimu" badge track when mentees hit milestones.
- **Why:** Peer teaching deepens the mentor's own mastery (the protégé effect), gives school deployments a built-in classroom structure, and adds a warm social layer that leaderboards can't.
- **Complexity:** Medium
- **Dependencies:** School portal + player profiles (exist).
- **Approach:** `mentorships` table; nudges delivered as `GameNotification`; milestone hooks piggyback on the existing chapter/badge events.

### Financial Personality Evolution
- **What it is:** The existing `financial_personality` tag becomes a living profile: every meaningful decision (save vs spend, insure vs risk it, invest vs hold cash) nudges hidden trait scores (Saver, Risk-Taker, Planner, Hustler). The profile page shows the evolving radar and how each trait affects the game (Risk-Takers get better deal odds shown, Planners get bill discounts).
- **Why:** Self-knowledge is a financial skill. Making the personality reactive rather than a one-time quiz label rewards consistent behaviour and gives players a mirror: "the game thinks I'm becoming a Risk-Taker — am I?"
- **Complexity:** Medium
- **Dependencies:** financial_personality field (exists), decision events throughout controllers.
- **Approach:** A `personality_traits` JSON column on `user_progress`; a small `PersonalityService::nudge($trait, $weight)` called from ~8 existing decision points; radar chart in the profile with SVG.

---

## Educational depth

### Concept Unlock Cards ("Pesa Wisdom")
- **What it is:** A collectible deck of ~40 financial concept cards (Compound Interest, Inflation, Diversification, Credit Utilisation, Insurance…). Each unlocks the first time the player *does* the thing — first matured investment unlocks "Compound Interest" with a 3-sentence explanation tied to what just happened plus their actual numbers.
- **Why:** Discovery-based learning is the game's stated philosophy; this makes the learning visible and collectible. The deck doubles as a revision tool before the certificate exam (below), and "cards left to unlock" is a completionist retention hook.
- **Complexity:** Medium
- **Dependencies:** QuestTriggerService (perfect delivery mechanism — same trigger vocabulary).
- **Approach:** `concept_cards` + `player_concept_cards` tables; unlock calls sit beside existing `QuestTriggerService::fire()` call sites; a "Wisdom" tab on the profile shows the deck; GameSet CRUD for card content.

### PesaAI Teachable Moments
- **What it is:** Mama Pesa (the existing AI chat) proactively opens with one context-aware insight when something notable happened: "I see your rent bill bounced twice — want to talk about an emergency fund?" One per session, dismissible, always grounded in the player's actual data.
- **Why:** The AI currently waits to be asked, but the players who most need guidance never ask. One well-timed, data-grounded nudge per session is high-value tutoring without being spammy.
- **Complexity:** Low–Medium
- **Dependencies:** AiChatController + context endpoint (exist), GameNotification history.
- **Approach:** Extend the `/ai/context` payload with a "notable events since last chat" digest; the chat UI requests a one-line opener when the panel is first opened in a session; guard with the existing API-cost controls.

### Certificate Milestones (Moski Certified)
- **What it is:** Three in-game examinations — Bronze (budgeting basics), Silver (credit & debt), Gold (investing & risk) — unlocked by chapter progress and concept-card counts. Passing awards a printable, verifiable PDF certificate with the Moski logo and a QR verification code.
- **Why:** For the NGO this is the impact-measurement instrument funders ask for; for schools it's a gradeable artifact; for players (especially 13–17) a certificate with their name on it is startlingly motivating.
- **Complexity:** Medium
- **Dependencies:** Quiz infrastructure (career quiz pattern exists), GD/PDF generation.
- **Approach:** Exams as Setting-stored JSON question banks (GameSet-editable); a `player_certificates` table with a UUID verify route; PDF via GD or dompdf; gate attempts by chapter + card count.

---

## Seasonal / time-limited content

### School Term Cycle ("Back to School Squeeze")
- **What it is:** The game year follows the Kenyan school calendar. In January, May and September, a "Back to School" pressure event hits: fees fall due for the player (or their in-game family), stationery prices spike in the marketplace, and a planning quest rewards players who saved ahead in a dedicated "Fees" scheme during the previous term.
- **Why:** School fees are the dominant recurring financial shock in Kenyan family life. Players who learn to *pre-save* for a known, scheduled expense have learned the most transferable lesson in the whole game. Aligning with real terms also matches when school deployments are actually in classrooms.
- **Complexity:** Medium
- **Dependencies:** Bills + savings schemes (exist), seasonal events calendar (above) or a simple month check.
- **Approach:** Calendar-triggered `Bill` assignments + a `fees_ready` quest checking a named savings scheme balance; content configurable in GameSet.

### December Splurge Trap ("Sherehe Season")
- **What it is:** Throughout real-world December, Fun World prices drop 25% and glittering limited-time luxury assets appear ("Holiday Package — Diani!") while a quiet counter tracks the player's December spend. In January, a "Njaanuary Report" shows the damage plus a survival quest — with a badge for players who enter January with 1.5× their monthly bills in cash.
- **Why:** Njaanuary is a universally understood Kenyan phenomenon. Letting players *feel* the December trap and then live its January consequences is experiential learning no lecture achieves — and a December/January retention spike built in.
- **Complexity:** Medium
- **Dependencies:** Fun World activities (now DB-driven — just seed December rows), mood system, badge system.
- **Approach:** Month-gated activity rows + a `december_spend` tally in `GameNotification` data; a January login hook in `LifeSimulator` generates the report and fires the badge check.

### Harvest & Drought Cycles (Agriculture)
- **What it is:** Agriculture assets (farm plots, greenhouse, dairy) gain seasonal yield modifiers on a repeating game-year cycle — long rains boom, dry season dip — with a rare drought event where insured farms survive and uninsured ones lose a season's income. A weather forecast in the world HUD gives one season's warning.
- **Why:** Introduces seasonality, forecasting, and crop insurance — critical for the rural players in Moski's audience — and makes agriculture assets strategically interesting instead of just another income line.
- **Complexity:** Medium
- **Dependencies:** Asset income engine + `income_period_ticks` (exist), insurance concept pairs with SACCO/bills work.
- **Approach:** A season index derived from `tick_count % 365`; yield multiplier applied in `settleAssets()` for agriculture-category assets; an `insure` action on farm assets creating a small recurring `PlayerBill` that flips the drought outcome.

---

## Platform & polish (bonus)

### Offline-first PWA for School Labs
- **What it is:** Progressive Web App packaging (service worker, manifest, install prompt) with the world map and core pages cached, plus request queuing for spotty connections.
- **Why:** School computer labs and rural connectivity are the reality of Moski's deployment environment. Every failed page load in a 40-minute class period is lost teaching time.
- **Complexity:** Medium
- **Dependencies:** None (Vite supports PWA plugins cleanly).
- **Approach:** `vite-plugin-pwa` with a network-first strategy for API calls and cache-first for assets; test on the school portal flow specifically.

### Data Cost Awareness Mode
- **What it is:** A settings toggle (default on for mobile) that skips heavy imagery, disables ambient audio, and lazy-loads district panels — with a small "Data Saver on — you've saved ~X MB" indicator.
- **Why:** The target audience buys data in 10-bob bundles. Respecting that is both practical UX and *itself a financial literacy statement* — the game practices what it preaches.
- **Complexity:** Low
- **Dependencies:** None.
- **Approach:** A `data_saver` user preference; conditional `loading="lazy"`/`srcset` swaps and SoundMgr auto-off; count bytes saved roughly by tallying skipped asset sizes.

---

*Prepared by the Fable 5 development session, July 2026. All recommendations assume the current stack (Laravel 12, Blade, Alpine.js, Tailwind, MySQL, tick-based GameClock) and reuse existing engines wherever possible.*
