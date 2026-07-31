<?php

/*
|--------------------------------------------------------------------------
| Quest Mixer vocabulary — the deep copy bank
|--------------------------------------------------------------------------
| Big pools per trigger theme so generated quests never read templated:
| NPC greeting (pesa_voice cast) × title × pitch × lesson × signoff gives
| thousands of combinations per theme. Urban-Kenya flavour, clean Sheng for
| teens, dry wit for adults, sunshine for kids. Placeholders: {amount} {name}.
|
| Bands: '8-12', '13-17', 'adult'. Each theme lists which NPCs "issue" it.
*/

return [

    'themes' => [

        'reach_savings' => [
            'icon' => '🏦',
            'npcs' => ['mama_pesa', 'mzee_kobe'],
            'titles' => [
                '8-12'  => ['The Treasure Pile', 'Coin Mountain Mission', 'Super Saver Level Up', 'The Growing Stash', 'Piggy Power Goal'],
                '13-17' => ['Stash Season', 'Akaunti Inanona', 'Silent Flex Fund', 'The Untouchables (Your Money Edition)', 'Loading… Savings {amount}'],
                'adult' => ['The Quiet Milestone', 'Fortress Arithmetic', 'Sleep-Easy Fund', 'Position of Strength', 'The Buffer Doctrine'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Grow your bank treasure to Ksh {amount}! Every coin you tuck in is a little soldier guarding your dreams.',
                    'Mission: get your savings all the way up to Ksh {amount}. Slow steps still climb mountains!',
                    'Your savings jar is hungry — feed it until it holds Ksh {amount}. Watch it grow like a mango tree!',
                    'Ksh {amount} in savings — that\'s the goal. Sweets run out; savings hang around and grow.',
                ],
                '13-17' => [
                    'Push your total savings to Ksh {amount}. Money you can\'t see is money you can\'t blow on chips funga.',
                    'Target: Ksh {amount} stashed. Broke fridays build rich futures — hide it from yourself na uache kuiangalia.',
                    'Get the account to Ksh {amount}. Your friends flex screenshots; you\'ll flex options.',
                    'Ksh {amount} in the vault. The 8% interest ni kama a silent side hustle that never sleeps.',
                    'Stack quietly to Ksh {amount}. Loud money leaves; quiet money stays na inazaa.',
                ],
                'adult' => [
                    'Bring total savings to Ksh {amount}. An emergency fund is the cheapest insurance you will ever own.',
                    'Ksh {amount} saved changes how you negotiate — with employers, landlords, and bad ideas.',
                    'Grow the reserve to Ksh {amount}. Rainy days don\'t send calendar invites.',
                    'Target Ksh {amount}. Savings are permission slips: to rest, to say no, to wait for better.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Saving first and spending what remains is the whole secret — in that order!',
                    'Small coins saved often beat big coins saved once.',
                    'Money in savings works for you even while you sleep.',
                    'A goal with a number is a plan; a wish without one is just a dream.',
                ],
                '13-17' => [
                    'Pay yourself first — before airtime, before outings, before everything.',
                    'An emergency fund turns a crisis into an inconvenience.',
                    'Discipline beats motivation: automate the saving, then forget it exists.',
                    'The account balance nobody sees is the flex that actually matters.',
                    'Interest rewards patience — the longer money sits, the harder it works.',
                ],
                'adult' => [
                    'Liquidity is optionality: cash reserves let you make decisions instead of taking whatever comes.',
                    'Savings rate matters more than income level — plenty of high earners are one bad week from zero.',
                    'Build the buffer before the investment; you can\'t compound money you were forced to withdraw.',
                    'Every shilling saved at a calm moment is a shilling you won\'t borrow at a desperate one.',
                ],
            ],
        ],

        'deposit_savings' => [
            'icon' => '💧',
            'npcs' => ['mama_pesa', 'mzee_kobe'],
            'titles' => [
                '8-12'  => ['One Big Drop', 'The Brave Deposit', 'Pocket to Vault', 'Splash Into Savings'],
                '13-17' => ['The Deposit Challenge', 'Weka Ndani', 'One-Tap Discipline', 'Feed The Account'],
                'adult' => ['The Deliberate Deposit', 'Single Decisive Transfer', 'Commitment, Banked', 'One Clean Move'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Put Ksh {amount} into one savings pocket in one go — one brave jump for your money!',
                    'Take Ksh {amount} from your wallet and tuck it safely into savings. Your future self says asante!',
                    'One big deposit of Ksh {amount}! It\'s like planting a whole handful of seeds at once.',
                ],
                '13-17' => [
                    'Grow one savings pocket to Ksh {amount}. One decisive weka — not fifty maybe-laters.',
                    'Move Ksh {amount} into a pocket before the weekend finds it first. Money left loose ni bait.',
                    'Ksh {amount}, one pocket, one tap. Commitment ni action, sio caption.',
                    'Deposit until a pocket reads Ksh {amount}. Kidogo kidogo ni sawa, lakini leo weka kubwa.',
                ],
                'adult' => [
                    'Get one savings pocket to Ksh {amount}. A single deliberate transfer beats a month of good intentions.',
                    'Fund one goal to Ksh {amount}. Specific money for a specific purpose survives temptation.',
                    'Move Ksh {amount} into one pocket. What gets earmarked gets protected.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Money with a name and a home is money that stays!',
                    'One brave deposit today beats ten promises for tomorrow.',
                    'Your wallet is for spending; your savings pocket is for growing.',
                ],
                '13-17' => [
                    'Name your savings goals — "Laptop Fund" survives temptation better than "misc".',
                    'Move money the moment it arrives; whatever lingers in the wallet gets spent.',
                    'Big occasional deposits are good; small automatic ones are unstoppable.',
                    'The transfer button is the most underrated wealth tool on your phone.',
                ],
                'adult' => [
                    'Earmarked money is defended money — give every goal its own pocket.',
                    'Transfer on payday, not month-end: you cannot spend what already left.',
                    'Savings goals fail in the gap between deciding and doing. Close the gap same-day.',
                ],
            ],
        ],

        'reach_balance' => [
            'icon' => '💰',
            'npcs' => ['mama_pesa', 'bazu'],
            'titles' => [
                '8-12'  => ['Wallet Power-Up', 'The Coin Collector', 'Cash Climb Challenge', 'Full Pockets, Big Smile'],
                '13-17' => ['Mfuko Check', 'Liquid Season', 'Wallet Yenye Weight', 'Cash On Standby'],
                'adult' => ['Working Capital', 'The Float', 'Cash Position: Strong', 'Liquidity Drill'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Get your wallet up to Ksh {amount}! Earn it, keep it, count it twice.',
                    'Cash challenge: hold Ksh {amount} in your wallet without letting it sneak away!',
                    'Grow your pocket money to Ksh {amount} — earning is fun, keeping is the real game!',
                ],
                '13-17' => [
                    'Hold Ksh {amount} liquid. Anaeza kuwa na salary lakini wallet iko flat — usiwe huyo.',
                    'Wallet check: Ksh {amount}. Cash on hand ni confidence unaweza kubeba.',
                    'Keep Ksh {amount} spendable without spending it. Hardest game in Pesa City, real talk.',
                    'Build the wallet to Ksh {amount}. Opportunities knock haraka — broke people answer pole pole.',
                ],
                'adult' => [
                    'Hold Ksh {amount} in ready cash. Deals, discounts and emergencies all pay the liquid first.',
                    'Build your float to Ksh {amount}. Cashflow gaps kill more plans than bad ideas do.',
                    'Ksh {amount} on hand. The difference between a setback and a crisis is usually working capital.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Keeping money is a skill you practice, just like football!',
                    'A full wallet comes from earning more AND leaking less.',
                    'Count your money often — what you watch, you protect.',
                ],
                '13-17' => [
                    'Track the leaks: it\'s rarely one big buy, it\'s forty small ones.',
                    'Cash on hand means you buy on your terms, not the seller\'s.',
                    'Income is what you make; balance is what you kept. Only one is real.',
                    'Before any purchase ask: is this a need, a want, or a trap dressed as a deal?',
                ],
                'adult' => [
                    'Revenue is vanity, cashflow is sanity, cash at hand is reality.',
                    'A cash cushion converts market panic into shopping opportunity.',
                    'Budget the boring way: money in, money out, difference banked. It never stops working.',
                ],
            ],
        ],

        'reach_net_worth' => [
            'icon' => '📈',
            'npcs' => ['mzee_kobe'],
            'titles' => [
                '8-12'  => ['The Big Number Climb', 'Everything-You-Own Goal', 'Treasure Map Total', 'Richer Than Yesterday'],
                '13-17' => ['Net Worth Loading', 'The Real Scoreboard', 'Silent Portfolio Szn', 'Total Weight Check'],
                'adult' => ['Balance Sheet Season', 'The Long Arithmetic', 'Compound Quietly', 'The Audit You\'ll Enjoy'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Grow your total treasure — cash, savings and things you own — to Ksh {amount}!',
                    'The Big Number counts EVERYTHING you own. Push it past Ksh {amount}!',
                    'Mzee Kobe\'s challenge: total worth of Ksh {amount}. Slow and steady owns the race.',
                ],
                '13-17' => [
                    'Push net worth past Ksh {amount}. Cash + savings + assets − debts. Move the whole board, sio wallet tu.',
                    'Ksh {amount} total worth. Outfits depreciate; assets appreciate. Choose your wardrobe wisely.',
                    'Get the real scoreboard to Ksh {amount}. Followers hawapandi value — portfolios do.',
                    'Total weight: Ksh {amount}. Hii game ni ya patience — hare stories ni za kuanguka.',
                ],
                'adult' => [
                    'Lift net worth beyond Ksh {amount}. Income tells a story; net worth signs the affidavit.',
                    'Ksh {amount} across the whole balance sheet. Every line item is a past decision — make better ones.',
                    'Grow total worth to Ksh {amount}. Wealth is assets working while you rest, minus debts working while you sleep.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Your real score is everything you own minus everything you owe.',
                    'Things that earn money make your Big Number grow twice as fast.',
                    'Rich is not what you spend — it\'s what you keep and grow.',
                ],
                '13-17' => [
                    'Net worth doesn\'t care what you posted. Only what you kept.',
                    'Buy assets that pay you; avoid liabilities that bill you.',
                    'Debt subtracts from your score even when nobody sees it.',
                    'Track net worth monthly — it\'s the one number that can\'t be faked.',
                ],
                'adult' => [
                    'Net worth is the only scoreboard immune to lifestyle inflation.',
                    'Wealth compounds along two curves: assets growing and debts shrinking. Work both.',
                    'The gap between looking rich and being rich is exactly one balance sheet.',
                ],
            ],
        ],

        'take_course' => [
            'icon' => '📚',
            'npcs' => ['madam_rita'],
            'titles' => [
                '8-12'  => ['Brain Gains!', 'The Smart Kid Move', 'Learn One, Earn Later', 'Certificate Hunter'],
                '13-17' => ['Ujuzi Ni Mali', 'Certificate Chase', 'Big Brain Behaviour', 'Somea Bag Yako'],
                'adult' => ['Invest In The Mirror', 'The Qualification Play', 'Skills Compound Too', 'Upgrade The Operator'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Finish any course at Skill Campus — your brain gets a new superpower every time!',
                    'Pick a course, any course, and finish it. Smart today, paid tomorrow!',
                    'One course, start to finish. Learning is the only treasure nobody can take from you!',
                ],
                '13-17' => [
                    'Clear any course at Skill Campus. Ujuzi ni mali — certificates open doors salary can\'t.',
                    'Somea kitu. One course, start to finish. Your CV won\'t build itself, boss.',
                    'Finish a course before the week finishes you. Knowledge ni the only asset with zero maintenance cost.',
                    'Any course, full completion. Half-read ni sawa na hujasoma.',
                ],
                'adult' => [
                    'Complete any course. Every certificate quietly raises your salary ceiling.',
                    'One course, finished properly. Skills are the rare asset that appreciates with use.',
                    'Pick a course and see it through. The syllabus is short; the return runs for years.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Learning first, earning second — that\'s the winning order!',
                    'Every finished course makes the next one easier.',
                    'Smart is built, not born — one lesson at a time.',
                ],
                '13-17' => [
                    'Skills can\'t be taxed, stolen, or repossessed.',
                    'The market pays for what you can DO, not what you meant to study.',
                    'Finishing is the skill — anyone can start.',
                    'Learn before you need it; desperate learning is expensive learning.',
                ],
                'adult' => [
                    'Education compounds like interest — each course raises the base for the next.',
                    'The best career insurance is a skill the market is currently short of.',
                    'An hour of learning at the right time is worth a month of hustle at the wrong one.',
                ],
            ],
        ],

        'get_job' => [
            'icon' => '💼',
            'npcs' => ['madam_rita', 'bazu'],
            'titles' => [
                '8-12'  => ['You\'re Hired!', 'The Big Yes', 'First Payslip Mission', 'Job Hunter Junior'],
                '13-17' => ['Employed Behaviour', 'Secure The Position', 'CV Yenye Nguvu', 'Ajira Loading…'],
                'adult' => ['Onto The Payroll', 'The Offer Letter', 'Income, Engineered', 'Gainful Pursuits'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Somewhere in Pesa City a boss needs exactly YOU. Get hired for any job!',
                    'Land a job at the Opportunity Hub! Working feels good — payday feels even better!',
                    'Get hired! Your skills are keys — go open a door with them!',
                ],
                '13-17' => [
                    'Secure any position at the Opportunity Hub. Payslips > promises, kila siku ya Mungu.',
                    'Get hired. Broke ni phase, hustle ni lifestyle, salary ni evidence.',
                    'Land a job. Kazi ni kazi — the first payslip funds the bigger dream.',
                    'Any role, get it. Bazu says opportunities hazikungoji — zinapita.',
                ],
                'adult' => [
                    'Land a position. Income is the engine; everything else in this city is bodywork.',
                    'Get hired somewhere. Cashflow first, optimisation later — you can\'t budget zero.',
                    'Convert your certificates into a payslip. Qualifications only count when deployed.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'A job turns your time and skills into shillings!',
                    'Every big career started with one small yes.',
                    'Show up, do the work, collect the pay — the oldest recipe there is.',
                ],
                '13-17' => [
                    'Your first job is rarely your dream job — it\'s the ladder\'s first rung.',
                    'Reliable beats brilliant in most hiring rooms.',
                    'Income first; you can\'t save, invest or plan on zero.',
                    'Jobs teach what courses can\'t: deadlines, bosses, and patience.',
                ],
                'adult' => [
                    'Secure the income stream before optimising it — order matters.',
                    'Careers are built on ordinary Tuesdays, not dramatic Mondays.',
                    'Negotiate from employment, not from need — the employed candidate is always stronger.',
                ],
            ],
        ],

        'buy_item_category' => [
            'icon' => '🏗️',
            'npcs' => ['mzee_kobe', 'bazu'],
            'titles' => [
                '8-12'  => ['My First Money Machine', 'Owner Alert!', 'The Thing That Pays Back', 'Little Landlord'],
                '13-17' => ['Asset Szn', 'Buy Once, Earn Forever', 'Ka-Investment Move', 'Own Something Fr'],
                'adult' => ['The First Brick', 'Things That Pay Rent', 'Deploy The Capital', 'Ownership Society'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Visit the Marketplace and buy something that pays YOU money. Magic? No — assets!',
                    'Buy your first money machine! Some things cost money; the best things MAKE money.',
                    'Get one thing from the Marketplace that earns while you play!',
                ],
                '13-17' => [
                    'Cop an asset from the Marketplace. Phones lose value kila mwezi; assets bring it back.',
                    'Buy something that earns. Let your money get a job too — it\'s been idle for too long.',
                    'One asset, secured. Ownership ni the cheat code nobody posts about.',
                    'Marketplace run: buy a thing that pays you back. Hiyo ndio smart shopping.',
                ],
                'adult' => [
                    'Acquire an asset from the Marketplace. The goal isn\'t owning things — it\'s owning things that pay.',
                    'Deploy some capital: one income-generating purchase. Salaries stop; assets don\'t.',
                    'Buy something with a yield. Every shilling should have a job description.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Assets put money IN your pocket; toys take money OUT.',
                    'The best shopping buys things that keep paying you back.',
                    'One little money machine today, a whole factory someday!',
                ],
                '13-17' => [
                    'Ask of every purchase: does this feed me or eat me?',
                    'Assets bought young have decades to compound — time is your unfair advantage.',
                    'Maintenance is part of the price — budget for it or lose the income.',
                    'Buy value, not hype: the loud purchase depreciates fastest.',
                ],
                'adult' => [
                    'Assets convert income into wealth; consumption converts it into receipts.',
                    'Yield, cost, and upkeep — read all three before any purchase.',
                    'Diversify your income sources before you diversify your wardrobe.',
                ],
            ],
        ],

        'join_chama' => [
            'icon' => '🤝',
            'npcs' => ['shiko', 'mzee_kobe'],
            'titles' => [
                '8-12'  => ['Team Money Time', 'The Circle Of Coins', 'Join The Money Squad', 'Better Together'],
                '13-17' => ['Chama Things', 'The Circle Is Sacred', 'Squad Economics', 'Wajenga Pamoja'],
                'adult' => ['The Cooperative Play', 'Circle Capital', 'Harambee Dividend', 'Strength In Numbers'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Join a chama — a money team where everyone helps everyone grow!',
                    'Find your money squad! Saving together is faster AND more fun.',
                    'Join hands with other savers — a chama makes every coin braver!',
                ],
                '13-17' => [
                    'Join a chama. Group money ni grown money — the circle only works ukiingia.',
                    'Get into a chama. Solo saving ni sawa; squad saving ina pressure ya positive.',
                    'Find a circle and commit. Accountability ni the feature, sio the bug.',
                ],
                'adult' => [
                    'Join a chama. Cooperative capital built half this country — get your share of the tradition.',
                    'Enter a savings circle. Social commitment succeeds where private willpower quits.',
                    'Join a chama: pooled funds, shared discipline, collective leverage.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'A team of savers beats a lonely spender every time!',
                    'Promises to friends are easier to keep than promises to yourself.',
                    'Money grows faster when everyone waters the same garden.',
                ],
                '13-17' => [
                    'Choose your circle carefully — you\'ll trade with them for life.',
                    'Trust is the real interest rate of any chama.',
                    'The group\'s deadline saves you from your own excuses.',
                ],
                'adult' => [
                    'Chamas run on one technology: people who show up.',
                    'Social capital pays dividends that money can\'t buy — and vice versa.',
                    'A good circle multiplies discipline; a bad one multiplies losses. Vet accordingly.',
                ],
            ],
        ],

        'spin_wheel' => [
            'icon' => '🎡',
            'npcs' => ['shiko', 'bazu'],
            'titles' => [
                '8-12'  => ['Round And Round!', 'The Free Spin', 'Lucky Wheel Day', 'Wheeee-l of Fortune'],
                '13-17' => ['Free Spin Energy', 'Bahati Test (Legal)', 'The Zero-Risk Gamble', 'Spin Kidogo'],
                'adult' => ['The Free Lottery', 'Calculated Luck', 'One Spin, Zero Downside', 'House Can\'t Win This One'],
            ],
            'pitches' => [
                '8-12'  => [
                    'The Lucky Wheel is calling your name — take your free spin!',
                    'Give the wheel one big spin and see what Pesa City gifts you today!',
                    'Free spin day! The only wheel where you never lose your coins!',
                ],
                '13-17' => [
                    'Spin the wheel. Only game in town where the house literally can\'t win.',
                    'Free spin available. Bahati hupenda watu wanaojitokeza.',
                    'Take the spin. Note the feeling — FREE luck is the only luck worth having.',
                ],
                'adult' => [
                    'Take your free spin. Enjoy it — and notice that real gambling never feels this cheap.',
                    'One spin, zero stake. The only sustainable relationship with luck.',
                    'Spin the wheel. When chance costs nothing, it\'s entertainment; when it costs rent, it\'s a problem.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Free surprises are fun — but never PAY to gamble!',
                    'Luck is a nice visitor, a terrible landlord.',
                    'Enjoy lucky moments; build on steady ones.',
                ],
                '13-17' => [
                    'Luck is a bonus, never a plan.',
                    'If a game needs your money to play, the game is winning — not you.',
                    'Betting apps are casinos in your pocket; the house always eats.',
                ],
                'adult' => [
                    'If luck charges an entry fee, it\'s not luck — it\'s revenue. Yours, leaving.',
                    'The lottery is a tax on hope; free spins are just fun. Know the difference.',
                    'Chance favours the prepared balance sheet.',
                ],
            ],
        ],

        'earn_badge' => [
            'icon' => '🏅',
            'npcs' => ['madam_rita', 'shiko'],
            'titles' => [
                '8-12'  => ['Badge Hunter!', 'Shiny Achievement Time', 'Medal Mission', 'Collector\'s Pride'],
                '13-17' => ['Trophy Cabinet Szn', 'Achievement Unlocked', 'Rack Up The Hardware', 'Badge Behaviour'],
                'adult' => ['Credentials, Earned', 'The Track Record', 'Proof Of Work', 'Merit Visible'],
            ],
            'pitches' => [
                '8-12'  => [
                    'Earn any badge! Do something great and let Pesa City pin a medal on it!',
                    'Badge mission: achieve something new and collect the shiny proof!',
                    'Go earn a badge — heroes collect medals, savers collect badges!',
                ],
                '13-17' => [
                    'Unlock any badge. Receipts za hustle — proof beats stories kila time.',
                    'Add to the trophy cabinet. Achievements compound like everything else here.',
                    'Earn a badge. Let the profile speak so you don\'t have to.',
                ],
                'adult' => [
                    'Earn any badge. A visible track record opens doors quietly.',
                    'Collect another credential — momentum you can point at.',
                    'One more badge: small proof, accumulating into reputation.',
                ],
            ],
            'lessons' => [
                '8-12'  => [
                    'Every badge is proof you can do hard things!',
                    'Achievements stack — one leads to the next.',
                    'Celebrate small wins; they\'re bricks of the big one.',
                ],
                '13-17' => [
                    'A track record is built one visible win at a time.',
                    'Consistency turns small achievements into a reputation.',
                    'What you measure and celebrate, you repeat.',
                ],
                'adult' => [
                    'Reputation is compounded evidence — deposit into it regularly.',
                    'Progress that goes unrecorded goes unrewarded; keep score.',
                    'Milestones are motivation infrastructure — build them deliberately.',
                ],
            ],
        ],
    ],
];
