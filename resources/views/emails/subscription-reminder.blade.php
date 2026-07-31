<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body { margin:0;padding:0;background:#07060f;font-family:Arial,sans-serif; }
  .wrap { max-width:520px;margin:0 auto;padding:32px 16px; }
  .card { background:linear-gradient(135deg,#120f2a,#0e0c22);border:1px solid rgba(99,102,241,.25);border-radius:16px;padding:32px;color:#fff; }
  .logo { text-align:center;margin-bottom:24px; }
  .logo-text { font-size:28px;font-weight:900;color:#FFBC00; }
  .logo-sub { font-size:12px;color:#6366f1;letter-spacing:.12em;text-transform:uppercase; }
  h1 { font-size:20px;font-weight:800;margin:0 0 12px;color:#fff; }
  p { font-size:14px;color:#9ca3af;line-height:1.6;margin:0 0 16px; }
  .cta { display:block;text-align:center;background:linear-gradient(135deg,#6366f1,#a78bfa);color:#fff;text-decoration:none;padding:14px 32px;border-radius:9999px;font-weight:800;font-size:15px;margin:24px 0; }
  .footer { text-align:center;margin-top:24px;font-size:11px;color:#4b5563; }
  .badge { display:inline-block;background:rgba(99,102,241,.2);border:1px solid rgba(99,102,241,.35);color:#a5b4fc;padding:4px 12px;border-radius:9999px;font-size:12px;font-weight:700;margin-bottom:16px; }
</style>
</head>
<body>
<div class="wrap">
<div class="card">
  <div class="logo">
    <div class="logo-text">moski</div>
    <div class="logo-sub">PesaQuest · It's Possible</div>
  </div>

  @if($reminderType === '7d')
    <div class="badge">⏰ 7 Days Remaining</div>
    <h1>Your Premium is expiring soon, {{ $user->name }}!</h1>
    <p>Your PesaQuest Premium subscription expires on <strong style="color:#fff">{{ $expiresAt }}</strong>. You have 7 days to renew and keep your streaks, badges, and city access intact.</p>
    <p>Premium gives you unlimited scenarios, full Pesa City access, and priority support. Don't let it slip!</p>

  @elseif($reminderType === '3d')
    <div class="badge">⚠️ 3 Days Left</div>
    <h1>Only 3 days left, {{ $user->name }}!</h1>
    <p>Your Premium subscription expires on <strong style="color:#fff">{{ $expiresAt }}</strong>. Renew now to avoid losing access to premium features and quests.</p>

  @elseif($reminderType === '1d')
    <div class="badge">🚨 Expires Tomorrow</div>
    <h1>Last chance — Premium expires tomorrow!</h1>
    <p>Hi {{ $user->name }}, your PesaQuest Premium subscription expires tomorrow. Renew today and keep building your financial future in Pesa City.</p>

  @elseif($reminderType === 'due')
    <div class="badge">📅 Expired Today</div>
    <h1>Your subscription expired today</h1>
    <p>Hi {{ $user->name }}, your PesaQuest Premium ended today. Renew now and your progress, city, and badges are all waiting for you — nothing was lost.</p>

  @elseif($reminderType === '4d_overdue')
    <div class="badge">💔 4 Days Overdue</div>
    <h1>Pesa City misses you, {{ $user->name }}</h1>
    <p>Your premium expired 4 days ago. Your city is paused and quests are waiting. Come back and keep building your financial legacy — the city needs its mayor.</p>

  @elseif($reminderType === '14d_overdue')
    <div class="badge">🏙️ 2 Weeks Gone</div>
    <h1>Don't let 2 weeks become 2 months</h1>
    <p>Hi {{ $user->name }}, it's been 2 weeks since your Premium expired. Every day you're away, the gap between you and your financial goals widens. Pesa City is ready when you are.</p>
  @endif

  <a href="{{ url('/subscribe') }}" class="cta">🔑 Renew My Premium</a>

  <p style="font-size:12px;color:#6b7280;text-align:center;">Questions? Reply to this email and our team will help.</p>
</div>
<div class="footer">
  You're receiving this because you have a PesaQuest account.<br>
  © {{ date('Y') }} Moski · PesaQuest · Nairobi, Kenya
</div>
</div>
</body>
</html>
