<?php

/*
|--------------------------------------------------------------------------
| Pesa City NPC cast + contract voice packs
|--------------------------------------------------------------------------
| The humour engine. Contracts and factory quests are assembled from two
| independent banks so a small amount of writing composes into thousands
| of distinct-feeling quests:
|
|   NPC       → greeting + signoff (personality wrapper)
|   Archetype → title + pitch + lesson + objective labels (the substance)
|
| Copy is written per age band: '8-12', '13-17', 'adult' (18-25 and 26+).
| Placeholders: {name} {amount} {days} {n} {course} {job} {employer} {chama}
| Composed by App\Services\PesaVoice.
*/

return [

    'bands' => ['8-12' => '8-12', '13-17' => '13-17', '18-25' => 'adult', '26+' => 'adult'],

    // ── The cast ──────────────────────────────────────────────────────────
    'npcs' => [

        'mama_pesa' => [
            'name'    => 'Mama Pesa',
            'emoji'   => '🧺',
            'role'    => 'Market mama & landlady of Pesa City',
            'domains' => ['bill_boss', 'clean_slate', 'stash_it', 'balance_builder', 'payday_pro'],
            'greetings' => [
                '8-12'  => ['Mama Pesa has spotted you, {name}! 👀', 'Mama Pesa says: come here, {name}!', '{name}! Mama Pesa needs a smart helper today.'],
                '13-17' => ['Wewe {name}! Mama Pesa amekuona 👀', 'Mama Pesa side-eye loading… yes {name}, it\'s about YOUR money.', 'Aki {name}, Mama Pesa has been asking about you.'],
                'adult' => ['Mama Pesa doesn\'t chase people — but for you, {name}, she made an exception.', '{name}. Mama Pesa. We need to talk about your money.', 'Mama Pesa closed her shop early just to tell you this, {name}.'],
            ],
            'signoffs' => [
                '8-12'  => ['Mama Pesa is SO proud of you! 🎉', 'Mama Pesa is telling the whole market about you!', 'You did it! Mama Pesa saved you a mandazi. 🥯'],
                '13-17' => ['Mama Pesa respects the hustle. Big W. 🏆', 'Even Mama Pesa can\'t argue with that. Cleared!', 'Mama Pesa just told the whole market you\'re serious. Usilegee.'],
                'adult' => ['Mama Pesa nodded. That\'s the highest honour in this city.', 'Noted in Mama Pesa\'s good book — a very short book.', 'Mama Pesa: "Finally. Someone who listens."'],
            ],
        ],

        'bazu' => [
            'name'    => 'Bazu',
            'emoji'   => '⚡',
            'role'    => 'Your hustler cousin — always has "a small deal"',
            'domains' => ['hustle_harder', 'get_hired', 'payday_pro', 'balance_builder'],
            'greetings' => [
                '8-12'  => ['Psst, {name}! Bazu has a plan! ⚡', 'Bazu zooms in: "{name}! Big chance today!"', 'Your cousin Bazu is jumping up and down about something…'],
                '13-17' => ['Bazu: "Niaje {name}, kuna mpango…" 👀⚡', 'Bazu has texted you 7 times. It\'s about a come-up.', '"{name}, si you\'re the sharp one? Prove it." — Bazu'],
                'adult' => ['Bazu appears from nowhere, as always: "{name}, opportunity. Don\'t overthink."', 'Bazu: "I only bring you the good ones, {name}."', 'Bazu, lowering his voice for no reason: "{name}. Listen."'],
            ],
            'signoffs' => [
                '8-12'  => ['Bazu does his happy dance! ⚡🕺', '"See?! Team {name}!" — Bazu', 'Bazu high-fives you so hard his cap falls off.'],
                '13-17' => ['Bazu: "Ayyy hii ndio energy!" ⚡', 'Bazu is already bragging about you kwa base.', '"Sasa hivyo ndio kunahustliwa." — Bazu, impressed'],
                'adult' => ['Bazu: "I knew it. Fifty-fifty next time?" (Say no.)', 'Bazu salutes. He learned that from a movie.', '"Smooth. Very smooth." — Bazu'],
            ],
        ],

        'madam_rita' => [
            'name'    => 'Madam Rita',
            'emoji'   => '📋',
            'role'    => 'Career mentor — sharp suit, sharper questions',
            'domains' => ['study_up', 'get_hired', 'level_head', 'payday_pro'],
            'greetings' => [
                '8-12'  => ['Madam Rita opens her big book: "Ah, {name}. Right on time."', 'Madam Rita has a gold star with your name on it, {name}… almost.', '"{name}! My favourite student. Well — potentially." — Madam Rita'],
                '13-17' => ['Madam Rita checks her list: "{name}… interesting file."', 'Madam Rita: "Talent is common, {name}. Follow-through is rare."', '"I don\'t do motivational speeches, {name}. I do assignments." — Madam Rita'],
                'adult' => ['Madam Rita slides a folder across the desk: "{name}. Your move."', 'Madam Rita: "I\'ve seen your file. You\'re capable of more."', '"Careers are built on Tuesdays, {name}. Ordinary days. Like today." — Madam Rita'],
            ],
            'signoffs' => [
                '8-12'  => ['Madam Rita gives you the gold star! ⭐', '"Excellent work, {name}. I expect nothing less next time."', 'Madam Rita writes your name on the Wall of Great Students!'],
                '13-17' => ['Madam Rita: "Noted. Impressive." That\'s basically a trophy.', 'Madam Rita almost smiled. ALMOST.', '"You may go far, {name}. Don\'t let it get to your head."'],
                'adult' => ['Madam Rita updates your file: "exceeds expectations."', '"Good. Now we raise the bar." — Madam Rita', 'Madam Rita shakes your hand. Firmly. You\'ll feel it tomorrow.'],
            ],
        ],

        'mzee_kobe' => [
            'name'    => 'Mzee Kobe',
            'emoji'   => '🐢',
            'role'    => 'The old tortoise — slow money, long memory',
            'domains' => ['first_brick', 'circle_up', 'worth_climb', 'stash_it', 'open_account'],
            'greetings' => [
                '8-12'  => ['Mzee Kobe wakes up sloooowly: "Ah… {name}… good."', 'Mzee Kobe: "Young {name}… come, sit. This is important."', 'The wise tortoise blinks at you: "I have a task, little one."'],
                '13-17' => ['Mzee Kobe: "{name}… fast money runs. Slow money walks — and arrives."', '"I have watched this city for 60 years, {name}. Listen." — Mzee Kobe', 'Mzee Kobe, chewing a leaf very slowly: "Patience test, {name}."'],
                'adult' => ['Mzee Kobe: "{name}. The best time to plant a tree was 20 years ago. The second best time — you know the rest."', '"Wealth whispers, {name}. Here is what it\'s saying." — Mzee Kobe', 'Mzee Kobe taps his shell: "Assets, {name}. Everything else is weather."'],
            ],
            'signoffs' => [
                '8-12'  => ['Mzee Kobe smiles the slowest, happiest smile. 🐢', '"Good… very good… now we wait and grow." — Mzee Kobe', 'Mzee Kobe tells the other tortoises. They\'ll know by Friday.'],
                '13-17' => ['Mzee Kobe nods once. In tortoise, that\'s a standing ovation.', '"You move slow when it matters. Rare skill." — Mzee Kobe', 'Mzee Kobe adds your name to his shell. Permanent record.'],
                'adult' => ['Mzee Kobe: "The compound interest of good habits. Well done."', '"I have seen many rush past me. You\'ll pass them all." — Mzee Kobe', 'Mzee Kobe closes his eyes, satisfied. That\'s the review.'],
            ],
        ],

        'shiko' => [
            'name'    => 'Shiko',
            'emoji'   => '📣',
            'role'    => 'Pesa City\'s social battery — knows EVERYONE',
            'domains' => ['circle_up', 'squad_up', 'street_voice', 'good_vibes', 'lucky_spin'],
            'greetings' => [
                '8-12'  => ['Shiko waves BOTH hands: "{name}!! Over here!!"', 'Shiko: "OMG {name}, I was JUST talking about you!"', 'Shiko appears with news. Shiko always has news.'],
                '13-17' => ['Shiko: "{name}!! Wewe umepotea wapi?" 📣', '"Breaking news, {name}, and YOU\'RE the headline." — Shiko', 'Shiko slides into your DMs. In person. That\'s her style.'],
                'adult' => ['Shiko: "{name}! Perfect timing — I need exactly your energy."', '"Networking is just friendship with a business plan, {name}." — Shiko', 'Shiko, already mid-conversation: "—and that\'s why I need you, {name}."'],
            ],
            'signoffs' => [
                '8-12'  => ['Shiko announces it to EVERYONE. You\'re famous now! 📣', '"Yes yes YES! That\'s my friend!" — Shiko', 'Shiko does the celebration whistle. Birds join in.'],
                '13-17' => ['Shiko has already posted about it. 3 fire emojis. 🔥🔥🔥', '"Hii ni content! Proud of you!" — Shiko', 'Shiko tells the group chat. The group chat approves.'],
                'adult' => ['Shiko: "This is why I keep you on speed dial."', 'Shiko starts a slow clap. It catches on. It always does.', '"Community points: maxed. Respect." — Shiko'],
            ],
        ],
    ],

    // ── Contract archetypes ───────────────────────────────────────────────
    // Each maps to a measurable metric (see ContractService::METRICS).
    // 'labels' are the objective lines shown in the contract card — written
    // to feel like an errand from the NPC, never like "trigger: course_complete".
    'archetypes' => [

        'study_up' => [
            'icon'   => '📚',
            'metric' => 'courses_completed',
            'titles' => [
                '8-12'  => ['Brain Power!', 'The Learning Mission', 'Smart Cookie Challenge'],
                '13-17' => ['Ujuzi Loading…', 'Certificate Chase', 'Big Brain Season'],
                'adult' => ['Invest In The Mirror', 'The Qualification Play', 'Skills Pay Bills'],
            ],
            'pitches' => [
                '8-12'  => ['Finish {n} course{s} at Skill Campus — your brain gets stronger every time!', 'There\'s a course with your name on it. Go finish {n}!'],
                '13-17' => ['Clear {n} course{s} before this expires. Certificates open doors salary can\'t.', 'Somea kidogo — {n} course{s}. Future you says thanks.'],
                'adult' => ['Complete {n} course{s}. Every certificate here unlocks a payslip somewhere.', 'The syllabus is short and the return is long: {n} course{s}.'],
            ],
            'labels' => ['Finish {n} course{s} at Skill Campus 📚', 'Graduate from {n} course{s}', 'Earn {n} new certificate{s}'],
            // Used by the Quest Factory when a SPECIFIC course is named
            'targeted_pitches' => [
                '8-12'  => ['A brand-new course just opened: "{course}"! Finish it and show everyone how smart you are!', 'Psst — "{course}" has your name on it. Go learn it!'],
                '13-17' => ['New on campus: "{course}". Finish it — certificates open doors salary can\'t.', '"{course}" just dropped. First graduates get first pick of the jobs.'],
                'adult' => ['New course available: "{course}". Every certificate here unlocks a payslip somewhere.', '"{course}" is open for enrolment. Short syllabus, long return.'],
            ],
            'targeted_labels' => ['Complete the "{course}" course 📚', 'Graduate from "{course}"'],
            'lessons' => [
                '8-12'  => ['Learning first, earning second — that\'s the secret order!'],
                '13-17' => ['Skills are the one asset nobody can tax, steal, or repossess.'],
                'adult' => ['Education compounds like interest — every course raises your ceiling.'],
            ],
        ],

        'get_hired' => [
            'icon'   => '💼',
            'metric' => 'jobs_started',
            'titles' => [
                '8-12'  => ['Job Hunter!', 'You\'re Hired!', 'The Big Interview'],
                '13-17' => ['Employed Behaviour', 'CV Yenye Nguvu', 'Position Secured'],
                'adult' => ['The Offer Letter', 'Gainful Pursuits', 'Onto The Payroll'],
            ],
            'pitches' => [
                '8-12'  => ['Somewhere in Pesa City, a boss is looking for YOU. Get hired for {n} job{s}!', 'Time to work! Land {n} job{s} at the Opportunity Hub.'],
                '13-17' => ['The Opportunity Hub has openings. Secure {n}. Broke is a phase, not a personality.', 'Get hired {n} time{s}. Payslips > promises.'],
                'adult' => ['Land {n} position{s}. Income is the engine — everything else is bodywork.', 'There are open roles that match your certificates. Convert {n} into income.'],
            ],
            'labels' => ['Get hired for {n} job{s} at the Opportunity Hub 💼', 'Land {n} new position{s}', 'Start {n} new job{s} or gig{s}'],
            // Used by the Quest Factory when a SPECIFIC job is named
            'targeted_pitches' => [
                '8-12'  => ['{employer} is hiring a {job}! Could that be YOU? Go get it!', 'Job alert! {employer} needs a {job} — show them what you\'ve got!'],
                '13-17' => ['{employer} inatafuta {job}. The salary won\'t chase you — go get it.', 'Opening: {job} at {employer}. Qualify, apply, secure.'],
                'adult' => ['{employer} has an opening: {job}. Convert your certificate into income.', 'A {job} position at {employer}. Your move.'],
            ],
            'targeted_labels' => ['Get hired as {job} at {employer} 💼', 'Land the {job} position'],
            'lessons' => [
                '8-12'  => ['A job turns your skills into shillings!'],
                '13-17' => ['Income first. You can\'t budget zero.'],
                'adult' => ['Cashflow is oxygen — secure it before you optimise anything else.'],
            ],
        ],

        'hustle_harder' => [
            'icon'   => '⚡',
            'metric' => 'gigs_completed',
            'titles' => [
                '8-12'  => ['Side Quest: Side Hustle!', 'Quick Job, Quick Cash', 'The Speedy Gig'],
                '13-17' => ['Side Hustle Szn', 'Mchongo Wa Leo', 'Gig Economy 101'],
                'adult' => ['The Side Income Files', 'Plural Income Society', 'Gig Discipline'],
            ],
            'pitches' => [
                '8-12'  => ['Bazu found {n} quick gig{s}! Finish the work, collect the coins!', 'Fast job alert! Deliver {n} gig{s} before the deadline!'],
                '13-17' => ['Kuna gig inakuita. Deliver {n} and collect. Hustle sio kelele — ni results.', 'One stream is a tightrope. Add {n} gig{s} this week.'],
                'adult' => ['Complete {n} freelance gig{s}. Side income is how salaries get backup dancers.', 'Deliver {n} gig{s} — lumpy money still folds the same.'],
            ],
            'labels' => ['Complete {n} freelance gig{s} ⚡', 'Deliver {n} gig{s} and collect the pay', 'Finish {n} side hustle{s}'],
            'lessons' => [
                '8-12'  => ['Small jobs add up to big pockets!'],
                '13-17' => ['Many small streams beat one dry river.'],
                'adult' => ['Diversified income is self-insurance.'],
            ],
        ],

        'stash_it' => [
            'icon'   => '🏦',
            'metric' => 'savings_balance',
            'titles' => [
                '8-12'  => ['Piggy Bank Power!', 'The Secret Stash', 'Coin Castle'],
                '13-17' => ['Stash Gang', 'Bank Yako Inakula', 'Untouchable Funds'],
                'adult' => ['Pay Yourself First', 'The Quiet Account', 'Fortress Fund'],
            ],
            'pitches' => [
                '8-12'  => ['Hide Ksh {amount} in your bank where sweets can\'t find it! It even GROWS in there!', 'Mission: stash Ksh {amount} in savings. The bank pays YOU for waiting!'],
                '13-17' => ['Move Ksh {amount} into savings. Money you can see is money you\'ll spend — hide it from yourself.', 'Stash Ksh {amount}. The 8% interest works night shift while you sleep.'],
                'adult' => ['Get your savings up by Ksh {amount}. An emergency fund is the cheapest peace of mind on the market.', 'Ksh {amount} into the quiet account. Interest rewards the patient.'],
            ],
            'labels' => ['Grow your bank savings by Ksh {amount} 🏦', 'Stash Ksh {amount} where spending can\'t reach it', 'Add Ksh {amount} to your savings'],
            'lessons' => [
                '8-12'  => ['Saved money grows all by itself — like a plant you water once!'],
                '13-17' => ['Save first, spend what remains — never the reverse.'],
                'adult' => ['Savings buy you the power to say no — to bad jobs, bad loans, bad days.'],
            ],
        ],

        'balance_builder' => [
            'icon'   => '💰',
            'metric' => 'wallet_balance',
            'titles' => [
                '8-12'  => ['Grow The Gold!', 'Wallet Workout', 'The Coin Climb'],
                '13-17' => ['Pockets On Deep', 'Mfuko Season', 'Liquid Goals'],
                'adult' => ['Working Capital', 'The Buffer Build', 'Cash Position'],
            ],
            'pitches' => [
                '8-12'  => ['Get your wallet up by Ksh {amount} — earn it, don\'t just wish it!', 'Coin challenge! Grow your cash by Ksh {amount}!'],
                '13-17' => ['End this contract Ksh {amount} richer than you started. Earn more, leak less.', 'Wallet check: +Ksh {amount} by the deadline. Hakuna shortcuts, kuna paydays.'],
                'adult' => ['Grow your cash position by Ksh {amount}. Collect pay, plug leaks, mind the bills.', 'Net +Ksh {amount} in the wallet. Simple to say; the discipline is the product.'],
            ],
            'labels' => ['Grow your wallet by Ksh {amount} 💰', 'End up Ksh {amount} richer than today', 'Increase your cash by Ksh {amount}'],
            'lessons' => [
                '8-12'  => ['Money grows when earning beats spending!'],
                '13-17' => ['Track it or lose it — cash leaks through invisible holes.'],
                'adult' => ['A cash buffer turns emergencies back into inconveniences.'],
            ],
        ],

        'bill_boss' => [
            'icon'   => '🧾',
            'metric' => 'bills_paid',
            'titles' => [
                '8-12'  => ['Bill Squasher!', 'The On-Time Hero', 'Beat The Bill!'],
                '13-17' => ['Bills Zii Stress', 'Due Date Assassin', 'Landlord Repellent'],
                'adult' => ['The Punctual Payer', 'Obligations Dept.', 'Zero Overdue Club'],
            ],
            'pitches' => [
                '8-12'  => ['Mama Pesa is watching the notice board! Pay {n} bill{s} before they turn red!', 'Bills are sneaky — squash {n} of them before the due date!'],
                '13-17' => ['Pay {n} bill{s} on time. Your credit score has a memory like a grudge.', 'Clear {n} bill{s} kabla deadline. Overdue ni aibu ya wallet.'],
                'adult' => ['Settle {n} bill{s} on schedule. Reliability is a currency — spend it wisely.', 'Pay {n} bill{s} before they age. Late fees are interest you pay for forgetting.'],
            ],
            'labels' => ['Pay {n} bill{s} from Life HQ 🧾', 'Settle {n} bill{s} before they go overdue', 'Clear {n} bill{s} on time'],
            'lessons' => [
                '8-12'  => ['Paying on time keeps your name golden!'],
                '13-17' => ['Your credit score is your money reputation — guard it.'],
                'adult' => ['On-time payment history is the backbone of every credit score.'],
            ],
        ],

        'clean_slate' => [
            'icon'   => '🧹',
            'metric' => 'overdue_cleared',
            'titles' => [
                '8-12'  => ['The Big Clean-Up!', 'Red Alert Rescue', 'Debt Buster'],
                '13-17' => ['Clear The Red', 'Damage Control', 'Fresh Start Loading'],
                'adult' => ['The Reckoning (Small)', 'Arrears Amnesty', 'Back To Zero'],
            ],
            'pitches' => [
                '8-12'  => ['Uh oh — something\'s overdue! Clean it up and make Mama Pesa smile again!', 'Rescue mission: clear {n} overdue bill{s} before they grow teeth!'],
                '13-17' => ['You have red on your board. Clear {n} overdue bill{s} — every game day it waits, your score bleeds.', 'Overdue vibes ni expensive. Handle {n} sasa hivi.'],
                'adult' => ['Clear {n} overdue item{s}. Old debts don\'t age like wine — they age like milk.', 'The fastest credit repair is paying what\'s already late. {n} item{s}.'],
            ],
            'labels' => ['Clear {n} overdue bill{s} 🧹', 'Get your board back to zero overdue', 'Rescue {n} bill{s} from the red zone'],
            'lessons' => [
                '8-12'  => ['Fixing a mistake fast makes it small!'],
                '13-17' => ['A problem paid late still beats a problem ignored.'],
                'adult' => ['Triage debts by damage — overdue first, always.'],
            ],
        ],

        'payday_pro' => [
            'icon'   => '🧾',
            'metric' => 'paydays_collected',
            'titles' => [
                '8-12'  => ['Payday Catcher!', 'Report For Duty!', 'The Salary Run'],
                '13-17' => ['Secure The Bag', 'Attendance 100%', 'Payslip Collector'],
                'adult' => ['The Reliable One', 'Clock In, Cash Out', 'Attendance Dividend'],
            ],
            'pitches' => [
                '8-12'  => ['Your boss is holding your money! Report to work {n} time{s} and collect it!', 'Payday alert! Show up and collect your pay {n} time{s}!'],
                '13-17' => ['Report to work and bank your pay {n} time{s}. Money uncollected is money on vacation.', 'Collect {n} payslip{s}. Employer akikuona hukuji, kazi inaenda.'],
                'adult' => ['Bank {n} paycheck{s} via Report to Work. Your wages stack, but your employer counts absences.', 'Collect pay {n} time{s}. Attendance is the quietest career strategy.'],
            ],
            'labels' => ['Report to Work and collect pay {n} time{s} 💼', 'Bank {n} payslip{s}', 'Collect your wages {n} time{s}'],
            'lessons' => [
                '8-12'  => ['Money you earn still needs collecting!'],
                '13-17' => ['Show up — jobs are kept on ordinary days.'],
                'adult' => ['Consistency compounds: paychecks, trust, and promotions all follow attendance.'],
            ],
        ],

        'first_brick' => [
            'icon'   => '🏗️',
            'metric' => 'assets_owned',
            'titles' => [
                '8-12'  => ['My First Treasure!', 'The Thing-That-Pays', 'Owner Alert!'],
                '13-17' => ['Asset Szn', 'Buy Once, Earn Forever', 'Ka-Investment'],
                'adult' => ['The First Brick', 'Things That Pay Rent', 'Ownership Society'],
            ],
            'pitches' => [
                '8-12'  => ['Buy {n} thing{s} from the marketplace that pay YOU money. Magic? No — assets!', 'Mzee Kobe says: own {n} thing{s} that work while you play!'],
                '13-17' => ['Cop {n} asset{s}. Phones lose value, assets bring it back — choose your team.', 'Buy {n} income-generating asset{s}. Let your money get a job too.'],
                'adult' => ['Acquire {n} asset{s}. The goal isn\'t owning things — it\'s owning things that pay.', 'Add {n} asset{s} to the portfolio. Salaries stop; assets don\'t.'],
            ],
            'labels' => ['Buy {n} income-earning asset{s} 🏗️', 'Own {n} new asset{s} from the marketplace', 'Add {n} asset{s} to your empire'],
            'lessons' => [
                '8-12'  => ['Assets are things that put money IN your pocket!'],
                '13-17' => ['Buy things that pay you back — that\'s the whole cheat code.'],
                'adult' => ['Assets convert income into wealth; spending converts it into memories and receipts.'],
            ],
        ],

        'circle_up' => [
            'icon'   => '🤝',
            'metric' => 'chama_contributions',
            'titles' => [
                '8-12'  => ['Team Money!', 'The Circle Of Coins', 'Better Together'],
                '13-17' => ['Chama Things', 'The Circle Is Sacred', 'Group Economics'],
                'adult' => ['The Cooperative Play', 'Circle Capital', 'Harambee Dividend'],
            ],
            'pitches' => [
                '8-12'  => ['Join hands! Make {n} chama contribution{s} — money grows faster in a team!', 'Your chama is counting on you — contribute {n} time{s}!'],
                '13-17' => ['Make {n} chama contribution{s}. The circle only works if YOU show up.', 'Chama check: {n} contribution{s}. Group money is grown money.'],
                'adult' => ['Contribute to your chama {n} time{s}. Cooperative capital built this country.', 'Honour the circle: {n} contribution{s}. Reliability is your share price.'],
            ],
            'labels' => ['Make {n} chama contribution{s} 🤝', 'Pay into your chama {n} time{s}', 'Keep your chama promise — {n} contribution{s}'],
            'lessons' => [
                '8-12'  => ['A team of savers beats a lonely spender!'],
                '13-17' => ['Trust is the interest rate of friendship.'],
                'adult' => ['Chamas work on one technology: people who show up.'],
            ],
        ],

        'worth_climb' => [
            'icon'   => '📈',
            'metric' => 'net_worth',
            'titles' => [
                '8-12'  => ['The Big Number!', 'Climb The Money Mountain', 'Richer Every Day'],
                '13-17' => ['Net Worth Loading…', 'Portfolio Glow-Up', 'Quiet Flex'],
                'adult' => ['The Long Game', 'Balance Sheet Season', 'Compounding Quietly'],
            ],
            'pitches' => [
                '8-12'  => ['Grow your total treasure by Ksh {amount} — save it, earn it, grow it!', 'Mzee Kobe\'s challenge: make your Big Number rise by Ksh {amount}!'],
                '13-17' => ['Raise your net worth by Ksh {amount}. Cash + savings + assets − debts. Move the whole board.', 'Net worth +Ksh {amount}. Slow flex > loud broke.'],
                'adult' => ['Lift your net worth by Ksh {amount}. Income is a story; net worth is the audit.', 'Grow the balance sheet by Ksh {amount}. Every line item is a decision.'],
            ],
            'labels' => ['Grow your net worth by Ksh {amount} 📈', 'Raise the Big Number by Ksh {amount}', 'Add Ksh {amount} to your total worth'],
            'lessons' => [
                '8-12'  => ['Your real score is everything you own minus everything you owe!'],
                '13-17' => ['Net worth doesn\'t care what you posted — only what you kept.'],
                'adult' => ['Track net worth monthly; it\'s the only scoreboard that can\'t be gamed.'],
            ],
        ],

        'level_head' => [
            'icon'   => '⭐',
            'metric' => 'xp_points',
            'titles' => [
                '8-12'  => ['XP Explosion!', 'Level-Up Mission', 'Star Collector'],
                '13-17' => ['Grind Arc', 'XP Farming (Legal)', 'Main Character Energy'],
                'adult' => ['Deliberate Practice', 'The XP Ledger', 'Progress On Purpose'],
            ],
            'pitches' => [
                '8-12'  => ['Earn {amount} XP doing ANYTHING awesome — courses, quests, wins!', 'Madam Rita\'s dare: collect {amount} XP before time runs out!'],
                '13-17' => ['Stack {amount} XP. Everything counts — study, work, community. Just move.', 'Farm {amount} XP. The grind is boring; the levels are not.'],
                'adult' => ['Earn {amount} XP across the city. Progress rewards the active, not the anxious.', 'Bank {amount} XP. Momentum is a habit wearing a costume.'],
            ],
            'labels' => ['Earn {amount} XP anywhere in Pesa City ⭐', 'Stack {amount} experience points', 'Collect {amount} XP before the deadline'],
            'lessons' => [
                '8-12'  => ['Doing things is how you grow — sitting still earns nothing!'],
                '13-17' => ['Consistency beats intensity. Small daily wins stack.'],
                'adult' => ['Activity is the raw material of progress.'],
            ],
        ],

        'squad_up' => [
            'icon'   => '👥',
            'metric' => 'friends_count',
            'titles' => [
                '8-12'  => ['Friend Finder!', 'Team Up!', 'The Buddy Mission'],
                '13-17' => ['Squad Assembly', 'Networking (But Fun)', 'Link Up'],
                'adult' => ['Social Capital', 'The Network Effect', 'Allies & Assets'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko says friends make everything better! Add {n} friend{s}!', 'Team up! Make {n} new friend{s} in Pesa City!'],
                '13-17' => ['Add {n} friend{s}. Chamas, loans, leaderboards — everything here is better with a squad.', 'Link up na {n} player{s}. Your network ni net worth ya baadaye.'],
                'adult' => ['Connect with {n} player{s}. Every chama, every P2P loan, starts with a handshake.', 'Grow your circle by {n}. Opportunity travels through people.'],
            ],
            'labels' => ['Make {n} new friend{s} 👥', 'Add {n} player{s} to your circle', 'Grow your squad by {n}'],
            'lessons' => [
                '8-12'  => ['Good friends help your money grow safely!'],
                '13-17' => ['Choose your circle — you\'ll trade with them for life.'],
                'adult' => ['Social capital pays dividends money can\'t buy.'],
            ],
        ],

        'street_voice' => [
            'icon'   => '🗣️',
            'metric' => 'forum_posts',
            'titles' => [
                '8-12'  => ['Speak Up!', 'The Town Crier', 'Share Your Story!'],
                '13-17' => ['Mic Check', 'Hot Take Loading', 'Forum Famous'],
                'adult' => ['The Contributor', 'Signal, Not Noise', 'Community Dividend'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko wants to hear from you! Post or reply {n} time{s} on the forums!', 'Share a money tip or ask a question — {n} post{s} on the town board!'],
                '13-17' => ['Drop {n} post{s} or repl{ies} on the forums. Teach one thing, learn three.', 'Sema kitu kwa forums — {n} time{s}. Karma zinajenga rep.'],
                'adult' => ['Contribute {n} post{s} or repl{ies}. Communities pay teachers in trust.', 'Add your voice {n} time{s}. Explaining money is how you master it.'],
            ],
            'labels' => ['Post or reply on the forums {n} time{s} 🗣️', 'Join {n} conversation{s} at Pesa Forums', 'Share {n} thought{s} with the community'],
            'lessons' => [
                '8-12'  => ['Teaching a friend makes YOU smarter too!'],
                '13-17' => ['If you can explain it, you actually understand it.'],
                'adult' => ['Writing about money exposes what you haven\'t figured out yet.'],
            ],
        ],

        'open_account' => [
            'icon'   => '🔑',
            'metric' => 'savings_balance',
            'titles' => [
                '8-12'  => ['Your Very Own Vault!', 'The Key Ceremony', 'First Account Day!'],
                '13-17' => ['Account Activated', 'Official Money Adult', 'The Vault Unlock'],
                'adult' => ['The Opening Move', 'Account Number One', 'Where Money Sleeps Safely'],
            ],
            'pitches' => [
                '8-12'  => ['Open your very own savings account — a treasure vault with YOUR name on it!', 'Every hero needs a base. Open a savings account and give your coins a home!'],
                '13-17' => ['Open a savings account. A wallet is a hallway; an account is a room with a lock.', 'Unlock your savings account — it\'s the first flex nobody sees and everybody feels.'],
                'adult' => ['Open your savings account. Every financial plan ever written starts on this exact line.', 'One account, zero excuses. Open it — the interest starts working the same day.'],
            ],
            'labels' => ['Open your bank savings account 🔑', 'Unlock your first savings account', 'Give your money a safe home 🏦'],
            'lessons' => [
                '8-12'  => ['Money with a home grows; money in your pocket wanders off!'],
                '13-17' => ['Separating spending money from saving money is the whole trick.'],
                'adult' => ['An account you don\'t touch is the cheapest financial discipline ever invented.'],
            ],
        ],

        'lucky_spin' => [
            'icon'   => '🎡',
            'metric' => 'mood_level',
            'titles' => [
                '8-12'  => ['Spin To Win!', 'The Lucky Wheel Calls', 'Round And Round!'],
                '13-17' => ['Free Spin Energy', 'Wheel Deal', 'Test Your Luck (Safely)'],
                'adult' => ['The Free Lottery', 'Calculated Luck', 'One Spin, Zero Risk'],
            ],
            'pitches' => [
                '8-12'  => ['The Lucky Wheel is spinning today — take your free turn and see what Pesa City gives you!', 'Round and round it goes! Give the wheel one big spin!'],
                '13-17' => ['Spin the wheel. It\'s the only game in town where the house can\'t win.', 'Free spin available. Luck favours those who actually show up.'],
                'adult' => ['Take your spin. Note how good free feels — real gambling never is.', 'One free spin. The only sustainable relationship with luck: when it costs nothing.'],
            ],
            'labels' => ['Spin the Lucky Wheel 🎡', 'Take your free spin', 'Try the wheel of fortune 🎡'],
            'lessons' => [
                '8-12'  => ['Free surprises are fun — but never PAY to gamble!'],
                '13-17' => ['Luck is a bonus, never a plan. Enjoy it when it\'s free.'],
                'adult' => ['If luck charges an entry fee, it\'s not luck — it\'s revenue.'],
            ],
        ],

        'good_vibes' => [
            'icon'   => '😊',
            'metric' => 'mood_level',
            'titles' => [
                '8-12'  => ['Happy Hero!', 'The Smile Mission', 'Recharge!'],
                '13-17' => ['Vibe Maintenance', 'Rest Is Productive', 'Battery Recharge'],
                'adult' => ['The Sustainable Grind', 'Burnout Insurance', 'Mood As Infrastructure'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko noticed you look tired! Get your mood up to {amount} — visit Fun World!', 'Happiness mission: raise your mood to {amount}! Play a little!'],
                '13-17' => ['Get mood to {amount}. Grinding on 40 mood is playing on hard mode for no reason.', 'Recharge to {amount}. Fun World exists — na budget pia inaexist. Balance.'],
                'adult' => ['Raise mood to {amount}. Low mood taxes your salary 10% — rest is a financial decision.', 'Mood to {amount}. You are the asset generating all the others; maintain it.'],
            ],
            'labels' => ['Get your mood up to {amount} 😊', 'Recharge your energy to {amount}', 'Reach {amount} mood — rest a little!'],
            'lessons' => [
                '8-12'  => ['A happy worker earns better — rest is part of the job!'],
                '13-17' => ['Rest isn\'t lazy — it\'s maintenance on your best machine.'],
                'adult' => ['Burnout is the most expensive way to save time.'],
            ],
        ],
    ],
];
