<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Your PesaQuest Weekly Summary</title>
<style>
  body { margin:0; padding:0; background:#0f0e1a; font-family:'Segoe UI',Arial,sans-serif; color:#e2e8f0; }
  .wrap { max-width:580px; margin:0 auto; padding:32px 16px; }
  .card { background:linear-gradient(145deg,#1a1830,#12112a); border:1px solid rgba(99,102,241,0.25); border-radius:20px; padding:32px; margin-bottom:20px; }
  .logo { text-align:center; margin-bottom:28px; }
  .logo-text { font-size:1.5rem; font-weight:900; background:linear-gradient(135deg,#6366f1,#a78bfa,#f59e0b); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text; }
  .greeting { font-size:1.1rem; font-weight:700; margin-bottom:6px; }
  .sub { font-size:0.85rem; color:#9ca3af; margin-bottom:24px; }
  .stat-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; margin-bottom:24px; }
  .stat { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 16px; }
  .stat-label { font-size:0.72rem; color:#9ca3af; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px; }
  .stat-val { font-size:1.25rem; font-weight:900; color:white; }
  .alert-row { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); border-radius:12px; padding:12px 16px; margin-bottom:16px; font-size:0.82rem; color:#f87171; }
  .success-row { background:rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.2); border-radius:12px; padding:12px 16px; margin-bottom:16px; font-size:0.82rem; color:#34d399; }
  .cta { display:block; text-align:center; background:linear-gradient(135deg,#6366f1,#a78bfa); color:white !important; font-weight:900; padding:14px 28px; border-radius:14px; text-decoration:none; font-size:0.95rem; margin-top:24px; }
  .footer { text-align:center; font-size:0.75rem; color:#6b7280; margin-top:24px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.06); }
</style>
</head>
<body>
<div class="wrap">
  <div class="logo">
    <div class="logo-text">💰 PesaQuest</div>
    <div style="font-size:0.78rem;color:#6b7280;margin-top:4px;">Your weekly financial progress report</div>
  </div>

  <div class="card">
    <div class="greeting">Hey {{ $summary['name'] }}! 👋</div>
    <div class="sub">Here's what's been happening in your financial journey this week.</div>

    <div class="stat-grid">
      <div class="stat">
        <div class="stat-label">💰 Balance</div>
        <div class="stat-val">Ksh {{ number_format($summary['balance']) }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">📈 Net Worth</div>
        <div class="stat-val">Ksh {{ number_format($summary['net_worth']) }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">⭐ Total XP</div>
        <div class="stat-val">{{ number_format($summary['xp']) }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">🎯 Level</div>
        <div class="stat-val">Level {{ $summary['level'] }}</div>
      </div>
      <div class="stat">
        <div class="stat-label">🔥 Streak</div>
        <div class="stat-val">{{ $summary['streak'] }} days</div>
      </div>
      <div class="stat">
        <div class="stat-label">🏆 Percentile</div>
        <div class="stat-val">Top {{ max(1, 100 - $summary['percentile'] + 1) }}%</div>
      </div>
    </div>

    {{-- Life chapter --}}
    <div style="background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.2);border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:0.82rem;">
      🗺️ Life Chapter: <strong style="color:#a5b4fc;">{{ $summary['chapter'] }}</strong> · Level: <strong style="color:white;">{{ $summary['level'] }}</strong>
    </div>

    {{-- Alerts --}}
    @if($summary['overdue_bills'] > 0)
    <div class="alert-row">
      ⚠️ You have <strong>{{ $summary['overdue_bills'] }} overdue bill(s)</strong> — log in to manage them before your credit score drops further.
    </div>
    @endif

    @if($summary['matured_count'] > 0)
    <div class="success-row">
      💰 <strong>{{ $summary['matured_count'] }} investment(s)</strong> have matured and are ready to claim!
    </div>
    @endif

    @if($summary['streak'] >= 5)
    <div class="success-row">
      🎉 <strong>{{ $summary['streak'] }}-day streak!</strong> Your 5-day bonus is active. Keep it going!
    </div>
    @endif

    @if($summary['week_events'] > 0)
    <div style="font-size:0.82rem;color:#9ca3af;margin-bottom:8px;">
      📬 <strong style="color:#e2e8f0;">{{ $summary['week_events'] }}</strong> events happened in your life simulation this week.
    </div>
    @endif

    <a href="{{ url('/dashboard') }}" class="cta">🚀 Open PesaQuest Dashboard</a>
  </div>

  <div class="footer">
    PesaQuest by Moski · <a href="{{ url('/dashboard') }}" style="color:#6366f1;text-decoration:none;">Visit Game</a><br>
    You're receiving this because you have an active PesaQuest account.
  </div>
</div>
</body>
</html>
