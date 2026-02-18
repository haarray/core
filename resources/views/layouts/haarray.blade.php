{{-- FILE: resources/views/layouts/haarray.blade.php --}}
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard') — HariLog</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;500&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/haarray.css') }}">
  <link rel="stylesheet" href="{{ asset('css/haarray.starter.css') }}">
  @yield('styles')
</head>
<body>

{{-- Sidebar overlay (mobile) --}}
<div class="h-sidebar-overlay" id="h-sidebar-overlay"></div>

{{-- Mobile toggle --}}
<button class="h-menu-toggle" aria-label="Menu">
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>
  </svg>
</button>

{{-- ═══ SIDEBAR ═══ --}}
<aside class="h-sidebar" id="h-sidebar">

  {{-- Brand --}}
  <div class="h-brand">
    <div class="h-brand-mark">H</div>
    <div>
      <div class="h-brand-name">HariLog</div>
      <div class="h-brand-sub">by Haarray</div>
    </div>
  </div>

  {{-- Nav --}}
  <div class="h-nav-sec">Finance</div>
  <a href="{{ route('dashboard') }}" class="h-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/>
      <rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>
    </svg>
    Dashboard
  </a>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
    </svg>
    Transactions
    <span class="h-nav-badge">Soon</span>
  </a>
  <a data-spa href="{{ route('docs.starter') }}" class="h-nav-item">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/>
    </svg>
    Docs
    <span class="h-nav-badge">Soon</span>
  </a>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/>
    </svg>
    Accounts
  </a>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>
    </svg>
    Portfolio
  </a>

  <div class="h-nav-sec">Market</div>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
    </svg>
    IPO Tracker
    <span class="h-nav-badge teal">3</span>
  </a>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="12" cy="12" r="5"/>
      <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
    </svg>
    Gold & Forex
  </a>

  <div class="h-nav-sec">Intelligence</div>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
    </svg>
    Suggestions
    <span class="h-nav-badge">2</span>
  </a>
  <a href="#" class="h-nav-item" onclick="HToast.info('Coming soon!');return false;">
    <svg class="h-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
      <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
    </svg>
    Telegram Bot
  </a>

  <div class="h-sidebar-spacer"></div>

  {{-- User + logout --}}
  <div class="h-sidebar-bottom">
    <div class="h-user-card" data-modal-open="user-menu-modal">
      <div class="h-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
      <div style="flex:1;min-width:0;">
        <div class="h-user-name">{{ auth()->user()->name }}</div>
        <div class="h-user-role">HariLog Free</div>
      </div>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--t3);flex-shrink:0;">
        <circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/>
      </svg>
    </div>
  </div>

</aside>

{{-- ═══ MAIN ═══ --}}
<div class="h-main" id="h-main">

  {{-- Topbar --}}
  <header class="h-topbar">
    <span class="h-page-title-bar">@yield('page_title', 'Dashboard')</span>
    <span id="h-clock" style="font-family:var(--fm);font-size:11px;color:var(--t3);"></span>
    <div class="h-topbar-right">
      @yield('topbar_extra')
      {{-- Theme toggle --}}
      <button class="h-theme-toggle h-icon-btn" title="Toggle theme">
        <span class="moon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg></span>
        <span class="sun" style="display:none"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg></span>
      </button>
    </div>
  </header>

  {{-- Flash messages --}}
  @if(session('success'))
  <div style="padding:14px 28px 0;">
    <div class="h-alert success">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
      {{ session('success') }}
    </div>
  </div>
  @endif
  @if(session('error'))
  <div style="padding:14px 28px 0;">
    <div class="h-alert error">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      {{ session('error') }}
    </div>
  </div>
  @endif

  {{-- Page content --}}
  <div class="h-page">
    @yield('content')
  </div>

</div>

{{-- ═══ GLOBAL MODALS ═══ --}}

{{-- User menu modal --}}
<div class="h-modal-overlay" id="user-menu-modal">
  <div class="h-modal" style="max-width:300px;">
    <div class="h-modal-head">
      <div class="h-modal-title">{{ auth()->user()->name }}</div>
      <button class="h-modal-close">×</button>
    </div>
    <div class="h-modal-body">
      <p style="font-size:13px;color:var(--t2);margin-bottom:18px;">{{ auth()->user()->email }}</p>
      <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" class="h-btn danger full">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Sign Out
        </button>
      </form>
    </div>
  </div>
</div>

{{-- App modals --}}
@yield('modals')

{{-- FAB --}}
@yield('fab')

{{-- Confirm Modal --}}
<x-confirm-modal />
{{-- Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="{{ asset('js/haarray.js') }}"></script>
<script src="{{ asset('js/haarray.plugins.js') }}"></script>
@yield('scripts')

</body>
</html>
