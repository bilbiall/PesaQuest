<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <title>Analytics – Admin – PesaQuest</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <style>
        body { background: #0a0a12; }
        .stat-card { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.25rem 1.5rem; }
        .panel { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.07); border-radius: 1rem; padding: 1.5rem; }
        canvas { max-height: 260px; }
        .input-field { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); border-radius: 0.75rem; color: white; padding: 0.5rem 0.875rem; font-size: 0.875rem; transition: border-color 0.2s; width: 100%; }
        .input-field:focus { outline: none; border-color: rgba(99,102,241,0.6); }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="min-h-screen text-white font-sans antialiased">

    <header class="bg-black/50 border-b border-white/5 sticky top-0 z-50 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.index') }}" class="flex items-center gap-2 text-gray-400 hover:text-white transition-colors text-sm mr-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Admin Panel
                </a>
                <div>
                    <h1 class="text-white font-bold text-lg leading-none">📊 Analytics</h1>
                    <p class="text-gray-500 text-xs mt-0.5">Operational view — what happens inside the game, not who visits it</p>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-6">

        <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
            @foreach([
                ['Active Today',      $activeToday,                              'text-emerald-400'],
                ['Live (last 15m)',   $liveOnline,                               'text-teal-400'],
                ['Quest Completion',  $questCompletionRate . '%',                'text-indigo-400'],
                ['Avg Credit Score',  $avgCreditScore,                           'text-amber-400'],
                ['Highest Level',     $highestLevel,                             'text-purple-400'],
                ['Total Savings',     'Ksh ' . number_format($totalSavings),     'text-cyan-400'],
            ] as [$label, $val, $color])
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">{{ $label }}</p>
                <p class="text-2xl font-extrabold {{ $color }}">{{ $val }}</p>
            </div>
            @endforeach
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Savings Accounts</p>
                <p class="text-2xl font-extrabold text-white">{{ number_format($savingsAccountsTotal) }}</p>
                <p class="text-xs text-gray-600 mt-1">+{{ $savingsAccountsToday }} today</p>
            </div>
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Quests Tracked</p>
                <p class="text-2xl font-extrabold text-white">{{ number_format($totalQuests) }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ number_format($completedQuests) }} completed</p>
            </div>
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">Top Level Player</p>
                <p class="text-lg font-extrabold text-white truncate">{{ $highestLevelPlayer?->name ?? '—' }}</p>
                <p class="text-xs text-gray-600 mt-1">Level {{ $highestLevel }}</p>
            </div>
            <div class="stat-card">
                <p class="text-xs text-gray-500 uppercase tracking-wider font-semibold mb-1">County Data Coverage</p>
                <p class="text-2xl font-extrabold text-white">{{ $byCounty->sum('cnt') }}</p>
                <p class="text-xs text-gray-600 mt-1">{{ $unknownCounty }} haven't set one</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="panel">
                <h3 class="font-bold text-white mb-3">💼 Most Popular Jobs</h3>
                @if($popularJobs->isEmpty())
                    <p class="text-sm text-gray-500">No employed players yet.</p>
                @else
                    <canvas id="jobsChart"></canvas>
                @endif
            </div>
            <div class="panel">
                <h3 class="font-bold text-white mb-3">📉 Bills Most Frequently Missed</h3>
                @if($missedBills->isEmpty())
                    <p class="text-sm text-gray-500">No missed bills recorded yet.</p>
                @else
                    <canvas id="billsChart"></canvas>
                @endif
            </div>
            <div class="panel">
                <h3 class="font-bold text-white mb-3">🌍 Players by County</h3>
                @if($byCounty->isEmpty())
                    <p class="text-sm text-gray-500">No players have set a county yet.</p>
                @else
                    <canvas id="countyChart"></canvas>
                @endif
            </div>
            <div class="panel">
                <h3 class="font-bold text-white mb-3">🎯 Quest Completion Rate</h3>
                @if($totalQuests === 0)
                    <p class="text-sm text-gray-500">No quests assigned yet.</p>
                @else
                    <canvas id="questChart" style="max-height:220px;"></canvas>
                @endif
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             THIRD-PARTY TRACKER SETUP
             ═══════════════════════════════════════════ --}}
        <div class="panel" x-data="trackerPanel()">
            <div class="flex items-center justify-between mb-1 flex-wrap gap-3">
                <div>
                    <h2 class="font-bold text-white text-base">🔌 Third-Party Tracking Setup</h2>
                    <p class="text-xs text-gray-500 mt-0.5">This dashboard is the operational view. These three tools cover what it can't: real visitor/marketing analytics, and usability. Paste a key below and it goes live immediately — no redeploy needed.</p>
                </div>
                <button @click="save()" :disabled="saving"
                        class="px-4 py-2 rounded-xl text-sm font-bold text-white shrink-0"
                        style="background:linear-gradient(135deg,#10b981,#059669);">
                    <span x-text="saving ? 'Saving…' : '💾 Save Tracker IDs'"></span>
                </button>
            </div>
            <div x-show="msg" x-cloak class="text-xs font-bold mt-2" :class="ok ? 'text-emerald-400' : 'text-red-400'" x-text="msg"></div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">

                {{-- PostHog --}}
                <div class="rounded-xl p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-white text-sm">📈 PostHog</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="posthog_key ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-500/15 text-gray-500'"
                              x-text="posthog_key ? 'Configured' : 'Not set'"></span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Primary game analytics — funnels, retention, session replay, feature flags on actual player behaviour (quests, jobs, purchases).</p>
                    <details class="mb-3 text-xs text-gray-400">
                        <summary class="cursor-pointer font-bold text-indigo-400 hover:text-indigo-300">How to set this up ▾</summary>
                        <ol class="list-decimal list-inside space-y-1 mt-2 leading-relaxed">
                            <li>Create a free project at <span class="text-gray-300">posthog.com</span> (choose the US or EU cloud — match the Host field below).</li>
                            <li>In the project, go to <span class="text-gray-300">Settings → Project → Project API Key</span> and copy the key starting with <span class="text-gray-300">phc_</span>.</li>
                            <li>Paste it into <b class="text-white">Project API Key</b> below. Leave <b class="text-white">API Host</b> as-is unless you picked the EU region (then use <span class="text-gray-300">https://eu.i.posthog.com</span>).</li>
                            <li>Save — every logged-in player page will start sending events automatically.</li>
                        </ol>
                    </details>
                    <label class="text-[11px] text-gray-400 font-bold">Project API Key
                        <x-help-tip text="PostHog's public project key. Saving one switches product analytics on across every logged-in player page immediately — funnels, retention, session replay and the Pesa Trail events — with no redeploy. Leave it blank and no PostHog script is loaded at all." example="phc_xxxxxxxxxxxx" />
                    </label>
                    <input type="text" x-model="posthog_key" placeholder="phc_xxxxxxxxxxxx" class="input-field mt-1 text-xs">
                    <label class="text-[11px] text-gray-400 font-bold mt-2 block">API Host
                        <x-help-tip text="Which PostHog cloud region your events are sent to — it must match the region you chose when creating the project, or events are accepted by nothing and your dashboards stay empty even though the key is valid. Leave the US default unless you deliberately picked EU." example="https://us.i.posthog.com — or https://eu.i.posthog.com for an EU project" />
                    </label>
                    <input type="text" x-model="posthog_host" placeholder="https://us.i.posthog.com" class="input-field mt-1 text-xs">
                </div>

                {{-- GA4 --}}
                <div class="rounded-xl p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-white text-sm">📣 Google Analytics 4</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="ga4_measurement_id ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-500/15 text-gray-500'"
                              x-text="ga4_measurement_id ? 'Configured' : 'Not set'"></span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Marketing & audience acquisition — where visitors come from (ads, social, search), landing page traffic, sign-up conversion.</p>
                    <details class="mb-3 text-xs text-gray-400">
                        <summary class="cursor-pointer font-bold text-indigo-400 hover:text-indigo-300">How to set this up ▾</summary>
                        <ol class="list-decimal list-inside space-y-1 mt-2 leading-relaxed">
                            <li>Go to <span class="text-gray-300">analytics.google.com</span> → Admin → create a GA4 property for this site.</li>
                            <li>Under <span class="text-gray-300">Data Streams</span>, add a Web stream for your domain.</li>
                            <li>Copy the <b class="text-white">Measurement ID</b> — it looks like <span class="text-gray-300">G-XXXXXXXXXX</span>.</li>
                            <li>Paste it below and save. It loads on the landing page and every player page.</li>
                        </ol>
                    </details>
                    <label class="text-[11px] text-gray-400 font-bold">Measurement ID
                        <x-help-tip text="Google Analytics 4 web-stream ID. Saving it loads GA4 on the landing page and every player page, which is what tells you where signups come from — ads, social or search — and how many visitors convert. Answers the marketing question, not the in-game one. Blank means no GA4 script is loaded." example="G-XXXXXXXXXX" />
                    </label>
                    <input type="text" x-model="ga4_measurement_id" placeholder="G-XXXXXXXXXX" class="input-field mt-1 text-xs">
                </div>

                {{-- Clarity --}}
                <div class="rounded-xl p-4" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.07);">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="font-bold text-white text-sm">🖱️ Microsoft Clarity</h3>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                              :class="clarity_project_id ? 'bg-emerald-500/15 text-emerald-400' : 'bg-gray-500/15 text-gray-500'"
                              x-text="clarity_project_id ? 'Configured' : 'Not set'"></span>
                    </div>
                    <p class="text-xs text-gray-500 mb-3">Usability testing — free heatmaps and session recordings to see exactly where players get stuck or rage-click.</p>
                    <details class="mb-3 text-xs text-gray-400">
                        <summary class="cursor-pointer font-bold text-indigo-400 hover:text-indigo-300">How to set this up ▾</summary>
                        <ol class="list-decimal list-inside space-y-1 mt-2 leading-relaxed">
                            <li>Go to <span class="text-gray-300">clarity.microsoft.com</span> → New project → add your domain.</li>
                            <li>Open <span class="text-gray-300">Settings → Setup</span> and copy the <b class="text-white">Project ID</b> (a short alphanumeric code, not the full script tag).</li>
                            <li>Paste it below and save. Heatmaps/recordings appear in the Clarity dashboard within a few minutes of traffic.</li>
                        </ol>
                    </details>
                    <label class="text-[11px] text-gray-400 font-bold">Project ID
                        <x-help-tip text="Microsoft Clarity project code — paste the short ID only, not the whole script tag Clarity also offers. Saving it starts free heatmaps and session recordings, which is how you find the screens where players rage-click or abandon a flow. Blank means no Clarity script is loaded." example="abcd1234ef" />
                    </label>
                    <input type="text" x-model="clarity_project_id" placeholder="e.g. abcd1234ef" class="input-field mt-1 text-xs">
                </div>
            </div>
            <p class="text-[10px] text-gray-600 mt-4">💡 Leave any field blank to keep that tracker disabled — no script for it is loaded on the site until an ID is saved here.</p>
        </div>
    </div>

    <script>
        const chartFont = { color: '#9ca3af', font: { family: 'Figtree' } };
        Chart.defaults.color = '#9ca3af';
        Chart.defaults.borderColor = 'rgba(255,255,255,0.07)';

        @if($popularJobs->isNotEmpty())
        new Chart(document.getElementById('jobsChart'), {
            type: 'bar',
            data: {
                labels: @json($popularJobs->pluck('label')),
                datasets: [{ data: @json($popularJobs->pluck('count')), backgroundColor: '#6366f1', borderRadius: 6 }],
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } },
        });
        @endif

        @if($missedBills->isNotEmpty())
        new Chart(document.getElementById('billsChart'), {
            type: 'bar',
            data: {
                labels: @json($missedBills->pluck('label')),
                datasets: [{ data: @json($missedBills->pluck('count')), backgroundColor: '#f87171', borderRadius: 6 }],
            },
            options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true } } },
        });
        @endif

        @if($byCounty->isNotEmpty())
        new Chart(document.getElementById('countyChart'), {
            type: 'bar',
            data: {
                labels: @json($byCounty->pluck('county')),
                datasets: [{ data: @json($byCounty->pluck('cnt')), backgroundColor: '#22d3ee', borderRadius: 6 }],
            },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } },
        });
        @endif

        @if($totalQuests > 0)
        new Chart(document.getElementById('questChart'), {
            type: 'doughnut',
            data: {
                labels: ['Completed', 'In Progress'],
                datasets: [{ data: [{{ $completedQuests }}, {{ $totalQuests - $completedQuests }}], backgroundColor: ['#34d399', 'rgba(255,255,255,0.08)'] }],
            },
            options: { plugins: { legend: { position: 'bottom' } } },
        });
        @endif

        function trackerPanel() {
            return {
                saving: false, msg: '', ok: true,
                posthog_key:        {{ Js::from($trackers['posthog_key']) }},
                posthog_host:       {{ Js::from($trackers['posthog_host']) }},
                ga4_measurement_id: {{ Js::from($trackers['ga4_measurement_id']) }},
                clarity_project_id: {{ Js::from($trackers['clarity_project_id']) }},

                async save() {
                    this.saving = true; this.msg = '';
                    try {
                        const res = await fetch('{{ route('admin.trackers.save') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                            body: JSON.stringify({
                                posthog_key: this.posthog_key,
                                posthog_host: this.posthog_host,
                                ga4_measurement_id: this.ga4_measurement_id,
                                clarity_project_id: this.clarity_project_id,
                            }),
                        });
                        const data = await res.json();
                        this.ok  = !!data.success;
                        this.msg = data.success ? '✓ ' + data.message : (Object.values(data.errors || {}).flat().join(' ') || 'Save failed.');
                    } catch (e) { this.ok = false; this.msg = 'Network error.'; }
                    finally { this.saving = false; setTimeout(() => this.msg = '', 5000); }
                },
            };
        }
    </script>
</body>
</html>
