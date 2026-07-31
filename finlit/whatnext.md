PesaQuest — State of the Game, Known Risks, and the Road Ahead
Written June 2026 | Moski Financial Literacy Platform

================================================================================
PART ONE: WHAT HAS BEEN BUILT
================================================================================

PesaQuest is a Laravel 12 web application that serves as Moski's flagship financial
literacy product. It has grown significantly from a simple node-based decision game
into a surprisingly deep virtual financial life simulator. This section documents
every major system that has been implemented.


--- CORE ARCHITECTURE ---

The platform is built on Laravel 12 using Blade templates, Tailwind CSS for styling,
and Alpine.js for in-page interactivity without page reloads. The database is MySQL.
The codebase runs on a local XAMPP setup at this time and is not yet deployed to a
live server.

There are two distinct but interconnected game systems running side by side:

The first is the Decision Layer — a branching narrative engine where players read
scenarios, make financial choices, and follow a story path. This is the original
game and the main way players encounter financial education content.

The second is the Life Simulator Layer — a background engine that runs continuously
even when the player is offline. Financial events happen in real time (governed by a
configurable clock). Bills come due, assets earn income, life chapters advance, and
random events occur. When a player logs back in, they see a "While You Were Away"
summary of what happened to their financial life.

These two layers share the same player balance, credit score, and level — so
decisions made in the narrative affect the life sim and vice versa.


--- THE NODE/DECISION GAME ---

This is the heart of PesaQuest. Scenarios are authored as "nodes" in a decision
graph. A start node presents a situation. The player is given two or three choices.
Each choice leads to a result node that explains the outcome and its financial
lesson. From a result, the player can continue to a follow-up scenario or branch
into a new story arc.

There is no fixed story path. The graph can branch infinitely. Game designers
(users with gameset permission) can author new scenarios through a visual admin
tool without writing any code.

Content is organized by age group: 8 to 12, 13 to 17, 18 to 25, and 26 and above.
When a player registers, their age group is determined automatically from their
date of birth. They only ever see scenarios calibrated for their group.

There are 40 bulk scenarios seeded across all four age groups plus the original
starter scenarios from the NodeSeeder. Topics covered include savings habits, peer
pressure around spending, M-Pesa usage, loans, SACCOs, investment basics,
insurance, retirement planning, tax, business, and real estate — all grounded in
the Kenyan financial context.


--- THE LIFE SIMULATOR ---

Phase 1: The Game Clock
The administrator can set how fast game time passes. The default is one real hour
equals one game week. The clock is configurable from the admin panel. A "tick"
represents one game day. The LifeSimulator service counts how many ticks have
passed since the player last logged in and processes each one.

To prevent players from coming back after a long absence and finding themselves in
financial ruin (or extreme wealth), there is a cap of 60 ticks processed per login.
This represents two game months of catch-up.

Phase 2: Bills and Financial Consequences
25 Kenyan bills are seeded across all age groups, covering rent, utilities, phone,
data, food budgets, school fees, health insurance, transport, and more. When a
player first logs in, appropriate bills for their age group are auto-assigned.
Bills settle automatically during tick processing. If a player cannot pay a bill,
their credit score drops. Paying on time maintains or improves credit. The
dashboard shows overdue bills in red with a pulsing animation, upcoming bills, and
a credit score gauge ranging from 300 (Poor) to 850 (Excellent).

Phase 3: The Marketplace
Players can browse and buy assets across three categories: Vehicles (from a Bajaj
bodaboda up to a Porsche), Real Estate (from a Mavoko plot to a Kilimani two-bedroom),
and Business and Investments (from a mama mboga stall to private equity, money
market funds, and a fuel station). There are 31 assets in total.

Each asset has a monthly income and a monthly cost. Vehicles and equipment have
maintenance costs. Real estate earns rental income. Businesses generate profits.
Investments pay returns. All of this settles automatically during tick processing.

Phase 4: Asset Economy
Asset values are not static. Each month of game time, investments appreciate or
depreciate with some randomness (volatility). Life events can trigger market-wide
effects that shift asset category values up or down. The platform snapshots asset
prices in a history table so that value changes over a player's game life can
eventually be displayed as charts.

Phase 5: Life Chapters and Events
A player's game age starts from their real age group and advances by one game year
every 365 ticks. As they age, they move through six life chapters:

Student (ages 8 to 17) — pocket money, savings goals, small hustles
Graduate (18 to 22) — first job, renting, starting from scratch
Hustler (23 to 28) — career growth, first investment, first loan
Settler (29 to 40) — mortgage, family costs, business stakes
Builder (41 to 55) — passive income, portfolio management, wealth transfer
Elder (56 and above) — retirement planning and legacy

Random life events fire during sessions, up to three per visit. These events cover
35 scenarios drawn from Kenyan life: job promotions, medical emergencies, family
obligations, market crashes, government policy changes, business windfalls, and more.
The "While You Were Away" modal shows the player which events fired and what changed.
A full life timeline page at /life/timeline shows the player's complete financial
story grouped by chapter.


--- THE CHAMA SYSTEM (COOPERATIVE FINANCE) ---

Chama is a distinctly Kenyan financial institution — an investment group. PesaQuest
has a full cooperative finance system built around this concept.

Players can create a Chama, invite others via a shareable link, and pool funds.
Members contribute to the Chama pool. When a proposal to use those funds is raised,
members vote on it. If approved, the Chama can acquire assets collectively. When the
group decides to distribute, earnings are shared among members.

This system has 7 database tables (chamas, chama_members, chama_contributions,
chama_proposals, chama_votes, chama_assets, chama_invites) and 13 controller
methods. It is the most socially complex feature in the game.


--- SCHOOL SUBSCRIPTION SYSTEM ---

Schools can subscribe as an institution. The admin creates a school subscription
with a name, contact email, number of seats, start and end dates, and a price in
KES. A unique portal token is generated. The school receives a link to their portal.
At the portal (which requires no login), school administrators can add and remove
students by email or user ID. Students added to an active school subscription get
full game access, bypassing the individual paywall.

This system allows PesaQuest to serve entire classrooms at once.


--- SUBSCRIPTION AND PAYWALL ---

Individual players hit a paywall after the first three free scenarios. From there,
they must subscribe. Subscription plans are managed by the admin. M-Pesa payment
is integrated via the Daraja API. The MpesaController handles the payment callback.
The admin can also manually grant or revoke subscriptions from the user management
panel.

There is a Free-for-All toggle in admin settings that can temporarily remove the
paywall for everyone — useful for demos, events, or during content testing.


--- MAMA PESA AI ASSISTANT ---

An AI chat widget powered by OpenRouter (any compatible LLM) floats on the game
interface. It is named Mama Pesa and is positioned as a knowledgeable, warm Kenyan
financial advisor. The system prompt includes Kenyan financial resource links from
CBK, CMA, NSE, SASRA, HELB, KRA, M-Pesa, and CRB.

When a player opens the chat, they see suggestion chips tailored to their age group
that they can tap to start a conversation. The widget can be minimized to a compact
pill or closed entirely. If no OpenRouter API key is configured in admin settings,
the widget does not appear at all.


--- GAMESET CONTENT EDITOR ---

Users with gameset permission have access to a visual scenario editor at /gameset.
They can:
- Create new scenario trees with a wizard interface (no code required)
- Browse existing scenarios filtered by age group with a search bar
- Add continuation nodes and branches to existing scenarios
- Manage the asset catalog (add, edit, deactivate assets in the marketplace)
- Award and revoke badges
- Configure level thresholds (how many XP points each level requires)

The editor renders each scenario as a collapsible tree showing the narrative arc
and choices, with creation timestamps visible.


--- BADGES AND ACHIEVEMENTS ---

Nine badges are seeded. Badges have names, descriptions, icons, and can be set to
award automatically based on image triggers (a field in the badge record). Gameset
admins can manually award or revoke badges from the badge management screen.


--- PLAYER PROFILES ---

Each player has a public profile showing their animated avatar, life chapter badge,
XP progress bar, statistics grid (balance, net worth, game days played), credit score
gauge, badge gallery, recent decisions journal (what choices they made recently), a
streak panel, and a life journey chapter timeline. The profile edit page also shows
a Financial Life Snapshot card with current balance, net worth, portfolio value, and
game days.

Players can search for other players and view their public profiles.


--- SAVINGS SCHEMES ---

Players can create named savings schemes (similar to a savings jar or goal), deposit
funds into them, and delete them when done. This is a lightweight goal-based savings
feature.


--- DAILY CHALLENGES AND QUESTS ---

A DailyChallenge system and a Quest system exist with their own models, seeders, and
admin approval workflow. Quests can be submitted by players and approved by admins
from a pending quests panel.


--- LEADERBOARD ---

A leaderboard exists at /game/leaderboard showing players ranked by XP points.


--- SMART MONEY TOOLS ---

A tools page exists at /tools that provides players with financial calculators and
reference tools. The full extent of what is on this page is determined by its view
template.


--- ADMIN PANEL ---

The admin panel at /admin is a comprehensive management interface with tabs for:
- User management (create users, toggle admin/gameset permissions, reset passwords,
  grant and revoke subscriptions)
- Subscription plan management (create, update, delete plans; add school plans)
- Pending quests approval
- School subscriptions management
- Platform settings (SMTP email configuration, M-Pesa Daraja credentials, AI API
  key, game clock speed)

Settings are persisted in the settings table and read at runtime.


--- LANDING PAGE ---

The public landing page has a responsive hero image (pesaquestheader.jpg), a
Learning Made Fun image showcase section, an About section with an educational image,
and a PesaMali board game section with a link to pesamali.moski.money. All images
scale responsively across device sizes.


================================================================================
PART TWO: AREAS THAT MAY BE BROKEN OR INCOMPLETE
================================================================================

This section is honest about what is risky, untested, or structurally incomplete.
These are not accusations — they are areas to verify before rolling out.


--- HIGH RISK: MPESA PAYMENT FLOW ---

The M-Pesa integration handles real money. The callback URL (POST /mpesa/callback)
is CSRF-exempt as required by Daraja's server-to-server calls. However, this entire
flow has likely only been tested with sandbox credentials if at all. Before going
live, the full cycle — player initiates payment, STK push appears on their phone,
they confirm, callback fires, subscription activates — needs to be tested with real
test credentials on the Daraja sandbox. If any step breaks, players who try to
subscribe will pay and get nothing, which is catastrophic for trust.

Additionally, the subscription approval flow exists (admins can manually approve),
which suggests the automated flow may not have been the primary path relied on.


--- HIGH RISK: CHAMA SYSTEM EDGE CASES ---

The Chama system is the most complex social feature in the game. With 7 tables and
13 controller actions, it has many moving parts. The following scenarios are likely
untested and could produce errors or unexpected behavior:

What happens when a Chama member leaves mid-proposal? Does their vote persist?
What happens if a proposal to buy an asset is approved but the Chama pool does not
have enough funds at execution time? Is there a race condition?
What happens if the last member leaves a Chama? Does the Chama orphan?
How does Chama asset income and cost settle — through the LifeSimulator or separately?
The ChamaAsset model exists but how it connects to the main Asset marketplace or
the LifeSimulator's settleAssets() method is not clear from the architecture review.

The Chama system should be audited and tested as a complete user journey before
exposing it to real players.


--- MEDIUM RISK: INVESTMENT MODEL VS MARKETPLACE CONFLICT ---

There is an older Investment model, investments table, and a route for claiming
investments (POST /game/investments/{investment}/claim via GameController). There
is also the newer marketplace system with the Asset and PlayerAsset models. These
appear to be two generations of the same idea. The older investment system may
conflict with the newer one or may simply be dead code. This needs clarification
— if the old system is unused, it should be removed to avoid confusion in the
codebase.


--- MEDIUM RISK: DAILY CHALLENGES AND QUESTS COMPLETENESS ---

Daily challenges and quests have models, a seeder, and admin approval routes.
However, the actual player journey is unclear. Can players browse available quests?
Is there a quest list view? The diary page at /game/diary exists but what it shows
is uncertain. The admin quest approval flow requires an admin to manually review
submissions, which could become a bottleneck at scale. Whether the daily challenge
system actually fires daily challenges to players, and how that notification is
delivered, has not been confirmed.


--- MEDIUM RISK: CAREER SYSTEM INTEGRATION ---

A CareerService.php exists and there are career fields on UserProgress (career_field,
career_title, career_income_rate, career_income_claimed_at). There are also career
fields added in a migration. However, there is no obvious career management UI —
no page where a player selects their career, sees their income rate, or claims their
salary independently. The LifeSimulator fires salary every 30 ticks, but whether
the CareerService is actually called or whether the salary amount simply comes from
the life chapter defaults is uncertain. This system may be partially wired.


--- MEDIUM RISK: STOCK PRICE HISTORY AND CHARTS ---

The stock_price_history table is being populated by the LifeSimulator during asset
settlement. The intention is clearly to display value-over-time charts to players.
However, there is no evidence of a chart component in the portfolio view or anywhere
else. This data is being collected but may not be surfaced to players at all. The
collection should either be connected to a chart or paused to avoid unnecessary
database writes.


--- MEDIUM RISK: EMAIL AND SMTP ---

Email verification is part of the auth flow (routes and views exist). Password reset
requires email. The admin can configure SMTP settings and test them. However, email
only works if the SMTP settings are correctly configured in the admin panel. A fresh
installation with no SMTP configuration will silently fail to send verification and
reset emails. This should be verified before rollout.


--- MEDIUM RISK: SCENARIO RATINGS ---

A ScenarioRating model and a POST /game/rate route exist. This suggests players can
rate scenarios. However, there is no visible UI in the game views for rating, and no
admin dashboard showing aggregated ratings. This feature may exist in route form
but be hidden or incomplete in the UI.


--- LOW RISK: NODECONTROLLER ORPHAN ---

A NodeController file exists in the controllers directory but has no corresponding
routes in web.php. This is dead code. It is low risk but creates confusion.


--- LOW RISK: ASSET IMAGES ---

The AssetSeeder seeds 31 assets. The assets table has an image_url column (added
in a later migration). Whether all 31 assets have meaningful image URLs or whether
the marketplace shows placeholder images for most items is unknown. A visual audit
of the marketplace is needed.


--- LOW RISK: GAMESET CONTENT GAPS ---

The 40 bulk scenarios are a good start but 10 per age group is thin, especially for
the 26+ adult group where the financial decisions are the most consequential and
nuanced. Players in that group could exhaust all scenarios quickly.


--- GENERAL: NO AUTOMATED TESTS ---

There is no evidence of a test suite (PHPUnit or Pest). Every feature mentioned
above has been manually tested at best. For a platform handling subscriptions and
financial data, even a basic set of feature tests for the critical paths (registration,
subscription payment, game progression, bill settlement) would dramatically reduce
the risk of regressions as new features are added.


================================================================================
PART THREE: WHAT TO PRIORITIZE IN THE NEXT BUILD
================================================================================

The following is a recommended priority order for the next development cycle, ordered
from most urgent to most strategic.


--- PRIORITY 1: STABILIZE BEFORE EVERYTHING ELSE ---

Before adding any new features, spend time confirming that the core game loop works
reliably end to end. This means:

Play through the game as a real new user. Register, go through the free scenarios,
hit the paywall, and try to subscribe. Then log out and log back in after some time
to trigger the While You Were Away screen. Check that bills have been deducted, that
the credit score reflects payment behavior, and that the modal shows accurate
information.

Verify the M-Pesa STK push flow on the Daraja sandbox. Confirm callbacks reach the
server and subscriptions activate. Document this process.

Test the Chama system as two different users. Create a group, invite the second user,
contribute funds, raise a proposal, vote, and execute it. Check what happens to the
funds.

Fix any errors discovered. These fixes take priority over everything else.


--- PRIORITY 2: SOCIAL LAYER (PHASE 6) ---

Phase 6 of the life simulator was planned and not built. This is the next major
architectural milestone. It was designed to include a net worth leaderboard and a
player-to-player marketplace. This matters because financial literacy becomes much
more powerful in a social context. Seeing that a peer has a higher net worth because
they made different choices is more motivating than an abstract score.

The minimum viable social layer is:
A net worth leaderboard (not just XP points) that resets periodically.
The ability to view any player's public profile (this exists already at /players/{user}).

The stretch social layer includes:
Player-to-player asset trading (sell your Bajaj bodaboda to another player).
Social challenges (challenge a friend to a savings race).
Chama visibility (show what Chamas exist and let anyone request to join).


--- PRIORITY 3: QUEST AND CHALLENGE SYSTEMS AS REAL ENGAGEMENT LOOPS ---

Daily challenges and quests exist but their player-facing experience is unclear.
These systems, when properly surfaced, are powerful engagement drivers. A player
who comes back daily for a challenge is a retained player.

What is needed:
A clear quest board page showing available quests, their rewards, and submission status.
Daily challenges that appear prominently on the dashboard with a countdown timer.
Push notifications or email reminders when a new daily challenge is available.
Automated quest completion where possible (rather than all quests requiring admin review).


--- PRIORITY 4: AGE-AGNOSTIC CONTENT AND REBRANDING ---

The game was conceived for Kenyan youth but the architecture already supports all
ages. The content, however, is skewed younger. The adult (26+) scenarios are the
thinnest. To serve all ages credibly:

Add 20 to 30 more scenarios for the 26+ group covering topics like SACCO dividends,
property investment due diligence, insurance products (life, medical, education),
retirement planning with NSSF and personal pension funds, business formalization,
tax filing, and investment portfolio diversification.

The landing page should present PesaQuest as a platform for everyone, not just kids.
The hero image, copy, and feature highlights should reflect that adults will find
it valuable too. Currently the imagery and tone lean toward children.

Onboarding should feel appropriately serious for an adult registering while remaining
fun and approachable for a teenager.


--- PRIORITY 5: PLAYER RETENTION MECHANICS ---

Once the core is stable and content is broader, focus on keeping players coming back.

Streaks: A streak system exists (UserStreak model) but how prominent it is on the
dashboard is unclear. A visible streak counter with a warning when it is about to
break is a proven retention mechanic.

Level milestones: When a player levels up, there should be a celebration moment —
a modal, a sound, a flash of color. Currently leveling may happen silently.

Notifications: The GameNotification model and notifications route exist. These
notifications should surface as a badge count on the navigation so players know
something needs their attention.

Email reminders: A weekly summary email ("Your financial life this week: you paid
3 bills on time, your net worth grew by KES 12,000, you are 3 days from your next
level") would bring players back who drifted away.


--- PRIORITY 6: PRODUCTION READINESS ---

When the game is stable and content-rich enough to roll out:

Deploy to a real server (not XAMPP). Laravel Forge or a VPS with Nginx and PHP-FPM
is the standard path. Certbot for HTTPS.

Configure proper environment variables (.env on the server) for database credentials,
M-Pesa keys, SMTP, and the OpenRouter AI key. Never commit real credentials.

Set up queue workers. Laravel's queue system (for email and background jobs) requires
a persistent worker process on the server, not just the web server.

Set up the Laravel scheduler for tasks like resetting daily challenges, sending
weekly summary emails, and cleaning old stock price history records.

Basic error monitoring. Laravel Telescope for local debugging and something like
Bugsnag or Sentry for production errors.

Database backups. Automated daily backups of the MySQL database to cloud storage.


================================================================================
PART FOUR: SUGGESTIONS FOR MAKING THIS A TRULY GREAT FINANCIAL LITERACY GAME
================================================================================

These are ideas for future builds once the foundation is solid. They are designed
to make PesaQuest genuinely valuable and engaging for people of all ages.


--- IDEA 1: THE BUDGET BUILDER (A REAL MONEY PLANNING TOOL) ---

Turn the savings schemes into a full monthly budget planner. Players enter their
real income (or estimated income) and their real expenses. PesaQuest tracks the
budget over time and gives them a budget score each month. This bridges the gap
between the game world and their real financial life. It would be the killer feature
that keeps adults coming back — not because of points, but because it genuinely
helps them manage their money.


--- IDEA 2: SCENARIO PACKS BY THEME ---

Package scenarios into themed packs that can be unlocked or purchased separately.
Examples:
The University Pack — student loans, part-time work, shared rent, HELB management.
The Entrepreneur Pack — registering a business, finding investors, managing cash flow.
The Family Pack — school fees planning, joint accounts, life insurance, wills.
The Retirement Pack — NSSF, pension funds, passive income strategies.
The Crypto and Modern Investing Pack — understanding digital assets responsibly.

Schools and organizations could license specific packs relevant to their curriculum.


--- IDEA 3: FINANCIAL PERSONALITY ASSESSMENT ---

At the end of every ten scenarios, calculate and show the player their financial
personality type based on the choices they made. Types could include: The Saver,
The Investor, The Spender, The Risk-Taker, The Planner. Give each type a fun
description and three improvement tips. This kind of self-knowledge is immediately
useful and highly shareable on social media.


--- IDEA 4: REAL MARKET DATA INTEGRATION ---

Connect the investment assets (NSE stocks, money market funds) to real or
simulated market data. When the NSE performs well in real life, investment assets
in PesaQuest go up. This makes the game feel alive and teaches players to pay
attention to real financial news. Even simulated data that approximates real market
volatility would be more educational than static returns.


--- IDEA 5: THE FAMILY MODE ---

Allow a parent and child to share a connected experience. The parent sets up a
family account. The child plays as a "junior" member with age-appropriate scenarios.
The parent can see the child's decisions and financial lessons learned in a parent
dashboard. They can set challenges ("save 500 game shillings this week") and reward
the child with bonus points. This makes PesaQuest a tool for family financial
conversations.


--- IDEA 6: CERTIFICATE OF FINANCIAL LITERACY ---

After completing a defined set of scenarios, passing a quiz, or reaching a certain
level, the player receives a downloadable certificate of financial literacy signed
by Moski. Schools could require this as part of their subscription. Adults could
add it to their LinkedIn profiles. This makes completion feel meaningful and gives
the organization a tangible credential to market.


--- IDEA 7: PEER LENDING AND BORROWING ---

Extend the Chama system to include peer-to-peer lending. A player can apply for a
short-term loan from the Chama pool. Other members vote to approve or deny based
on the applicant's in-game credit score. If approved, the loan must be repaid with
interest by a deadline. Default reduces the borrower's credit score and alerts the
Chama. This is arguably the most powerful financial literacy mechanic possible —
it teaches credit responsibility through lived consequences in a safe environment.


--- IDEA 8: MENTOR AND MENTEE PAIRING ---

Pair high-level players (Builders and Elders) with new players (Students and
Graduates) as in-game mentors. A mentor can see their mentee's decisions and leave
short notes of advice visible on the mentee's dashboard. The mentor earns bonus
points for each milestone their mentee hits. This drives engagement from advanced
players and creates a support network for beginners.


--- IDEA 9: PERIODIC FINANCIAL CRISES ---

Run server-wide economic events that affect all players simultaneously. Examples:
a simulated inflation spike (bills increase 20% for two weeks of game time), a
market crash (all investments drop 30%), a drought (food costs double for players
in farming-related scenarios). These events are announced in advance through
in-game notifications. Players who prepared (savings buffer, diversified assets,
insurance) weather the crisis better than those who did not. This teaches emergency
preparedness at scale.


--- IDEA 10: AUDIO AND LOCALIZATION ---

Add voice narration for scenario text in Swahili and English. Many Kenyans are
more comfortable with Swahili in financial discussions. Audio narration also
makes the game accessible to players with lower literacy levels — an important
consideration for a development-focused NGO. Even basic text-to-speech narration
would be a significant accessibility improvement. A full Swahili interface toggle
would open the game to a much wider audience.


--- IDEA 11: EMPLOYER AND CORPORATE PARTNERSHIPS ---

Allow companies to sponsor PesaQuest as an employee financial wellness benefit.
Similar to the school subscription model, a company buys seats for their employees.
Employees play through scenarios calibrated to working adults (payslip deductions,
NHIF, NSSF, investment options, home loan planning). The company gets an aggregate
report on their employees' financial wellness scores. This is a B2B revenue stream
that does not depend on individual consumer subscriptions.


================================================================================
CLOSING NOTES FOR THE TEAM
================================================================================

PesaQuest is already a genuinely sophisticated platform. The life simulator alone,
with its tick-based clock, bill settlement, asset economy, and life chapters, is
more complex than most financial literacy apps in the Kenyan market. The Chama
system is a unique differentiator that no competitor has.

The game does not yet feel complete to an outside player because the rough edges
are still visible. The priority right now is not more features — it is making what
exists feel polished, reliable, and coherent.

The financial literacy mission is strongest when the game mechanics teach real
lessons without the player feeling like they are in a classroom. Every time a player
pays a bill on time and sees their credit score go up, or watches their real estate
asset appreciate over game years, or loses money to a bad investment in a random
life event — that is a lesson that sticks. The goal of the next build phase is to
make those moments feel real, feel consequential, and feel worth coming back for.

The audience for PesaQuest is every Kenyan who has ever wondered how to manage
money better. That is most of Kenya.
