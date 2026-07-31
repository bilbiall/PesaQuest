<nav x-data="{ open: false }" class="border-b border-white/8 sticky top-0 z-50"
     style="background:rgba(7,6,15,0.85);backdrop-filter:blur(16px);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            {{-- Logo --}}
            <div class="shrink-0 flex items-center">
                <a href="{{ route('landing') }}" class="group">
                    <img src="{{ asset('moski-logo.png') }}" alt="PesaQuest"
                         class="h-10 w-auto rounded-xl object-cover group-hover:opacity-85 transition-opacity"
                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                    <span class="hidden items-center gap-2 text-white font-black text-lg">
                        <span class="text-2xl">💰</span> PesaQuest
                    </span>
                </a>
            </div>

            {{-- Desktop right side --}}
            <div class="hidden sm:flex sm:items-center gap-2">

                {{-- Nav links --}}
                <a href="{{ route('dashboard') }}"
                   class="text-sm px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/6' }}">
                    Dashboard
                </a>

                <a href="{{ route('subscribe.index') }}"
                   class="text-sm px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('subscribe.*') ? 'text-green-300 bg-green-500/10' : 'text-slate-400 hover:text-white hover:bg-white/6' }}">
                    Premium
                </a>

                <a href="{{ route('chama.index') }}"
                   class="text-sm px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('chama.*') ? 'text-amber-300 bg-amber-500/10' : 'text-slate-400 hover:text-white hover:bg-white/6' }}">
                    🤝 Chama
                </a>

                <a href="{{ route('how-to') }}"
                   class="text-sm px-3 py-1.5 rounded-lg transition-colors {{ request()->routeIs('how-to') ? 'text-white bg-white/10' : 'text-slate-400 hover:text-white hover:bg-white/6' }}">
                    ❓ How To
                </a>

                @if(auth()->user()->is_admin)
                <a href="{{ route('admin.index') }}"
                   class="text-xs text-orange-400 hover:text-orange-300 border border-orange-500/30 hover:border-orange-500/60 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Admin
                </a>
                @endif

                @if(auth()->user()->is_gameset || auth()->user()->is_admin)
                <a href="{{ route('gameset.index') }}"
                   class="text-xs text-purple-400 hover:text-purple-300 border border-purple-500/30 hover:border-purple-500/60 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                    </svg>
                    GameSet
                </a>
                @endif

                {{-- Notification bell --}}
                @php
                    $navUnreadCount = \App\Models\GameNotification::where('user_id', auth()->id())->where('is_read', false)->count();
                @endphp
                <a href="{{ route('dashboard') }}#notifications" class="relative p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/6 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($navUnreadCount > 0)
                    <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 items-center justify-center rounded-full text-[9px] font-black text-white"
                          style="background:linear-gradient(135deg,#ef4444,#f97316);">
                        {{ $navUnreadCount > 9 ? '9+' : $navUnreadCount }}
                    </span>
                    @endif
                </a>

                {{-- Profile avatar dropdown --}}
                <x-dropdown align="right" width="52">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-full border border-white/10 hover:border-white/25 transition-all group"
                                style="background:rgba(255,255,255,0.05);">
                            {{-- Avatar: photo if set, else initials --}}
                            @if(Auth::user()->profile_photo)
                            <img src="{{ Auth::user()->profile_photo }}" alt="{{ Auth::user()->name }}"
                                 class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-indigo-500/40">
                            @else
                            <span class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-black text-white shrink-0"
                                  style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1)) }}
                            </span>
                            @endif
                            <span class="text-slate-300 text-sm font-medium max-w-[100px] truncate">{{ Auth::user()->name }}</span>
                            <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-300 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- User info header --}}
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                            @if(Auth::user()->hasActiveSubscription())
                            <span class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-green-700 bg-green-100 px-2 py-0.5 rounded-full">
                                ✓ Premium Active
                            </span>
                            @else
                            <a href="{{ route('subscribe.index') }}" class="inline-flex items-center gap-1 mt-1 text-xs font-semibold text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full hover:bg-amber-200 transition-colors">
                                ⭐ Upgrade to Premium
                            </a>
                            @endif
                        </div>

                        <x-dropdown-link :href="route('profile.edit')">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                My Profile
                            </span>
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('dashboard')">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Dashboard
                            </span>
                        </x-dropdown-link>

                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                    <span class="flex items-center gap-2 text-red-600">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                        Log Out
                                    </span>
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            {{-- Hamburger (mobile) --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = !open"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition duration-150">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div :class="{'block': open, 'hidden': !open}" class="hidden sm:hidden border-t border-white/8"
         style="background:rgba(7,6,15,0.95);">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a href="{{ route('dashboard') }}"
               class="block text-sm py-2.5 px-3 rounded-lg {{ request()->routeIs('dashboard') ? 'text-white bg-white/10' : 'text-slate-400' }}">
                Dashboard
            </a>
            <a href="{{ route('subscribe.index') }}"
               class="block text-sm py-2.5 px-3 rounded-lg text-slate-400">
                Premium
            </a>
            <a href="{{ route('chama.index') }}"
               class="block text-sm py-2.5 px-3 rounded-lg text-slate-400">
                🤝 Chama
            </a>
            <a href="{{ route('how-to') }}"
               class="block text-sm py-2.5 px-3 rounded-lg text-slate-400">
                ❓ How To
            </a>
            @if(auth()->user()->is_admin)
            <a href="{{ route('admin.index') }}" class="block text-sm py-2.5 px-3 rounded-lg text-orange-400">
                🛡 Admin Panel
            </a>
            @endif
            @if(auth()->user()->is_gameset || auth()->user()->is_admin)
            <a href="{{ route('gameset.index') }}" class="block text-sm py-2.5 px-3 rounded-lg text-purple-400">
                ⚙ GameSet
            </a>
            @endif
        </div>

        <div class="pt-3 pb-4 border-t border-white/8 px-4">
            <div class="flex items-center gap-3 mb-3">
                @if(Auth::user()->profile_photo)
                <img src="{{ Auth::user()->profile_photo }}" alt="{{ Auth::user()->name }}"
                     class="w-10 h-10 rounded-full object-cover shrink-0 ring-2 ring-indigo-500/40">
                @else
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-black text-white shrink-0"
                     style="background:linear-gradient(135deg,#6366f1,#a78bfa);">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', Auth::user()->name)[1] ?? '', 0, 1)) }}
                </div>
                @endif
                <div>
                    <p class="text-white font-semibold text-sm">{{ Auth::user()->name }}</p>
                    <p class="text-slate-500 text-xs">{{ Auth::user()->email }}</p>
                </div>
            </div>

            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="block text-sm py-2 px-3 rounded-lg text-slate-400 hover:text-white">
                    My Profile
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm py-2 px-3 rounded-lg text-red-400 hover:text-red-300">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
