@extends('layouts.app')

@section('title', 'Notification Center')

@section('content')
<div class="mx-auto max-w-4xl space-y-5 sm:space-y-7">
    <header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-brand-700">Practice updates</p>
            <h1 class="mt-1 text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">Notifications</h1>
            <p class="mt-1 text-sm text-slate-500">{{ $unreadNotificationCount }} unread {{ $unreadNotificationCount === 1 ? 'notification' : 'notifications' }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if($unreadNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <button class="btn-pill-secondary min-h-10 text-xs" type="submit">Mark all as read</button>
                </form>
            @endif
            @if($readNotificationCount > 0)
                <form method="POST" action="{{ route('notifications.clear-read') }}" onsubmit="return confirm('Clear all read notifications?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn-pill-secondary min-h-10 text-xs !text-rose-600" type="submit">Clear read</button>
                </form>
            @endif
        </div>
    </header>

    <section class="space-y-3">
        <nav class="flex gap-2 overflow-x-auto pb-1" aria-label="Notification category">
            @foreach(['all' => 'All activity', 'appointments' => 'Appointments', 'patients' => 'Patients', 'clinical' => 'Clinical', 'billing' => 'Billing'] as $key => $label)
                <a href="{{ route('notifications.index', ['category' => $key, 'status' => $currentStatus]) }}"
                   @class([
                       'shrink-0 rounded-xl px-3.5 py-2 text-xs font-semibold transition',
                       'bg-brand-700 text-white' => $currentFilter === $key,
                       'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' => $currentFilter !== $key,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="flex gap-4 border-b border-slate-200" aria-label="Notification status">
            @foreach(['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label)
                <a href="{{ route('notifications.index', ['category' => $currentFilter, 'status' => $key]) }}"
                   @class([
                       'border-b-2 px-1 pb-2 text-xs font-semibold transition',
                       'border-brand-600 text-brand-700' => $currentStatus === $key,
                       'border-transparent text-slate-500 hover:text-slate-800' => $currentStatus !== $key,
                   ])>
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </section>

    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        @forelse($notifications as $notification)
            @php($data = $notification->data)
            <article class="flex items-start gap-3 border-b border-slate-100 p-4 last:border-0 sm:gap-4 sm:p-5 {{ $notification->read_at ? '' : 'bg-brand-50/50' }}">
                <span class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ ($data['category'] ?? '') === 'billing' ? 'bg-emerald-50 text-emerald-700' : (($data['category'] ?? '') === 'clinical' ? 'bg-teal-50 text-teal-700' : 'bg-brand-50 text-brand-700') }}">
                    @if(($data['category'] ?? '') === 'billing')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5-2z"/></svg>
                    @elseif(($data['category'] ?? '') === 'clinical')
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @else
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4v-4m-9 8h10M5 21h14a2 2 0 01-2-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @endif
                </span>

                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h2 class="text-sm font-bold text-slate-900">{{ $data['title'] ?? 'Practice update' }}</h2>
                        @unless($notification->read_at)
                            <span class="rounded-full bg-brand-100 px-2 py-0.5 text-[10px] font-bold text-brand-700">New</span>
                        @endunless
                    </div>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">{{ $data['message'] ?? '' }}</p>
                    <p class="mt-1.5 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>

                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <form method="POST" action="{{ route('notifications.open', $notification->id) }}">
                            @csrf
                            <button type="submit" class="text-xs font-bold text-brand-700 hover:text-brand-900">Open update <span aria-hidden="true">→</span></button>
                        </form>
                        @unless($notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-slate-800">Mark read</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('notifications.unread', $notification->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-brand-700">Mark unread</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-slate-500 hover:text-rose-700">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="px-5 py-14 text-center">
                <span class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-slate-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 00-12 0v3.2a2 2 0 01-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                </span>
                <h2 class="text-sm font-bold text-slate-800">No notifications here</h2>
                <p class="mt-1 text-xs text-slate-500">Try another filter or come back when there’s a new practice update.</p>
            </div>
        @endforelse
    </section>

    @if($notifications->hasPages())
        <div>{{ $notifications->links() }}</div>
    @endif
</div>
@endsection
