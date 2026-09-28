@php
    $mainNavigation = [
        ['label' => 'Dashboard', 'permission' => 'dashboard.view', 'route' => 'dashboard', 'active' => 'dashboard*', 'icon' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-6v-6h-4v6H4a1 1 0 0 1-1-1z"/>'],
        ['label' => 'Doctors', 'permission' => 'doctors.view', 'route' => 'doctors.index', 'active' => 'doctors*', 'icon' => '<path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"/><path d="M5 21v-1a7 7 0 0 1 14 0v1M19 8h4m-2-2v4"/>'],
        ['label' => 'Patient Details', 'permission' => 'patients.view', 'route' => 'patients.index', 'active' => 'patients*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6"/>'],
        ['label' => 'Appointments', 'permission' => 'appointments.view', 'route' => 'appointments.index', 'active' => 'appointments*', 'icon' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 11h18m-12 4h.01M15 15h.01"/>'],
        ['label' => 'Prescriptions', 'permission' => 'prescriptions.view', 'route' => 'prescriptions.index', 'active' => 'prescriptions*', 'icon' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8zM14 2v6h6M8 13h8m-8 4h8"/>'],
        ['label' => 'Billing & Invoices', 'permission' => 'invoices.view', 'route' => 'invoices.index', 'active' => 'invoices*', 'icon' => '<path d="M4 2v20l4-2 4 2 4-2 4 2V2l-4 2-4-2-4 2zM8 10h8m-8 4h8"/>'],
    ];
    $operationsNavigation = [
        ['label' => 'Reports', 'permission' => 'reports.view', 'route' => 'reports.index', 'active' => 'reports*', 'icon' => '<path d="M4 19V5m0 14h17M8 15l3-4 3 2 5-7"/>'],
        ['label' => 'Staff Directory', 'permission' => 'subaccounts.view', 'route' => 'subaccounts.index', 'active' => 'subaccounts*', 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM20 8v6m3-3h-6"/>'],
        ['label' => 'Clinic Settings', 'permission' => 'settings.view', 'route' => 'settings.index', 'active' => 'settings*', 'icon' => '<path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8Z"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.7a8 8 0 0 1-1.7 1l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.7-1l-1.7.7-1.4-2.4L7.3 15a8 8 0 0 1 0-2l-1.4-1.1 1.4-2.4 1.7.7a8 8 0 0 1 1.7-1l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.7 1l1.7-.7 1.4 2.4-1.4 1.1a8 8 0 0 1-.1 2Z"/>'],
    ];
@endphp

<p class="px-3 pb-1 pt-1 text-[10px] font-bold uppercase tracking-widest text-slate-400">Main</p>
@foreach($mainNavigation as $item)
    @can($item['permission'])
        <a href="{{ route($item['route']) }}" @class(['group mx-2 mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold transition nav-jelly-item', 'bg-brand-100 text-brand-900 font-bold border border-brand-200/80 shadow-2xs' => request()->routeIs($item['active']), 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !request()->routeIs($item['active'])])>
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg><span>{{ $item['label'] }}</span>
        </a>
    @endcan
@endforeach

<p class="px-3 pb-1 pt-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Operations</p>
@foreach($operationsNavigation as $item)
    @can($item['permission'])
        <a href="{{ route($item['route']) }}" @class(['group mx-2 mb-0.5 flex items-center gap-3 rounded-lg px-3 py-2.5 text-xs font-semibold transition nav-jelly-item', 'bg-brand-100 text-brand-900 font-bold border border-brand-200/80 shadow-2xs' => request()->routeIs($item['active']), 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' => !request()->routeIs($item['active'])])>
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg><span>{{ $item['label'] }}</span>
        </a>
    @endcan
@endforeach
