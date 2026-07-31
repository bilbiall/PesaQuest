{{--
    pesAI — AI Financial Assistant floating chat widget.
    Include before </body> in any Blade view.
    Hidden automatically when no OpenRouter API key is configured.
    Requires: <meta name="csrf-token"> in <head>.
--}}
@php
    $apiKey     = \App\Models\Setting::get('openrouter_api_key', '');
    if (empty(trim($apiKey))) return; // Hide entirely when no key set

    $dailyLimit = (int) \App\Models\Setting::get('ai_daily_limit', 15);
    $userId     = auth()->id();
    $used       = (int) \Illuminate\Support\Facades\Cache::get("ai_chat_limit_{$userId}", 0);
    $remaining  = max(0, $dailyLimit - $used);
    $aiIcon     = \App\Models\Setting::get('ai_agent_icon', '🤖');
    $isImgIcon  = str_starts_with($aiIcon, 'http');
    $ageGroup   = auth()->user()?->age_group ?? '18-25';

    $suggestions = match($ageGroup) {
        '8-12'  => ['How do I save pocket money? 🐷', 'Why is saving important? 🌱', 'Spend or save?'],
        '13-17' => ['How can I earn money? 💡', 'What is M-Pesa? 📱', 'Save for something big?'],
        '18-25' => ['Should I join a SACCO? 🏦', 'How to budget my salary? 💼', 'Invest in NSE? 📈'],
        '26+'   => ['How to grow my wealth? 🏗️', 'What are CBK T-bills? 📊', 'Plan for retirement? 👑'],
        default => ['How to save more? 💰', 'What is a SACCO? 🏦', 'How to invest? 📈'],
    };
@endphp

<style>
    @keyframes mamaPulse {
        0%, 100% { box-shadow: 0 0 0 0 rgba(124,58,237,0.5); }
        50%       { box-shadow: 0 0 0 12px rgba(124,58,237,0); }
    }
    .mama-pulse { animation: mamaPulse 2.4s ease-in-out infinite; }

    @keyframes mamaTyping {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30%            { transform: translateY(-5px); opacity: 1; }
    }
    .typing-dot { animation: mamaTyping 1.2s ease-in-out infinite; }
    .typing-dot:nth-child(2) { animation-delay: 0.15s; }
    .typing-dot:nth-child(3) { animation-delay: 0.3s; }

    @keyframes mamaSlideUp {
        from { opacity: 0; transform: translateY(20px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0)  scale(1); }
    }
    .mama-slide-up { animation: mamaSlideUp 0.22s cubic-bezier(0.34,1.56,0.64,1) both; }

    .mama-messages::-webkit-scrollbar { width: 4px; }
    .mama-messages::-webkit-scrollbar-track { background: transparent; }
    .mama-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 9999px; }
</style>

<div
    x-data="{
        open: false,
        minimized: false,
        messages: [],
        input: '',
        loading: false,
        limitReached: {{ $remaining <= 0 ? 'true' : 'false' }},
        remaining: {{ $remaining }},

        async send() {
            if (!this.input.trim() || this.loading || this.limitReached) return;
            const msg = this.input.trim();
            this.input = '';
            this.messages.push({ role: 'user', content: msg });
            this.loading = true;
            this.$nextTick(() => this.scrollToBottom());
            try {
                const res = await fetch('/ai/chat', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: msg }),
                });
                const data = await res.json();
                if (data.limit_reached) {
                    this.limitReached = true;
                    this.remaining = 0;
                    this.messages.push({ role: 'assistant', content: data.error });
                } else {
                    this.messages.push({ role: 'assistant', content: data.reply });
                    this.remaining = Math.max(0, this.remaining - 1);
                    if (this.remaining === 0) this.limitReached = true;
                }
            } catch(e) {
                this.messages.push({ role: 'assistant', content: 'Connection error — please try again.' });
            }
            this.loading = false;
            this.$nextTick(() => this.scrollToBottom());
        },

        async sendSuggestion(text) {
            if (this.loading || this.limitReached) return;
            this.input = text;
            await this.send();
        },

        async clearChat() {
            await fetch('/ai/clear', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            });
            this.messages = [];
        },

        scrollToBottom() {
            const el = this.\$refs.messages;
            if (el) el.scrollTop = el.scrollHeight;
        },

        handleKeydown(e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); this.send(); }
        },

        openPanel() {
            this.open      = true;
            this.minimized = false;
            this.\$nextTick(() => {
                this.scrollToBottom();
                const inp = this.\$refs.inputField;
                if (inp) inp.focus();
            });
        },

        minimize() {
            this.open      = false;
            this.minimized = true;
        },

        closeWidget() {
            this.open      = false;
            this.minimized = false;
        },
    }"
    style="position:fixed;bottom:24px;right:24px;z-index:8800;"
>
    {{-- ── Floating Button (fully visible state) ── --}}
    <button
        x-show="!open && !minimized"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90"
        x-transition:enter-end="opacity-100 scale-100"
        @click="openPanel()"
        class="mama-pulse flex items-center justify-center w-14 h-14 rounded-full text-white font-black text-lg shadow-2xl transition-transform hover:scale-110 active:scale-95 select-none overflow-hidden"
        style="background:linear-gradient(135deg,#7c3aed,#db2777);width:56px;height:56px;border:2px solid rgba(255,255,255,0.15);"
        title="Chat with pesAI"
    >
        @if($isImgIcon)
            <img src="{{ $aiIcon }}" class="w-full h-full object-cover" alt="pesAI">
        @else
            {{ $aiIcon }}
        @endif
    </button>

    {{-- ── Minimized Tray Pill ── --}}
    <div
        x-show="!open && minimized"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-90 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        @click="openPanel()"
        class="flex items-center gap-2.5 px-4 py-2.5 rounded-full cursor-pointer transition-all hover:scale-105 active:scale-95 select-none shadow-2xl"
        style="background:linear-gradient(135deg,#7c3aed,#db2777);border:2px solid rgba(255,255,255,0.15);box-shadow:0 4px 20px rgba(124,58,237,0.4);"
        title="Open pesAI"
    >
        @if($isImgIcon)
            <img src="{{ $aiIcon }}" class="w-5 h-5 rounded-full object-cover flex-shrink-0" alt="pesAI">
        @else
            <span class="text-base flex-shrink-0">{{ $aiIcon }}</span>
        @endif
        <span class="text-white text-xs font-black">pesAI</span>
        <span class="w-2 h-2 rounded-full bg-emerald-400 flex-shrink-0" style="animation:mamaPulse 2.4s ease-in-out infinite;"></span>
    </div>

    {{-- ── Chat Panel ── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-4"
        class="mama-slide-up flex flex-col rounded-2xl overflow-hidden shadow-2xl"
        style="width:360px;height:500px;background:#0f0e1a;border:1px solid rgba(124,58,237,0.35);"
    >
        {{-- Header --}}
        <div class="flex-shrink-0 px-4 py-3 flex items-center gap-3" style="background:linear-gradient(135deg,rgba(124,58,237,0.25),rgba(219,39,119,0.15));border-bottom:1px solid rgba(255,255,255,0.07);">
            <div class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-xl font-bold shadow-lg overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#db2777);border:2px solid rgba(255,255,255,0.15);">
                @if($isImgIcon)
                    <img src="{{ $aiIcon }}" class="w-full h-full object-cover" alt="pesAI">
                @else
                    {{ $aiIcon }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-black text-sm leading-tight">pesAI</p>
                <p class="text-xs leading-tight" style="color:rgba(196,181,253,0.8);">AI Financial Mentor</p>
                <div class="flex flex-wrap gap-1 mt-1">
                    <span class="text-xs rounded-full px-1.5 py-0.5" style="background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.3);color:#c4b5fd;font-size:0.6rem;">Knows your balance</span>
                    <span class="text-xs rounded-full px-1.5 py-0.5" style="background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.3);color:#c4b5fd;font-size:0.6rem;">assets</span>
                    <span class="text-xs rounded-full px-1.5 py-0.5" style="background:rgba(124,58,237,0.2);border:1px solid rgba(124,58,237,0.3);color:#c4b5fd;font-size:0.6rem;">portfolio</span>
                </div>
            </div>
            {{-- Minimize button --}}
            <button @click="minimize()"
                    class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors text-base leading-none"
                    title="Minimize to tray">
                &#8722;
            </button>
            {{-- Close button --}}
            <button @click="closeWidget()"
                    class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-gray-400 hover:text-white hover:bg-white/10 transition-colors text-sm"
                    title="Close pesAI">
                ✕
            </button>
        </div>

        {{-- Messages area --}}
        <div
            x-ref="messages"
            class="mama-messages flex-1 overflow-y-auto px-3 py-3 space-y-3"
            style="scroll-behavior:smooth;"
        >
            {{-- Welcome message + suggestion chips --}}
            <template x-if="messages.length === 0">
                <div>
                    <div class="flex gap-2 items-start mb-3">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                            @if($isImgIcon)
                                <img src="{{ $aiIcon }}" class="w-full h-full object-cover" alt="pesAI">
                            @else
                                {{ $aiIcon }}
                            @endif
                        </div>
                        <div class="rounded-2xl rounded-tl-sm px-3 py-2.5 text-sm text-gray-200 max-w-[85%]" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);">
                            <p>Habari! Mimi ni <strong class="text-purple-300">pesAI</strong>. I know your full financial picture — ask me anything about money, investing, or how to level up. <em class="text-purple-400">Uchumi wa kweli starts here!</em> 💜</p>
                        </div>
                    </div>
                    {{-- Quick suggestion chips --}}
                    <div class="flex flex-wrap gap-1.5 px-1 mb-1">
                        @foreach($suggestions as $suggestion)
                        <button
                            @click="sendSuggestion({{ Js::from($suggestion) }})"
                            :disabled="limitReached"
                            class="text-xs px-3 py-1.5 rounded-full transition-all hover:scale-105 active:scale-95"
                            style="background:rgba(124,58,237,0.15);border:1px solid rgba(124,58,237,0.3);color:#c4b5fd;cursor:pointer;"
                        >{{ $suggestion }}</button>
                        @endforeach
                    </div>
                </div>
            </template>

            {{-- Rendered messages --}}
            <template x-for="(msg, idx) in messages" :key="idx">
                <div>
                    {{-- User message --}}
                    <div x-show="msg.role === 'user'" class="flex justify-end">
                        <div class="rounded-2xl rounded-tr-sm px-3 py-2.5 text-sm text-white max-w-[82%]" style="background:linear-gradient(135deg,rgba(99,102,241,0.45),rgba(139,92,246,0.35));border:1px solid rgba(99,102,241,0.3);">
                            <p x-text="msg.content" class="whitespace-pre-wrap break-words"></p>
                        </div>
                    </div>
                    {{-- Assistant message --}}
                    <div x-show="msg.role === 'assistant'" class="flex gap-2 items-start">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                            @if($isImgIcon)
                                <img src="{{ $aiIcon }}" class="w-full h-full object-cover" alt="pesAI">
                            @else
                                {{ $aiIcon }}
                            @endif
                        </div>
                        <div class="rounded-2xl rounded-tl-sm px-3 py-2.5 text-sm text-gray-200 max-w-[85%]" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);">
                            <p x-text="msg.content" class="whitespace-pre-wrap break-words"></p>
                        </div>
                    </div>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="loading" class="flex gap-2 items-start">
                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-sm overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#db2777);">
                    @if($isImgIcon)
                        <img src="{{ $aiIcon }}" class="w-full h-full object-cover" alt="pesAI">
                    @else
                        {{ $aiIcon }}
                    @endif
                </div>
                <div class="rounded-2xl rounded-tl-sm px-4 py-3 flex gap-1.5 items-center" style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.08);">
                    <span class="typing-dot w-2 h-2 rounded-full block" style="background:#a78bfa;"></span>
                    <span class="typing-dot w-2 h-2 rounded-full block" style="background:#a78bfa;"></span>
                    <span class="typing-dot w-2 h-2 rounded-full block" style="background:#a78bfa;"></span>
                </div>
            </div>

            {{-- Limit reached message --}}
            <template x-if="limitReached && messages.length === 0">
                <div class="text-center py-4">
                    <div class="text-3xl mb-2">🔒</div>
                    <p class="text-sm text-gray-400 font-semibold">Daily limit reached.</p>
                    <p class="text-xs text-gray-500 mt-1">Come back tomorrow — pesAI will be waiting!</p>
                </div>
            </template>
        </div>

        {{-- Footer / Input --}}
        <div class="flex-shrink-0" style="border-top:1px solid rgba(255,255,255,0.07);background:rgba(0,0,0,0.3);">
            {{-- Limit indicator --}}
            <div class="px-3 pt-2 pb-0 flex items-center justify-between">
                <template x-if="!limitReached">
                    <span class="text-xs" style="color:rgba(167,139,250,0.7);">
                        <span x-text="remaining"></span> messages left today
                    </span>
                </template>
                <template x-if="limitReached">
                    <span class="text-xs text-red-400 font-semibold">Daily limit reached — come back tomorrow!</span>
                </template>
                <button
                    @click="clearChat()"
                    class="text-xs px-2 py-1 rounded-lg transition-colors"
                    style="color:rgba(156,163,175,0.7);background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);"
                    onmouseover="this.style.color='rgba(255,255,255,0.7)'"
                    onmouseout="this.style.color='rgba(156,163,175,0.7)'"
                >
                    Clear
                </button>
            </div>

            {{-- Text input --}}
            <div class="px-3 pb-3 pt-2 flex gap-2">
                <input
                    x-ref="inputField"
                    type="text"
                    x-model="input"
                    @keydown="handleKeydown($event)"
                    :disabled="loading || limitReached"
                    placeholder="Ask pesAI anything…"
                    maxlength="500"
                    class="flex-1 rounded-xl px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none transition-colors"
                    style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);"
                    onfocus="this.style.borderColor='rgba(124,58,237,0.6)'"
                    onblur="this.style.borderColor='rgba(255,255,255,0.1)'"
                >
                <button
                    @click="send()"
                    :disabled="loading || limitReached || !input.trim()"
                    class="flex-shrink-0 w-9 h-9 rounded-xl flex items-center justify-center text-white transition-all"
                    style="background:linear-gradient(135deg,#7c3aed,#db2777);"
                    :style="(loading || limitReached || !input.trim()) ? 'opacity:0.45;cursor:not-allowed;' : 'opacity:1;cursor:pointer;'"
                    title="Send (Enter)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
