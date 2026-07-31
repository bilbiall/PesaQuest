# PesaQuest — Game Concept & Learning Documentation

**Prepared for: Kenya Institute of Curriculum Development (KICD)**
**Prepared by: Moski**
**Version: 1.0 — July 2026**

---

## Table of Contents

1. [Executive Summary](#1-executive-summary)
2. [The Core Idea — Learning by Living](#2-the-core-idea--learning-by-living)
3. [The Player Journey](#3-the-player-journey)
4. [The Game World — Full Component Walkthrough](#4-the-game-world--full-component-walkthrough)
5. [Playability & Replayability](#5-playability--replayability)
6. [Educational Value & Experiential Learning](#6-educational-value--experiential-learning)
7. [The Moral Fabric of the Game](#7-the-moral-fabric-of-the-game)
8. [Safety & Security for Minors](#8-safety--security-for-minors)
9. [Engagement Mechanics](#9-engagement-mechanics)
10. [Practicality — Mirroring the Real Economic Landscape](#10-practicality--mirroring-the-real-economic-landscape)
11. [Entertainment & Fun](#11-entertainment--fun)
12. [Reliability — A Solid Product](#12-reliability--a-solid-product)
13. [Deployment Readiness](#13-deployment-readiness)
14. [The Schools Programme](#14-the-schools-programme)
15. [Closing Summary](#15-closing-summary)

---

## 1. Executive Summary

**PesaQuest** is a browser-based financial literacy life-simulation game built in Kenya, for Kenyan learners. Players do not read about money — they **live a financial life**: they earn a virtual salary, pay rent and school fees, save toward goals, take and repay loans, build a credit score, buy income-generating assets, join a chama, weather emergencies, and watch the consequences of every decision compound over time.

The game runs on its own **living clock**. Time in the game keeps moving even when the player is away, so bills fall due, salaries arrive, investments mature, and life events happen — exactly as they do in real life. When a player returns, a "While You Were Away" summary shows them what their financial life did without them, teaching the single most important money lesson there is: **your finances don't pause when you stop paying attention.**

PesaQuest is not a quiz app with game skins. It is a genuine game — a persistent city, a character, quests with deadlines, careers, assets, mood, community — inside which financial literacy is the *native language*. Every mechanic the player touches is a real financial concept wearing play clothes.

**Key numbers at a glance:**

| Dimension | Scale |
|---|---|
| Interactive decision scenarios | 640+ scenario nodes across 40+ branching story trees |
| Age-differentiated content tracks | 4 (ages 8–12, 13–17, 18–25, 26+) |
| Explorable city districts | 11 (bank, marketplace, career hub, estates, car yard, fun world, community centre, and more) |
| Career tracks | 8 (technology, business, healthcare, agriculture, finance, education, engineering, creative) |
| Purchasable assets | 42 (from a piggy bank and boda-boda to apartments and money-market funds) |
| Recurring bills modelled | 25 authentic Kenyan bills |
| Random life events | 51 Kenyan-context events (positive and negative) |
| Timed quests | 18 financial challenges with in-game deadlines |
| Achievement badges | 21, earned through real financial behaviour |
| Loan products, investment deals, savings schemes, chamas | All fully simulated with real interest, risk, and group dynamics |

---

## 2. The Core Idea — Learning by Living

### 2.1 The problem PesaQuest answers

Financial literacy is traditionally taught as *information*: definitions of saving, interest, budgeting. But money behaviour is not an information problem — it is a **habit and consequence problem**. A learner can define "compound interest" perfectly and still take a predatory mobile loan at 24% because they have never *felt* what that interest does to a budget over three months.

PesaQuest's founding principle: **let learners make financial mistakes where mistakes are free.** A 14-year-old who defaults on a virtual loan and watches their virtual credit score collapse from 620 to 520 — locking them out of the better loan products they now need — has learned something no textbook paragraph can teach. And they learned it at zero real-world cost.

### 2.2 Discovery, not lectures

The game deliberately **does not tell players what to do.** This is a core design philosophy that runs through every system:

- A quest says *"Get Connected — buy a phone within 30 game days."* It does not say "go take a course, then apply for a job, then save your salary." The player must explore the city, discover that courses unlock jobs, jobs pay salaries, and salaries buy phones. The **"aha moment"** when a player independently connects *upskill → employment → income → purchasing power* is the actual educational payoff — and because they discovered it themselves, it sticks.
- Rotating **tip cards** nudge without instructing, written in an authentic Kenyan youth voice: *"Upskill → Get hired → Earn salary → Buy the device. The path isn't direct, but it's real."*
- Every scenario outcome carries a short **lesson note** — but only *after* the player has chosen and seen the consequence. The lesson explains what just happened to them; it never pre-empts the choice.

### 2.3 Consequences that compound

What separates PesaQuest from point-and-click "edutainment" is that **nothing is an isolated event.** Systems interlock the way real finances do:

- Buy a car → an insurance bill and a fuel bill automatically attach to your life.
- Miss a bill → credit score drops → the affordable loan products lock → you're pushed toward the expensive starter loan → more of your salary goes to interest.
- Neglect your wellbeing (mood) → your work income drops 10% — the game's way of modelling burnout.
- Save consistently for a month of game time → the credit system quietly rewards the habit.

Players don't memorise these relationships. They **inhabit** them.

---

## 3. The Player Journey

### 3.1 Onboarding

1. **Registration** requires only a name, email, password, and date of birth. From the date of birth, the game automatically assigns one of four **age groups** — 8–12, 13–17, 18–25, 26+ — and from that moment, every scenario, tip, AI suggestion, and challenge the player sees is written for their age band. A 10-year-old negotiates pocket money and playground peer pressure; a 22-year-old negotiates HELB repayment and first-salary budgeting.
2. **First session**: the player lands on their dashboard — their character, starting wallet balance, and an invitation to enter **Pesa City**.
3. **City orientation**: the first quest walks them through the world map. Their character (a friendly stick figure) physically walks the city roads from district to district — the game is spatial, not menu-driven.

### 3.2 The core loop

A typical play session looks like:

```
Return to game
   → "While You Were Away" recap (salary arrived, rent fell due, an event happened)
   → Check active quest and its deadline
   → Walk the city: attend to bills, work, shop, invest, or socialise
   → Make scenario decisions as life events and career events fire
   → Watch balance, credit score, mood, XP, and net worth respond
   → Leave — and the city keeps living
```

### 3.3 The long arc — Life Chapters

Across weeks and months of play, a player's **net worth** (wallet + assets + savings + investments + chama share − debts) moves them through six life chapters: **Student → Graduate → Hustler → Settler → Builder → Elder.** Chapters are earned through financial substance, not playtime — a player who hoards XP but never builds net worth stays a Hustler. This makes the game's definition of "winning" identical to real life's definition of financial progress.

---

## 4. The Game World — Full Component Walkthrough

This section describes every gameplay system so an evaluator can understand the complete game logic.

### 4.1 The Scenario Engine — branching financial stories

The narrative heart of the game is a **node-based decision graph** — not a linear course. Each story presents a situation, the player picks from 2–3 choices, each choice leads to a *result* node showing consequences (money gained/lost, lesson learned), which can branch into further situations. Because stories are graphs rather than lines, they support **infinite branching depth** and multiple paths through the same dilemma.

- **640+ nodes across 40+ complete story trees**, ten per age group.
- Topics span the full financial literacy syllabus in lived form: savings habits, peer pressure spending, M-Pesa use, mobile loans, SACCOs, insurance, investment, retirement, tax, side businesses, real estate.
- **24 career-specific event scenarios** across the 8 career tracks fire contextually — a player employed in agriculture faces agricultural opportunities and costs; a tech worker faces tech ones.
- Every choice's outcome adjusts the player's **actual game wallet** — story decisions and simulation economy are one connected system.

### 4.2 Pesa City — the world map

The game world is a stylised Kenyan city rendered as an explorable map. The player's character walks real roads between districts (with celebration animations on arrival, ambient district soundscapes, and a day-lit city aesthetic). The districts:

| District | What happens there |
|---|---|
| 🏠 **Home** | The player's base and spawn point |
| 🛒 **Mama Mboga Marketplace** | Buy assets: gadgets, vehicles, businesses, property, investments |
| 🎓 **Opportunity Hub** | Enrol in courses, browse and apply for jobs |
| 💼 **Workplace District** | Daily work life: performance score, workplace encounters (rotating financial lessons), promotion eligibility |
| 🏦 **Equity Square (The Bank)** | Credit score gauge, savings schemes, investment deals, loan products — a full retail bank experience |
| 💰 **Bank & Savings** | Create and manage goal-based savings schemes |
| 🌳 **Kiambu Estates** | Property market — plots, bedsitters, apartments, with deposits, financing, and rental income *(locked until the player proves themselves — see 4.10)* |
| 🚗 **Jua Kali Car Yard** | Vehicle market — boda-bodas, taxis, school vans, each with gig income vs. loan repayment maths *(also gated)* |
| 🎡 **Fun World** | Leisure spending — restores mood, teaches entertainment budgeting |
| 🤝 **Community Centre** | Chamas, forums, and the social layer |
| 📜 **Quest Board** | Active quests, deadlines, and progress |

### 4.3 The Living Clock — time as a teacher

- **1 game tick = 1 game day.** At the default speed, one real hour ≈ one game week, so a real week of casual play covers months of financial life — long enough for interest to compound and habits to show their effects.
- The clock **runs while the player is away**. On return, the game settles everything that happened in order: salaries paid, bills fallen due or gone overdue, assets appreciated or depreciated, loans accrued interest, deals matured, life events rolled.
- The **"While You Were Away" (WYWA)** modal narrates it all — the single most distinctive teaching moment in the game. *"While you were away: your salary of KES 18,000 arrived. Your rent of KES 6,500 fell due. Your shares gained 4%. You were hired at Safari Tech! 🎉"*
- Catch-up is **capped at two game months per return**, so a long absence can't bury a player in unrecoverable debt — a deliberate fairness guard.

### 4.4 Quests — deadlines create urgency, discovery creates learning

- **18 quests**, from *Start a Piggy Bank* (younger players) through *Register M-Pesa*, *Make a Monthly Budget*, *Join a Chama*, *Invest KSh 1,000*, *Start an Emergency Fund*.
- Each quest has a **deadline in game days** with a colour-coded urgency bar: green (safe) → gold (past halfway) → pulsing red (final stretch). Deadlines are *soft* — missing one never hard-blocks a child, it simply urges.
- Quests are **level-gated**, forming a progression ladder: early quests teach foundations, later quests assume them.
- Quests **auto-complete through real behaviour**: the *"First Deposit"* quest completes the moment the player actually deposits into a savings scheme — not when they tick a box claiming they did. The game verifies learning through action.
- Completion triggers a gold celebration overlay with the financial lesson restated and XP awarded (bonus XP if the player's mood is high — wellbeing pays).

### 4.5 Careers — the earning engine

- A playful **career aptitude quiz** helps players discover a fit across **8 tracks**: technology, business, healthcare, agriculture, finance, education, engineering, creative.
- **Courses** at the Opportunity Hub build qualifications; **jobs** require them. Salaries pay automatically every game month.
- The **Workplace District** gives employment texture: a rotating "today's encounter" (a workplace financial lesson), a performance score, and promotion eligibility as the player levels up.
- The chain the player discovers — *learn → qualify → apply → earn → budget* — is the employability narrative of real life, compressed into play.

### 4.6 The Marketplace & Assets — ownership with consequences

- **42 assets in five categories** — vehicles, property, businesses, investments, gadgets — priced along a realistic Kenyan ladder from a starter phone to a Kilimani apartment.
- Assets are not trophies. Income-generating assets **pay income on a weekly game cycle**; properties and investments **appreciate or depreciate with volatility**; every asset can be **sold at a 5% transaction fee** (markets always take a cut — another quiet lesson).
- **Assets create obligations**: buy a vehicle and insurance + fuel bills attach automatically; buy property and a service charge appears. Ownership costs money — the game never lets players forget it.
- Assets carry honest **risk labels** (stable / risky / trending) and editorial groupings (*Starter Moves*, *High Growth*, *Dividend Builders*) that teach portfolio vocabulary through shopping.

### 4.7 Money Systems — the financial institutions of Pesa City

**Savings schemes** — goal-based savings pots ("School Trip", "Emergency Fund") with progress bars. Deposits genuinely leave the wallet (saving is a real trade-off, not free points); closing a scheme refunds it.

**Credit score (300–850)** — a full behavioural credit model, the game's moral memory:
| Behaviour | Credit effect |
|---|---|
| Pay a bill / loan instalment on time | +5 |
| Fully repay a loan | +30 |
| Hold a savings scheme a full game month | +10 |
| Long job tenure ("loyal worker") | +10 |
| Build a 3+ asset portfolio | +5 |
| Advance a life chapter | +15 |
| Miss a payment | −20 |
| Default on a loan | −100 |
| Sitting at zero balance | −10 |

Every change is **logged with its reason** in a credit history panel, so players can audit exactly which behaviours built or broke their score — credit as a transparent, learnable system rather than a mystery number.

**Loans** — three products mirroring the real market: a Starter loan (small amounts, 24% p.a. — expensive, like real mobile lending), Growth (18%), Premium (large amounts, 12% — but you need the credit score to reach it). Interest compounds per payment period using real amortisation maths; instalments auto-deduct; a maximum of two concurrent loans prevents spiral borrowing. The structural lesson is priced into the products themselves: *good credit is literally cheaper.*

**Investment deals** — short-horizon opportunities at the bank (NSE blue chips, a property flip, a side-hustle seed, a deliberately risky "crypto quick flip"), each with a cost, a maturity period, a success probability, and a potential loss. Players experience risk-return trade-offs viscerally: sometimes the risky deal pays; sometimes it takes 40% of their stake. **Expected value stops being a formula and becomes a memory.**

**Chama** — the beloved Kenyan group-savings institution, fully simulated: monthly contributions on the game calendar, a shared pool, **democratic proposals and voting** (buy an asset together, change the contribution amount, sell a holding, remove a member), proportional shares, and expiring invitations. Players learn group finance, governance, and accountability — cornerstones of Kenyan economic life almost never found in a game.

**Portfolio** — a consolidated net-worth view: assets by category, savings, pending deals, loans, and a net-worth trend line. Players learn to read their own balance sheet.

### 4.8 The Mood Economy — wellbeing is financial

A quietly profound mechanic. Mood decays with the grind of game days. If it drops low, **work income falls by 10%** (burnout has a cost). Spending at Fun World restores it — but Fun World tracks *"spent on fun this game month"* against a budget, turning red when leisure overshoots. High mood earns **+10% quest XP**. The player is pushed to discover a real adult truth: **neither the workaholic nor the spendthrift wins — balance does.**

### 4.9 Life Events & Bills — the weather of financial life

- **25 authentic Kenyan recurring bills** (rent, school-related costs, transport, airtime…) assigned by age group, with an urgency board and overdue consequences.
- **51 random life events** in Kenyan context — a relative's harambee, a market boom, a phone theft, a bonus — some helpful, some costly, all requiring adaptation. Life events can move markets, hit the wallet, or affect credit; each carries an educational note. The lesson: **you cannot plan for everything, which is exactly why you save.**

### 4.10 Earned access — districts that unlock

The premium districts are gated by achievement, not payment: **Kiambu Estates** opens at KES 200,000 balance or three completed quests; the **Car Yard** requires KES 100,000 *and* a chosen career. Aspiration is built into geography — players see the locked district on the map every day and reverse-engineer what financial standing would open it.

### 4.11 Progression & Recognition

- **XP and levels** from scenarios, quests, courses, and community participation.
- **21 badges** triggered by verified behaviour — reaching a net-worth threshold, being hired, completing courses, finishing quests — never by clicking.
- **8 journey milestones** (*First Steps → First Job → Student of Finance → First Savings Goal → Quest Seeker → Property Owner → Investor → Wealth Builder*) charted on a personal timeline that reads back the player's whole financial life story, chapter by chapter, decision by decision.
- **Daily streaks and a daily login bonus paid in XP** (deliberately *not* cash — showing up is rewarded with growth, not free money).

### 4.12 Community — Forums & the Social Layer

- Community **forums** with six categories (including a *School Corner*), where players ask questions, share wins, and discuss money topics. Posting earns XP — **capped per day** so the forum rewards contribution, not spam.
- Display is **names only — no email addresses or contact details ever appear** anywhere in the social layer.
- **Moderation**: administrators and content managers can edit, delete, pin, and lock any thread.
- Schools get **private forum boards** visible only to their own students (see §14).

### 4.13 pesAI — the in-game financial coach

An optional AI chat companion, styled as a friendly Kenyan money mentor:

- Offers **age-appropriate suggestion chips** (a 10-year-old sees "How do I save my pocket money?"; a 20-year-old sees "How does HELB repayment work?").
- Grounded in **real Kenyan institutions** — its knowledge frame includes CBK, CMA, NSE, SASRA, HELB, KRA, M-Pesa, and CRBs — so answers point learners toward legitimate resources.
- Strictly a coach: it explains and encourages; it never transacts, never collects personal information, and is fully minimisable/dismissible.

### 4.14 Notifications & the Journey Bar

An in-game notification bell surfaces contextual, personal nudges — a quest reminder, a low-balance alert, a "You're hired!" celebration, a credit-change explanation. A journey bar tracks milestone progress from *First Step* to *Millionaire Mind*. Nothing external, nothing addictive-by-design — all notification life stays inside the game session.

---

## 5. Playability & Replayability

**Playability.** PesaQuest runs in any modern browser on phone, tablet, or computer — no installation, no downloads, no console. Sessions are meaningful at any length: two minutes (check bills, claim salary) or an hour (career building, chama meeting, quest chain). Controls are tap/click-based walking and choosing — a 9-year-old can navigate it unassisted after the orientation quest. The interface is bilingual in spirit — English with natural Kenyan expression — and every mechanic is introduced by doing, not by manual.

**Replayability** is structural, not cosmetic:

1. **Branching scenarios** — 40+ story trees with 2–3 choices per node mean different playthroughs genuinely diverge. Replaying a story to explore "what if I'd taken the loan instead?" is itself the lesson.
2. **A persistent, ever-moving economy** — because the clock never stops, the game state is never the same twice. There is always a new bill, a maturing deal, a market movement, a life event.
3. **Eight career tracks** — a player can live a full arc as a farmer-investor, then wonder what the tech-entrepreneur path feels like.
4. **51 randomised life events and probabilistic investments** — no two financial lives play out identically.
5. **An endgame that recedes healthily** — the chapter ladder (Student → Elder) is measured in net worth, and net worth can always grow. The game does not "finish"; it matures with the player, exactly as financial life does.
6. **Social freshness** — chama politics, forum discussions, and teacher-posted school challenges are generated by people, so content self-renews.

---

## 6. Educational Value & Experiential Learning

PesaQuest implements the full experiential learning cycle (Kolb: *concrete experience → reflective observation → abstract conceptualisation → active experimentation*) as its core loop:

| Kolb stage | In PesaQuest |
|---|---|
| **Concrete experience** | The player takes a loan, misses a bill, buys an asset — real (virtual) stakes, felt consequences |
| **Reflective observation** | The WYWA recap, the credit-history log with reasons, the life timeline, per-scenario lesson notes |
| **Abstract conceptualisation** | Lesson cards name the principle *after* the experience ("That was compound interest working against you") |
| **Active experimentation** | The player replays the situation, tries the other branch, adjusts strategy in the ongoing simulation |

**Curriculum coverage in lived form.** Every core financial literacy strand appears as a playable system, not a reading:

- *Earning & careers* → courses, jobs, salaries, performance, promotion
- *Budgeting* → bills board, fun-spending budget, monthly burn rate on the dashboard
- *Saving* → goal schemes with real wallet trade-offs; emergency-fund quests
- *Borrowing & credit* → the full loan/credit-score system with transparent cause-and-effect
- *Investing & risk* → assets, appreciation/volatility, deals with probability and loss
- *Group finance* → chama contributions, voting, shared ownership — deeply Kenyan
- *Consumer awareness* → transaction fees, insurance costs, asset running costs, risk labels
- *Digital finance* → M-Pesa framing, mobile-loan cost lessons, awareness of CBK/CMA/CRB

**Assessment through behaviour.** The game never asks "what is a budget?" It watches whether the player *keeps* one. Badges, quests, credit score, and net worth are all **behavioural assessments** — a teacher looking at a student's chapter, credit score, and milestone timeline is reading a portfolio of demonstrated competencies, not quiz marks.

**Differentiation.** Four age tracks mean the same platform serves an entire school: content, scenarios, bill sets, AI suggestions, and tone all shift with the learner's registered age band.

---

## 7. The Moral Fabric of the Game

PesaQuest's value system is engineered into its incentives — the game *pays* virtue and *charges* vice:

- **Honest effort is the only path to wealth.** There are no cheats, no gambling-your-way-up, no shortcuts. Every shilling comes from work, saving, enterprise, or patient investment. The daily login reward is XP (growth), deliberately not cash.
- **Promises kept are rewarded; promises broken cost you.** The credit system is, at heart, a *trustworthiness meter*: pay what you owe and doors open; default and the whole city's financial system remembers (−100). This is integrity taught as mechanics.
- **Debt is a tool, not a trap — and the game says so honestly.** Loans exist because loans exist in life; but the expensive starter loan, the two-loan cap, and the compounding interest teach *respect* for debt rather than fear or recklessness.
- **Community and cooperation are wealth strategies.** The chama system teaches contribution discipline, democratic decision-making, transparency (shared pool, visible shares), and accountability (members vote; proposals expire). Kenyan communal finance values — harambee spirit with governance — are celebrated, not sidelined.
- **Balance is a virtue.** The mood economy penalises both joyless grinding and reckless leisure spending. Moderation is mathematically optimal.
- **Consequences, never punishment.** Deadlines are soft; catch-up debt is capped; a collapsed credit score can always be rebuilt through the same honest behaviours. The game's moral stance is redemptive: *mistakes teach; they do not condemn.*
- **Risk is presented truthfully.** The "Crypto Quick Flip" deal exists — labelled risky, and it genuinely loses money at its stated probability. The game neither glamorises speculation nor pretends it away; it lets a learner lose KES 2,000 of play money at 15 instead of KES 200,000 of real money at 25.
- **Respectful community.** Forums are moderated, XP-capped against spam, name-only, and lockable. Teacher-posted challenges model adult guidance inside the social space.

---

## 8. Safety & Security for Minors

Child safety shaped the architecture, not just the policy page:

**No real money in gameplay.** All in-game currency is virtual. Children cannot spend, lose, gamble, or transfer real money through play. Subscription payment is a parent/school-side action entirely separate from the game world.

**Minimal data, purposeful data.** Registration collects only name, email, password, and date of birth — the DOB exists solely to assign the age-appropriate content track. No location tracking, no contact lists, no photos, no third-party advertising, no data sale. Passwords are encrypted with industry-standard hashing (bcrypt); all forms carry anti-forgery protection; sessions are securely managed.

**No exposure of personal information.** Emails and contact details are stripped from every social surface — leaderboards, forums, profiles, and player search show display names only. There is **no private messaging between players** anywhere in the game: all interaction is in public, moderated, lockable forum spaces or structured chama actions.

**Age-appropriate content by design.** The four age tracks are hard content boundaries. An 8–12 player's world is pocket money, piggy banks, and playground peer pressure; they are never served teen or adult scenarios.

**School-supervised spaces.** School forum boards are private to the school's own students; teachers post challenges through a secure school portal; school students' social experience can live entirely inside their school's walled garden.

**Moderated by humans with tools.** Administrators and designated content managers can edit, delete, pin, or lock any forum content, and the platform operator (Moski, an NGO) curates all scenario content editorially before it reaches learners.

**A bounded AI.** pesAI answers financial literacy questions in age-tuned language, points to legitimate Kenyan institutions, is rate-limited, fully dismissible, and collects nothing.

**Healthy engagement patterns.** The offline simulation is capped (max two game months of catch-up), deadlines are soft, and there are no exploitative "pay to un-stick" mechanics — a child who steps away for two weeks returns to a recoverable, narrated situation, never a ruined one.

---

## 9. Engagement Mechanics

PesaQuest layers proven engagement loops, each anchored to a learning purpose:

| Mechanic | Loop length | Learning anchor |
|---|---|---|
| Daily login bonus (XP) + streaks | Daily | Consistency as a financial habit |
| "While You Were Away" recap | Per return | Reflection; time-consequence awareness |
| Salary & bill cycles | Game-monthly | Cash-flow rhythm; budgeting |
| Timed quests with urgency colours | Days–weeks | Goal-setting under deadlines |
| Level & XP progression | Continuous | Visible growth |
| Badges & journey milestones | Weeks | Long-horizon identity ("I am an Investor now") |
| Life chapters via net worth | Months | The true win condition = financial substance |
| District unlocks (Estates, Car Yard) | Weeks | Aspiration; reverse-engineering requirements |
| Mood & Fun World | Continuous | Balance; leisure budgeting |
| Chama meetings, votes, proposals | Social, recurring | Group accountability |
| Forums & teacher challenges | Social, fresh | Peer learning |
| Notification bell (in-game) | Session | Contextual nudges, celebrations |

Craft details deepen immersion: the character *walks* the city rather than teleporting between menus; districts have ambient soundscapes and arrival celebrations; quest completions burst gold; being hired triggers a "🎉 You're Hired!" moment on the next visit. The emotional palette is celebratory — the game cheers effort loudly and lets consequences speak quietly.

Critically, **every reward is earned through financial behaviour.** There are no engagement mechanics detached from learning — no cosmetic loot, no random reward escalation patterns. Retention and education are the same loop.

---

## 10. Practicality — Mirroring the Real Economic Landscape

PesaQuest is unapologetically **Kenyan**. Its economy is not a generic "coins and gems" abstraction but a faithful miniature of the economy learners will actually enter:

- **The currency is KES**, and prices are real: a bedsitter deposit, a Bajaj Boxer boda-boda (~KES 80,000), a Toyota Fielder for the taxi business, a quarter-acre in Ruiru, mama-mboga stock, an NSE blue-chip position.
- **The institutions are recognisable**: M-Pesa-style mobile money framing, SACCO and chama structures, bank savings and loan products, insurance obligations, the NSE, HELB and KRA in the AI coach's world.
- **The hustles are real hustles**: the Car Yard's vehicles come with *gig income vs. loan repayment* arithmetic — a school van earns KES 75,000/month against its financing, netting KES 51,000 — the exact spreadsheet a real matatu investor runs.
- **The financial physics are real**: amortised loan repayments, risk-adjusted credit pricing (better score → cheaper products), asset appreciation with volatility, transaction fees on sales, running costs on ownership, income that arrives on payday rhythm, and emergencies that don't ask permission.
- **The career ladder is real**: qualification before employment, performance before promotion, and the discovered chain of *learn → earn → own*.
- **The events are culturally true**: the 51 life events and 40+ scenario trees are written from Kenyan life — harambees, school fees pressure, mobile-loan temptation, market days, peer spending pressure.

A learner who plays PesaQuest seriously for a school term has rehearsed — in compressed, safe time — the financial decade that awaits them: first income, first budget, first debt, first default temptation, first investment, first group venture, first property ambition.

---

## 11. Entertainment & Fun

Would students *choose* to immerse themselves in it? The game was built assuming they must — because unchosen education games die in the drawer:

- **It looks and feels like a game students already play**: a living city map, a walking character, quests, XP, levels, unlocks, celebrations, sound. The financial literacy is load-bearing but invisible as "syllabus".
- **Aspiration is the fantasy.** The core power-fantasy of PesaQuest is one Kenyan youth genuinely hold: *making it* — the first phone, the first job, the boda that pays for itself, the plot in Ruiru, Millionaire Mind on the journey bar. The game lets them rehearse the dream mechanically.
- **The voice is theirs.** Tips and copy are written in Kenyan youth register — warm, wry, street-smart, never preachy. (*"The path isn't direct, but it's real."*)
- **Drama is built in**: a deal maturing tonight, rent due in three game days, a chama vote splitting 2–2, the red pulse of a quest in its final stretch, the gamble of the risky deal. Financial life, honestly simulated, is *inherently* dramatic — the game just doesn't sand that off.
- **Fun World makes fun canonical.** Leisure is a real system with a real function (mood), so enjoying yourself is part of playing well — a rare and healthy message.
- **Social play**: chama politics with friends, forum bragging rights, teacher challenges, and comparing chapters/badges give the game a life between sessions.

The result is a game with a **retention engine** (streaks, cycles, deadlines, unfinished business in a living world) and a **meaning engine** (every session leaves the player measurably more financially capable) — the combination that makes students return without being told to.

---

## 12. Reliability — A Solid Product

PesaQuest is a mature, hardened platform, not a prototype:

- **20+ development phases shipped and documented**, with an internal build tracker, iterative user-feedback rounds, and a maintained changelog. Systems have been refined through real play, not just planned.
- **Proven, boring-in-the-best-way technology**: Laravel 12 (PHP) with a MySQL database — the most widely deployed, hosting-friendly web stack in the region. It runs on standard shared hosting; no exotic infrastructure is required.
- **Engineered for consistency**: all economic settlements (salaries, bills, interest, asset income) run inside database transactions and settle chronologically, so a player's balance is always coherent; the simulation is deterministic and auditable.
- **Performance-optimised**: database indexing, query-deduplication, settings caching, and image compression (the world map is a 508 KB optimised asset, down from 3.7 MB) keep the game fast on low-bandwidth connections and modest school devices. Audio is procedurally generated in the browser — zero media downloads.
- **Defensive by design**: schema guards, capped catch-up processing, idempotent content seeding, and graceful handling of edge states (new players, returning players, expired invitations) mean the game degrades gently rather than breaking.
- **Mobile-first and responsive** across phones, tablets, and desktops, with touches like screen wake-lock during play sessions.
- **Operationally self-service**: the entire content library — scenarios, quests, assets, loans, deals, activities, milestones, quiz questions — is managed through an internal content studio, so the curriculum team can update, localise, and extend content without engineering work or downtime.

---

## 13. Deployment Readiness

PesaQuest is **live-deployable today** and structured for staged mass rollout:

- **Zero-install access**: any browser, any device, one URL. A school computer lab, a student's phone, or a home tablet are all equally valid classrooms.
- **Instant school onboarding**: a school can be created and its access portal issued in minutes; students are added by name — no procurement of devices, apps, or licences per machine.
- **Content pipeline ready**: seeded with 640+ scenario nodes, 18 quests, 42 assets, 25 bills, 51 events, and 21 badges at launch, with the content studio able to grow the library continuously — including content co-developed with curriculum experts.
- **Scales horizontally**: the stack (Laravel/MySQL) scales with ordinary web-hosting upgrades; there is no architectural ceiling before national-scale numbers, and the heaviest computation (offline catch-up) is bounded per player by design.
- **Localisation-ready**: all learner-facing text lives in the content layer, so Kiswahili or other language tracks are a content project, not a rebuild.
- **Low-bandwidth conscious**: compressed assets, no video dependencies, no app-store updates.
- **Governance-ready**: age gating, moderation tooling, school-scoped social spaces, and an auditable credit/decision history per learner give institutions the oversight surface they need.

A realistic national pathway: **pilot cohort of schools → feedback-driven content calibration with KICD → county-level rollout → national availability** — with no re-engineering required between stages, only content and hosting scale.

---

## 14. The Schools Programme

PesaQuest was built to meet schools where they are, and the schools model is deliberately priced to **push financial literacy into classrooms**, not to maximise revenue.

### 14.1 How it works

1. **A school subscribes once** and receives a number of student **seats** plus a private, secure **school portal link**.
2. Through the portal, the school **assigns students to seats by name** — adding and removing students takes seconds, and a seat freed by one student can be given to another. No per-student payment, no per-student paperwork.
3. Every seated student gets **full premium access** to the entire game — all scenarios, districts, quests, careers, assets, and community features — for the life of the subscription.
4. **Teachers get a presence inside the game**: from the portal, a teacher can post **🎯 Teacher Challenges** — pinned tasks that appear on the school's private forum board and notify every student in the school. ("This week: reach a credit score of 650 and post how you did it.")
5. Each school gets a **private school board** in the community forums — visible only to that school's students, ideal for class discussions, challenge submissions, and peer teaching, all within the moderated environment.

### 14.2 Why the school route is dramatically cheaper

Individual access is KES 299/month (or KES 2,499/year). School seats collapse that cost:

| School plan | Seats | Duration | Price | Effective cost per student |
|---|---|---|---|---|
| Starter | 20 | 1 month | KES 3,000 | KES 150 /month (half the individual rate) |
| Uno | 50 | 12 months | KES 15,000 | **KES 300 /year** (vs. KES 2,499 individually — an ~88% reduction) |
| Small School | 120 | 12 months | KES 20,000 | **KES 167 /year** (~93% reduction) |

At scale, a student's entire year of financial literacy gaming costs a school **less than a single exercise book budget line** — a pricing posture chosen deliberately, as an NGO-backed platform, to remove cost as a barrier to financial literacy in Kenyan schools. Larger and custom institutional plans follow the same per-seat economics.

### 14.3 What a teacher sees

While this document focuses on gameplay, it is worth noting what the model gives an educator *pedagogically*: a class of students whose **chapters, credit scores, badges, and milestone timelines are behavioural evidence** of financial competencies; a challenge channel to direct that behaviour toward the current classroom topic; and a private discussion board where students articulate their reasoning — reflection, the final step of the experiential cycle, happening in the students' own words.

---

## 15. Closing Summary

PesaQuest's answer to each evaluation area, in one line each:

1. **Playability & replayability** — a zero-install browser game with branching stories, eight careers, a randomised living economy, and a net-worth endgame that never runs out of road.
2. **Education & experiential learning** — the complete Kolb cycle as a game loop: learners *do*, *see the consequence*, *read the principle*, and *try again*, with behaviour — not quizzes — as the assessment.
3. **Moral fabric** — integrity, diligence, moderation, and community are the mathematically optimal strategies; mistakes teach and are always redeemable.
4. **Safety for minors** — virtual money only, minimal data, no private messaging, name-only social surfaces, hard age-content boundaries, moderated and school-scoped community spaces.
5. **Engagement** — daily, weekly, and monthly loops (streaks, salaries, deadlines, unlocks, chama life) in which every reward is earned through a financial behaviour.
6. **Practicality** — a faithful miniature of the Kenyan economy: KES prices, chamas and SACCOs, boda economics, amortised loans, credit-priced products, payday rhythms, and Kenyan life events.
7. **Entertainment** — the aspiration fantasy Kenyan youth actually hold ("making it"), told in their own voice, with built-in drama, celebration, and social play.
8. **Reliability** — 20+ shipped development phases on a proven Laravel/MySQL stack, transaction-safe economics, performance-tuned for low-bandwidth school devices.
9. **Readiness** — live today, minutes to onboard a school, content-extensible without engineering, localisation-ready, and priced (from ~KES 167 per student per year) to make national adoption a decision, not a budget battle.

PesaQuest exists because the cheapest place for a young Kenyan to make their first financial mistakes is inside a game — and the best time is before their first payslip. We would be honoured to walk KICD through a live playthrough and to co-develop curriculum-aligned content for Kenyan classrooms.

---

*Document ends. For a live demonstration or pilot discussion, contact the Moski team.*
