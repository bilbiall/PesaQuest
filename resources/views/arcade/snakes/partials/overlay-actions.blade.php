{{-- Shared end-of-round actions: primary CTA is always a fresh game (never
     "resume" — the session that just ended is over), with a plain link back
     to the lobby for anyone who'd rather pick multiplayer instead. --}}
<form method="POST" action="{{ route('arcade.snakes.solo') }}" style="margin-bottom:.6rem;">
    @csrf
    <button type="submit" class="roll-btn" style="width:100%;">🎲 Play Again</button>
</form>
<a href="{{ route('arcade.snakes.lobby') }}" class="text-xs font-bold text-gray-400 hover:text-white" style="display:inline-block;">← Lobby</a>
