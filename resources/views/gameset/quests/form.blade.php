<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="{{ asset('moski-logo.png') }}" type="image/png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $mode === 'create' ? 'New Quest' : 'Edit Quest' }} — GameSet</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { background:#07060f; font-family:'Figtree',sans-serif; }
        [x-cloak]{ display:none !important; }
        .fl { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-bottom:6px; }
        .fi { width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; color:#e2e8f0; font-size:13px; outline:none; transition:border-color .2s; }
        .fi:focus { border-color:rgba(124,58,237,0.6); }
        .fi::placeholder { color:#4b5563; }
        textarea.fi { resize:vertical; min-height:80px; }
        select.fi option { background:#1e1b4b; }
        .sc { background:rgba(255,255,255,0.03); border:1px solid rgba(255,255,255,0.07); border-radius:16px; padding:20px 22px; }
        .st { font-size:13px; font-weight:800; color:#a78bfa; margin-bottom:14px; display:flex; align-items:center; gap:8px; }
        .hint { font-size:11px; color:#4b5563; margin-top:4px; }
        .trig-row { background:rgba(255,255,255,0.02); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:14px 16px; margin-bottom:10px; position:relative; }
        .opt-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:6px; margin-top:8px; max-height:220px; overflow-y:auto; padding:2px; }
        .opt-item { display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:8px; padding:6px 10px; cursor:pointer; transition:background .15s; }
        .opt-item:hover { background:rgba(124,58,237,0.12); border-color:rgba(124,58,237,0.3); }
        .opt-item.selected { background:rgba(124,58,237,0.2); border-color:rgba(124,58,237,0.5); }
        .opt-label { font-size:12px; color:#d1d5db; flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
        .group-label { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#6b7280; margin-top:10px; margin-bottom:4px; }
    </style>
</head>
<body class="text-white min-h-screen">

<nav class="border-b border-white/5 bg-black/40 backdrop-blur-xl sticky top-0 z-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center gap-3">
        <a href="{{ route('gameset.quests.index') }}" class="text-gray-400 hover:text-white transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Quests
        </a>
        <span class="text-white/20">/</span>
        <span class="text-white font-bold text-sm">{{ $mode === 'create' ? 'New Quest' : 'Edit: '.($quest->title ?? '') }}</span>
    </div>
</nav>

<div class="max-w-3xl mx-auto px-4 sm:px-6 py-8"
     x-data="questForm()"
     x-init="init()">

    @if($errors->any())
    <div class="mb-6 px-4 py-3 rounded-xl text-sm font-semibold text-red-300 border border-red-500/30" style="background:rgba(239,68,68,0.1);">
        <div class="font-bold mb-1">Fix the following:</div>
        <ul class="list-disc list-inside space-y-0.5">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <form method="POST"
          enctype="multipart/form-data"
          action="{{ $mode === 'create' ? route('gameset.quests.store') : route('gameset.quests.update', $quest) }}"
          @submit="prepareSubmit">
        @csrf
        @if($mode === 'edit') @method('PUT') @endif

        {{-- BASICS --}}
        <div class="sc mb-5">
            <div class="st">📜 Quest Basics</div>
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="fl">Icon (emoji)</label>
                    <input type="text" name="icon" class="fi text-center text-2xl" maxlength="4"
                           value="{{ old('icon', $quest->icon ?? '📜') }}" placeholder="📜"/>
                </div>
                <div class="sm:col-span-3">
                    <label class="fl">Quest Title *</label>
                    <input type="text" name="title" class="fi" required maxlength="120"
                           value="{{ old('title', $quest->title ?? '') }}" placeholder="e.g. Get Connected"/>
                </div>
            </div>

            {{-- Quest image --}}
            <div class="mt-4">
                <label class="fl">Quest Image (optional — replaces emoji in card)</label>
                <div class="flex items-start gap-4 flex-wrap">
                    <div class="relative flex-shrink-0">
                        <div x-show="!imagePreview && !existingImage"
                             style="width:88px;height:88px;border-radius:12px;background:rgba(255,255,255,0.04);border:2px dashed rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;font-size:28px;cursor:pointer;"
                             @click="$refs.imgInput.click()">📷</div>
                        <img x-show="imagePreview || existingImage"
                             :src="imagePreview || existingImage"
                             style="width:88px;height:88px;border-radius:12px;object-fit:cover;border:2px solid rgba(124,58,237,0.4);cursor:pointer;"
                             @click="$refs.imgInput.click()">
                        <button type="button" x-show="imagePreview || existingImage"
                                @click="clearImage()"
                                style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;border-radius:50%;background:rgba(239,68,68,0.8);border:none;color:#fff;font-size:11px;display:flex;align-items:center;justify-content:center;cursor:pointer;">✕</button>
                    </div>
                    <div class="flex-1 min-w-0">
                        <input type="file" name="image" accept="image/*,.svg" x-ref="imgInput"
                               @change="previewImage($event)" style="display:none;">
                        <button type="button" @click="$refs.imgInput.click()"
                                class="text-sm text-violet-400 border border-violet-500/30 rounded-lg px-4 py-2 hover:bg-violet-500/10 transition-colors">
                            {{ $mode === 'edit' && $quest?->image ? 'Replace Image' : 'Upload Image' }}
                        </button>
                        <div class="hint mt-2">SVG vectors or raster images (JPG/PNG). Auto-compressed to 400×400px max, ~100KB. Leave blank to use the emoji icon.</div>
                        @if($mode === 'edit' && $quest?->image)
                        <input type="hidden" name="_remove_image" x-bind:value="removeImage ? '1' : '0'">
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <label class="fl">Description (shown in quest list)</label>
                <textarea name="description" class="fi" maxlength="500" placeholder="Short overview of what this quest is about...">{{ old('description', $quest->description ?? '') }}</textarea>
            </div>
            <div class="mt-4">
                <label class="fl">How to Complete (guide shown in quest popup)</label>
                <textarea name="instructions" class="fi" style="min-height:100px;"
                          placeholder="Step-by-step guide. e.g. Head to the Marketplace → Electronics. Buy a smartphone. Once you own a mobile device you unlock mobile money, online jobs, and digital banking.">{{ old('instructions', $quest->instructions ?? '') }}</textarea>
            </div>
            <div class="mt-4">
                <label class="fl">Lesson Learned (shown on quest completion)</label>
                <textarea name="lesson" class="fi" style="min-height:70px;"
                          placeholder="e.g. Owning a mobile phone gives you access to M-Pesa — Kenya's mobile money service — letting you save, send, and receive money digitally.">{{ old('lesson', $quest->lesson ?? '') }}</textarea>
                <div class="hint">A short financial insight shown when the player completes the quest.</div>
            </div>
        </div>

        {{-- LEVEL & REWARDS --}}
        <div class="sc mb-5">
            <div class="st">⭐ Level Gate & Rewards</div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label class="fl">Level Required *</label>
                    <select name="level_required" class="fi" required>
                        @for($i = 1; $i <= 10; $i++)
                        <option value="{{ $i }}" {{ old('level_required', $quest->level_required ?? 1) == $i ? 'selected' : '' }}>Level {{ $i }}</option>
                        @endfor
                    </select>
                    <div class="hint">Players below this level see the quest as locked.</div>
                </div>
                <div>
                    <label class="fl">XP Reward *</label>
                    <input type="number" name="xp_reward" class="fi" required min="0" max="99999"
                           value="{{ old('xp_reward', $quest->xp_reward ?? 50) }}"/>
                </div>
                <div>
                    <label class="fl">Ksh Reward</label>
                    <input type="number" name="kes_reward" class="fi" min="0" max="9999999"
                           value="{{ old('kes_reward', $quest->kes_reward ?? 0) }}"/>
                    <div class="hint">0 = no cash reward.</div>
                </div>
                <div>
                    <label class="fl">Sort Order</label>
                    <input type="number" name="sort_order" class="fi" min="0"
                           value="{{ old('sort_order', $quest->sort_order ?? 0) }}"/>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="fl">Age Group *</label>
                    <select name="age_group" class="fi" required>
                        @foreach(['all','8-12','13-17','18-25','26+'] as $ag)
                        <option value="{{ $ag }}" {{ old('age_group', $quest->age_group ?? 'all') === $ag ? 'selected' : '' }}>{{ $ag }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="w-4 h-4 rounded accent-violet-500"
                               {{ old('is_active', $quest->is_active ?? true) ? 'checked' : '' }}>
                        <span class="text-sm font-semibold text-gray-300">Active (visible to players)</span>
                    </label>
                </div>
            </div>

            {{-- Career Path Targeting --}}
            @php $existingCareerFields = old('career_fields', $quest->career_fields ?? []); @endphp
            <div class="mt-4" x-data="{ allPaths: {{ empty($existingCareerFields) ? 'true' : 'false' }} }">
                <label class="fl">Career Path Targeting</label>
                <label class="flex items-center gap-3 cursor-pointer mb-2.5">
                    <input type="checkbox" name="career_fields_all" value="1" x-model="allPaths" class="w-4 h-4 rounded accent-violet-500">
                    <span class="text-sm font-semibold text-gray-300">🌍 All career paths (default)</span>
                </label>
                <div x-show="!allPaths" x-cloak class="opt-grid" style="grid-template-columns:repeat(auto-fill,minmax(160px,1fr));">
                    @foreach(\App\Services\CareerService::fields() as $cf)
                    <label class="opt-item" :class="!allPaths ? '' : 'opacity-40'">
                        <input type="checkbox" name="career_fields[]" value="{{ $cf['key'] }}"
                               {{ in_array($cf['key'], $existingCareerFields) ? 'checked' : '' }}
                               class="w-3.5 h-3.5 rounded accent-violet-500 flex-shrink-0">
                        <span class="opt-label">{{ $cf['icon'] }} {{ $cf['label'] }}</span>
                    </label>
                    @endforeach
                </div>
                <div class="hint">Only players who chose one of the checked career paths will see this quest. Pick "All career paths" for general quests (saving, budgeting, etc.) and target specific ones for career-flavored goals (e.g. "Get your first Tech job").</div>
            </div>
        </div>

        {{-- QUEST BUILDING GUIDE (collapsible) --}}
        <div class="mb-5" x-data="{ guideOpen: false }">
            <button type="button" @click="guideOpen = !guideOpen"
                    style="width:100%;display:flex;align-items:center;justify-content:space-between;padding:12px 16px;border-radius:12px;background:rgba(99,102,241,0.08);border:1px solid rgba(99,102,241,0.25);color:#a5b4fc;font-size:13px;font-weight:800;cursor:pointer;transition:background .15s;"
                    :style="guideOpen ? 'background:rgba(99,102,241,0.14);border-color:rgba(99,102,241,0.4);' : ''">
                <span>📖 Quest Building Guide</span>
                <svg class="w-4 h-4 transition-transform" :class="guideOpen ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="guideOpen" x-transition style="margin-top:8px;padding:16px 18px;border-radius:12px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.07);">

                {{-- Simple Quest examples --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#818cf8;margin-bottom:8px;">Simple Quest Examples</div>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <div style="padding:10px 12px;border-radius:10px;background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.18);">
                            <div style="font-size:12px;font-weight:800;color:#e2e8f0;margin-bottom:4px;">📱 Buy the Smartphone</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">trigger_type: <strong style="color:#fbbf24;">buy_item_slug</strong></span>
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">value: <strong style="color:#34d399;">smartphone</strong></span>
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">label: "Buy a smartphone from the Marketplace"</span>
                            </div>
                        </div>
                        <div style="padding:10px 12px;border-radius:10px;background:rgba(99,102,241,0.07);border:1px solid rgba(99,102,241,0.18);">
                            <div style="font-size:12px;font-weight:800;color:#e2e8f0;margin-bottom:4px;">⚡ Reach Level 5</div>
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">trigger_type: <strong style="color:#fbbf24;">reach_level</strong></span>
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">value: <strong style="color:#34d399;">5</strong></span>
                                <span style="font-size:11px;padding:2px 8px;border-radius:6px;background:rgba(255,255,255,0.06);color:#9ca3af;">label: "Reach Level 5 in PesaCity"</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Multi-Step example --}}
                <div style="margin-bottom:16px;">
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#34d399;margin-bottom:8px;">Multi-Step Quest Example</div>
                    <div style="padding:10px 12px;border-radius:10px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.2);">
                        <div style="font-size:12px;font-weight:800;color:#e2e8f0;margin-bottom:8px;">🏦 Start Your Savings Journey</div>
                        <div style="display:flex;flex-direction:column;gap:6px;">
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <span style="font-size:11px;padding:2px 6px;border-radius:6px;background:rgba(16,185,129,0.15);color:#34d399;font-weight:700;flex-shrink:0;">Step 1</span>
                                <div style="font-size:11px;color:#9ca3af;">type: <strong style="color:#fbbf24;">open_savings</strong> · value: any · label: "Open a savings scheme at the Bank"</div>
                            </div>
                            <div style="display:flex;align-items:flex-start;gap:8px;">
                                <span style="font-size:11px;padding:2px 6px;border-radius:6px;background:rgba(16,185,129,0.15);color:#34d399;font-weight:700;flex-shrink:0;">Step 2</span>
                                <div style="font-size:11px;color:#9ca3af;">type: <strong style="color:#fbbf24;">deposit_savings</strong> · value: <strong style="color:#34d399;">5000</strong> · label: "Save at least KES 5,000"</div>
                            </div>
                        </div>
                        <div style="margin-top:8px;font-size:11px;color:#6b7280;">Mode: <strong style="color:#a5b4fc;">ALL steps required</strong> — player completes both steps to finish the quest.</div>
                    </div>
                </div>

                {{-- Trigger types reference table --}}
                <div>
                    <div style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#f59e0b;margin-bottom:8px;">Available Trigger Types</div>
                    <div style="overflow-x:auto;border-radius:10px;border:1px solid rgba(255,255,255,0.08);">
                        <table style="width:100%;border-collapse:collapse;font-size:11px;">
                            <thead>
                                <tr style="background:rgba(255,255,255,0.05);">
                                    <th style="padding:7px 10px;text-align:left;color:#9ca3af;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.08);">Type</th>
                                    <th style="padding:7px 10px;text-align:left;color:#9ca3af;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.08);">Value</th>
                                    <th style="padding:7px 10px;text-align:left;color:#9ca3af;font-weight:700;border-bottom:1px solid rgba(255,255,255,0.08);">Example</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach([
                                    ['asset_purchased / buy_item_slug', 'asset slug', 'smartphone'],
                                    ['asset_category / buy_item_category', 'category name', 'electronics'],
                                    ['open_savings / savings_created', 'any — fires when the player opens ANY savings pocket', 'any'],
                                    ['deposit_savings / savings_amount', 'KES target for ONE savings pocket — checked after every deposit into that pocket', '5000'],
                                    ['reach_savings', 'KES target for TOTAL saved across ALL pockets combined — 3 pockets of 4k each = 12k total', '10000'],
                                    ['reach_balance / balance_reached', 'KES target for WALLET cash only (savings & assets don\'t count)', '100000'],
                                    ['reach_net_worth', 'KES target for net worth: cash + savings + assets − loans', '250000'],
                                    ['play_scenario / scenario_completed', 'scenario id', '1'],
                                    ['reach_level / level_reached', 'level number', '5'],
                                    ['take_course', 'course slug', 'personal-finance-101'],
                                    ['get_job', 'job id or slug', '1'],
                                    ['join_chama', '(none)', '—'],
                                ] as [$type, $value, $example])
                                <tr style="border-bottom:1px solid rgba(255,255,255,0.05);">
                                    <td style="padding:6px 10px;color:#fbbf24;font-family:monospace;font-size:10px;white-space:nowrap;">{{ $type }}</td>
                                    <td style="padding:6px 10px;color:#6b7280;">{{ $value }}</td>
                                    <td style="padding:6px 10px;color:#34d399;font-family:monospace;font-size:10px;">{{ $example }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- AUTO-TRIGGERS --}}
        <div class="sc mb-8">
            <div class="st">🎯 Auto-Triggers</div>
            <p class="text-xs text-gray-500 mb-4">Add one or more triggers. Use the completion mode to control when the quest completes. Leave all triggers empty for a manually-submitted quest.</p>

            {{-- Trigger mode toggle (only meaningful with 2+ triggers) --}}
            <div class="mb-5">
                <label class="fl">Completion Mode
                    <span style="font-size:10px;font-weight:500;text-transform:none;letter-spacing:0;color:#6b7280;"> — applies when you have multiple triggers</span>
                </label>
                <div style="display:flex;gap:8px;margin-top:4px;">
                    <label style="flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;cursor:pointer;transition:all .15s;"
                           :style="triggerMode==='all' ? 'background:rgba(99,102,241,0.18);border:1px solid rgba(99,102,241,0.5);' : 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);'">
                        <input type="radio" x-model="triggerMode" value="all" style="accent-color:#818cf8;">
                        <div>
                            <div style="font-size:12px;font-weight:800;color:#a5b4fc;">ALL steps required</div>
                            <div style="font-size:11px;color:#6b7280;">Player must complete every trigger step</div>
                        </div>
                    </label>
                    <label style="flex:1;display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;cursor:pointer;transition:all .15s;"
                           :style="triggerMode==='any' ? 'background:rgba(16,185,129,0.15);border:1px solid rgba(16,185,129,0.4);' : 'background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.1);'">
                        <input type="radio" x-model="triggerMode" value="any" style="accent-color:#34d399;">
                        <div>
                            <div style="font-size:12px;font-weight:800;color:#34d399;">ANY step completes quest</div>
                            <div style="font-size:11px;color:#6b7280;">First trigger that fires wins</div>
                        </div>
                    </label>
                </div>
                <input type="hidden" name="trigger_mode" x-bind:value="triggerMode">
            </div>

            {{-- Trigger rows --}}
            <template x-for="(trig, idx) in triggers" :key="idx">
                <div class="trig-row">
                    <button type="button" @click="removeTrigger(idx)"
                            style="position:absolute;top:10px;right:10px;width:22px;height:22px;border-radius:50%;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.25);color:#f87171;font-size:11px;display:flex;align-items:center;justify-content:center;cursor:pointer;">✕</button>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-3">
                        <div>
                            <label class="fl">Trigger Type</label>
                            <select class="fi" x-model="trig.type" @change="onTriggerTypeChange(trig, idx)">
                                <option value="">— Select trigger —</option>
                                <optgroup label="Marketplace">
                                    <option value="buy_item_category">Buy item from category</option>
                                    <option value="buy_item_slug">Buy specific item</option>
                                </optgroup>
                                <optgroup label="Savings & Balance">
                                    <option value="open_savings">Open a savings account</option>
                                    <option value="deposit_savings">ONE savings pocket reaches Ksh X</option>
                                    <option value="reach_savings">TOTAL saved (all pockets combined) reaches Ksh X</option>
                                    <option value="reach_balance">Wallet cash (spendable) reaches Ksh X</option>
                                    <option value="reach_net_worth">Net worth (cash + savings + assets − loans) reaches Ksh X</option>
                                </optgroup>
                                <optgroup label="Career & Skills">
                                    <option value="take_course">Complete a course</option>
                                    <option value="get_job">Get a job</option>
                                </optgroup>
                                <optgroup label="Community">
                                    <option value="join_chama">Join a Chama</option>
                                </optgroup>
                                <optgroup label="Activities">
                                    <option value="spin_wheel">Spin the wheel</option>
                                    <option value="play_scenario">Play a scenario</option>
                                    <option value="reach_level">Reach a player level</option>
                                    <option value="earn_badge">Earn a badge</option>
                                </optgroup>
                                <optgroup label="Chains">
                                    <option value="complete_quest">Complete another quest first (chain)</option>
                                </optgroup>
                            </select>
                        </div>
                        <div>
                            <label class="fl">Trigger Label (shown to player)</label>
                            <input type="text" class="fi" x-model="trig.label"
                                   placeholder="e.g. Buy any vehicle in the Marketplace"/>
                        </div>
                    </div>

                    {{-- No-value triggers (always fires, no value needed) --}}
                    <template x-if="noValueTypes.includes(trig.type)">
                        <div class="text-xs text-emerald-400/70 mt-1">✓ This trigger fires automatically — no specific value needed.</div>
                    </template>

                    {{-- Numeric threshold triggers --}}
                    <template x-if="numericTypes.includes(trig.type)">
                        <div>
                            <label class="fl">Threshold Amount (Ksh)</label>
                            <input type="number" class="fi" min="0"
                                   :value="trig.values[0] || ''"
                                   @input="trig.values = [$event.target.value]"
                                   placeholder="e.g. 15000"/>
                            <div class="hint" x-text="'Quest completes when the amount reaches this threshold.'"></div>
                        </div>
                    </template>

                    {{-- Selectable options (multi-select checkboxes) --}}
                    <template x-if="trig.type && !noValueTypes.includes(trig.type) && !numericTypes.includes(trig.type)">
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                                <label class="fl" style="margin-bottom:0;">Select Values <span style="color:#6b7280;font-weight:500;text-transform:none;letter-spacing:0;">(leave all unchecked = any)</span></label>
                                <span x-show="trig.values.length > 0" class="text-xs text-violet-400"
                                      x-text="trig.values.length + ' selected'"></span>
                            </div>
                            <template x-if="optionsLoading[idx]">
                                <div class="text-xs text-gray-500">Loading options…</div>
                            </template>
                            <template x-if="!optionsLoading[idx] && (!options[trig.type] || options[trig.type].length === 0)">
                                <div class="text-xs text-gray-500">No options available for this trigger type.</div>
                            </template>
                            <template x-if="!optionsLoading[idx] && options[trig.type] && options[trig.type].length > 0">
                                <div>
                                    {{-- Group headers (for buy_item_slug) --}}
                                    <template x-if="options[trig.type][0] && options[trig.type][0].group">
                                        <div>
                                            <template x-for="grp in getGroups(trig.type)" :key="grp">
                                                <div>
                                                    <div class="group-label" x-text="grp"></div>
                                                    <div class="opt-grid">
                                                        <template x-for="opt in getGroupOptions(trig.type, grp)" :key="opt.value">
                                                            <div class="opt-item"
                                                                 :class="{ selected: trig.values.includes(opt.value) }"
                                                                 @click="toggleValue(trig, opt.value)">
                                                                <div class="opt-label" x-text="opt.label"></div>
                                                                <span x-show="trig.values.includes(opt.value)"
                                                                      style="flex-shrink:0;color:#a78bfa;font-size:12px;">✓</span>
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                    {{-- Flat list --}}
                                    <template x-if="!options[trig.type][0] || !options[trig.type][0].group">
                                        <div class="opt-grid">
                                            <template x-for="opt in options[trig.type]" :key="opt.value">
                                                <div class="opt-item"
                                                     :class="{ selected: trig.values.includes(opt.value) }"
                                                     @click="toggleValue(trig, opt.value)">
                                                    <div class="opt-label" x-text="opt.label"></div>
                                                    <span x-show="trig.values.includes(opt.value)"
                                                          style="flex-shrink:0;color:#a78bfa;font-size:12px;">✓</span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>

            <button type="button" @click="addTrigger()"
                    style="display:flex;align-items:center;gap:6px;padding:8px 16px;border-radius:10px;background:rgba(124,58,237,0.1);border:1px dashed rgba(124,58,237,0.4);color:#a78bfa;font-size:13px;font-weight:700;cursor:pointer;transition:background .15s;"
                    onmouseover="this.style.background='rgba(124,58,237,0.18)'" onmouseout="this.style.background='rgba(124,58,237,0.1)'">
                <span style="font-size:16px;">+</span> Add Trigger
            </button>

            {{-- Hidden input carries the JSON to the server --}}
            <input type="hidden" name="triggers" x-bind:value="triggersJson">
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="flex-1 py-3 rounded-xl font-black text-white text-sm transition-all hover:scale-[1.01]"
                    style="background:linear-gradient(135deg,#7c3aed,#6d28d9);box-shadow:0 4px 14px rgba(124,58,237,.35);">
                {{ $mode === 'create' ? '✓ Create Quest' : '✓ Save Changes' }}
            </button>
            <a href="{{ route('gameset.quests.index') }}"
               class="px-5 py-3 rounded-xl text-sm font-semibold text-gray-400 border border-white/10 hover:bg-white/5 transition-colors">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
function questForm() {
    return {
        triggers: @json(old('triggers') ? json_decode(old('triggers'), true) : ($quest?->triggers ?? [])),
        triggerMode: @json(old('trigger_mode', $quest?->trigger_mode ?? 'all')),
        options:  {},
        optionsLoading: {},
        imagePreview: null,
        existingImage: @json($quest?->image ? asset('storage/'.$quest->image) : null),
        removeImage: false,

        noValueTypes:  ['open_savings', 'join_chama', 'spin_wheel', 'play_scenario'],
        numericTypes:  ['reach_balance', 'reach_savings', 'reach_net_worth', 'deposit_savings'],

        get triggersJson() {
            return JSON.stringify(this.triggers);
        },

        init() {
            // Pre-load options for all existing triggers
            this.triggers.forEach((t, i) => {
                if (t.type) this._loadOptions(t.type, i);
            });
        },

        addTrigger() {
            this.triggers.push({ type: '', values: [], label: '' });
        },

        removeTrigger(idx) {
            this.triggers.splice(idx, 1);
        },

        onTriggerTypeChange(trig, idx) {
            trig.values = [];
            if (trig.type) this._loadOptions(trig.type, idx);
        },

        async _loadOptions(type, idx) {
            if (this.options[type]) return; // cached
            if (this.noValueTypes.includes(type) || this.numericTypes.includes(type)) return;
            this.optionsLoading[idx] = true;
            try {
                const r = await fetch(`/gameset/quests/trigger-options?type=${type}`, {
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, Accept: 'application/json' }
                });
                const d = await r.json();
                this.options[type] = d.options ?? [];
            } catch (_) {
                this.options[type] = [];
            }
            this.optionsLoading[idx] = false;
        },

        toggleValue(trig, val) {
            const i = trig.values.indexOf(val);
            if (i === -1) trig.values.push(val);
            else trig.values.splice(i, 1);
        },

        getGroups(type) {
            const opts = this.options[type] ?? [];
            return [...new Set(opts.map(o => o.group).filter(Boolean))];
        },

        getGroupOptions(type, grp) {
            return (this.options[type] ?? []).filter(o => o.group === grp);
        },

        prepareSubmit() {
            // Ensure triggersJson input is current
        },

        previewImage(e) {
            const f = e.target.files[0];
            if (!f) return;
            const reader = new FileReader();
            reader.onload = ev => { this.imagePreview = ev.target.result; this.removeImage = false; };
            reader.readAsDataURL(f);
        },

        clearImage() {
            this.imagePreview = null;
            this.existingImage = null;
            this.removeImage = true;
            this.$refs.imgInput.value = '';
        }
    };
}
</script>
</body>
</html>
