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
                '8-12'  => ['Mama Pesa has spotted you, {name}! 👀', 'Mama Pesa says: come here, {name}!', '{name}! Mama Pesa needs a smart helper today.', 'Mama Pesa waves a wooden spoon: "{name}, come, come!"', 'Mama Pesa saved you the good mandazi, {name} — but first, business.', '{name}! Mama Pesa has been asking the whole market about you.', 'Mama Pesa claps once: "{name} is here! Good, good."'],
                '13-17' => ['Wewe {name}! Mama Pesa amekuona 👀', 'Mama Pesa side-eye loading… yes {name}, it\'s about YOUR money.', 'Aki {name}, Mama Pesa has been asking about you.', 'Mama Pesa: "Umefika finally. Sit, {name}, sit."', '{name}, Mama Pesa heard things. Good things, kwa mabadiliko.', 'Mama Pesa folds her arms: "Explain yourself, {name}. Kidding. Mostly."', '"Huyu ndiye {name}?" Mama Pesa nods at the neighbours, impressed already.'],
                'adult' => ['Mama Pesa doesn\'t chase people — but for you, {name}, she made an exception.', '{name}. Mama Pesa. We need to talk about your money.', 'Mama Pesa closed her shop early just to tell you this, {name}.', 'Mama Pesa sets down her ledger: "{name}. Sit. This won\'t take long."', '"{name}," Mama Pesa says, already counting on her fingers, "we have work."', 'Mama Pesa has heard about your account. She has opinions, {name}.', 'Mama Pesa waves you over without looking up from her books: "{name}. Now."'],
            ],
            'signoffs' => [
                '8-12'  => ['Mama Pesa is SO proud of you! 🎉', 'Mama Pesa is telling the whole market about you!', 'You did it! Mama Pesa saved you a mandazi. 🥯', 'Mama Pesa pinches your cheek — the good kind of pinch!', 'Mama Pesa rings her little bell just for you! 🔔', '"That\'s my helper!" Mama Pesa tells everyone at the kiosk.', 'Mama Pesa packs you extra sweets. You earned it, {name}!'],
                '13-17' => ['Mama Pesa respects the hustle. Big W. 🏆', 'Even Mama Pesa can\'t argue with that. Cleared!', 'Mama Pesa just told the whole market you\'re serious. Usilegee.', '"Sasa ndio mtoto wangu!" Mama Pesa beams at you.', 'Mama Pesa slips you an extra mandazi. No comment needed.', 'Mama Pesa: "Umenishtua kidogo. In a good way, {name}."', 'The whole kiosk claps. Mama Pesa started it, obviously.'],
                'adult' => ['Mama Pesa nodded. That\'s the highest honour in this city.', 'Noted in Mama Pesa\'s good book — a very short book.', 'Mama Pesa: "Finally. Someone who listens."', 'Mama Pesa closes her ledger with a satisfied thud.', '"{name} handled that properly," Mama Pesa tells the next customer.', 'Mama Pesa allows herself a small smile. Rare sighting.', 'Mama Pesa: "You\'ll do. More than most, {name}."'],
            ],
        ],

        'bazu' => [
            'name'    => 'Bazu',
            'emoji'   => '⚡',
            'role'    => 'Your hustler cousin — always has "a small deal"',
            'domains' => ['hustle_harder', 'get_hired', 'payday_pro', 'balance_builder'],
            'greetings' => [
                '8-12'  => ['Psst, {name}! Bazu has a plan! ⚡', 'Bazu zooms in: "{name}! Big chance today!"', 'Your cousin Bazu is jumping up and down about something…', 'Bazu slides in on one shoe: "{name}! You will NOT believe this!"', 'Bazu whispers loudly (he can\'t whisper): "{name}, big news!"', '"{name}!" Bazu is already out of breath from running to find you.', 'Bazu gives you the secret handshake first, business second.'],
                '13-17' => ['Bazu: "Niaje {name}, kuna mpango…" 👀⚡', 'Bazu has texted you 7 times. It\'s about a come-up.', '"{name}, si you\'re the sharp one? Prove it." — Bazu', 'Bazu appears leaning on a wall that wasn\'t there before: "Sasa {name}."', '"{name}, hii ni between us tu." — Bazu, already too loud', 'Bazu: "Nimekuona ukicheza safe lately. Time for a move."', '"Wewe. {name}. Come." Bazu already has the plan half-explained.'],
                'adult' => ['Bazu appears from nowhere, as always: "{name}, opportunity. Don\'t overthink."', 'Bazu: "I only bring you the good ones, {name}."', 'Bazu, lowering his voice for no reason: "{name}. Listen."', 'Bazu checks over his shoulder out of habit: "{name}. This one\'s clean, promise."', '"{name}, you\'re the only one I trust with this," Bazu says, as always.', 'Bazu slides into the seat across from you like he owns the place.', '"Got a minute, {name}?" Bazu\'s already sitting down.'],
            ],
            'signoffs' => [
                '8-12'  => ['Bazu does his happy dance! ⚡🕺', '"See?! Team {name}!" — Bazu', 'Bazu high-fives you so hard his cap falls off.', 'Bazu spins in a circle. He does this when he\'s proud.', '"Knew it! KNEW IT!" Bazu tells absolutely everyone nearby.', 'Bazu gives you a thumbs up, then two thumbs up. Big win.', 'Bazu\'s cap comes off AGAIN. New record.'],
                '13-17' => ['Bazu: "Ayyy hii ndio energy!" ⚡', 'Bazu is already bragging about you kwa base.', '"Sasa hivyo ndio kunahustliwa." — Bazu, impressed', 'Bazu records a video. "For the archives," he says.', '"Umenishinda leo, {name}." Bazu, genuinely surprised.', 'Bazu does the whistle-point combo. Highest honour.', '"Base itasikia hii leo." — Bazu, already texting'],
                'adult' => ['Bazu: "I knew it. Fifty-fifty next time?" (Say no.)', 'Bazu salutes. He learned that from a movie.', '"Smooth. Very smooth." — Bazu', 'Bazu nods slowly, like he planned it all along.', '"{name}, you\'re wasted on me. In a good way." — Bazu', 'Bazu pretends he\'s not impressed. He is impressed.', '"Next one\'s bigger," Bazu says. It\'s always bigger.'],
            ],
        ],

        'madam_rita' => [
            'name'    => 'Madam Rita',
            'emoji'   => '📋',
            'role'    => 'Career mentor — sharp suit, sharper questions',
            'domains' => ['study_up', 'get_hired', 'level_head', 'payday_pro'],
            'greetings' => [
                '8-12'  => ['Madam Rita opens her big book: "Ah, {name}. Right on time."', 'Madam Rita has a gold star with your name on it, {name}… almost.', '"{name}! My favourite student. Well — potentially." — Madam Rita', 'Madam Rita adjusts her glasses: "{name}. Let\'s see what you\'ve got."', 'Madam Rita taps her pen twice — her signal that class has begun.', '"Punctual," notes Madam Rita, writing something down.', 'Madam Rita: "{name}. I have exactly one assignment. Ready?"'],
                '13-17' => ['Madam Rita checks her list: "{name}… interesting file."', 'Madam Rita: "Talent is common, {name}. Follow-through is rare."', '"I don\'t do motivational speeches, {name}. I do assignments." — Madam Rita', 'Madam Rita closes the door behind you: "{name}. Sit."', '"Your file says potential," says Madam Rita. "Let\'s test that."', 'Madam Rita doesn\'t smile. It\'s not personal. It\'s an assignment.', '"{name}, you\'ve been coasting. Let\'s fix that." — Madam Rita'],
                'adult' => ['Madam Rita slides a folder across the desk: "{name}. Your move."', 'Madam Rita: "I\'ve seen your file. You\'re capable of more."', '"Careers are built on Tuesdays, {name}. Ordinary days. Like today." — Madam Rita', 'Madam Rita doesn\'t look up from her notes: "{name}. Sit, please."', '"I don\'t waste time on people I don\'t believe in," Madam Rita says. "So — {name}."', 'Madam Rita closes her laptop when you walk in. That means business.', '"{name}. Let\'s talk about the next five years, not the next five days."'],
            ],
            'signoffs' => [
                '8-12'  => ['Madam Rita gives you the gold star! ⭐', '"Excellent work, {name}. I expect nothing less next time."', 'Madam Rita writes your name on the Wall of Great Students!', 'Madam Rita underlines your name in her book. Twice.', '"Well done," says Madam Rita, and she means it.', 'Madam Rita gives the smallest nod. From her, that\'s a parade.', '"{name}, you may sit at the front row now." High honours.'],
                '13-17' => ['Madam Rita: "Noted. Impressive." That\'s basically a trophy.', 'Madam Rita almost smiled. ALMOST.', '"You may go far, {name}. Don\'t let it get to your head."', 'Madam Rita closes your file with something like satisfaction.', '"Consider your file updated," says Madam Rita. In good ink.', 'Madam Rita: "You surprised me. Don\'t make it a habit of surprising — make it a habit of doing."', '"Front row," says Madam Rita. You\'ve earned it, apparently.'],
                'adult' => ['Madam Rita updates your file: "exceeds expectations."', '"Good. Now we raise the bar." — Madam Rita', 'Madam Rita shakes your hand. Firmly. You\'ll feel it tomorrow.', 'Madam Rita makes a note in the margin: "promotable."', '"That," says Madam Rita, "is how it\'s done."', 'Madam Rita allows a rare compliment: "Thorough. I like thorough."', '"You\'ve earned a harder assignment next time," Madam Rita says. It\'s a compliment.'],
            ],
        ],

        'mzee_kobe' => [
            'name'    => 'Mzee Kobe',
            'emoji'   => '🐢',
            'role'    => 'The old tortoise — slow money, long memory',
            'domains' => ['first_brick', 'circle_up', 'worth_climb', 'stash_it', 'open_account'],
            'greetings' => [
                '8-12'  => ['Mzee Kobe wakes up sloooowly: "Ah… {name}… good."', 'Mzee Kobe: "Young {name}… come, sit. This is important."', 'The wise tortoise blinks at you: "I have a task, little one."', 'Mzee Kobe takes a very long, very slow breath: "{name}… at last."', 'Patience, {name}…" Mzee Kobe says, though you only just arrived.', 'Mzee Kobe pats the ground beside him: "Sit. This will take… a while."', 'The old tortoise opens one eye: "{name}. Good timing. Slowly good."'],
                '13-17' => ['Mzee Kobe: "{name}… fast money runs. Slow money walks — and arrives."', '"I have watched this city for 60 years, {name}. Listen." — Mzee Kobe', 'Mzee Kobe, chewing a leaf very slowly: "Patience test, {name}."', 'Mzee Kobe: "Everyone runs past me, {name}. I\'m still here. Notice that."', '"{name}, sit. I have outlived three hares who didn\'t." — Mzee Kobe', 'Mzee Kobe blinks once, very deliberately: "Ready, {name}?"', '"You\'re in a hurry," notes Mzee Kobe. "We\'ll fix that."'],
                'adult' => ['Mzee Kobe: "{name}. The best time to plant a tree was 20 years ago. The second best time — you know the rest."', '"Wealth whispers, {name}. Here is what it\'s saying." — Mzee Kobe', 'Mzee Kobe taps his shell: "Assets, {name}. Everything else is weather."', 'Mzee Kobe: "{name}. Sit. I\'ve been thinking about this for eleven years."', '"You\'re still chasing hares," Mzee Kobe says. "Let\'s talk about shells instead."', 'Mzee Kobe doesn\'t rush his words, and he won\'t rush yours: "{name}. Begin."', '"Everything fast eventually stops," says Mzee Kobe. "Let\'s build something slow."'],
            ],
            'signoffs' => [
                '8-12'  => ['Mzee Kobe smiles the slowest, happiest smile. 🐢', '"Good… very good… now we wait and grow." — Mzee Kobe', 'Mzee Kobe tells the other tortoises. They\'ll know by Friday.', 'Mzee Kobe nods. It takes a full minute. Worth it.', '"You listened," says Mzee Kobe. "Rare, for one so young."', 'Mzee Kobe closes his eyes, content. That\'s applause, tortoise-style.', '"Slow and steady," Mzee Kobe says, "just like you did it."'],
                '13-17' => ['Mzee Kobe nods once. In tortoise, that\'s a standing ovation.', '"You move slow when it matters. Rare skill." — Mzee Kobe', 'Mzee Kobe adds your name to his shell. Permanent record.', '"Patience paid," says Mzee Kobe. "It always does, eventually."', 'Mzee Kobe: "You didn\'t rush. I noticed. I notice everything, slowly."', 'The old tortoise gives the smallest smile. It took a while to arrive, but it\'s genuine.', '"Come back when you\'re ready to go even slower," Mzee Kobe says. High praise.'],
                'adult' => ['Mzee Kobe: "The compound interest of good habits. Well done."', '"I have seen many rush past me. You\'ll pass them all." — Mzee Kobe', 'Mzee Kobe closes his eyes, satisfied. That\'s the review.', '"Correct, and unhurried," Mzee Kobe says. "My favourite combination."', 'Mzee Kobe: "In sixty years, I\'ve seen few do it this way. Keep doing it this way."', 'The old tortoise inclines his head. A full bow, by his standards.', '"You\'ll outlast the hares," says Mzee Kobe. "That was always the plan."'],
            ],
        ],

        'shiko' => [
            'name'    => 'Shiko',
            'emoji'   => '📣',
            'role'    => 'Pesa City\'s social battery — knows EVERYONE',
            'domains' => ['circle_up', 'squad_up', 'street_voice', 'good_vibes', 'lucky_spin'],
            'greetings' => [
                '8-12'  => ['Shiko waves BOTH hands: "{name}!! Over here!!"', 'Shiko: "OMG {name}, I was JUST talking about you!"', 'Shiko appears with news. Shiko always has news.', 'Shiko practically teleports next to you: "{name}!! Guess what!!"', '"{name}!" Shiko is already talking before she\'s fully arrived.', 'Shiko waves a piece of paper like it\'s a trophy: "Look look LOOK!"', 'Shiko finds you somehow, always: "There you are, {name}!!"'],
                '13-17' => ['Shiko: "{name}!! Wewe umepotea wapi?" 📣', '"Breaking news, {name}, and YOU\'RE the headline." — Shiko', 'Shiko slides into your DMs. In person. That\'s her style.', 'Shiko is already recording: "{name}! Say hi to the story!"', '"Umeskia? No? Sawa, mimi ndio nakwambia." — Shiko', 'Shiko taps your shoulder seven times before you even turn around.', '"{name}, we need to talk. It\'s not bad, I promise. Mostly." — Shiko'],
                'adult' => ['Shiko: "{name}! Perfect timing — I need exactly your energy."', '"Networking is just friendship with a business plan, {name}." — Shiko', 'Shiko, already mid-conversation: "—and that\'s why I need you, {name}."', 'Shiko waves you over from across the room like you\'re old friends. You are now.', '"{name}, I\'ve been telling everyone about you," Shiko says. She has.', 'Shiko already has two introductions lined up before you\'ve said hello.', '"I collect interesting people," Shiko says. "{name}, you qualify."'],
            ],
            'signoffs' => [
                '8-12'  => ['Shiko announces it to EVERYONE. You\'re famous now! 📣', '"Yes yes YES! That\'s my friend!" — Shiko', 'Shiko does the celebration whistle. Birds join in.', 'Shiko spins you around in a hug-adjacent motion. Very celebratory.', '"EVERYONE, come see what {name} did!!" — Shiko, immediately', 'Shiko gives you a sticker. A real one. She was saving it.', 'Shiko does a little victory lap around you. Just because.'],
                '13-17' => ['Shiko has already posted about it. 3 fire emojis. 🔥🔥🔥', '"Hii ni content! Proud of you!" — Shiko', 'Shiko tells the group chat. The group chat approves.', 'Shiko screenshots everything. "For the memories," she says.', '"Umeninyamazisha, {name}. Which never happens." — Shiko', 'Shiko starts a chant. It\'s just your name, repeated. It catches on.', '"Story posted. Tagged you. You\'re welcome." — Shiko'],
                'adult' => ['Shiko: "This is why I keep you on speed dial."', 'Shiko starts a slow clap. It catches on. It always does.', '"Community points: maxed. Respect." — Shiko', '"I\'m introducing you to everyone I know," Shiko says. She means it.', 'Shiko sends the update to four separate group chats. Immediately.', '"That\'s the {name} I\'ve been telling people about," Shiko says, satisfied.', 'Shiko raises her glass: "To reliable people. There aren\'t enough."'],
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
                '8-12'  => ['Brain Power!', 'The Learning Mission', 'Smart Cookie Challenge', 'Homework Hero', 'Certificate Sprint'],
                '13-17' => ['Ujuzi Loading…', 'Certificate Chase', 'Big Brain Season', 'Syllabus Slay', 'Grade A Grind'],
                'adult' => ['Invest In The Mirror', 'The Qualification Play', 'Skills Pay Bills', 'The Credential Ladder', 'Continuing Ed, Continuing Earnings'],
            ],
            'pitches' => [
                '8-12'  => ['Finish {n} course{s} at Skill Campus — your brain gets stronger every time!', 'There\'s a course with your name on it. Go finish {n}!', 'Skill Campus has a new lesson waiting — go finish {n}!', 'Every course is a superpower unlocked. Grab {n}!'],
                '13-17' => ['Clear {n} course{s} before this expires. Certificates open doors salary can\'t.', 'Somea kidogo — {n} course{s}. Future you says thanks.', '{n} course{s}, zero excuses. Campus doesn\'t close.', 'Stack {n} certificate{s} while everyone else scrolls.'],
                'adult' => ['Complete {n} course{s}. Every certificate here unlocks a payslip somewhere.', 'The syllabus is short and the return is long: {n} course{s}.', '{n} course{s} down, one step closer to the next payslip.', 'Skills don\'t expire. Bank {n} course{s} while you can.'],
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
                '8-12'  => ['Learning first, earning second — that\'s the secret order!', 'The more you learn, the more tools you have to earn!'],
                '13-17' => ['Skills are the one asset nobody can tax, steal, or repossess.', 'Nobody can repossess what you know.'],
                'adult' => ['Education compounds like interest — every course raises your ceiling.', 'A certificate is proof you bet on yourself and won.'],
            ],
        ],

        'get_hired' => [
            'icon'   => '💼',
            'metric' => 'jobs_started',
            'titles' => [
                '8-12'  => ['Job Hunter!', 'You\'re Hired!', 'The Big Interview', 'Boss Wanted You!', 'The Hiring Line'],
                '13-17' => ['Employed Behaviour', 'CV Yenye Nguvu', 'Position Secured', 'Application Accepted', 'Payroll Or Bust'],
                'adult' => ['The Offer Letter', 'Gainful Pursuits', 'Onto The Payroll', 'The Employment Play', 'Signed, Sealed, Employed'],
            ],
            'pitches' => [
                '8-12'  => ['Somewhere in Pesa City, a boss is looking for YOU. Get hired for {n} job{s}!', 'Time to work! Land {n} job{s} at the Opportunity Hub.', 'There\'s a desk with your name on it — go claim {n} job{s}!', 'Bosses are hiring! Land {n} and start earning!'],
                '13-17' => ['The Opportunity Hub has openings. Secure {n}. Broke is a phase, not a personality.', 'Get hired {n} time{s}. Payslips > promises.', '{n} job{s}, zero waiting around. Apply today.', 'Kazi haiji peke yake. Go secure {n}.'],
                'adult' => ['Land {n} position{s}. Income is the engine — everything else is bodywork.', 'There are open roles that match your certificates. Convert {n} into income.', '{n} position{s} closer to a real paycheck.', 'Applications don\'t submit themselves. Land {n}.'],
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
                '8-12'  => ['A job turns your skills into shillings!', 'Every job is a chance to learn something new!'],
                '13-17' => ['Income first. You can\'t budget zero.', 'The first job is never the last — it\'s the start.'],
                'adult' => ['Cashflow is oxygen — secure it before you optimise anything else.', 'Employment is the fastest bridge from skill to shilling.'],
            ],
        ],

        'hustle_harder' => [
            'icon'   => '⚡',
            'metric' => 'gigs_completed',
            'titles' => [
                '8-12'  => ['Side Quest: Side Hustle!', 'Quick Job, Quick Cash', 'The Speedy Gig', 'Gig Rush!', 'Fast Cash Friday'],
                '13-17' => ['Side Hustle Szn', 'Mchongo Wa Leo', 'Gig Economy 101', 'No Off Days', 'Extra Plate Energy'],
                'adult' => ['The Side Income Files', 'Plural Income Society', 'Gig Discipline', 'The Second Stream', 'Moonlighting, Properly'],
            ],
            'pitches' => [
                '8-12'  => ['Bazu found {n} quick gig{s}! Finish the work, collect the coins!', 'Fast job alert! Deliver {n} gig{s} before the deadline!', 'Someone needs quick help! Finish {n} gig{s} today!', 'Small jobs, fast coins — grab {n}!'],
                '13-17' => ['Kuna gig inakuita. Deliver {n} and collect. Hustle sio kelele — ni results.', 'One stream is a tightrope. Add {n} gig{s} this week.', '{n} gig{s} while others wait for payday. Move.', 'Extra kazi, extra pesa. Simple math: {n}.'],
                'adult' => ['Complete {n} freelance gig{s}. Side income is how salaries get backup dancers.', 'Deliver {n} gig{s} — lumpy money still folds the same.', '{n} gig{s} closed — because one income stream is a risk.', 'Freelance work doesn\'t ask permission. Deliver {n}.'],
            ],
            'labels' => ['Complete {n} freelance gig{s} ⚡', 'Deliver {n} gig{s} and collect the pay', 'Finish {n} side hustle{s}'],
            'lessons' => [
                '8-12'  => ['Small jobs add up to big pockets!', 'Every finished gig is money you made happen yourself!'],
                '13-17' => ['Many small streams beat one dry river.', 'A side hustle today is a safety net tomorrow.'],
                'adult' => ['Diversified income is self-insurance.', 'Gig income is proof your skills work outside your job title too.'],
            ],
        ],

        'stash_it' => [
            'icon'   => '🏦',
            'metric' => 'savings_balance',
            'titles' => [
                '8-12'  => ['Piggy Bank Power!', 'The Secret Stash', 'Coin Castle', 'Piggy Bank Level Up', 'The Growing Stash'],
                '13-17' => ['Stash Gang', 'Bank Yako Inakula', 'Untouchable Funds', 'Untouched And Proud', 'Savings Arc'],
                'adult' => ['Pay Yourself First', 'The Quiet Account', 'Fortress Fund', 'The Discipline Account', 'Interest, Working Nights'],
            ],
            'pitches' => [
                '8-12'  => ['Hide Ksh {amount} in your bank where sweets can\'t find it! It even GROWS in there!', 'Mission: stash Ksh {amount} in savings. The bank pays YOU for waiting!', 'Ksh {amount} tucked away where it\'s safe and growing!', 'Feed the piggy bank — Ksh {amount} and counting!'],
                '13-17' => ['Move Ksh {amount} into savings. Money you can see is money you\'ll spend — hide it from yourself.', 'Stash Ksh {amount}. The 8% interest works night shift while you sleep.', 'Ksh {amount} saved is Ksh {amount} you can\'t accidentally spend.', 'Bank yako inakula slowly — add Ksh {amount}.'],
                'adult' => ['Get your savings up by Ksh {amount}. An emergency fund is the cheapest peace of mind on the market.', 'Ksh {amount} into the quiet account. Interest rewards the patient.', 'Ksh {amount} closer to a fund that lets you sleep at night.', 'The account that grows quietly. Add Ksh {amount}.'],
            ],
            'labels' => ['Grow your bank savings by Ksh {amount} 🏦', 'Stash Ksh {amount} where spending can\'t reach it', 'Add Ksh {amount} to your savings'],
            'lessons' => [
                '8-12'  => ['Saved money grows all by itself — like a plant you water once!', 'A growing stash means good things are coming!'],
                '13-17' => ['Save first, spend what remains — never the reverse.', 'What you don\'t see, you don\'t spend.'],
                'adult' => ['Savings buy you the power to say no — to bad jobs, bad loans, bad days.', 'Discipline compounds exactly like interest does.'],
            ],
        ],

        'balance_builder' => [
            'icon'   => '💰',
            'metric' => 'wallet_balance',
            'titles' => [
                '8-12'  => ['Grow The Gold!', 'Wallet Workout', 'The Coin Climb', 'Coin Count-Up', 'Wallet Watch'],
                '13-17' => ['Pockets On Deep', 'Mfuko Season', 'Liquid Goals', 'Mfuko Glow-Up', 'Cash Flow Check'],
                'adult' => ['Working Capital', 'The Buffer Build', 'Cash Position', 'Liquidity Season', 'The Buffer Zone'],
            ],
            'pitches' => [
                '8-12'  => ['Get your wallet up by Ksh {amount} — earn it, don\'t just wish it!', 'Coin challenge! Grow your cash by Ksh {amount}!', 'Watch your coins grow by Ksh {amount}!', 'More earning than spending — that\'s the goal! +Ksh {amount}.'],
                '13-17' => ['End this contract Ksh {amount} richer than you started. Earn more, leak less.', 'Wallet check: +Ksh {amount} by the deadline. Hakuna shortcuts, kuna paydays.', '+Ksh {amount} in the wallet. Track it, don\'t just feel it.', 'Cash in hand matters. Grow it by Ksh {amount}.'],
                'adult' => ['Grow your cash position by Ksh {amount}. Collect pay, plug leaks, mind the bills.', 'Net +Ksh {amount} in the wallet. Simple to say; the discipline is the product.', '+Ksh {amount} in working capital. Small buffer, big peace of mind.', 'Liquidity by Ksh {amount} — because opportunities don\'t wait for payday.'],
            ],
            'labels' => ['Grow your wallet by Ksh {amount} 💰', 'End up Ksh {amount} richer than today', 'Increase your cash by Ksh {amount}'],
            'lessons' => [
                '8-12'  => ['Money grows when earning beats spending!', 'Watching your money grow teaches you how to grow it faster!'],
                '13-17' => ['Track it or lose it — cash leaks through invisible holes.', 'What gets tracked gets managed.'],
                'adult' => ['A cash buffer turns emergencies back into inconveniences.', 'Cash on hand is optionality — the ability to say yes fast.'],
            ],
        ],

        'bill_boss' => [
            'icon'   => '🧾',
            'metric' => 'bills_paid',
            'titles' => [
                '8-12'  => ['Bill Squasher!', 'The On-Time Hero', 'Beat The Bill!', 'On-Time Champion', 'The Bill Slayer'],
                '13-17' => ['Bills Zii Stress', 'Due Date Assassin', 'Landlord Repellent', 'Deadline Domination', 'Never Overdue'],
                'adult' => ['The Punctual Payer', 'Obligations Dept.', 'Zero Overdue Club', 'The Reliability Score', 'Clean Ledger Season'],
            ],
            'pitches' => [
                '8-12'  => ['Mama Pesa is watching the notice board! Pay {n} bill{s} before they turn red!', 'Bills are sneaky — squash {n} of them before the due date!', 'Beat {n} bill{s} before the due date turns red!', 'Show those bills who\'s boss — pay {n}!'],
                '13-17' => ['Pay {n} bill{s} on time. Your credit score has a memory like a grudge.', 'Clear {n} bill{s} kabla deadline. Overdue ni aibu ya wallet.', '{n} bill{s}, zero drama. Pay before deadline day.', 'Kesho ni deadline. Leo ni {n} bill{s} paid.'],
                'adult' => ['Settle {n} bill{s} on schedule. Reliability is a currency — spend it wisely.', 'Pay {n} bill{s} before they age. Late fees are interest you pay for forgetting.', '{n} bill{s} settled ahead of schedule. That\'s the whole strategy.', 'On-time, every time — clear {n} bill{s}.'],
            ],
            'labels' => ['Pay {n} bill{s} from Life HQ 🧾', 'Settle {n} bill{s} before they go overdue', 'Clear {n} bill{s} on time'],
            'lessons' => [
                '8-12'  => ['Paying on time keeps your name golden!', 'Paying early means one less thing to worry about!'],
                '13-17' => ['Your credit score is your money reputation — guard it.', 'On-time payments build trust you can\'t buy later.'],
                'adult' => ['On-time payment history is the backbone of every credit score.', 'Payment history outlives every excuse for missing it.'],
            ],
        ],

        'clean_slate' => [
            'icon'   => '🧹',
            'metric' => 'overdue_cleared',
            'titles' => [
                '8-12'  => ['The Big Clean-Up!', 'Red Alert Rescue', 'Debt Buster', 'The Rescue Squad', 'No More Red!'],
                '13-17' => ['Clear The Red', 'Damage Control', 'Fresh Start Loading', 'Damage Undone', 'Zero Red Zone'],
                'adult' => ['The Reckoning (Small)', 'Arrears Amnesty', 'Back To Zero', 'The Turnaround', 'Arrears, Handled'],
            ],
            'pitches' => [
                '8-12'  => ['Uh oh — something\'s overdue! Clean it up and make Mama Pesa smile again!', 'Rescue mission: clear {n} overdue bill{s} before they grow teeth!', 'Something\'s overdue — swoop in and fix {n} of them!', 'Rescue {n} bill{s} before things get messy!'],
                '13-17' => ['You have red on your board. Clear {n} overdue bill{s} — every game day it waits, your score bleeds.', 'Overdue vibes ni expensive. Handle {n} sasa hivi.', '{n} overdue item{s} cleared — red doesn\'t suit you.', 'Fix {n} sasa before it becomes a bigger shida.'],
                'adult' => ['Clear {n} overdue item{s}. Old debts don\'t age like wine — they age like milk.', 'The fastest credit repair is paying what\'s already late. {n} item{s}.', '{n} overdue item{s} resolved. The fastest repair job in finance.', 'Clean up {n} item{s} — small debts don\'t improve with age.'],
            ],
            'labels' => ['Clear {n} overdue bill{s} 🧹', 'Get your board back to zero overdue', 'Rescue {n} bill{s} from the red zone'],
            'lessons' => [
                '8-12'  => ['Fixing a mistake fast makes it small!', 'The faster you fix a mistake, the smaller it stays!'],
                '13-17' => ['A problem paid late still beats a problem ignored.', 'Ignoring a problem never makes it cheaper.'],
                'adult' => ['Triage debts by damage — overdue first, always.', 'Speed is the only discount arrears ever offer.'],
            ],
        ],

        'payday_pro' => [
            'icon'   => '🧾',
            'metric' => 'paydays_collected',
            'titles' => [
                '8-12'  => ['Payday Catcher!', 'Report For Duty!', 'The Salary Run', 'Show Up, Get Paid!', 'The Collection Run'],
                '13-17' => ['Secure The Bag', 'Attendance 100%', 'Payslip Collector', 'Full Attendance Flex', 'Never Miss A Payday'],
                'adult' => ['The Reliable One', 'Clock In, Cash Out', 'Attendance Dividend', 'The Steady Hand', 'Payroll Loyalty'],
            ],
            'pitches' => [
                '8-12'  => ['Your boss is holding your money! Report to work {n} time{s} and collect it!', 'Payday alert! Show up and collect your pay {n} time{s}!', 'Go collect what you earned — {n} payday{s} waiting!', 'Your work is done, now go get paid {n} time{s}!'],
                '13-17' => ['Report to work and bank your pay {n} time{s}. Money uncollected is money on vacation.', 'Collect {n} payslip{s}. Employer akikuona hukuji, kazi inaenda.', '{n} payday{s} banked. Showing up IS the strategy.', 'Kazi umefanya, sasa collect {n} time{s}.'],
                'adult' => ['Bank {n} paycheck{s} via Report to Work. Your wages stack, but your employer counts absences.', 'Collect pay {n} time{s}. Attendance is the quietest career strategy.', '{n} payday{s} collected — reliability compounds quietly.', 'Bank pay {n} time{s}. Attendance is underrated leverage.'],
            ],
            'labels' => ['Report to Work and collect pay {n} time{s} 💼', 'Bank {n} payslip{s}', 'Collect your wages {n} time{s}'],
            'lessons' => [
                '8-12'  => ['Money you earn still needs collecting!', 'You have to go collect your pay — it doesn\'t chase you!'],
                '13-17' => ['Show up — jobs are kept on ordinary days.', 'Showing up is half of every financial plan.'],
                'adult' => ['Consistency compounds: paychecks, trust, and promotions all follow attendance.', 'The most boring habit — attendance — is often the most profitable one.'],
            ],
        ],

        'first_brick' => [
            'icon'   => '🏗️',
            'metric' => 'assets_owned',
            'titles' => [
                '8-12'  => ['My First Treasure!', 'The Thing-That-Pays', 'Owner Alert!', 'Own Something Cool', 'The Money-Maker Machine'],
                '13-17' => ['Asset Szn', 'Buy Once, Earn Forever', 'Ka-Investment', 'Portfolio Starter Pack', 'Own It, Earn It'],
                'adult' => ['The First Brick', 'Things That Pay Rent', 'Ownership Society', 'The Acquisition Phase', 'Building The Base'],
            ],
            'pitches' => [
                '8-12'  => ['Buy {n} thing{s} from the marketplace that pay YOU money. Magic? No — assets!', 'Mzee Kobe says: own {n} thing{s} that work while you play!', 'Buy {n} thing{s} that pays YOU back — that\'s the trick!', 'Own {n} asset{s} and watch them work for you!'],
                '13-17' => ['Cop {n} asset{s}. Phones lose value, assets bring it back — choose your team.', 'Buy {n} income-generating asset{s}. Let your money get a job too.', '{n} asset{s} added — things that earn beat things that impress.', 'Cop {n} more — assets don\'t lose value to hype.'],
                'adult' => ['Acquire {n} asset{s}. The goal isn\'t owning things — it\'s owning things that pay.', 'Add {n} asset{s} to the portfolio. Salaries stop; assets don\'t.', '{n} asset{s} acquired. Ownership is the long game.', 'Add {n} to the portfolio — income-generating, not ego-generating.'],
            ],
            'labels' => ['Buy {n} income-earning asset{s} 🏗️', 'Own {n} new asset{s} from the marketplace', 'Add {n} asset{s} to your empire'],
            'lessons' => [
                '8-12'  => ['Assets are things that put money IN your pocket!', 'The best toys are the ones that make you MORE money!'],
                '13-17' => ['Buy things that pay you back — that\'s the whole cheat code.', 'Buying something that earns beats buying something that just looks good.'],
                'adult' => ['Assets convert income into wealth; spending converts it into memories and receipts.', 'Every asset you own is a tiny employee working while you sleep.'],
            ],
        ],

        'circle_up' => [
            'icon'   => '🤝',
            'metric' => 'chama_contributions',
            'titles' => [
                '8-12'  => ['Team Money!', 'The Circle Of Coins', 'Better Together', 'Piggy Bank Squad', 'Team Treasure'],
                '13-17' => ['Chama Things', 'The Circle Is Sacred', 'Group Economics', 'Circle Check-In', 'Group Grind'],
                'adult' => ['The Cooperative Play', 'Circle Capital', 'Harambee Dividend', 'The Standing Commitment', 'Collective Capital'],
            ],
            'pitches' => [
                '8-12'  => ['Join hands! Make {n} chama contribution{s} — money grows faster in a team!', 'Your chama is counting on you — contribute {n} time{s}!', 'Your chama friends are counting on you — {n} contribution{s}!', 'Team money grows faster! Contribute {n} time{s}!'],
                '13-17' => ['Make {n} chama contribution{s}. The circle only works if YOU show up.', 'Chama check: {n} contribution{s}. Group money is grown money.', '{n} chama contribution{s} — the circle notices who shows up.', 'Weka pesa kwa chama — {n} time{s}, no excuses.'],
                'adult' => ['Contribute to your chama {n} time{s}. Cooperative capital built this country.', 'Honour the circle: {n} contribution{s}. Reliability is your share price.', '{n} contribution{s} — cooperative capital only works if everyone pays in.', 'Honour the circle {n} time{s}. Trust is the real currency here.'],
            ],
            'labels' => ['Make {n} chama contribution{s} 🤝', 'Pay into your chama {n} time{s}', 'Keep your chama promise — {n} contribution{s}'],
            'lessons' => [
                '8-12'  => ['A team of savers beats a lonely spender!', 'When everyone saves together, everyone wins together!'],
                '13-17' => ['Trust is the interest rate of friendship.', 'A chama is only as strong as its most consistent member.'],
                'adult' => ['Chamas work on one technology: people who show up.', 'Cooperative capital has funded generations — because people showed up.'],
            ],
        ],

        'worth_climb' => [
            'icon'   => '📈',
            'metric' => 'net_worth',
            'titles' => [
                '8-12'  => ['The Big Number!', 'Climb The Money Mountain', 'Richer Every Day', 'Treasure Tower', 'Bigger Every Day'],
                '13-17' => ['Net Worth Loading…', 'Portfolio Glow-Up', 'Quiet Flex', 'Number Go Up', 'The Real Flex'],
                'adult' => ['The Long Game', 'Balance Sheet Season', 'Compounding Quietly', 'The Balance Sheet Grind', 'Quiet Compounding'],
            ],
            'pitches' => [
                '8-12'  => ['Grow your total treasure by Ksh {amount} — save it, earn it, grow it!', 'Mzee Kobe\'s challenge: make your Big Number rise by Ksh {amount}!', 'Make your Big Number climb by Ksh {amount}!', 'Everything you own, growing — +Ksh {amount}!'],
                '13-17' => ['Raise your net worth by Ksh {amount}. Cash + savings + assets − debts. Move the whole board.', 'Net worth +Ksh {amount}. Slow flex > loud broke.', 'Net worth +Ksh {amount}. That\'s the number that actually matters.', 'Grow the real score by Ksh {amount} — not the one you post.'],
                'adult' => ['Lift your net worth by Ksh {amount}. Income is a story; net worth is the audit.', 'Grow the balance sheet by Ksh {amount}. Every line item is a decision.', '+Ksh {amount} net worth. The only audit that never lies.', 'Lift the balance sheet by Ksh {amount} — one decision at a time.'],
            ],
            'labels' => ['Grow your net worth by Ksh {amount} 📈', 'Raise the Big Number by Ksh {amount}', 'Add Ksh {amount} to your total worth'],
            'lessons' => [
                '8-12'  => ['Your real score is everything you own minus everything you owe!', 'Your Big Number only grows when you save AND earn!'],
                '13-17' => ['Net worth doesn\'t care what you posted — only what you kept.', 'What you keep matters more than what you flash.'],
                'adult' => ['Track net worth monthly; it\'s the only scoreboard that can\'t be gamed.', 'Net worth is the scoreboard nobody can fake.'],
            ],
        ],

        'level_head' => [
            'icon'   => '⭐',
            'metric' => 'xp_points',
            'titles' => [
                '8-12'  => ['XP Explosion!', 'Level-Up Mission', 'Star Collector', 'XP Sprint!', 'Star Power'],
                '13-17' => ['Grind Arc', 'XP Farming (Legal)', 'Main Character Energy', 'Grind Mode: On', 'Level-Up Energy'],
                'adult' => ['Deliberate Practice', 'The XP Ledger', 'Progress On Purpose', 'The Growth Ledger', 'Momentum, Tracked'],
            ],
            'pitches' => [
                '8-12'  => ['Earn {amount} XP doing ANYTHING awesome — courses, quests, wins!', 'Madam Rita\'s dare: collect {amount} XP before time runs out!', 'Collect {amount} XP just by doing awesome things!', 'Every win counts! Reach {amount} XP!'],
                '13-17' => ['Stack {amount} XP. Everything counts — study, work, community. Just move.', 'Farm {amount} XP. The grind is boring; the levels are not.', '{amount} XP — study, hustle, show up. It all counts.', 'Farm {amount} XP the honest way: by actually doing stuff.'],
                'adult' => ['Earn {amount} XP across the city. Progress rewards the active, not the anxious.', 'Bank {amount} XP. Momentum is a habit wearing a costume.', '{amount} XP earned across the city — progress rewards motion.', 'Bank {amount} XP. Small consistent actions, big total.'],
            ],
            'labels' => ['Earn {amount} XP anywhere in Pesa City ⭐', 'Stack {amount} experience points', 'Collect {amount} XP before the deadline'],
            'lessons' => [
                '8-12'  => ['Doing things is how you grow — sitting still earns nothing!', 'Every activity you try makes you a little bit stronger!'],
                '13-17' => ['Consistency beats intensity. Small daily wins stack.', 'XP doesn\'t come from thinking about it — it comes from doing it.'],
                'adult' => ['Activity is the raw material of progress.', 'Progress is just activity, measured honestly over time.'],
            ],
        ],

        'squad_up' => [
            'icon'   => '👥',
            'metric' => 'friends_count',
            'titles' => [
                '8-12'  => ['Friend Finder!', 'Team Up!', 'The Buddy Mission', 'New Buddy Alert', 'The Friendship Mission'],
                '13-17' => ['Squad Assembly', 'Networking (But Fun)', 'Link Up', 'Squad Goals, Actually', 'Link Up Season'],
                'adult' => ['Social Capital', 'The Network Effect', 'Allies & Assets', 'The Warm Network', 'Relationship Capital'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko says friends make everything better! Add {n} friend{s}!', 'Team up! Make {n} new friend{s} in Pesa City!', 'Make {n} new friend{s} — everything\'s better with a squad!', 'Say hi! Add {n} friend{s} to your crew!'],
                '13-17' => ['Add {n} friend{s}. Chamas, loans, leaderboards — everything here is better with a squad.', 'Link up na {n} player{s}. Your network ni net worth ya baadaye.', '{n} new friend{s} — bigger circle, bigger opportunities.', 'Link up na {n} zaidi. Solo mode is overrated.'],
                'adult' => ['Connect with {n} player{s}. Every chama, every P2P loan, starts with a handshake.', 'Grow your circle by {n}. Opportunity travels through people.', '{n} new connection{s} — every deal starts as a conversation.', 'Grow your network by {n}. Opportunity rarely arrives alone.'],
            ],
            'labels' => ['Make {n} new friend{s} 👥', 'Add {n} player{s} to your circle', 'Grow your squad by {n}'],
            'lessons' => [
                '8-12'  => ['Good friends help your money grow safely!', 'The more good friends you have, the more you learn together!'],
                '13-17' => ['Choose your circle — you\'ll trade with them for life.', 'Your circle shapes your habits more than you think.'],
                'adult' => ['Social capital pays dividends money can\'t buy.', 'Every opportunity you\'ve ever had probably came through a person.'],
            ],
        ],

        'street_voice' => [
            'icon'   => '🗣️',
            'metric' => 'forum_posts',
            'titles' => [
                '8-12'  => ['Speak Up!', 'The Town Crier', 'Share Your Story!', 'Story Time!', 'Speak And Share'],
                '13-17' => ['Mic Check', 'Hot Take Loading', 'Forum Famous', 'Drop The Tea (Wisely)', 'Forum Regular'],
                'adult' => ['The Contributor', 'Signal, Not Noise', 'Community Dividend', 'The Steady Voice', 'Community Ledger'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko wants to hear from you! Post or reply {n} time{s} on the forums!', 'Share a money tip or ask a question — {n} post{s} on the town board!', 'Tell your money story! Post {n} time{s} on the forums!', 'Ask, share, teach — {n} post{s} today!'],
                '13-17' => ['Drop {n} post{s} or repl{ies} on the forums. Teach one thing, learn three.', 'Sema kitu kwa forums — {n} time{s}. Karma zinajenga rep.', '{n} post{s} or repl{ies} — your take might help someone else\'s week.', 'Sema experience yako — {n} time{s} kwa forum.'],
                'adult' => ['Contribute {n} post{s} or repl{ies}. Communities pay teachers in trust.', 'Add your voice {n} time{s}. Explaining money is how you master it.', '{n} post{s} or repl{ies} — the community runs on people who explain things.', 'Contribute {n} time{s}. Good answers age well here.'],
            ],
            'labels' => ['Post or reply on the forums {n} time{s} 🗣️', 'Join {n} conversation{s} at Pesa Forums', 'Share {n} thought{s} with the community'],
            'lessons' => [
                '8-12'  => ['Teaching a friend makes YOU smarter too!', 'Sharing what you learned helps everyone grow together!'],
                '13-17' => ['If you can explain it, you actually understand it.', 'The forum rewards people who actually say something useful.'],
                'adult' => ['Writing about money exposes what you haven\'t figured out yet.', 'A community\'s knowledge is only as deep as its most active members.'],
            ],
        ],

        'open_account' => [
            'icon'   => '🔑',
            'metric' => 'savings_balance',
            'titles' => [
                '8-12'  => ['Your Very Own Vault!', 'The Key Ceremony', 'First Account Day!', 'Vault Unlocked!', 'My Own Bank Day'],
                '13-17' => ['Account Activated', 'Official Money Adult', 'The Vault Unlock', 'Account Secured', 'The First Lock'],
                'adult' => ['The Opening Move', 'Account Number One', 'Where Money Sleeps Safely', 'Day One, Line One', 'The Starting Account'],
            ],
            'pitches' => [
                '8-12'  => ['Open your very own savings account — a treasure vault with YOUR name on it!', 'Every hero needs a base. Open a savings account and give your coins a home!', 'Get your very first savings account — your own money home!', 'Unlock the vault! Open your account today!'],
                '13-17' => ['Open a savings account. A wallet is a hallway; an account is a room with a lock.', 'Unlock your savings account — it\'s the first flex nobody sees and everybody feels.', 'Open your account — the first real money-adult move.', 'One account, one lock, zero regrets. Open it.'],
                'adult' => ['Open your savings account. Every financial plan ever written starts on this exact line.', 'One account, zero excuses. Open it — the interest starts working the same day.', 'Open the account every plan eventually needs.', 'Day one starts with an account, not a spreadsheet.'],
            ],
            'labels' => ['Open your bank savings account 🔑', 'Unlock your first savings account', 'Give your money a safe home 🏦'],
            'lessons' => [
                '8-12'  => ['Money with a home grows; money in your pocket wanders off!', 'A savings account is like a treasure chest only YOU can open!'],
                '13-17' => ['Separating spending money from saving money is the whole trick.', 'Nothing serious gets built without somewhere to put it.'],
                'adult' => ['An account you don\'t touch is the cheapest financial discipline ever invented.', 'The account matters less than the habit of using it.'],
            ],
        ],

        'lucky_spin' => [
            'icon'   => '🎡',
            'metric' => 'mood_level',
            'titles' => [
                '8-12'  => ['Spin To Win!', 'The Lucky Wheel Calls', 'Round And Round!', 'Spin Time!', 'Wheel Wonders'],
                '13-17' => ['Free Spin Energy', 'Wheel Deal', 'Test Your Luck (Safely)', 'Free Luck, No Catch', 'Spin Season'],
                'adult' => ['The Free Lottery', 'Calculated Luck', 'One Spin, Zero Risk', 'The No-Cost Chance', 'Risk-Free Roulette'],
            ],
            'pitches' => [
                '8-12'  => ['The Lucky Wheel is spinning today — take your free turn and see what Pesa City gives you!', 'Round and round it goes! Give the wheel one big spin!', 'Give the Lucky Wheel a spin — see what happens!', 'Free spin, free fun! Try your luck today!'],
                '13-17' => ['Spin the wheel. It\'s the only game in town where the house can\'t win.', 'Free spin available. Luck favours those who actually show up.', 'Free spin, zero cost. Take it while it\'s free.', 'The wheel\'s spinning — one free go, no strings.'],
                'adult' => ['Take your spin. Note how good free feels — real gambling never is.', 'One free spin. The only sustainable relationship with luck: when it costs nothing.', 'A free spin — enjoy it precisely because nothing\'s riding on it.', 'Take the spin. The lesson is in the price tag: zero.'],
            ],
            'labels' => ['Spin the Lucky Wheel 🎡', 'Take your free spin', 'Try the wheel of fortune 🎡'],
            'lessons' => [
                '8-12'  => ['Free surprises are fun — but never PAY to gamble!', 'Free games are fun — just remember, real gambling isn\'t free!'],
                '13-17' => ['Luck is a bonus, never a plan. Enjoy it when it\'s free.', 'The moment luck costs money, it stops being luck.'],
                'adult' => ['If luck charges an entry fee, it\'s not luck — it\'s revenue.', 'Free chance is entertainment; paid chance is a business model — theirs, not yours.'],
            ],
        ],

        'good_vibes' => [
            'icon'   => '😊',
            'metric' => 'mood_level',
            'titles' => [
                '8-12'  => ['Happy Hero!', 'The Smile Mission', 'Recharge!', 'Happy Refill', 'Mood Boost Mission'],
                '13-17' => ['Vibe Maintenance', 'Rest Is Productive', 'Battery Recharge', 'Recharge Arc', 'Vibe Check Passed'],
                'adult' => ['The Sustainable Grind', 'Burnout Insurance', 'Mood As Infrastructure', 'Preventive Maintenance', 'The Rest Strategy'],
            ],
            'pitches' => [
                '8-12'  => ['Shiko noticed you look tired! Get your mood up to {amount} — visit Fun World!', 'Happiness mission: raise your mood to {amount}! Play a little!', 'Time to recharge! Get your mood up to {amount}!', 'Play a little, smile a lot — reach {amount} mood!'],
                '13-17' => ['Get mood to {amount}. Grinding on 40 mood is playing on hard mode for no reason.', 'Recharge to {amount}. Fun World exists — na budget pia inaexist. Balance.', 'Mood to {amount} — burnout doesn\'t pay better, it just tires you faster.', 'Recharge kidogo. Mood to {amount}, then back to it.'],
                'adult' => ['Raise mood to {amount}. Low mood taxes your salary 10% — rest is a financial decision.', 'Mood to {amount}. You are the asset generating all the others; maintain it.', 'Mood to {amount} — the one investment with zero downside.', 'Rest to {amount}. You can\'t pour from an empty account either.'],
            ],
            'labels' => ['Get your mood up to {amount} 😊', 'Recharge your energy to {amount}', 'Reach {amount} mood — rest a little!'],
            'lessons' => [
                '8-12'  => ['A happy worker earns better — rest is part of the job!', 'A rested mind makes smarter choices — even about money!'],
                '13-17' => ['Rest isn\'t lazy — it\'s maintenance on your best machine.', 'You perform better rested than you do exhausted and proud of it.'],
                'adult' => ['Burnout is the most expensive way to save time.', 'Sustainable effort beats heroic bursts followed by collapse.'],
            ],
        ],
    ],
];
