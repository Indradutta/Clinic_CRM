<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MediFlow CRM') }} - @yield('title', 'Practice Management')</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"></noscript>

    <style>
        body { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; text-rendering: optimizeLegibility; -webkit-font-smoothing: antialiased; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-text-primary atmospheric-canvas"
      x-data="{
          sidebarOpen: false,
          userMenu: false,
          notifOpen: false
      }">

@php
    $authUser     = auth()->user();
    $userName     = $authUser->name  ?? 'Doctor';
    $userEmail    = $authUser->email ?? '';
    $userRole     = str_replace('_', ' ', $authUser->role ?? 'Doctor');
    $userInitials = strtoupper(substr($userName, 0, 1) . (str_contains($userName, ' ') ? substr(strrchr($userName, ' '), 1, 1) : substr($userName, 1, 1)));
@endphp

<div class="min-h-full flex flex-col lg:flex-row">

    {{-- ── MOBILE SIDEBAR OVERLAY ── --}}
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm lg:hidden"
         x-on:click="sidebarOpen = false"
         style="display: none;"></div>

    {{-- ── MOBILE DRAWER SIDEBAR ── --}}
    <aside x-show="sidebarOpen"
           x-transition:enter="transition ease-in-out duration-250 transform"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in-out duration-200 transform"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 z-50 w-72 bg-white flex flex-col shadow-2xl border-r border-slate-100 lg:hidden"
           style="display: none;">

        {{-- Logo + Close --}}
        <div class="flex items-center justify-between h-16 px-5 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/mediflow-logo.svg') }}" alt="" class="h-9 w-9 shrink-0">
                <div>
                    <span class="text-sm font-bold tracking-tight text-text-primary">MediFlow</span>
                    <span class="text-[10px] block text-brand-500 font-bold tracking-widest uppercase leading-none">Practice CRM</span>
                </div>
            </div>
            <button type="button" x-on:click="sidebarOpen = false"
                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition"
                    aria-label="Close sidebar">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5" aria-label="Mobile sidebar navigation">
            @include('partials.navigation-items')
        </nav>

        {{-- Bottom Profile Row --}}
        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                @if($profilePhoto)
                    <img src="{{ $profilePhoto }}" alt="{{ $userName }} profile photo" class="w-9 h-9 rounded-full object-cover shrink-0 border border-slate-200">
                @else
                    <div class="w-9 h-9 rounded-full bg-[#27758F] text-white font-bold text-sm flex items-center justify-center shrink-0">{{ $userInitials }}</div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-text-primary truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-slate-400 font-medium truncate capitalize">{{ $userRole }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-8 h-8 rounded-lg hover:bg-rose-50 flex items-center justify-center text-slate-400 hover:text-rose-500 transition" title="Sign out">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ── DESKTOP PERSISTENT SIDEBAR ── --}}
    <aside class="hidden lg:flex lg:flex-col lg:w-64 shrink-0 bg-white border-r border-slate-100 z-20">
        {{-- Logo --}}
        <div class="flex items-center h-16 px-5 border-b border-slate-100 gap-3">
            <img src="{{ asset('images/mediflow-logo.svg') }}" alt="" class="h-9 w-9 shrink-0">
            <div>
                <span class="text-sm font-bold tracking-tight text-text-primary">MediFlow</span>
                <span class="text-[10px] block text-brand-500 font-bold tracking-widest uppercase leading-none">Practice CRM</span>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5" aria-label="Sidebar navigation">
            @include('partials.navigation-items')
        </nav>

        {{-- Bottom User Row --}}
        <div class="p-3 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2 py-2.5 rounded-xl hover:bg-slate-50 transition cursor-default">
                @if($profilePhoto)
                    <img src="{{ $profilePhoto }}" alt="{{ $userName }} profile photo" class="w-8 h-8 rounded-full object-cover shrink-0 border border-slate-200">
                @else
                    <div class="w-8 h-8 rounded-full bg-[#27758F] text-white font-bold text-xs flex items-center justify-center shrink-0">{{ $userInitials }}</div>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-text-primary truncate">{{ $userName }}</p>
                    <p class="text-[10px] text-brand-500 font-semibold truncate capitalize">{{ $userRole }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── MAIN CONTENT AREA ── --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        {{-- ── MOBILE TOPBAR ── --}}
        <header class="lg:hidden bg-white border-b border-slate-100 sticky top-0 z-30">
            <div class="flex items-center justify-between h-14 px-4 gap-3">
                {{-- Left: Hamburger + Logo --}}
                <div class="flex items-center gap-3">
                    <button type="button" x-on:click="sidebarOpen = true"
                            class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition"
                            aria-label="Open navigation">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="flex items-center gap-2">
                        <img src="{{ asset('images/mediflow-logo.svg') }}" alt="" class="h-7 w-7">
                        <span class="text-sm font-bold text-text-primary">MediFlow</span>
                    </div>
                </div>

                {{-- Right: Bell + Avatar --}}
                <div class="flex items-center gap-2">
                    {{-- Notification Bell --}}
                    <div class="relative">
                        <button type="button" x-on:click="notifOpen = !notifOpen"
                                class="w-9 h-9 rounded-lg flex items-center justify-center text-slate-500 hover:bg-slate-100 transition relative"
                                aria-label="Notifications">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="{{ $unreadNotificationCount }} > 0"
                                  class="absolute top-1 right-1 min-w-[16px] h-4 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center px-0.5 ring-2 ring-white"
                                  x-text="'{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}'"></span>
                        </button>

                        {{-- Mobile Notification Dropdown --}}
                        <div x-show="notifOpen"
                             x-on:click.away="notifOpen = false"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 -translate-y-1"
                             class="fixed left-3 right-3 top-[4.25rem] w-auto max-w-none max-h-[calc(100dvh-6rem)] bg-white rounded-2xl shadow-2xl border border-slate-100 z-50 overflow-hidden lg:absolute lg:left-auto lg:right-0 lg:top-full lg:mt-2 lg:w-96"
                             style="display: none;">
                            @include('partials.notification-panel')
                        </div>
                    </div>

                    {{-- Avatar --}}
                    <div class="relative">
                        <button type="button" x-on:click="userMenu = !userMenu" class="w-9 h-9 rounded-full focus-visible:outline focus-visible:outline-2 focus-visible:outline-brand-500" aria-label="Open profile menu" :aria-expanded="userMenu.toString()">
                            @if($profilePhoto)
                                <img src="{{ $profilePhoto }}" alt="{{ $userName }} profile photo" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                            @else
                                <span class="w-9 h-9 rounded-full bg-[#27758F] text-white font-bold text-xs flex items-center justify-center">{{ $userInitials }}</span>
                            @endif
                        </button>
                        <div x-show="userMenu" x-on:click.away="userMenu = false" x-transition class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50" style="display:none">
                            <div class="px-4 py-3 border-b border-slate-100">
                                <p class="text-xs font-bold text-text-primary truncate">{{ $userName }}</p>
                                <p class="text-[11px] text-slate-400 truncate">{{ $userEmail }}</p>
                            </div>
                            @can('settings.view')
                                <a href="{{ route('settings.index') }}" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition">Practice profile &amp; settings</a>
                                <a href="{{ route('settings.index', ['tab' => 'security']) }}" class="block px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition">Security &amp; sessions</a>
                            @endcan
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 transition font-medium">Sign out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- ── DESKTOP TOPBAR ── --}}
        <header class="hidden lg:flex h-16 bg-white border-b border-slate-100 sticky top-0 z-30 items-center justify-between px-6 lg:px-8">
            {{-- Search --}}
            <div class="flex items-center flex-1 max-w-lg">
                <form action="{{ route('search') }}" method="GET" class="w-full">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" name="q" value="{{ request('q') }}"
                               placeholder="Search patients, appointments, invoices..."
                               class="w-full pl-10 pr-14 py-2.5 bg-slate-50 hover:bg-slate-100/70 focus:bg-white text-xs text-text-primary rounded-xl border border-slate-200/60 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/12 outline-none transition font-medium placeholder-slate-400">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <kbd class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-200/60 text-slate-400">Ctrl K</kbd>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2 ml-4">
                @can('appointments.create')
                    <a href="{{ route('appointments.create') }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 text-[11px] font-bold rounded-lg bg-[#27758F] text-white hover:bg-brand-700 shadow-sm transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                        New Appointment
                    </a>
                @endcan

                {{-- Notification Bell --}}
                <div class="relative">
                    <button type="button" x-on:click="notifOpen = !notifOpen"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-50 transition relative outline-none"
                            aria-label="Notifications">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        <span x-show="{{ $unreadNotificationCount }} > 0"
                              class="absolute top-1.5 right-1.5 min-w-[16px] h-4 rounded-full bg-rose-500 text-white text-[9px] font-bold flex items-center justify-center px-0.5 ring-2 ring-white"
                              x-text="'{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}'"></span>
                    </button>

                    {{-- Desktop Notification Dropdown --}}
                    <div x-show="notifOpen"
                         x-on:click.away="notifOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-2 w-[calc(100vw-1.5rem)] max-w-96 bg-white rounded-2xl shadow-xl border border-slate-100 z-50 overflow-hidden"
                         style="display: none;">
                        @include('partials.notification-panel')
                    </div>
                </div>

                {{-- User Profile Dropdown --}}
                <div class="relative">
                    <button type="button" x-on:click="userMenu = !userMenu"
                            class="flex items-center gap-2 pl-2 pr-2 rounded-xl hover:bg-slate-50 border border-transparent hover:border-slate-200 transition outline-none h-10"
                            aria-label="User menu">
                        @if($profilePhoto)
                            <img src="{{ $profilePhoto }}" alt="{{ $userName }} profile photo" class="w-7 h-7 rounded-full object-cover shrink-0 border border-slate-200">
                        @else
                            <span class="w-7 h-7 rounded-full bg-[#27758F] text-white font-bold text-xs flex items-center justify-center shrink-0">{{ $userInitials }}</span>
                        @endif
                        <div class="flex flex-col items-start text-left leading-tight">
                            <span class="text-xs font-bold text-text-primary">{{ $userName }}</span>
                            <span class="text-[9px] font-semibold text-slate-400 capitalize">{{ $userRole }}</span>
                        </div>
                        <svg class="w-3.5 h-3.5 text-slate-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="userMenu"
                         x-on:click.away="userMenu = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-50"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="text-xs font-bold text-text-primary truncate">{{ $userName }}</p>
                            <p class="text-[11px] text-slate-400 truncate">{{ $userEmail }}</p>
                        </div>
                        @can('settings.view')
                            <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition gap-2.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                Practice Profile
                            </a>
                            <a href="{{ route('settings.index', ['tab' => 'security']) }}" class="flex items-center px-4 py-2.5 text-xs text-slate-700 hover:bg-slate-50 transition gap-2.5">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6m10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                Security &amp; Sessions
                            </a>
                        @endcan
                        <div class="border-t border-slate-100 my-1"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2.5 text-xs text-rose-600 hover:bg-rose-50 transition font-medium gap-2.5">
                                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Sign Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="font-semibold text-rose-900">Please review and correct the errors below:</p>
                        <ul class="list-disc list-inside mt-1 space-y-0.5 text-rose-700">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Main Content --}}
        <main class="flex-1 overflow-y-auto p-3 sm:p-5 lg:p-8 pb-24 lg:pb-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>
</div>

{{-- ── MOBILE BOTTOM DOCK ── --}}
<nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-white border-t border-slate-100 safe-bottom"
     aria-label="Mobile navigation">
    <div class="flex items-stretch justify-around h-16">

        {{-- Dashboard --}}
        @can('dashboard.view')
        <a href="{{ route('dashboard') }}"
           class="flex flex-col items-center justify-center gap-0.5 flex-1 px-2 transition-colors dock-jelly-item
                  {{ request()->routeIs('dashboard*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('dashboard*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-[10px] font-semibold leading-none">Home</span>
        </a>
        @endcan

        {{-- Appointments --}}
        @can('appointments.view')
        <a href="{{ route('appointments.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 flex-1 px-2 transition-colors dock-jelly-item
                  {{ request()->routeIs('appointments*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('appointments*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-none">Schedule</span>
        </a>
        @endcan

        {{-- Patients --}}
        @can('patients.view')
        <a href="{{ route('patients.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 flex-1 px-2 transition-colors dock-jelly-item
                  {{ request()->routeIs('patients*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('patients*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-none">Patients</span>
        </a>
        @endcan

        {{-- Invoices --}}
        @can('invoices.view')
        <a href="{{ route('invoices.index') }}"
           class="flex flex-col items-center justify-center gap-0.5 flex-1 px-2 transition-colors dock-jelly-item
                  {{ request()->routeIs('invoices*') ? 'text-brand-600' : 'text-slate-400 hover:text-slate-600' }}">
            <svg class="w-5 h-5" fill="{{ request()->routeIs('invoices*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
            </svg>
            <span class="text-[10px] font-semibold leading-none">Billing</span>
        </a>
        @endcan

        {{-- More --}}
        <button type="button" x-on:click="sidebarOpen = true"
                class="flex flex-col items-center justify-center gap-0.5 flex-1 px-2 text-slate-400 hover:text-slate-600 transition dock-jelly-item"
                aria-label="More menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <span class="text-[10px] font-semibold leading-none">More</span>
        </button>
    </div>
</nav>

@stack('scripts')

{{-- Hover Prefetcher --}}
<script>
(function() {
    if (!('fetch' in window)) return;
    const prefetchedUrls = new Set();
    function prefetchLink(url) {
        if (!url || prefetchedUrls.has(url)) return;
        prefetchedUrls.add(url);
        const link = document.createElement('link');
        link.rel = 'prefetch'; link.href = url;
        document.head.appendChild(link);
    }
    document.addEventListener('mouseover', function(e) {
        const a = e.target.closest('a');
        if (!a || !a.href || a.origin !== location.origin) return;
        if (a.target && a.target !== '_self') return;
        if (a.href.includes('#') || a.href.includes('logout') || a.hasAttribute('download') || a.hasAttribute('data-no-prefetch')) return;
        prefetchLink(a.href);
    }, { passive: true });
    document.addEventListener('touchstart', function(e) {
        const a = e.target.closest('a');
        if (a && a.href && a.origin === location.origin) prefetchLink(a.href);
    }, { passive: true });
})();
</script>
</body>
</html>
