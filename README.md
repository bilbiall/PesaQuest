# PesaQuest 🎮💰

**PesaQuest** is a browser-based financial literacy game built for **Moski**, an NGO focused on equipping young people with practical money skills. Instead of lectures, players learn by *living* — earning a salary, paying bills, taking out loans, investing, and recovering from setbacks inside a simulated life and city, with real consequences for real financial habits.

Built with Laravel 12, it runs entirely in the browser (no app install) and is designed for low-bandwidth, budget-device access so it's usable in the environments Moski actually serves.

---

## What's inside

### 🏙️ Pesa City — the core life simulation
An open-world map where players build a financial life over an accelerated in-game calendar:
- **Jobs & careers** — full-time, part-time, and freelance work across multiple career tracks, with salary collection, missed-shift penalties, and career progression.
- **Courses & quests** — bite-sized financial lessons and timed quests that unlock jobs, badges, and XP.
- **Bills & budgeting** — recurring bills, a savings engine with bank interest, and a crisis system that forces real trade-off decisions.
- **Marketplace & assets** — buy/finance cars and property, take out loans, and grow (or lose) net worth over time.
- **Investing** — Equity Square, a lightweight investment-deals system for practicing risk and return.
- **Social layer** — friends, peer-to-peer loans, chamas (savings groups), a forum, and shareable profiles.

### 🐍 PesaTrail — arcade mini-game
A Snakes & Ladders-style board game layered on top of the same in-game economy:
- Solo play against an AI opponent ("Robo") or real-time multiplayer matches.
- **Rivals Trail** — an optional head-to-head stakes mode where players pool an entry amount and the winner takes a cut, with clear non-gambling framing throughout.
- Fully admin-configurable board (tile effects, mystery outcomes, flavor text, stake tiers) via the GameSet admin panel.

### 🏫 School & teacher portal
Schools can onboard as organizations with seat-based subscriptions, invite teachers, and track a class roster's progress from a dedicated dashboard.

### 🛠️ GameSet — the admin control room
Nearly every gameplay number (XP curves, job salaries, quest requirements, bill cycles, career tracks, arcade tile layout, free-plan gates, and more) is configurable from the admin panel without touching code — see [`docs/GAMESET_GUIDE.md`](docs/GAMESET_GUIDE.md).

### 🔔 Engagement & retention
Web Push notifications (with quiet hours and daily caps), a "While You Were Away" summary when players return, streaks, daily login bonuses, and a first-time onboarding wizard.

---

## Tech stack

| Layer | Choice |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Blade + Alpine.js, Tailwind CSS |
| Build tool | Vite |
| Database | MySQL |
| Auth | Laravel Breeze, with optional Google Sign-In (Socialite) |
| Notifications | Web Push (`minishlink/web-push`) |
| Hosting model | Designed for shared hosting (cPanel) — background work runs via cron, not a persistent queue worker |

---

## Getting started

### Requirements
- PHP 8.2+
- Composer
- Node.js + npm
- MySQL

### Setup

```bash
git clone https://github.com/bilbiall/PesaQuest.git
cd PesaQuest

composer install
npm install

cp .env.example .env
php artisan key:generate
```

Set your database credentials in `.env`, then:

```bash
php artisan migrate
npm run build
```

### Running locally

```bash
composer dev
```

This starts the PHP server, queue listener, log viewer, and Vite dev server together. Visit `http://localhost:8000`.

### Seeding demo content

The GameSet admin panel includes a "Seed All" action for populating courses, jobs, quests, and arcade content in one step — see [`docs/ADMIN-GUIDE.md`](docs/ADMIN-GUIDE.md) for the full walkthrough.

---

## Documentation

| Guide | Covers |
|---|---|
| [`docs/GAMEPLAY.md`](docs/GAMEPLAY.md) | How the game plays, from a player's perspective |
| [`docs/ADMIN-GUIDE.md`](docs/ADMIN-GUIDE.md) | Running the platform day-to-day as an admin |
| [`docs/GAMESET_GUIDE.md`](docs/GAMESET_GUIDE.md) | Every admin-configurable gameplay setting |
| [`docs/BILLS_GUIDE.md`](docs/BILLS_GUIDE.md) | The bills/crisis/savings economy in detail |
| [`docs/SMART_TOOLS_GUIDE.md`](docs/SMART_TOOLS_GUIDE.md) | In-game financial tools and calculators |

---

## Project structure notes

- Deployment is cron-driven, not queue-worker-driven — see the copy-paste cPanel cron setup card in the GameSet admin panel.
- Nearly all gameplay tuning lives in the database (via GameSet), not in code — check there before assuming a number needs a code change.
- Uploaded images are normalized to `/uploads/` rather than relying on the storage symlink, since that's unreliable on typical shared hosting.

---

## License

This project is proprietary software built for Moski. All rights reserved unless stated otherwise.
