# 🏙️ Pesa City — MVP Build Log
**PesaQuest World Expansion · Moski NGO · June 2026**

> *"A player should feel like they left a world behind when they log out — not closed a browser tab."*

---

## What We're Building

A 2D Kenyan city map that wraps every existing PesaQuest system in a **spatial layer**. The player controls a stickman character that walks between districts, unlocks opportunities, and completes 3 sequential missions that teach real financial habits — all under 500KB initial load, all on shared hosting.

**MVP ships when:** A first-time player opens `/world`, walks to 3 districts, completes 3 chained missions, earns 3 badges, and closes their laptop having genuinely enjoyed 30 minutes of financial education.

---

## The 3 Missions (Full Spec)

### Mission 1 — "Get Connected" 📱
| Field | Value |
|-------|-------|
| **Trigger** | Auto-activated on first `/world` visit |
| **Goal** | Buy any phone from the Marketplace |
| **Location** | Walk to → Mama Mboga Market district |
| **Condition** | `player_assets` where `asset.category = 'devices'` AND `created_at > mission.activated_at` |
| **Existing code used** | `MarketplaceController::buy()` — no changes needed |
| **Rewards** | +250 XP · +KES 1,000 bonus · 📱 Connected badge |
| **Unlocks** | Mission 2 |

### Mission 2 — "Level Up Your Skills" 🎓
| Field | Value |
|-------|-------|
| **Trigger** | Unlocks when Mission 1 is completed |
| **Goal** | Enroll in and complete any free course |
| **Location** | Walk to → Opportunity Hub district → Courses tab |
| **Condition** | `player_courses` where `status = 'completed'` AND `completed_at > mission.activated_at` |
| **MVP simplification** | One-click completion (Enroll → read description → Complete) |
| **Rewards** | +400 XP · 🎓 Scholar badge · Job board unlocked |
| **Unlocks** | Mission 3 |

### Mission 3 — "First Hustle" 💼
| Field | Value |
|-------|-------|
| **Trigger** | Unlocks when Mission 2 is completed |
| **Goal** | Apply for your first job or internship |
| **Location** | Walk to → Opportunity Hub district → Jobs tab |
| **Condition** | `player_jobs` where `status = 'employed'` AND `started_at > mission.activated_at` |
| **Existing code triggered** | `LifeSimulator::processLogin()` begins paying salary on next login |
| **Rewards** | +500 XP · +KES 5,000 sign-on bonus · 💼 Hustler badge |
| **Unlocks** | Salary starts. Mission chain complete. |

---

## Phase Checklist

### Phase 0 — Map Art + Assets
> Do this **now**, in parallel with Phase 3. Art generation has the longest lead time.

- [ ] Generate map background image with AI tool (prompt below)
- [ ] Compress to WebP at 75% quality — target **under 350KB** (use squoosh.app)
- [ ] Save to `public/images/world/pesa-city.webp`
- [ ] Identify pixel coordinates of each district building on the image (use browser DevTools)
- [ ] Update `DISTRICT_POSITIONS` in `public/js/world.js` with real coordinates
- [ ] Confirm stickman SVG looks good on the real map background

**AI Image Prompt (Midjourney / DALL-E 3 / Ideogram):**
```
Top-down 2D illustrated Kenyan city map, bright cartoon style, lush green grass, 
curved asphalt roads, central fountain plaza surrounded by trees. 
Districts visible: a colorful marketplace stall (left), a glass office tower (center-right), 
a bank building with columns (bottom-right), a car yard with vehicles (bottom-left), 
a school/tech hub building (top-left), an amusement park ferris wheel (top-right), 
a residential cluster with houses (bottom-center). 
Blue river at the bottom edge. Matatu buses on roads. Street lights. 
Style: isometric-flat hybrid, similar to mobile city game art. 
No text. Soft shadows. Vibrant but not oversaturated. 16:10 aspect ratio.
```

---

### Phase 1 — Map Shell + Layout + Static HUD ✅
> Route, layout, CSS placeholder map, HUD, sidebar — no walking yet.

- [x] `GET /world` route added to `routes/web.php`
- [x] `WorldController` created — passes player data to view
- [x] `resources/views/layouts/world.blade.php` — full-screen, no nav
- [x] `resources/views/world/index.blade.php` — map + HUD + sidebar + panel
- [x] `public/css/world.css` — all world-specific styles
- [x] `public/js/world.js` — district clicks, stickman, panel management
- [x] Dashboard nav link: 🗺️ Pesa City
- [x] Dashboard "Enter Pesa City" banner added

---

### Phase 2 — Stickman Walking Animation ✅
> Refine the walking. The Phase 1 stickman moves via CSS transition — Phase 2 polishes it.

- [x] Tune walk animation timing (distance-scaled, 700ms–2400ms)
- [x] Add "Heading to [District]..." floating label above stickman during transit
- [x] Handle edge case: player clicks another district while stickman is mid-walk (cancel + reroute)
- [x] Add arrive sound hook (wired to SoundMgr — activate by adding public/sounds/arrive.mp3)
- [x] Test on all 7 district zones

---

### Phase 3 — Database Migrations + Seeders ✅ (run on cPanel)
> Run these in parallel with Phase 0. No code dependencies — just SQL.

- [x] Migration: `missions` table (`2026_06_28_000001_create_missions_table.php`)
- [x] Migration: `player_missions` table
- [x] Migration: `city_courses` table (NOTE: named `city_courses`, not `courses`)
- [x] Migration: `player_city_courses` table
- [x] Migration: `city_jobs` table (NOTE: named `city_jobs` — avoids Laravel queue `jobs` conflict)
- [x] Migration: `player_city_jobs` table
- [x] Migration: `2026_06_28_000007_add_slug_type_to_badges_table.php` — adds slug + badge_type
- [x] Seeder: `MissionSeeder` — 3 missions matching specs above
- [x] Seeder: `CourseSeeder` — 4 free courses (Communication Basics, Digital Marketing, Hustle Basics, Financial Literacy 101)
- [x] Seeder: `CityJobSeeder` — 6 level-1 jobs across career tracks
- [x] Update `BadgeSeeder` — uses `updateOrCreate(['slug'=>...])`, adds 3 mission badges
- [ ] **TODO (cPanel):** Run `php artisan migrate && php artisan db:seed`
- [ ] **TODO (cPanel):** Verify tables in phpMyAdmin

---

### Phase 4 — Mission 1: "Get Connected" ✅
> Build the mission engine here. Phases 5 and 6 reuse it.

- [x] Create `MissionController` with `active()` and `check($id)` methods
- [x] Create `app/Services/MissionChecker.php` — switch on `requirements.type`
- [x] Handler: type `asset_category` — queries `player_assets` + boundary check `created_at >= activated_at`
- [x] On award: XP via `addPoints()`, KES to balance, badge attached, next mission activated
- [x] Marketplace district panel: bottom sheet with link to existing `/marketplace`
- [x] Badge pop-up overlay: full-screen, badge icon animates in, reward chips, "Continue" button
- [x] Sidebar mission card updates dynamically via `GET /missions/active`
- [x] Routes: `GET /missions/active`, `POST /missions/{id}/check`

---

### Phase 5 — Mission 2: "Level Up Your Skills" ✅
> Opportunity Hub district goes live. Course catalog, enroll, complete.

- [x] Create `OpportunityController` — courses + jobs with player status overlay
- [x] Opportunity Hub district panel: two tabs (Courses / Jobs — Jobs locked until any course completed)
- [x] Courses tab: renders seeded course cards with Enrol/Complete buttons
- [x] `POST /opportunities/courses/{id}/enroll` — creates `PlayerCityCourse` (status: enrolled)
- [x] `POST /opportunities/courses/{id}/complete` — sets status 'completed', triggers mission check
- [x] `MissionChecker`: handler for type `course_completed`
- [x] Routes: `GET /opportunities/courses`, `POST /opportunities/courses/{id}/enroll`, `POST /opportunities/courses/{id}/complete`

---

### Phase 6 — Mission 3: "First Hustle" ✅
> Job board, qualification filter, apply flow, salary start.

- [x] Jobs tab in Opportunity Hub panel — locked until player has completed any course
- [x] Job cards: employer logo, title, salary chip, requirement label (green tick if met)
- [x] `POST /opportunities/jobs/{id}/apply` — qualification gate + creates `PlayerCityJob`
- [x] `MissionChecker`: handler for type `job_employed`
- [x] Extend `LifeSimulator::processLogin()` — `settleJobSalaries()` pays every 30 ticks
- [x] Route: `POST /opportunities/jobs/{id}/apply`

---

### Phase 7 — Connect the Loop + Polish ✅
> Everything talks. First-time flow is smooth. No dead ends.

- [x] Auto-activate Mission 1 on first `/world` visit (`bootFirstMission()` — idempotent)
- [x] Active mission districts pulse with green glow animation (`pc-district-mission` class)
- [x] "Go There" button in sidebar mission card triggers walk programmatically
- [x] Bank district panel: static — shows credit score + balance (no mission)
- [x] Locked districts (Estates, Car Yard): unlock hint panel
- [x] Coming-soon districts (Community, Fun World 🎡): "Coming Soon" panel
- [x] Howler.js scaffold: volume toggle (🔊/🔇) in HUD, `SoundMgr` in world.js — hooks wired, add mp3 files to activate
- [x] Mobile responsive: sidebar drawer + bottom nav strip (📊 🛒 🎓 🏛️ 👤)
- [x] Mission sequence dots fixed (3 explicit Alpine `:class` bindings — no fragile nested x-for/x-if)
- [x] Neighborhood tint zones + streetlamps + matatu buses on map
- [ ] **TODO:** Swap CSS placeholder with real WebP background + remap district coordinates
- [ ] **TODO:** Regression test full 3-mission chain as a new player after cPanel deploy

---

## File Map

### New Files Created
```
app/Http/Controllers/WorldController.php
app/Http/Controllers/MissionController.php          (Phase 4)
app/Http/Controllers/OpportunityController.php      (Phase 5)
app/Services/MissionChecker.php                     (Phase 4)
resources/views/layouts/world.blade.php
resources/views/world/index.blade.php
public/css/world.css
public/js/world.js
public/images/world/pesa-city.webp                  (Phase 0 art)
database/migrations/..._create_missions_table.php       (Phase 3)
database/migrations/..._create_player_missions_table.php
database/migrations/..._create_courses_table.php
database/migrations/..._create_player_courses_table.php
database/migrations/..._create_jobs_table.php
database/migrations/..._create_player_jobs_table.php
database/seeders/MissionSeeder.php
database/seeders/CourseSeeder.php
database/seeders/JobSeeder.php
```

### Modified Files
```
routes/web.php                          — /world + /missions/* + /opportunities/* routes
resources/views/dashboard.blade.php    — Pesa City nav link + hero banner
app/Services/LifeSimulator.php         — job salary settlement (Phase 6)
resources/views/game/partials/life-sim-catchup.blade.php  — job income in WYWA (Phase 6)
database/seeders/BadgeSeeder.php       — 3 mission badges (Phase 3)
database/seeders/DatabaseSeeder.php    — register new seeders
```

---

## NOT in MVP (Scope Guard)

These ship **after** the 3-mission loop is live and tested:

- ❌ Shoutouts / social feed
- ❌ Business ownership
- ❌ Career promotions
- ❌ Paid courses
- ❌ Character cosmetics
- ❌ Night/day map cycle
- ✅ Sound scaffold (Howler.js wired — add mp3 files to activate)
- ✅ Mobile responsive layout (drawer sidebar + bottom nav)
- ❌ Leaderboard
- ❌ Flash sales / dynamic map events
- ❌ Fast travel perk
- ❌ Dreams Board
- ❌ Player-to-player trading

---

## Running the World

```bash
# After Phase 3 migrations + seeders:
php artisan migrate
php artisan db:seed --class=MissionSeeder
php artisan db:seed --class=CourseSeeder
php artisan db:seed --class=JobSeeder

# Visit:
http://localhost/world

# If CSS/JS not showing, ensure Vite is running:
npm run dev
# or for production:
npm run build
```

---

## Tech Decisions (Locked)

| Decision | Choice | Reason |
|----------|--------|--------|
| Map art (MVP) | CSS placeholder → swap WebP | Ships faster, no art dependency |
| Stickman | Inline SVG + CSS transitions | Zero images, cross-browser, swappable |
| Walking | CSS `transition: left/top` | Simple, smooth, no framework needed |
| Map framework | None (Phase 1) | CSS zones over background is sufficient for MVP |
| District panels | Alpine.js bottom sheet | Already used throughout app |
| Active districts | 3 (Marketplace, Opp Hub, Bank) | Matches 3 missions exactly |
| Layout | Desktop-first | School lab computers + laptops are primary device |
| Navigation | Full-screen world view | No Laravel nav bar — game immersion |

---

*Last updated: June 2026 · Phases 1–7 complete · Deploy to cPanel → run migrations → seed → test*
