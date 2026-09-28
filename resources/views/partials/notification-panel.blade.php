<div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-100">
    <div class="flex items-center gap-2 min-w-0">
        <span class="font-bold text-sm text-text-primary">Notifications</span>
        @if($unreadNotificationCount > 0)
            <span class="rounded-full bg-rose-100 text-rose-700 text-[10px] font-bold px-2 py-0.5 whitespace-nowrap">
                {{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }} new
            </span>
        @endif
    </div>
    <div class="flex items-center gap-3 shrink-0">
        @if($unreadNotificationCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}">
                @csrf
                <button type="submit" class="text-[11px] font-semibold text-brand-600 hover:text-brand-700">Mark all read</button>
            </form>
        @endif
        <a href="{{ route('notifications.index') }}" class="text-[11px] font-semibold text-slate-500 hover:text-slate-700">View all</a>
    </div>
</div>

<div class="max-h-[min(60dvh,24rem)] overflow-y-auto overscroll-contain divide-y divide-slate-100">
    @forelse($headerNotifications as $notification)
        @php($data = $notification->data)
        <div class="flex items-start gap-2 px-3 py-2.5 {{ $notification->read_at ? 'bg-white' : 'bg-brand-50/50' }} hover:bg-slate-50">
            <form method="POST" action="{{ route('notifications.open', $notification->id) }}" class="min-w-0 flex-1">
                @csrf
                <button type="submit" class="w-full flex items-start gap-3 text-left">
                    <span class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ ($data['category'] ?? '') === 'billing' ? 'bg-emerald-100 text-emerald-700' : (($data['category'] ?? '') === 'clinical' ? 'bg-teal-100 text-teal-700' : 'bg-brand-100 text-brand-700') }}">
                        @if(($data['category'] ?? '') === 'billing')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5-2z"/></svg>
                        @elseif(($data['category'] ?? '') === 'clinical')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4v-4m-9 8h10M5 21h14a2 2 0 01-2-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        @endif
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="flex items-start justify-between gap-2">
                            <span class="text-xs font-bold text-text-primary truncate">{{ $data['title'] ?? 'Practice update' }}</span>
                            @unless($notification->read_at)
                                <span class="mt-1 w-2 h-2 rounded-full bg-brand-500 shrink-0" aria-label="Unread"></span>
                            @endunless
                        </span>
                        <span class="mt-0.5 block text-[11px] text-slate-500 leading-snug line-clamp-2">{{ $data['message'] ?? '' }}</span>
                        <span class="mt-1 block text-[10px] text-slate-400">{{ $notification->created_at?->diffForHumans() }}</span>
                    </span>
                </button>
            </form>
            <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-8 h-8 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 flex items-center justify-center" aria-label="Delete notification" title="Delete notification">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12m-10 0 .7 13h6.6L16 7M9 7V4h6v3m-4 4v5m2-5v5"/></svg>
                </button>
            </form>
        </div>
    @empty
        <div class="px-5 py-8 text-center">
            <span class="mx-auto mb-2 flex w-10 h-10 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </span>
            <p class="text-xs font-semibold text-slate-700">You’re all caught up</p>
            <p class="mt-1 text-[11px] text-slate-500">New practice updates will appear here.</p>
        </div>
    @endforelse
</div>

<div class="px-4 py-2.5 border-t border-slate-100 bg-slate-50/70 flex items-center justify-between">
    @if($headerNotifications->contains(fn ($notification) => $notification->read_at !== null))
        <form method="POST" action="{{ route('notifications.clear-read') }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="text-[11px] font-semibold text-slate-500 hover:text-rose-600">Clear read</button>
        </form>
    @else
        <span></span>
    @endif
    <a href="{{ route('notifications.index') }}" class="text-[11px] font-bold text-brand-600 hover:text-brand-700">Notification center <span aria-hidden="true">→</span></a>
</div>
