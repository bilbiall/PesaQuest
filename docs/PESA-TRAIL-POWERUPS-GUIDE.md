# Pesa Trail Strategy Powers — Player Guide

**What this is:** a plain-language explanation of the four Strategy Powers in Pesa Trail —
🚀 Boost, 🎲 Reroll, 🛡️ Protect, and 🏦 Bank — how they work, when they show up, and how to
use them well. Written for players, teachers, and anyone showing someone else how to play.
If you're looking for the engineering reference (code, data model, troubleshooting), see
`docs/PESA-TRAIL-POWERS.md` instead — this file is the "how do I play it" companion to that
"how is it built" one.

---

## 1. The idea in one sentence

**Luck decides what happens to you on the trail. Strategy decides how well you handle it.**

The dice still control the race — you can't choose to skip your turn, pick your roll, or
decide when the game ends. What you *can* control is how you react to a lucky or unlucky
roll once it happens, using four limited-use powers.

## 2. Where to find them

Every 1v1 Pesa Trail game — whether that's practicing solo against Robo the bot, or a real
head-to-head Rivals Trail wager against a friend — shows a **Strategy Powers** panel in the
sidebar next to the dice, with four badges:

```
 🚀 BOOST    🎲 REROLL    🛡️ PROTECT    🏦 BANK
    3            2            3            4
```

The number under each icon is how many uses you have **left this game**. They don't refill
mid-game and don't carry over to your next game — every fresh round starts you back at the
full amounts above.

Powers only appear in **1v1 games**. Standard multiplayer matches with 3 or more players
don't get them (yet) — the dice-only economy stays exactly as it's always been there.

## 3. The four powers, one at a time

### 🚀 Boost — "I think this is a good time to go for more"

- **3 uses per game.**
- **You use it *before* rolling** — tap the Boost badge, and it arms for your very next
  roll. You don't know yet what tile you'll land on.
- If your next roll lands you on a **gain** (a reward tile, or a lucky Mystery tile),
  that gain is **50% bigger**. Land on a loss instead, and Boost is used up with nothing to
  show for it — that's the risk.
- **Example:** you arm Boost, then roll into a tile that would normally pay you
  KES 2,000. With Boost active, you get **KES 3,000** instead — an extra KES 1,000.

### 🎲 Reroll — "I don't like this number"

- **2 uses per game** — the smallest allowance of the four, on purpose. Reroll is the
  most direct way to fight the dice, so you only get two chances to use it.
- **Offered automatically** the instant you see your roll, before you move anywhere. A
  short prompt appears: *"You rolled a 4. Reroll?"*
- If you Reroll, you roll again immediately and **must move by the new number** — there's
  no rerolling the reroll, even if the second number is worse.
- If you skip it, you move by your original roll as normal.

### 🛡️ Protect — "This loss is too painful to accept"

- **3 uses per game.**
- **Offered automatically** the moment you land on a tile (or an unlucky Mystery tile)
  that's about to cost you money — *before* the loss actually happens. You're told exactly
  how much you'd lose.
- Use it, and the loss is cancelled completely — your balance doesn't move.
- Skip it (or let the 8-second timer run out), and the loss applies as normal.
- Since you only have three, the game is really asking: *is this particular loss big
  enough to spend one on?*

### 🏦 Bank — "I've made money — should I lock some of it in?"

- **4 uses per game.**
- **Offered automatically** right after you land on a gain — the money has already been
  added to your Active Balance, and now you get the option to move some of it somewhere
  safer.
- You can bank **up to 20% of your current Active Balance** — quick preset buttons let you
  pick 25%, 50%, 75%, or the full 100% of that 20% allowance.
- Banked money moves to a separate **Banked Balance** (shown as its own 🏦 chip at the top
  of the screen). From that moment on, it:
  - can never be reduced by a future tile — good or bad tiles alike leave it untouched;
  - is **completely protected from your opponent's claim** if they win the race (see
    below) — this is the entire point of banking;
  - is yours for good once the game ends, added straight on top of whatever's left in your
    Active Balance.
- The trade-off: money you bank stops being exposed to *future* gains too. It's safety,
  not free money.

**Worked example (straight from the design spec):** you have KES 10,000 Active. You land
on a gain and grow to KES 12,000. Bank offers you up to 20% of that — **KES 2,400**. Bank
the full amount and your balances become **Active: KES 9,600 / Banked: KES 2,400**.

## 4. What happens if you don't answer in time?

Every Reroll/Protect/Bank prompt shows a countdown ("Auto-skip in 8s"). If you don't
respond, the game auto-resolves it as **skip** — never as "use." That's a deliberate
fairness rule: an idle prompt can only ever cost you a missed opportunity, never
accidentally burn one of your limited uses or apply an effect you never agreed to.

## 5. Winning, losing, and what your powers actually protected

When someone reaches the finish tile, the race ends immediately (the dice still decide
*when* that happens — powers never change that). Then:

- The winner keeps their **entire Active Balance**, plus **60% of the loser's Active
  Balance**.
- The loser keeps the other 40% of their Active Balance.
- **Everyone's Banked Balance is untouched by any of this** — winner or loser, whatever
  you banked during the game is yours, in full, on top of the above.

So banking isn't just "safer growth" — in a real wager round, it's the one part of your
balance your opponent's win can never touch.

## 6. A quick strategy cheat sheet

- **Protect** the losses that would actually hurt — skip it for small ones so you still
  have it later for a big one.
- **Bank** early and often once you're ahead — a small, repeated habit of securing 20% at a
  time beats hoping you'll remember to do it once near the end.
- **Reroll** away from a roll that would land you on a snake head or a heavy expense tile —
  you can usually see what's coming before you decide.
- **Boost** when you're a fair way from the finish and have several reward-heavy tiles
  still ahead of you — arming it right before the finish tile (which pays no tile effect at
  all) wastes it.
- You're not trying to guarantee a win — the goal, per the game's own design philosophy, is
  to finish able to say *"I know exactly where I could have played that better,"* not
  *"there was nothing I could have done."*

## 7. Money Mined — the leaderboard that isn't just about winning

Pesa Trail's arcade lobby has a **Money Mined leaderboard**, separate from your win/loss
record. It ranks players by their total kept savings (Active + Banked) across every
finished game — not just how many races they've won. A player who wins less often but
manages their Active/Banked split well can still climb higher than someone who wins a lot
but always finishes broke. It's there to reward good money decisions, not just fast dice.

---

*For the technical side of any of this — data model, the pause/resume mechanics behind the
decision prompts, bot behavior, or what to check if a power ever misbehaves — see
`docs/PESA-TRAIL-POWERS.md`.*
