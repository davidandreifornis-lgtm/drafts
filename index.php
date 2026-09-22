<?php
require_once __DIR__ . '/config/auth_lib.php';
auth_require_login(); // Admin only — redirect to login.php if not signed in

/**
 * Toner Inventory — single entry file (index.php only, no index.html needed)
 * Place this file in htdocs/toner-system/ together with api/ and config/ folders.
 */
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = rtrim($scriptDir, '/');
$apiBase = ($scriptDir === '' || $scriptDir === '.') ? '/api' : ($scriptDir . '/api');

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
?><!doctype html>
<html lang="en">
<head>
  <script>window.TONER_API_BASE=<?php echo json_encode($apiBase); ?>;console.info('[Toner] API base =', window.TONER_API_BASE);</script>

  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Toner Inventory</title>
  <meta name="description" content="Approved ticket-driven printer toner and ink inventory management system with real-time tracking, serial verification, and audit logs." />
  <meta property="og:title" content="Printer Toner & Ink Inventory Management System" />
  <meta property="og:description" content="Approved ticket-driven printer toner and ink inventory management system with real-time tracking, serial verification, and audit logs." />
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
          },
          colors: {
            brand: {
              50: '#f8fafc',
              100: '#f1f5f9',
              500: '#0f172a',
              600: '#0f172a',
              700: '#020617',
              800: '#020617',
              900: '#020617'
            }
          },
          boxShadow: {
            soft: '0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.04)',
            lift: '0 8px 30px rgba(15, 23, 42, 0.08)',
          },
          borderRadius: {
            xl: '0.875rem',
            '2xl': '1rem',
          }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --surface: #ffffff;
      --canvas: #fafafa;
      --line: #eef0f3;
      --line-strong: #e5e7eb;
      --ink: #0f172a;
      --muted: #64748b;
      --accent: #0f172a;
    }
    html { scroll-behavior: smooth; }
    body {
      background: var(--canvas);
      color: var(--ink);
      letter-spacing: -0.011em;
    }
    /* Quieter chrome */
    #app-header {
      background: rgba(255,255,255,0.86) !important;
      backdrop-filter: saturate(180%) blur(12px);
      -webkit-backdrop-filter: saturate(180%) blur(12px);
      border-bottom-color: var(--line) !important;
      box-shadow: none !important;
    }
    #sidebar {
      background: #fff !important;
      border-right-color: var(--line) !important;
      position: fixed !important;
    }
    @media (min-width: 1024px) {
      #sidebar {
        transform: translateX(0) !important;
      }
    }
    .nav-link {
      border-radius: 0.75rem !important;
      font-weight: 500 !important;
      letter-spacing: -0.01em;
    }
    .nav-link.bg-blue-50,
    .nav-link.text-blue-700 {
      background: #f4f4f5 !important;
      color: #18181b !important;
    }
    /* Cards / panels */
    .bg-white.rounded-xl.border,
    .bg-white.rounded-2xl.border,
    .bg-white.border.border-slate-200 {
      border-color: var(--line) !important;
      box-shadow: 0 1px 2px rgba(15,23,42,0.03) !important;
    }
    /* Tables */
    table thead {
      background: #fafafa !important;
    }
    table thead th {
      font-size: 0.6875rem !important;
      letter-spacing: 0.06em;
      font-weight: 600 !important;
      color: #71717a !important;
      text-transform: uppercase;
      border-bottom-color: var(--line) !important;
    }
    table tbody td {
      border-color: var(--line) !important;
      vertical-align: middle;
    }
    table tbody tr:hover {
      background: #fafafa !important;
    }
    /* Inputs */
    input[type="text"], input[type="number"], input[type="date"], input[type="password"],
    input[type="email"], select, textarea {
      border-color: var(--line-strong) !important;
      background: #fff !important;
      border-radius: 0.75rem !important;
      transition: border-color .15s ease, box-shadow .15s ease;
    }
    input:focus, select:focus, textarea:focus {
      border-color: #a1a1aa !important;
      box-shadow: 0 0 0 3px rgba(24,24,27,0.06) !important;
      outline: none !important;
      --tw-ring-color: transparent !important;
    }
    input:read-only, input[readonly] {
      background: #fafafa !important;
      color: #52525b !important;
    }
    /* Primary buttons — near-black minimal */
    .bg-blue-600, .bg-emerald-600, .bg-rose-600, .bg-slate-800, .bg-slate-900 {
      box-shadow: none !important;
    }
    button.bg-blue-600, a.bg-blue-600 {
      background: #18181b !important;
    }
    button.bg-blue-600:hover, a.bg-blue-600:hover {
      background: #09090b !important;
    }
    button.bg-emerald-600 {
      background: #18181b !important;
    }
    button.bg-emerald-600:hover {
      background: #09090b !important;
    }
    /* Keep rose for destructive/defective only slightly softer */
    button.bg-rose-600 {
      background: #e11d48 !important;
    }
    button.bg-rose-600:hover {
      background: #be123c !important;
    }
    /* Soft secondary buttons */
    .bg-blue-50 {
      background: #f4f4f5 !important;
      color: #27272a !important;
      border-color: #e4e4e7 !important;
    }
    .text-blue-700, .text-blue-600, .font-mono.font-bold.text-blue-700 {
      color: #27272a !important;
    }
    .text-emerald-700, .text-emerald-600 {
      color: #3f3f46 !important;
    }
    /* KPI cards */
    .text-3xl.font-bold {
      letter-spacing: -0.03em;
      font-weight: 600 !important;
    }
    /* Page titles */
    h2.text-xl, h3.text-lg, h3.text-base {
      letter-spacing: -0.02em;
    }
    /* Tabs minimal underline */
    .txn-main-tab, .loc-sup-tab {
      font-weight: 500 !important;
    }
    .border-b-2.border-blue-600,
    .border-b-2.border-emerald-600,
    .border-b-2.border-rose-600 {
      border-color: #18181b !important;
    }
    .bg-blue-50.border-b-2,
    .bg-emerald-50.border-b-2,
    .bg-rose-50.border-b-2 {
      background: transparent !important;
    }
    /* Badges quieter */
    .rounded-full.bg-slate-100,
    span.bg-slate-100 {
      background: #f4f4f5 !important;
      border-color: transparent !important;
    }
    /* Scrollbars */
    * {
      scrollbar-width: thin;
      scrollbar-color: #d4d4d8 transparent;
    }
    /* Modal polish */
    .modal-card {
      border-color: var(--line) !important;
      box-shadow: 0 25px 50px -12px rgba(15,23,42,0.18) !important;
    }
    #main-area, .flex-1.overflow-y-auto {
      background: var(--canvas);
    }
  </style>
</head>
<body class="bg-[#fafafa] text-slate-900 font-sans antialiased min-h-screen flex flex-col">
  <!-- Top App Navigation / Bar -->
  <header id="app-header" class="fixed top-0 left-0 right-0 z-40 bg-white/90 border-b border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <button id="mobile-menu-toggle" type="button" aria-label="Toggle navigation menu" class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 focus:outline-none transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
        </button>
        <div class="flex items-center gap-2.5">
          <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center font-bold shadow-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
          </div>
          <div>
            <h1 class="font-bold text-slate-900 leading-tight text-base sm:text-lg">Toner Inventory Manager</h1>
            <p class="text-xs text-slate-500 font-medium">Ticket-Governed Inventory Authority</p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2">
        <!-- Notification Bell -->
        <div class="relative">
          <button id="btn-notifications" type="button" class="relative p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors" aria-label="Notifications" title="Notifications">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span id="notif-badge" class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center hidden leading-none">0</span>
          </button>

          <!-- Notification Dropdown Panel -->
          <div id="notif-panel" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-xl border border-slate-200 shadow-xl z-50 overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50">
              <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
              <button id="btn-clear-notifs" type="button" class="text-xs font-medium text-slate-500 hover:text-slate-800 transition-colors">Clear all</button>
            </div>
            <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
              <div class="p-6 text-center text-sm text-slate-400" id="notif-empty">No notifications yet</div>
            </div>
          </div>
        </div>

        <!-- Logout -->
        <button id="btn-logout" type="button" class="inline-flex items-center gap-2 p-2.5 sm:px-3.5 sm:py-2 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors text-sm font-medium" title="Log out" aria-label="Log out">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
          <span class="hidden sm:inline">Log out</span>
        </button>
      </div>
    </div>
  </header>

  <!-- Spacer so content isn't hidden under fixed header -->
  <div class="h-14 shrink-0" aria-hidden="true"></div>

  <div class="flex-1 flex max-w-7xl w-full mx-auto lg:pl-64">
    <!-- Sidebar Navigation -->
    <aside id="sidebar" class="fixed top-14 bottom-0 left-0 z-30 w-64 bg-white border-r border-slate-200 transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out flex flex-col justify-between overflow-y-auto">
      <div class="p-4 space-y-1">
        <div class="px-3 py-2 text-xs font-bold uppercase tracking-wider text-slate-400">Main Navigation</div>
        <button id="nav-dashboard" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors bg-blue-50 text-blue-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
          Dashboard
        </button>
        <button id="nav-inventory" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
          Toner Inventory
        </button>
        <button id="nav-transactions" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          Transaction History
        </button>
        <button id="nav-locations" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Locations &amp; Suppliers
        </button>

        <button id="nav-logs" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
          System Logs
        </button>
        <button id="nav-email-config" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
          Email Configuration
        </button>
        <button id="nav-users" class="nav-link w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-colors text-slate-600 hover:bg-slate-100 hover:text-slate-900">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
          User Management
        </button>

      </div>

      <div class="p-4 border-t border-slate-100 mt-auto shrink-0">
        <div class="p-3 rounded-xl border border-slate-100 bg-zinc-50/80">
          <div class="text-[11px] font-semibold uppercase tracking-wider text-zinc-500">Signed in as</div>
          <div class="mt-2 flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-full bg-zinc-900 text-white flex items-center justify-center text-xs font-bold shrink-0" id="sidebar-user-avatar">—</div>
            <div class="min-w-0">
              <div id="sidebar-user-name" class="text-sm font-semibold text-zinc-900 truncate">Loading…</div>
              <div id="sidebar-user-email" class="text-[11px] text-zinc-500 truncate"></div>
            </div>
          </div>
          <div class="mt-2 text-[10px] font-medium text-zinc-400 uppercase tracking-wider">Admin</div>
        </div>
      </div>
    </aside>

    <!-- Sidebar Backdrop for Mobile -->
    <div id="sidebar-backdrop" class="fixed inset-0 top-14 bg-slate-900/40 z-20 hidden lg:hidden"></div>

    <!-- Main Content Area -->
    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
      <!-- Alerts Banner Container -->
      <div id="global-alerts-container" class="mb-6 space-y-2 hidden"></div>

      <!-- VIEW: DASHBOARD -->
      <section id="view-dashboard" class="page-view space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Inventory Dashboard</h2>
            <p class="text-sm text-slate-500 mt-0.5">Overview of toner stock levels, movements, and ticket statuses.</p>
          </div>
          <div class="flex items-center gap-2">
            <button id="quick-btn-receive" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
              Receive Delivery
            </button>
            <button id="quick-btn-release" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
              Stock Issuance
            </button>
            <button id="quick-btn-defective" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-medium rounded-lg bg-rose-600 text-white hover:bg-rose-700 shadow-xs transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
              Return Defective
            </button>
          </div>
        </div>

        <!-- Dashboard date filter -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs px-4 py-3 flex flex-wrap items-center gap-3">
          <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Period</span>
          <select id="filter-dash-date" class="py-2 px-3 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-slate-400 min-w-[10rem]">
            <option value="ALL">All time</option>
            <option value="TODAY">Today</option>
            <option value="WEEK">Last 7 days</option>
            <option value="MONTH" selected>This month</option>
            <option value="CUSTOM">Custom range</option>
          </select>
          <div id="dash-custom-range" class="hidden flex flex-wrap items-center gap-2">
            <label class="text-xs font-semibold text-slate-500" for="filter-dash-from">From</label>
            <input id="filter-dash-from" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
            <label class="text-xs font-semibold text-slate-500" for="filter-dash-to">To</label>
            <input id="filter-dash-to" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
            <button type="button" id="btn-dash-apply-range" class="px-3 py-2 text-sm font-semibold rounded-xl bg-slate-900 text-white hover:bg-black">Apply</button>
          </div>
          <span id="dash-period-label" class="text-xs text-slate-500 ml-auto"></span>
          <button type="button" id="btn-open-mail-log" class="text-xs font-semibold text-slate-600 border border-slate-200 rounded-lg px-3 py-1.5 hover:bg-slate-50">Mail log</button>
        </div>

        <!-- KPI Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="skus" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Toner SKUs</div>
            <div id="kpi-total-skus" class="text-3xl font-bold text-slate-900 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Toner models in master list</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="stock" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Stock On-Hand</div>
            <div id="kpi-total-stock" class="text-3xl font-bold text-blue-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Available physical units</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-amber-200 transition-colors" data-kpi="low" title="View details">
            <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider">Low Stock Items</div>
            <div id="kpi-low-stock" class="text-3xl font-bold text-amber-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">At or below reorder level</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-rose-200 transition-colors" data-kpi="out" title="View details">
            <div class="text-xs font-semibold text-rose-600 uppercase tracking-wider">Out of Stock</div>
            <div id="kpi-out-stock" class="text-3xl font-bold text-rose-600 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Zero units remaining</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="deliveries" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Deliveries (period)</div>
            <div id="kpi-today-deliveries" class="text-2xl font-bold text-slate-900 mt-2">0 units</div>
            <div id="kpi-today-del-tickets" class="text-xs text-slate-500 mt-1">0 tickets processed</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="releases" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Releases (period)</div>
            <div id="kpi-today-releases" class="text-2xl font-bold text-slate-900 mt-2">0 units</div>
            <div id="kpi-today-rel-tickets" class="text-xs text-slate-500 mt-1">0 tickets processed</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="tickets" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tickets Processed</div>
            <div id="kpi-total-tickets" class="text-2xl font-bold text-slate-900 mt-2">0</div>
            <div class="text-xs text-slate-500 mt-1">Ticket refs in selected period</div>
          </div>
          <div class="kpi-card bg-white p-5 rounded-xl border border-slate-200 shadow-xs cursor-pointer hover:border-slate-300 transition-colors" data-kpi="last" title="View details">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Last Processed Ticket</div>
            <div id="kpi-last-ticket" class="text-lg font-bold text-slate-900 mt-2 truncate">None</div>
            <div id="kpi-last-ticket-time" class="text-xs text-slate-500 mt-1">Awaiting first transaction</div>
          </div>
        </div>

        <!-- Charts row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs lg:col-span-2">
            <div class="mb-4">
              <h3 class="font-bold text-slate-900">Departments Needing Toner Most Often</h3>
              <p class="text-xs text-slate-500">Based on issuance volume (units issued per department)</p>
            </div>
            <div class="relative h-72">
              <canvas id="chart-activity"></canvas>
            </div>
          </div>
          <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
            <div class="mb-4">
              <h3 class="font-bold text-slate-900">Stock Status</h3>
              <p class="text-xs text-slate-500">Share of toner codes by stock level</p>
            </div>
            <div class="relative h-72">
              <canvas id="chart-stock-status"></canvas>
            </div>
          </div>
        </div>

        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs">
          <div class="mb-4 flex flex-wrap items-end justify-between gap-2">
            <div>
              <h3 class="font-bold text-slate-900">Average pages before toner change</h3>
              <p class="text-xs text-slate-500">Mean actual yield (pages) from issuances that recorded page count — selected dashboard period</p>
            </div>
            <div id="kpi-avg-yield" class="text-sm font-semibold text-slate-700">Avg: —</div>
          </div>
          <div class="relative h-72">
            <canvas id="chart-avg-yield"></canvas>
          </div>
        </div>

      </section>

      <!-- VIEW: INVENTORY -->
      <section id="view-inventory" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Toner Inventory</h2>
            <p class="text-sm text-slate-500 mt-0.5">Master list of toners. Click a row for stock card · use actions to add or remove.</p>
          </div>
          <button type="button" id="btn-add-toner" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Toner
          </button>
        </div>

        <!-- Filters Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-wrap items-center gap-3">
          <div class="flex-1 min-w-[200px]">
            <label for="filter-inv-search" class="sr-only">Toner Code</label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              </div>
              <input id="filter-inv-search" type="text" placeholder="Search item code, description, supplier..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
          </div>
          <div class="w-44">
            <select id="filter-inv-status" class="w-full py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="ALL">All Statuses</option>
              <option value="IN_STOCK">In Stock</option>
              <option value="LOW_STOCK">Low Stock</option>
              <option value="OUT_OF_STOCK">Out of Stock</option>
            </select>
          </div>
          <button type="button" id="btn-clear-inv-filters" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
            Reset Filters
          </button>
        </div>

        <!-- Inventory Table Container -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3.5 w-[14%]">Toner Code</th>
                  <th class="px-4 py-3.5 w-[22%]">Description</th>
                  <th class="px-4 py-3.5 w-[22%]">Compatible Printer(s)</th>
                  <th class="px-4 py-3.5 w-[12%]">Supplier</th>
                  <th class="px-4 py-3.5 w-[10%] text-right">Quantity</th>
                  <th class="px-4 py-3.5 w-[10%]">Stock Status</th>
                  <th class="px-4 py-3.5 w-[10%] text-center">Actions</th>
                </tr>
              </thead>
              <tbody id="inventory-tbody" class="divide-y divide-slate-100 text-slate-700">
                <!-- Dynamically populated -->
              </tbody>
            </table>
          </div>
          <div id="inventory-empty-state" class="hidden p-8 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
            <h4 class="font-bold text-slate-800">No Inventory Items Found</h4>
            <p class="text-sm text-slate-500 mt-1">No toner items match the active search or filters.</p>
          </div>
        </div>
      </section>

      <!-- VIEW: RECEIVE DELIVERY -->
      <section id="view-receive" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Receive Delivery</h2>
          <p class="text-sm text-slate-500 mt-0.5">Ingest approved deliveries to automatically increment stock. The ticket is the sole source of truth.</p>
        </div>

        <!-- Search Ticket Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs max-w-2xl">
          <label for="input-del-ref" class="block text-sm font-semibold text-slate-800 mb-1">
            Delivery Reference Number
          </label>
          <p class="text-xs text-slate-500 mb-3">
            Enter the pre-approved ticket reference code (e.g. <span class="font-mono text-blue-600 font-semibold cursor-pointer sample-ref" data-ref="DEL-2026-00125">DEL-2026-00125</span> or <span class="font-mono text-blue-600 font-semibold cursor-pointer sample-ref" data-ref="DEL-2026-00126">DEL-2026-00126</span>).
          </p>
          <div class="flex gap-2">
            <input id="input-del-ref" type="text" placeholder="DEL-2026-00125" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button id="btn-search-delivery" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-xs transition-colors flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              Search Ticket
            </button>
          </div>
        </div>

        <!-- Delivery Ticket Preview Container -->
        <div id="del-ticket-preview" class="hidden bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden max-w-4xl">
          <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-blue-100 text-blue-800">
                  Delivery Ticket
                </span>
                <span id="del-preview-status" class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  APPROVED
                </span>
              </div>
              <h3 id="del-preview-ref" class="text-xl font-bold text-slate-900 font-mono mt-1">DEL-2026-00125</h3>
            </div>
            <div class="text-right text-sm">
              <div class="text-slate-500">Delivery Date</div>
              <div id="del-preview-date" class="font-semibold text-slate-800">September 9, 2026</div>
              <div class="text-xs text-slate-500 mt-1">Supplier: <span id="del-preview-supplier" class="font-semibold text-slate-700">ABC Supplies</span></div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Ticket Items & Quantities</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Ink Code</th>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Printer Model</th>
                    <th class="px-4 py-3">Color</th>
                    <th class="px-4 py-3">Serial Number</th>
                    <th class="px-4 py-3 text-right">Current Stock</th>
                    <th class="px-4 py-3 text-right font-bold text-blue-600">Incoming Qty</th>
                    <th class="px-4 py-3 text-right">New Stock</th>
                  </tr>
                </thead>
                <tbody id="del-preview-tbody" class="divide-y divide-slate-100 text-slate-700">
                  <!-- Injected via JavaScript -->
                </tbody>
              </table>
            </div>

            <div class="p-4 bg-blue-50 border border-blue-100 rounded-lg flex items-center justify-between">
              <div class="text-sm text-blue-900">
                <span class="font-bold">Total Items:</span> <span id="del-preview-total-items" class="font-mono">0</span> | 
                <span class="font-bold">Total Quantity to Ingest:</span> <span id="del-preview-total-qty" class="font-bold font-mono">0</span> units
              </div>
              <div class="text-xs text-blue-700 font-medium">Automatic stock increment ready</div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button id="btn-cancel-del" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                Cancel
              </button>
              <button id="btn-process-delivery" type="button" class="px-6 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Process Delivery
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- VIEW: RELEASE TONER -->
      <section id="view-release" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Release / Give Toner</h2>
          <p class="text-sm text-slate-500 mt-0.5">Fulfill approved toner release tickets. Stock is decremented automatically after atomic pre-validation.</p>
        </div>

        <!-- Search Ticket Card -->
        <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-xs max-w-2xl">
          <label for="input-rel-ref" class="block text-sm font-semibold text-slate-800 mb-1">
            Release Reference Number
          </label>
          <p class="text-xs text-slate-500 mb-3">
            Enter the pre-approved release ticket reference code (e.g. <span class="font-mono text-emerald-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00451">REL-2026-00451</span>, <span class="font-mono text-emerald-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00452">REL-2026-00452</span>, or insufficient test <span class="font-mono text-rose-600 font-semibold cursor-pointer sample-ref" data-ref="REL-2026-00453">REL-2026-00453</span>).
          </p>
          <div class="flex gap-2">
            <input id="input-rel-ref" type="text" placeholder="REL-2026-00451" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            <button id="btn-search-release" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-xs transition-colors flex items-center gap-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
              Search Ticket
            </button>
          </div>
        </div>

        <!-- Release Ticket Preview Container -->
        <div id="rel-ticket-preview" class="hidden bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden max-w-4xl">
          <div class="p-6 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
            <div>
              <div class="flex items-center gap-2.5">
                <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  Release Ticket
                </span>
                <span id="rel-preview-status" class="text-xs font-bold uppercase tracking-wider px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800">
                  APPROVED
                </span>
              </div>
              <h3 id="rel-preview-ref" class="text-xl font-bold text-slate-900 font-mono mt-1">REL-2026-00451</h3>
            </div>
            <div class="text-right text-sm">
              <div class="text-slate-500">Release Date</div>
              <div id="rel-preview-date" class="font-semibold text-slate-800">September 9, 2026</div>
              <div class="text-xs text-slate-500 mt-1">Department: <span id="rel-preview-dept" class="font-semibold text-slate-700">Human Resources</span></div>
              <div class="text-xs text-slate-500">Given To: <span id="rel-preview-givento" class="font-semibold text-slate-700">Juan Dela Cruz</span></div>
            </div>
          </div>

          <div class="p-6 space-y-4">
            <div class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 text-xs text-slate-600 flex items-center gap-2">
              <span class="font-bold text-slate-700">Stated Purpose:</span>
              <span id="rel-preview-purpose" class="italic">Regular office printer replenishment</span>
            </div>

            <!-- Insufficient Stock Warning Alert Box -->
            <div id="rel-stock-error-box" class="hidden p-4 bg-rose-50 border border-rose-200 rounded-lg text-rose-800">
              <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-rose-600 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <div>
                  <h5 class="font-bold text-sm text-rose-900">Unable to Process Release: Insufficient Stock</h5>
                  <p id="rel-stock-error-msg" class="text-xs mt-1 text-rose-700"></p>
                  <p class="text-xs mt-1 font-semibold text-rose-800">Atomic Rule Enforced: No inventory changes were made. All items must be in stock.</p>
                </div>
              </div>
            </div>

            <h4 class="text-sm font-bold text-slate-900 uppercase tracking-wider">Requested Items & Stock Validation</h4>
            <div class="overflow-x-auto border border-slate-200 rounded-lg">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-4 py-3">Ink Code</th>
                    <th class="px-4 py-3">Brand</th>
                    <th class="px-4 py-3">Color</th>
                    <th class="px-4 py-3 text-right">Available Stock</th>
                    <th class="px-4 py-3 text-right font-bold text-emerald-600">Release Qty</th>
                    <th class="px-4 py-3 text-right">Post-Release Stock</th>
                    <th class="px-4 py-3 text-center">Validation</th>
                  </tr>
                </thead>
                <tbody id="rel-preview-tbody" class="divide-y divide-slate-100 text-slate-700">
                  <!-- Dynamically injected -->
                </tbody>
              </table>
            </div>

            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-lg flex items-center justify-between">
              <div class="text-sm text-emerald-900">
                <span class="font-bold">Total Items:</span> <span id="rel-preview-total-items" class="font-mono">0</span> | 
                <span class="font-bold">Total Units to Release:</span> <span id="rel-preview-total-qty" class="font-bold font-mono">0</span> units
              </div>
              <div id="rel-validation-status-badge" class="text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-200 text-emerald-800">
                Stock Verified
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
              <button id="btn-cancel-rel" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                Cancel
              </button>
              <button id="btn-process-release" type="button" class="px-6 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Process Release
              </button>
            </div>
          </div>
        </div>
      </section>

      <!-- VIEW: TRANSACTIONS -->
      <section id="view-transactions" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">Transaction History</h2>
            <p class="text-sm text-slate-500 mt-0.5">Separate logs for incoming deliveries and toner releases by department.</p>
          </div>
          <button id="btn-export-txns" title="Exports only the rows matching the current tab and date filter" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 shadow-xs transition-colors">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
            Export Filtered CSV
          </button>
        </div>

        <!-- Main tabs: Deliveries vs Releases -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="flex border-b border-slate-200" id="txn-main-tabs">
            <button type="button" data-txn-tab="RECEIVED" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-blue-50 border-b-2 border-blue-600 transition-colors">
              Incoming Deliveries
              <span id="txn-tab-count-received" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-blue-100 text-blue-800">0</span>
            </button>
            <button type="button" data-txn-tab="RELEASED" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Releases by Department
              <span id="txn-tab-count-released" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">0</span>
            </button>
            <button type="button" data-txn-tab="DEFECTIVE" class="txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Defective Returns
              <span id="txn-tab-count-defective" class="ml-1.5 inline-flex items-center justify-center min-w-[1.25rem] px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-slate-100 text-slate-600">0</span>
            </button>
          </div>

          <!-- Department sub-tabs (releases only) -->
          <div id="txn-dept-tabs" class="hidden px-4 py-3 border-b border-slate-100 bg-slate-50/80 flex flex-wrap gap-2">
            <!-- Filled dynamically from locations + release history -->
          </div>

          <!-- Search + date filters -->
          <div class="p-4 border-b border-slate-100 flex flex-wrap gap-3 items-center">
            <div class="flex-1 min-w-[200px]">
              <input id="filter-txn-search" type="text" placeholder="Search ref, item code, description, location, department..." class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="w-44">
              <select id="filter-txn-date" class="w-full py-2 px-3 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="ALL">All Time</option>
                <option value="TODAY">Today</option>
                <option value="WEEK">This Week</option>
                <option value="MONTH" selected>This Month</option>
                <option value="CUSTOM">Custom Range</option>
              </select>
            </div>
            <div id="txn-custom-range" class="hidden flex flex-wrap items-center gap-2">
              <label class="text-xs font-semibold text-slate-500" for="filter-txn-from">From</label>
              <input id="filter-txn-from" type="date" class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <label class="text-xs font-semibold text-slate-500" for="filter-txn-to">To</label>
              <input id="filter-txn-to" type="date" class="px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="button" id="btn-txn-apply-range" class="px-3 py-2 text-sm font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition-colors">Apply</button>
            </div>
            <button type="button" id="btn-clear-txn-filters" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-900 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">Reset Filters</button>
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr id="txns-thead-row">
                  <!-- Headers set by renderTransactions() -->
                </tr>
              </thead>
              <tbody id="txns-tbody" class="divide-y divide-slate-100 text-slate-700">
              </tbody>
            </table>
          </div>
          <div id="txns-empty-state" class="hidden p-8 text-center">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            <h4 class="font-bold text-slate-800">No Transactions Found</h4>
            <p class="text-sm text-slate-500 mt-1" id="txns-empty-msg">There are no records in this tab yet.</p>
          </div>
        </div>
      </section>

        
      <!-- VIEW: USERS -->
      
      <!-- ===================== LOCATIONS & PRINTERS ===================== -->
      
      <!-- ===================== LOCATIONS & SUPPLIERS ===================== -->
      <section id="view-locations" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-slate-900">Locations, Printers &amp; Suppliers</h2>
            <p class="text-sm text-slate-500 mt-0.5">Department / location / printer for issuance, and suppliers for stock card edit.</p>
          </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="flex border-b border-slate-200 bg-slate-50">
            <button type="button" id="tab-locations" data-loc-tab="locations" class="loc-sup-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-white border-b-2 border-blue-600 transition-colors">
              Locations &amp; Printers
            </button>
            <button type="button" id="tab-suppliers" data-loc-tab="suppliers" class="loc-sup-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors">
              Suppliers
            </button>
          </div>

          <!-- TAB: Locations -->
          <div id="panel-locations" class="loc-sup-panel p-5 space-y-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Add location and printer</h3>
              <p class="text-xs text-slate-500 mt-0.5">Creates a new option for Stock Issuance. To change an existing row, use <strong>Edit</strong> in the table.</p>
            </div>
            <input type="hidden" id="page-loc-edit-id" value="">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-dept">Department <span class="text-rose-500">*</span></label>
                <input id="page-loc-dept" type="text" placeholder="e.g. ACCT" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-location">Location <span class="text-rose-500">*</span></label>
                <input id="page-loc-location" type="text" placeholder="e.g. Acctg Office" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-loc-printer">Printer assigned</label>
                <input id="page-loc-printer" type="text" placeholder="e.g. Canon MF237W" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <button type="button" id="btn-page-loc-save" class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add location
              </button>
              <button type="button" id="btn-page-loc-clear" class="px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl border border-slate-200">Clear</button>
            </div>

            <div class="border-t border-slate-100 pt-4">
              <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-3">
                <div class="flex items-center gap-2">
                  <h3 class="text-sm font-bold text-slate-900">All locations</h3>
                  <span id="page-loc-count" class="text-xs font-semibold text-slate-500">0</span>
                </div>
                <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                  <input id="filter-locations-search" type="search" placeholder="Search department, location, printer…" class="flex-1 sm:w-64 px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <button type="button" id="btn-locations-search-clear" class="px-3 py-2 text-xs font-medium text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">Clear</button>
                </div>
              </div>
              <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left">
                  <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                      <th class="px-4 py-3 font-semibold">Department</th>
                      <th class="px-4 py-3 font-semibold">Location</th>
                      <th class="px-4 py-3 font-semibold">Printer</th>
                      <th class="px-4 py-3 font-semibold w-36">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="page-locations-tbody" class="divide-y divide-slate-100 text-slate-700">
                  </tbody>
                </table>
              </div>
              <div id="page-locations-empty" class="hidden px-5 py-12 text-center text-slate-400 text-sm">
                No locations yet. Add department, location, and optional printer above.
              </div>
            </div>
          </div>

          <!-- TAB: Suppliers -->
          <div id="panel-suppliers" class="loc-sup-panel hidden p-5 space-y-5">
            <div>
              <h3 class="text-sm font-bold text-slate-900">Add supplier</h3>
              <p class="text-xs text-slate-500 mt-0.5">Suppliers appear as a dropdown when you edit a stock card.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 sm:items-end">
              <div class="flex-1">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="page-supplier-name">Supplier name <span class="text-rose-500">*</span></label>
                <input id="page-supplier-name" type="text" placeholder="e.g. INKRITE" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <button type="button" id="btn-page-supplier-add" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-xs shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add supplier
              </button>
            </div>

            <div class="border-t border-slate-100 pt-4">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold text-slate-900">All suppliers</h3>
                <span id="page-supplier-count" class="text-xs font-semibold text-slate-500">0</span>
              </div>
              <div class="overflow-x-auto rounded-xl border border-slate-200">
                <table class="w-full text-sm text-left">
                  <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                    <tr>
                      <th class="px-4 py-3 font-semibold">Supplier</th>
                      <th class="px-4 py-3 font-semibold w-36">Actions</th>
                    </tr>
                  </thead>
                  <tbody id="page-suppliers-tbody" class="divide-y divide-slate-100 text-slate-700"></tbody>
                </table>
              </div>
              <div id="page-suppliers-empty" class="hidden px-5 py-10 text-center text-slate-400 text-sm">No suppliers yet. Add one above.</div>
            </div>
          </div>
        </div>
      </section>



      <section id="view-logs" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
          <div>
            <h2 class="text-xl font-bold text-slate-900 tracking-tight">System Logs</h2>
            <p class="text-sm text-slate-500 mt-0.5">What each admin did in the system — newest first.</p>
          </div>
          <button type="button" id="btn-refresh-logs" class="px-3 py-2 text-sm font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 self-start">Refresh</button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-4 flex flex-wrap gap-3 items-end">
          <div class="flex-1 min-w-[140px]">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-search">Search</label>
            <input id="filter-logs-search" type="text" placeholder="Action, details, ref, item…" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200">
          </div>
          <div class="w-40">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-period">Period</label>
            <select id="filter-logs-period" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL">All time</option>
              <option value="TODAY">Today</option>
              <option value="WEEK">Last 7 days</option>
              <option value="MONTH" selected>This month</option>
              <option value="CUSTOM">Custom range</option>
            </select>
          </div>
          <div id="logs-custom-range" class="hidden flex flex-wrap items-end gap-2">
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-from">From</label>
              <input id="filter-logs-from" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-to">To</label>
              <input id="filter-logs-to" type="date" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
            </div>
          </div>
          <div class="w-48">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-action">Action type</label>
            <select id="filter-logs-action" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL">All actions</option>
            </select>
          </div>
          <div class="w-48">
            <label class="block text-[11px] font-semibold text-slate-500 mb-1" for="filter-logs-actor">Admin</label>
            <select id="filter-logs-actor" class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white">
              <option value="ALL">All admins</option>
            </select>
          </div>
          <button type="button" id="btn-apply-logs-filter" class="px-4 py-2 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Apply</button>
          <button type="button" id="btn-clear-logs-filter" class="px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50 rounded-xl border border-slate-200">Reset</button>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
              <thead class="bg-slate-50 text-xs uppercase tracking-wider text-slate-500 border-b border-slate-100">
                <tr>
                  <th class="px-4 py-3 font-semibold">When</th>
                  <th class="px-4 py-3 font-semibold">Action</th>
                  <th class="px-4 py-3 font-semibold">Details</th>
                  <th class="px-4 py-3 font-semibold">Admin</th>
                </tr>
              </thead>
              <tbody id="logs-tbody" class="divide-y divide-slate-100 text-slate-700">
                <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

      <section id="view-email-config" class="page-view hidden space-y-6">
        <div>
          <h2 class="text-xl font-bold text-slate-900 tracking-tight">Email Configuration</h2>
          <p class="text-sm text-slate-500 mt-0.5">SMTP and alert settings used when the system sends low-stock and system emails.</p>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden max-w-2xl">
          <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <div class="flex items-center gap-2">
              <span class="w-9 h-9 rounded-xl bg-zinc-900 text-white flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
              </span>
              <div>
                <h3 class="text-sm font-bold text-slate-900">SMTP &amp; outbound mail</h3>
                <p class="text-xs text-slate-500">Host, port, security, and credentials for alert delivery</p>
              </div>
            </div>
          </div>
          <form id="form-email-settings" class="p-5 space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-host">SMTP Host</label>
                <input id="cfg-smtp-host" type="text" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="mail.example.com" required>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-port">Port</label>
                <input id="cfg-smtp-port" type="number" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="465" required>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-enc">Security</label>
                <select id="cfg-smtp-enc" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white">
                  <option value="ssl">SSL</option>
                  <option value="tls">TLS</option>
                  <option value="none">None</option>
                </select>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-user">SMTP username</label>
                <input id="cfg-smtp-user" type="text" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="you@company.com" required>
                <p class="text-[11px] text-slate-400 mt-1">Also used as the From address (and fallback alert recipient if needed).</p>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-smtp-pass">SMTP password</label>
                <input id="cfg-smtp-pass" type="password" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200" placeholder="Leave blank to keep the saved SMTP password" autocomplete="new-password">
                <p id="cfg-smtp-pass-hint" class="text-[11px] text-slate-400 mt-1"></p>
              </div>
              <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-alert-recipient">Alert recipient <span class="text-rose-500">*</span></label>
                <select id="cfg-alert-recipient" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white" required>
                  <option value="">— Select registered admin email —</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">Low-stock and system alerts are sent to this admin. Usernames in User Management must be email addresses.</p>
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1" for="cfg-cooldown">Low-stock alert cooldown (hours)</label>
                <input id="cfg-cooldown" type="number" min="0" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200">
              </div>
              </div>
            <div class="flex flex-wrap gap-2 pt-2 border-t border-slate-100">
              <button type="submit" id="btn-save-email-settings" class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Save email settings</button>
              <button type="button" id="btn-test-email-settings" class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Test low-stock email</button>
            </div>
            <p id="cfg-email-status" class="text-xs text-slate-500"></p>
          </form>
        </div>
      </section>
<section id="view-users" class="page-view hidden space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <h2 class="text-2xl font-bold text-slate-900 tracking-tight">User Management</h2>
            <p class="text-sm text-slate-500 mt-0.5">Create admin accounts. Email is the login username and low-stock notification address.</p>
          </div>
          <button type="button" id="btn-add-user" class="inline-flex items-center gap-2 px-3.5 py-2 text-sm font-semibold rounded-lg bg-blue-600 text-white hover:bg-blue-700 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Admin
          </button>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full text-left text-sm table-fixed">
              <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
                <tr>
                  <th class="px-4 py-3 w-[36%]">Email (login)</th>
                  <th class="px-4 py-3 w-[28%]">Full Name</th>
                  <th class="px-4 py-3 w-[14%]">Status</th>
                  <th class="px-4 py-3 w-[22%]">Actions</th>
                </tr>
              </thead>
              <tbody id="users-tbody" class="divide-y divide-slate-100 text-slate-700">
                <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Loading users…</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>

<div id="modal-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-md z-50 hidden flex items-center justify-center p-4">
<!-- duplicate modal moved to body end -->

    <!-- Ticket Not Found Modal -->
    <div id="modal-not-found" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
      <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div>
          <div class="text-sm font-bold">Ticket Not Found</div>
          <div class="text-xs opacity-80">No approved ticket matches this reference in the registry.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-3">
        The system could not locate an approved delivery or release ticket for:
      </p>
      <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-100 text-center">
        <div class="text-[10px] uppercase tracking-wider text-rose-500 font-semibold mb-1">Reference Number</div>
        <div class="text-base font-mono font-bold text-rose-700" id="modal-notfound-ref">REF-UNKNOWN</div>
      </div>
      <ul class="mt-4 space-y-1.5 text-xs text-slate-500">
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Check for typos in the ticket number</li>
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Confirm the ticket was approved in the system</li>
        <li class="flex items-start gap-2"><span class="text-slate-400 mt-0.5">•</span> Use a sample valid ticket from the list if testing</li>
      </ul>
      <div class="mt-6 flex justify-end">
        <button id="btn-close-notfound-modal" type="button" class="px-5 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl transition-colors">
          Close
        </button>
      </div>
    </div>

    <!-- Reset Demo Confirmation Modal -->
    <div id="modal-reset-confirm" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
      <div class="w-12 h-12 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-900">Reset Demo Data?</h3>
      <p class="text-sm text-slate-600 mt-2">
        This action will wipe all current LocalStorage transactions and stock changes, restoring the pristine demo dataset with all approved delivery and release tickets ready for test runs.
      </p>
      <div class="mt-6 flex justify-end gap-3">
        <button id="btn-cancel-reset" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
          Cancel
        </button>
        <button id="btn-confirm-reset" type="button" class="px-4 py-2 text-sm font-bold bg-rose-600 text-white hover:bg-rose-700 rounded-lg transition-colors">
          Yes, Reset All
        </button>
      </div>
    </div>

        <!-- ===================== RECEIVE DELIVERY MODAL ===================== -->
    <div id="modal-receive" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Receive Delivery</h3>
            <p class="text-xs text-slate-500">Look up MRR number from ERP, then confirm to post stock</p>
          </div>
        </div>
        <button type="button" id="btn-close-receive-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <div id="receive-step-form" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-del-ref">MRR Number <span class="text-rose-500">*</span></label>
            <div class="flex gap-2">
              <input id="modal-del-ref" type="text" placeholder="e.g. MG009105" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
              <button type="button" id="btn-modal-search-mrr" class="px-4 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shrink-0">Search MRR</button>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">Details are loaded from ERP (VM-EGNSERVER) using the MRR number.</p>
          </div>

          <div id="mrr-lookup-status" class="hidden text-sm px-3 py-2 rounded-xl border"></div>

          <div id="mrr-preview" class="hidden space-y-3">
            <div class="flex flex-wrap items-center gap-2 text-xs">
              <span class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-100 font-semibold">MRR <span id="mrr-preview-no" class="font-mono"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Date <span id="mrr-preview-date" class="font-semibold"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Lines <span id="mrr-preview-lines" class="font-semibold"></span></span>
              <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-700 border border-slate-200">Total qty <span id="mrr-preview-qty" class="font-semibold font-mono"></span></span>
            </div>
            <div class="overflow-x-auto rounded-xl border border-slate-200">
              <table class="w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                  <tr>
                    <th class="px-3 py-2">Item code</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2 text-right">Qty</th>
                    <th class="px-3 py-2 text-right">On hand</th>
                    <th class="px-3 py-2 text-right">After</th>
                  </tr>
                </thead>
                <tbody id="mrr-preview-tbody" class="divide-y divide-slate-100"></tbody>
              </table>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-del-supplier">Supplier <span class="text-slate-400 font-normal">(optional)</span></label>
              <input id="modal-del-supplier" type="text" placeholder="Optional — leave blank if not needed" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <p id="mrr-already-warn" class="hidden text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded-xl px-3 py-2">This MRR was already posted in toner inventory.</p>
          </div>

          <div class="flex justify-end gap-2 pt-1">
            <button type="button" id="btn-cancel-receive" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-del" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed" disabled>Confirm &amp; Post Stock</button>
          </div>
        </div>

        <div id="receive-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Delivery Recorded</h4>
          <p class="text-sm text-slate-500 mb-1">Reference <span id="m-del-success-ref" class="font-mono font-semibold text-blue-700"></span></p>
          <p class="text-xs text-slate-400 mb-6">Stock increased · Transaction logged</p>
          <button type="button" id="btn-receive-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>

    <!-- ===================== RELEASE TONER MODAL ===================== -->
    <div id="modal-release" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Stock Issuance</h3>
            <p class="text-xs text-slate-500">Enter ticket reference and issuance details manually</p>
          </div>
        </div>
        <button type="button" id="btn-close-release-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <div id="release-step-form" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-ref">Issuance Reference No. <span class="text-rose-500">*</span></label>
            <input id="modal-rel-ref" type="text" placeholder="e.g. AMEC-006353" class="w-full px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-toner">Item (Description) <span class="text-rose-500">*</span></label>
            <select id="modal-rel-toner" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
              <option value="">— Select toner —</option>
            </select>
            <p id="modal-rel-stock-hint" class="text-xs text-slate-500 mt-1"></p>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-dept">Department <span class="text-rose-500">*</span></label>
              <select id="modal-rel-dept" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select department —</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-location">Location <span class="text-rose-500">*</span></label>
              <select id="modal-rel-location" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select department first —</option>
              </select>
              <div id="m-rel-location-auto" class="hidden mt-2">
                <div class="px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 font-semibold text-slate-800" id="m-rel-location-auto-text">—</div>
                <p class="text-xs text-slate-500 mt-1">Only location for this department — applied automatically.</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-printer">Printer assigned</label>
            <input id="modal-rel-printer" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-700" placeholder="Select location to see printer">
            <p class="text-[11px] text-slate-500 mt-1">From Locations &amp; Suppliers (sidebar).</p>
          </div>

          <div>
            <label class="flex items-start gap-2.5 cursor-pointer select-none">
              <input id="modal-rel-has-yield" type="checkbox" class="mt-1 w-4 h-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
              <span class="text-sm text-slate-800">
                <span class="font-semibold">Printer reports page yield</span>
                <span class="block text-xs font-normal text-slate-500 mt-0.5">Check only if this printer shows total pages printed before the toner ran out / was changed.</span>
              </span>
            </label>
            <div id="modal-rel-yield-wrap" class="hidden mt-2">
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-yield">Actual yield (pages before change) <span class="text-rose-500">*</span></label>
              <input id="modal-rel-yield" type="number" min="0" step="1" placeholder="e.g. 4500" class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>
            <p class="text-[11px] text-slate-500 mt-1">How many pages the previous toner printed before replacement.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-issued-by">Issued by (admin) <span class="text-rose-500">*</span></label>
              <select id="modal-rel-issued-by" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                <option value="">— Select admin —</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1">Recorded by (logged in)</label>
              <input id="modal-rel-recorded-by" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed" value="">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="modal-rel-date">Date</label>
            <input id="modal-rel-date" type="date" readonly tabindex="-1" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-500 cursor-not-allowed">
            <p class="text-[11px] text-slate-500 mt-1">Always set to today — not editable</p>
          </div>


          <p class="text-xs text-slate-500">Issuance is always <strong>1 unit</strong> per ticket.</p>
          <div class="flex justify-end gap-3 pt-2">
            <button type="button" id="btn-modal-cancel-rel" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-rel" class="px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl">Record Issuance</button>
          </div>
        </div>

        <div id="release-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Issuance Recorded</h4>
          <p class="text-sm text-slate-500 mb-1">Reference <span id="m-rel-success-ref" class="font-mono font-semibold text-emerald-700"></span></p>
          <p class="text-xs text-slate-400 mb-6">1 unit deducted · Transaction logged</p>
          <button type="button" id="btn-release-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>

<!-- ===================== RETURN DEFECTIVE MODAL ===================== -->
    <div id="modal-defective" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Return Defective Toner</h3>
            <p class="text-xs text-slate-500">Flag an issued ticket as a defective return</p>
          </div>
        </div>
        <button type="button" id="btn-close-defective-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="p-6 space-y-5 overflow-y-auto flex-1 min-h-0">
        <div id="defective-step-search">
          <label class="block text-sm font-semibold text-slate-800 mb-1">Issuance Ticket Number</label>
          <p class="text-xs text-slate-500 mb-3">Enter the stock issuance ticket that was already released. That ticket will be flagged as defective.</p>
          <div class="flex gap-2">
            <input id="modal-def-ref" type="text" placeholder="e.g. REL-2026-00451" class="flex-1 px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
            <button id="btn-modal-search-def" type="button" class="px-5 py-2.5 text-sm font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-colors">Search</button>
          </div>
          <div class="mt-4 p-3.5 rounded-xl bg-rose-50 border border-rose-100 hidden" id="defective-sample-tickets" aria-hidden="true" style="display:none">
            <div class="text-xs font-semibold text-rose-800 mb-2">Sample issued tickets (click to load)</div>
            <div class="flex flex-wrap gap-2">
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors" data-ref="REL-2026-00451">
                REL-2026-00451 · LOGISTICS
              </button>
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-rose-200 text-rose-700 hover:bg-rose-100 transition-colors" data-ref="REL-2026-00452">
                REL-2026-00452 · ACCT
              </button>
              <button type="button" class="sample-def-ref inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-mono font-semibold rounded-lg bg-white border border-slate-200 text-slate-500 hover:bg-slate-50 transition-colors" data-ref="REL-2026-99999">
                REL-2026-99999 (not found)
              </button>
            </div>
            <p class="text-[11px] text-rose-600/80 mt-2">These are pre-completed issuances. Use one to try flagging as defective.</p>
          </div>
        </div>

        <div id="defective-step-error" class="hidden space-y-3 relative z-10 rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-lg">
          <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
              <div class="text-sm font-bold" id="defective-error-title">Not Found</div>
              <div class="text-xs opacity-80" id="defective-error-desc">No matching issuance found.</div>
            </div>
          </div>
          <div class="p-3 bg-rose-50 rounded-xl border border-rose-100 text-center">
            <div class="text-[10px] uppercase tracking-wider text-rose-500 font-semibold mb-1">Reference</div>
            <div class="text-base font-mono font-bold text-rose-700" id="defective-error-ref">—</div>
          </div>
          <div class="flex justify-end">
            <button type="button" id="btn-defective-error-back" class="px-5 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl">Try another ticket</button>
          </div>
        </div>

        <div id="defective-step-preview" class="hidden space-y-4 relative z-10 rounded-2xl border-2 border-rose-200 bg-white p-4 shadow-lg">
          <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
              <div class="text-sm font-bold">Issuance Found — Mark as Defective</div>
              <div class="text-xs opacity-80">This will flag the issued toner as defective. Stock is not returned to usable inventory.</div>
            </div>
          </div>
          <div class="p-4 bg-slate-50 rounded-xl border border-slate-200 space-y-2 text-sm">
            <div class="flex justify-between gap-2"><span class="text-slate-500">Ticket</span><span id="m-def-ref" class="font-mono font-bold text-rose-700"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Toner Code</span><span id="m-def-code" class="font-mono font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Department</span><span id="m-def-dept" class="font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Location</span><span id="m-def-loc" class="font-semibold"></span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Qty issued</span><span id="m-def-qty" class="font-mono font-bold">1</span></div>
            <div class="flex justify-between gap-2"><span class="text-slate-500">Issued on</span><span id="m-def-date" class="text-slate-700"></span></div>
          </div>
          <div>
            <label for="modal-def-notes" class="block text-sm font-semibold text-slate-800 mb-1">Defect notes (optional)</label>
            <textarea id="modal-def-notes" rows="2" placeholder="e.g. Leaking cartridge, print quality failure..." class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
          </div>
          <div class="flex justify-end gap-3 pt-1">
            <button type="button" id="btn-modal-cancel-def" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
            <button type="button" id="btn-modal-process-def" class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">
              Flag as Defective
            </button>
          </div>
        </div>

        <div id="defective-step-success" class="hidden text-center py-8">
          <div class="w-16 h-16 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
          </div>
          <h4 class="text-xl font-bold text-slate-900 mb-1">Marked as Defective</h4>
          <p class="text-sm text-slate-500 mb-1">Issuance <span id="m-def-success-ref" class="font-mono font-semibold text-rose-700"></span> flagged.</p>
          <p class="text-xs text-slate-400 mb-6">Logged under Defective Returns · usable stock not increased</p>
          <button type="button" id="btn-defective-done" class="px-6 py-2.5 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-lg">Done</button>
        </div>
      </div>
    </div>


    <!-- ===================== STOCK CARD / MOVEMENT HISTORY ===================== -->
    <div id="modal-stock-card" class="modal-card hidden bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-bold text-slate-900 truncate">Stock Card — <span id="stock-card-code" class="font-mono text-blue-700">—</span></h3>
            <p class="text-xs text-slate-500 truncate" id="stock-card-meta">Edit quantity, printers & supplier · view movement history</p>
          </div>
        </div>
        <button type="button" id="btn-close-stock-card" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>

      <div class="px-6 py-3 border-b border-slate-100 bg-slate-50 space-y-2 relative">
        <input type="hidden" id="stock-card-ink-code" value="">
        <div class="flex flex-wrap items-center gap-3 text-sm">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-xs">
            <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">On hand</span>
            <span id="stock-card-onhand" class="font-bold font-mono text-lg text-slate-900">0</span>
          </div>
        </div>
        <div>
          <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Compatible printers</div>
          <div id="stock-card-printer" class="flex flex-wrap gap-1.5 text-sm"></div>
        </div>
        <div>
          <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">Supplier(s)</div>
          <div id="stock-card-supplier" class="flex flex-wrap gap-1.5 text-sm"></div>
        </div>

              </div>

      <div class="p-4 overflow-y-auto flex-1 min-h-0 space-y-3">
        <div class="flex flex-wrap items-end gap-2">
          <div>
            <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-period">Period</label>
            <select id="stock-card-period" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200 bg-white">
              <option value="ALL" selected>All time</option>
              <option value="TODAY">Today</option>
              <option value="WEEK">Last 7 days</option>
              <option value="MONTH">This month</option>
              <option value="CUSTOM">Custom range</option>
            </select>
          </div>
          <div id="stock-card-custom-range" class="hidden flex flex-wrap items-end gap-2">
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-from">From</label>
              <input id="stock-card-from" type="date" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200">
            </div>
            <div>
              <label class="block text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1" for="stock-card-to">To</label>
              <input id="stock-card-to" type="date" class="px-2.5 py-1.5 text-xs rounded-lg border border-slate-200">
            </div>
          </div>
          <button type="button" id="btn-stock-card-apply-dates" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-zinc-900 text-white hover:bg-black">Apply</button>
          <button type="button" id="btn-stock-card-reset-dates" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50">Reset</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-left text-sm table-fixed">
            <thead class="bg-slate-50 text-slate-600 text-xs uppercase font-semibold border-b border-slate-200">
              <tr>
                <th class="px-3 py-3 w-[16%]">Date</th>
                <th class="px-3 py-3 w-[22%]">Transaction</th>
                <th class="px-3 py-3 w-[18%]">Reference</th>
                <th class="px-3 py-3 w-[12%] text-right">Stock In</th>
                <th class="px-3 py-3 w-[12%] text-right">Stock Out</th>
                <th class="px-3 py-3 w-[12%] text-right">Balance</th>
                <th class="px-3 py-3 w-[8%]">Notes</th>
              </tr>
            </thead>
            <tbody id="stock-card-tbody" class="divide-y divide-slate-100 text-slate-700">
            </tbody>
          </table>
        </div>
        <p id="stock-card-empty" class="hidden text-center text-sm text-slate-400 py-8">No movement history for this toner yet.</p>
      </div>

      <div class="shrink-0 px-6 py-3 border-t border-slate-200 flex flex-wrap justify-end gap-2">
        <button type="button" id="btn-stock-card-edit" class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-xl text-blue-700 bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-300 transition-colors">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          Edit details
        </button>
        <button type="button" id="btn-stock-card-done" class="px-5 py-2 text-sm font-semibold bg-slate-800 text-white hover:bg-slate-900 rounded-xl">Close</button>
      </div>
    </div>


    
    <!-- STOCK CARD EDIT — full-screen pop-out with blur -->
    <div id="stock-card-edit-panel" class="hidden fixed inset-0 z-[80] flex items-center justify-center p-4 sm:p-6">
      <!-- blurred dim backdrop -->
      <div id="stock-card-edit-backdrop" class="absolute inset-0 bg-slate-900/50 backdrop-blur-md"></div>
      <!-- dialog -->
      <div class="relative z-10 w-full max-w-md bg-white rounded-2xl shadow-2xl border border-slate-200/80 overflow-hidden ring-1 ring-black/5">
        <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 via-white to-white flex items-center gap-3">
          <div class="w-11 h-11 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-blue-600/25">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h4 class="text-base font-bold text-slate-900">Edit item details</h4>
            <p class="text-xs text-slate-500">Changes save to toner inventory</p>
          </div>
          <button type="button" id="btn-stock-card-edit-cancel" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors" title="Close">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>

        <div class="px-5 py-4 space-y-4 max-h-[min(60vh,28rem)] overflow-y-auto">
          <div class="rounded-xl bg-slate-50 border border-slate-100 px-3.5 py-2.5 flex items-center justify-between gap-2">
            <div>
              <div class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Item code</div>
              <div id="stock-card-edit-code-display" class="font-mono font-bold text-slate-900 text-sm mt-0.5">—</div>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-md bg-slate-200/80 text-slate-600">Read-only</span>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-description-input">Description</label>
            <input id="stock-card-description-input" type="text" readonly tabindex="-1" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed shadow-xs" placeholder="—">
            <p class="text-[11px] text-slate-500 mt-1">From MRR / master data — not editable here.</p>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-qty">Quantity on hand</label>
              <input id="stock-card-qty" type="number" min="0" step="1" class="w-full px-3.5 py-2.5 text-sm font-mono font-bold rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
            </div>
            <div>
              <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-reorder">Reorder level</label>
              <input id="stock-card-reorder" type="number" min="0" step="1" class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5" for="stock-card-supplier-input">Supplier</label>
            <select id="stock-card-supplier-input" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400">
              <option value="">— Select supplier —</option>
            </select>
            <p class="text-[11px] text-slate-500 mt-1">Managed under <strong>Locations &amp; Suppliers</strong>.</p>
          </div>

          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1.5">Compatible printer(s)</label>
            <div id="stock-card-printers-checkboxes" class="max-h-40 overflow-y-auto rounded-xl border border-slate-200 bg-white p-3 space-y-2 shadow-xs">
              <p class="text-xs text-slate-400">Loading printers…</p>
            </div>
            <p class="text-[11px] text-slate-500 mt-1.5">Check all printers this toner works with. List comes from <strong>Locations &amp; Printers</strong>.</p>
            <input type="hidden" id="stock-card-printers-input" value="">
            <select id="stock-card-printers-select" class="hidden" multiple></select>
          </div>

          <p class="text-[11px] text-amber-900 bg-amber-50 border border-amber-100 rounded-xl px-3 py-2.5 leading-relaxed">
            Changing <strong>quantity</strong> records a stock adjustment in transaction history.
          </p>
        </div>

        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50/90 flex justify-end gap-2">
          <button type="button" id="btn-stock-card-edit-cancel-2" class="px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-white border border-slate-200 rounded-xl transition-colors">Cancel</button>
          <button type="button" id="btn-stock-card-save" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold bg-blue-600 text-white hover:bg-blue-700 rounded-xl shadow-md shadow-blue-600/20 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            Save changes
          </button>
        </div>
      </div>
    </div>

    
    <!-- MANAGE LOCATIONS -->
    <div id="modal-locations" class="modal-card hidden bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col z-[90]">
      <div class="shrink-0 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
        <div>
          <h3 class="text-lg font-bold text-slate-900">Departments, locations &amp; printers</h3>
          <p class="text-xs text-slate-500">These options appear on Stock Issuance</p>
        </div>
        <button type="button" id="btn-close-locations" class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-4 overflow-y-auto flex-1 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <input id="loc-edit-dept" type="text" placeholder="Department e.g. ACCT" class="px-3 py-2 text-sm rounded-xl border border-slate-200 uppercase font-mono">
          <input id="loc-edit-location" type="text" placeholder="Location e.g. Acctg Office" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
          <input id="loc-edit-printer" type="text" placeholder="Printer name (optional)" class="px-3 py-2 text-sm rounded-xl border border-slate-200">
        </div>
        <input type="hidden" id="loc-edit-id" value="">
        <div class="flex gap-2">
          <button type="button" id="btn-loc-save" class="px-4 py-2 text-sm font-semibold bg-emerald-600 text-white rounded-xl hover:bg-emerald-700">Add / Update</button>
          <button type="button" id="btn-loc-clear" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-xl">Clear form</button>
        </div>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
          <table class="w-full text-sm text-left">
            <thead class="bg-slate-50 text-xs uppercase text-slate-500 border-b">
              <tr>
                <th class="px-3 py-2">Department</th>
                <th class="px-3 py-2">Location</th>
                <th class="px-3 py-2">Printer</th>
                <th class="px-3 py-2 w-24">Actions</th>
              </tr>
            </thead>
            <tbody id="locations-tbody" class="divide-y divide-slate-100"></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ===================== ADD TONER MODAL ===================== -->
    <div id="modal-add-toner" class="modal-card hidden bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between z-20 rounded-t-2xl">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
          </div>
          <div>
            <h3 class="text-lg font-bold text-slate-900">Add Toner</h3>
            <p class="text-xs text-slate-500">Register a new toner code in inventory</p>
          </div>
        </div>
        <button type="button" id="btn-close-add-toner" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-6 space-y-4 overflow-y-auto flex-1 min-h-0">
        <p class="text-xs text-slate-500 bg-slate-50 border border-slate-100 rounded-xl px-3 py-2">
          Same fields as an <strong>MRR line</strong> (except dates). Saved to <code class="font-mono text-[11px]">dbo.toner_inventory</code>.
        </p>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-code">Item Code <span class="text-rose-500">*</span></label>
          <input id="add-toner-code" type="text" required placeholder="e.g. OS00000251" class="w-full px-3.5 py-2.5 text-sm font-mono uppercase rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">Item_code</span> → column <span class="font-mono">item_code</span>.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-description">Description <span class="text-rose-500">*</span></label>
          <input id="add-toner-description" type="text" required placeholder="e.g. INK CRG-737 - CANON" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">Item_Desc</span> → column <span class="font-mono">description</span>.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="add-toner-qty">Quantity <span class="text-rose-500">*</span></label>
          <input id="add-toner-qty" type="number" min="0" value="0" required class="w-full px-3.5 py-2.5 text-sm font-mono rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          <p class="text-[11px] text-slate-500 mt-1">Same as MRR <span class="font-mono">MRR_Qty</span> → column <span class="font-mono">quantity</span>.</p>
        </div>
        <!-- Hidden defaults (not on MRR; system defaults) -->
        <input type="hidden" id="add-toner-supplier" value="">
        <input type="hidden" id="add-toner-brand" value="">
        <input type="hidden" id="add-toner-reorder" value="3">
        <div id="add-toner-printer-list" class="hidden">
          <input type="text" class="add-printer-input" value="">
        </div>
      </div>
      <div class="shrink-0 px-6 py-4 border-t border-slate-200 flex justify-end gap-3">
        <button type="button" id="btn-cancel-add-toner" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-save-add-toner" class="px-5 py-2.5 text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl">Save Toner</button>
      </div>
    </div>

    <!-- ===================== REMOVE TONER CONFIRM ===================== -->
    <div id="modal-remove-toner" class="modal-card hidden bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
      <div class="flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 mb-4">
        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
          <div class="text-sm font-bold">Remove toner from inventory?</div>
          <div class="text-xs opacity-80">This deletes the stock card entry for this code.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600 mb-2">You are about to remove:</p>
      <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-center mb-2">
        <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-1">Toner Code</div>
        <div class="text-lg font-mono font-bold text-rose-700" id="remove-toner-code-label">—</div>
      </div>
      <p class="text-xs text-slate-500 mb-5">Past transaction history is kept for audit. Only the inventory master record is removed.</p>
      <div class="flex justify-end gap-3">
        <button type="button" id="btn-cancel-remove-toner" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-confirm-remove-toner" class="px-5 py-2.5 text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Yes, Remove</button>
      </div>
    </div>

    <!-- Logout Confirmation Modal -->
    
    <div id="modal-user" class="modal-card hidden bg-white rounded-2xl max-w-md w-full max-h-[90vh] overflow-hidden shadow-xl border border-slate-200 flex flex-col">
      <div class="shrink-0 border-b border-slate-200 px-6 py-4 flex items-center justify-between">
        <div>
          <h3 id="modal-user-title" class="text-lg font-bold text-slate-900">Add Admin</h3>
          <p class="text-xs text-slate-500">Email is used to sign in and for low-stock alerts</p>
        </div>
        <button type="button" id="btn-close-user-modal" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="p-6 space-y-4 overflow-y-auto flex-1">
        <input type="hidden" id="user-edit-id" value="">
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-username">Email <span class="text-rose-500">*</span></label>
          <input id="user-username" type="email" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="admin@company.com" autocomplete="off">
          <p class="text-[11px] text-slate-500 mt-1">This email is the login username and notification address.</p>
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-fullname">Full Name</label>
          <input id="user-fullname" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional">
        </div>
        <div>
          <label class="block text-sm font-semibold text-slate-800 mb-1" for="user-password">Password <span id="user-pass-req" class="text-rose-500">*</span></label>
          <input id="user-password" type="password" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Min. 6 characters" autocomplete="new-password">
          <p id="user-pass-hint" class="text-[11px] text-slate-500 mt-1 hidden">Leave blank to keep the current password</p>
        </div>
        <div id="user-active-wrap" class="hidden">
          <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input id="user-active" type="checkbox" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500" checked>
            Active (can sign in)
          </label>
        </div>
      </div>
      <div class="shrink-0 border-t border-slate-200 px-6 py-4 flex justify-end gap-2">
        <button type="button" id="btn-cancel-user" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl">Cancel</button>
        <button type="button" id="btn-save-user" class="px-4 py-2 text-sm font-bold bg-blue-600 text-white hover:bg-blue-700 rounded-xl">Save</button>
      </div>
    </div>

    <div id="modal-logout" class="modal-card hidden bg-white rounded-2xl max-w-sm w-full p-6 shadow-xl border border-slate-200">
      <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
      </div>
      <h3 class="text-lg font-bold text-slate-900">Log out?</h3>
      <p class="text-sm text-slate-600 mt-2">
        You will be signed out of the Toner Inventory Manager. Unsaved work is already stored locally.
      </p>
      <div class="mt-6 flex justify-end gap-3">
        <button id="btn-cancel-logout" type="button" class="px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-xl transition-colors">
          Cancel
        </button>
        <button id="btn-confirm-logout" type="button" class="px-4 py-2 text-sm font-bold bg-slate-800 text-white hover:bg-slate-900 rounded-xl transition-colors">
          Log out
        </button>
      </div>
    </div>

  </div>

  <!-- TOAST NOTIFICATION STACK -->
  <div id="toast-container" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 max-w-sm pointer-events-none"></div>

  <!-- Complete Application Logic Script (Sectioned) -->
  <script>
// ==========================================
// 1. CONSTANTS
// ==========================================
const STORAGE_KEYS = {
  INKS: 'inventoryToners_v2',
  TRANSACTIONS: 'inventoryTransactions_v3',
  TICKETS: 'inventoryTickets_v2',
  SETTINGS: 'inventorySettings'
};

const STOCK_STATUS = {
  IN_STOCK: 'IN STOCK',
  LOW_STOCK: 'LOW STOCK',
  OUT_OF_STOCK: 'OUT OF STOCK'
};

// Department → Location options for release destination
const RELEASE_LOCATIONS = [
  { department: 'ACCT', location: 'Acctg Office' },
  { department: 'BD', location: 'BD Office' },
  { department: 'BMS', location: 'General Warehouse' },
  { department: 'LOGISTICS', location: 'Logistics Office' },
  { department: 'PRODUCTION', location: 'Production Office' },
  { department: 'PRODUCTION', location: 'Packaging Office' },
  { department: 'PURCHASING', location: 'Purchasing Office' },
  { department: 'QC', location: 'QC Laboratory' },
  { department: 'LOGISTICS', location: 'PM warehouse' }
];

const DEMO_INITIAL_INVENTORY = [
  {
    id: "TNR-001",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "ACCT",
    serialNumbers: [],
    quantity: 8,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Acctg Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-002",
    inkCode: "CF276A",
    brand: "HP",
    printerModel: "HP LaserJet Pro MFP M428fdn",
    color: "Black",
    department: "BD",
    serialNumbers: [],
    quantity: 6,
    reorderLevel: 3,
    supplier: "JAN A",
    location: "BD Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-003",
    inkCode: "Q2612A",
    brand: "Canon",
    printerModel: "Canon LBP 2900",
    color: "Black",
    department: "BMS",
    serialNumbers: [],
    quantity: 10,
    reorderLevel: 3,
    supplier: "JMD",
    location: "General Warehouse",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-004",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "LOGISTICS",
    serialNumbers: [],
    quantity: 5,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Logistics Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-005",
    inkCode: "CAN 045 HBK",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Black",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 7,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-006",
    inkCode: "CAN 045 HC",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Cyan",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-007",
    inkCode: "CAN 045 HY",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Yellow",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-008",
    inkCode: "CAN 045 HM",
    brand: "Canon",
    printerModel: "Canon MF633DW",
    color: "Magenta",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 4,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Production Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-009",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "PRODUCTION",
    serialNumbers: [],
    quantity: 6,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Packaging Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-010",
    inkCode: "CRG-737",
    brand: "Canon",
    printerModel: "Canon MF237W",
    color: "Black",
    department: "PURCHASING",
    serialNumbers: [],
    quantity: 5,
    reorderLevel: 3,
    supplier: "INKRITE",
    location: "Purchasing Office",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-011",
    inkCode: "CF280A",
    brand: "HP",
    printerModel: "HP Color LaserJet Pro 400 MFP M425dn",
    color: "Black",
    department: "QC",
    serialNumbers: [],
    quantity: 3,
    reorderLevel: 3,
    supplier: "JMD",
    location: "QC Laboratory",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  },
  {
    id: "TNR-012",
    inkCode: "Q2612A",
    brand: "Canon",
    printerModel: "Canon LBP 2900",
    color: "Black",
    department: "LOGISTICS",
    serialNumbers: [],
    quantity: 9,
    reorderLevel: 3,
    supplier: "JMD",
    location: "PM warehouse",
    createdAt: "2026-09-01T09:00:00",
    updatedAt: "2026-09-09T10:00:00"
  }
];

const DEMO_APPROVED_TICKETS = [
  {
    id: "TICKET-DEL-01",
    referenceNumber: "DEL-2026-00125",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "ABC Office Supplies",
    items: [
      {
        serialNumber: "SN-H682-001",
        inkCode: "HP-682-BLK",
        brand: "HP",
        printerModel: "HP DeskJet 2775",
        color: "Black",
        quantity: 20
      },
      {
        serialNumber: "SN-H682-002",
        inkCode: "HP-682-CYN",
        brand: "HP",
        printerModel: "HP DeskJet 2775",
        color: "Cyan",
        quantity: 15
      }
    ],
    createdAt: "2026-09-09T08:30:00"
  },
  {
    id: "TICKET-DEL-02",
    referenceNumber: "DEL-2026-00126",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "TechInk Distributors",
    items: [
      {
        serialNumber: "SN-CPG47-101",
        inkCode: "Canon PG-47",
        brand: "Canon",
        printerModel: "Canon PIXMA E410 / E470",
        color: "Black",
        quantity: 25
      },
      {
        serialNumber: "SN-CCL57-201",
        inkCode: "Canon CL-57",
        brand: "Canon",
        printerModel: "Canon PIXMA E410 / E470",
        color: "Tri-color",
        quantity: 20
      }
    ],
    createdAt: "2026-09-09T09:15:00"
  },
  {
    id: "TICKET-DEL-03",
    referenceNumber: "DEL-2026-00127",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "Universal Supplies Co.",
    items: [
      {
        serialNumber: "SN-EP003-BK1",
        inkCode: "Epson 003 Black",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Black",
        quantity: 30
      },
      {
        serialNumber: "SN-EP003-CY1",
        inkCode: "Epson 003 Cyan",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Cyan",
        quantity: 15
      }
    ],
    createdAt: "2026-09-09T10:00:00"
  },
  {
    id: "TICKET-DEL-04",
    referenceNumber: "DEL-2026-00128",
    type: "DELIVERY",
    status: "APPROVED",
    date: "2026-09-09",
    supplier: "Prime Office Gear",
    items: [
      {
        serialNumber: "SN-BR60-01",
        inkCode: "Brother BTD60BK",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Black",
        quantity: 18
      },
      {
        serialNumber: "SN-BR50-01",
        inkCode: "Brother BT5000C",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Cyan",
        quantity: 12
      }
    ],
    createdAt: "2026-09-09T10:30:00"
  },
  {
    id: "TICKET-REL-01",
    referenceNumber: "REL-2026-00451",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Neressa J. Siman",
    department: "LOGISTICS",
    purpose: "Toner request for Logistics Office printer",
    items: [
      {
        serialNumber: "",
        inkCode: "CRG-737",
        brand: "Canon",
        printerModel: "Canon MF237W",
        color: "Black",
        quantity: 1
      }
    ],
    createdAt: "2026-09-09T11:00:00"
  },
  {
    id: "TICKET-REL-02",
    referenceNumber: "REL-2026-00452",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Maria Santos",
    department: "ACCT",
    purpose: "Accounting office toner replenishment",
    items: [
      {
        serialNumber: "",
        inkCode: "CRG-737",
        brand: "Canon",
        printerModel: "Canon MF237W",
        color: "Black",
        quantity: 1
      }
    ],
    createdAt: "2026-09-09T11:30:00"
  },
  {
    id: "TICKET-REL-03",
    referenceNumber: "REL-2026-00453",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Alex Rivera",
    department: "PRODUCTION",
    purpose: "Production Office toner request",
    items: [
      {
        serialNumber: "SN-BR60-01",
        inkCode: "Brother BTD60BK",
        brand: "Brother",
        printerModel: "Brother DCP-T510W",
        color: "Black",
        quantity: 50 // Explicitly exceeds available stock (7) to test atomic rejection!
      }
    ],
    createdAt: "2026-09-09T12:00:00"
  },
  {
    id: "TICKET-REL-04",
    referenceNumber: "REL-2026-00454",
    type: "RELEASE",
    status: "APPROVED",
    date: "2026-09-09",
    givenTo: "Sarah Gomez",
    department: "QC",
    purpose: "Product catalog print run",
    items: [
      {
        serialNumber: "SN-EP003-B01",
        inkCode: "Epson 003 Black",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Black",
        quantity: 3
      },
      {
        serialNumber: "SN-EP003-C01",
        inkCode: "Epson 003 Cyan",
        brand: "Epson",
        printerModel: "Epson EcoTank L3110",
        color: "Cyan",
        quantity: 2
      }
    ],
    createdAt: "2026-09-09T12:30:00"
  }
];

const DEMO_INITIAL_TRANSACTIONS = [
  {
    id: "TXN-00001",
    type: "RECEIVED",
    referenceNumber: "DEL-2026-00100",
    inkId: "TNR-001",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 5,
    date: "2026-09-01",
    supplier: "INKRITE",
    givenTo: "",
    department: "",
    location: "",
    purpose: "Initial stock receipt",
    status: "APPROVED",
    createdAt: "2026-09-01T09:00:00"
  },
  {
    id: "TXN-00002",
    type: "RELEASED",
    referenceNumber: "REL-2026-00451",
    inkId: "TNR-004",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 1,
    date: "2026-09-09",
    supplier: "",
    givenTo: "Neressa J. Siman",
    department: "LOGISTICS",
    location: "Logistics Office",
    purpose: "Toner request for Logistics Office printer",
    status: "APPROVED",
    defective: false,
    createdAt: "2026-09-09T11:00:00"
  },
  {
    id: "TXN-00003",
    type: "RELEASED",
    referenceNumber: "REL-2026-00452",
    inkId: "TNR-001",
    inkCode: "CRG-737",
    serialNumber: "N/A",
    brand: "Canon",
    color: "Black",
    quantity: 1,
    date: "2026-09-09",
    supplier: "",
    givenTo: "Maria Santos",
    department: "ACCT",
    location: "Acctg Office",
    purpose: "Accounting office toner replenishment",
    status: "APPROVED",
    defective: false,
    createdAt: "2026-09-09T11:30:00"
  }
];

// ==========================================
// 2. APPLICATION STATE
// ==========================================
const AppState = {
  activeMrrLookup: null,
  releaseLocations: [],
  suppliers: [],
  adminUsers: [],
  currentUser: null,
  users: [],
  currentPage: "dashboard",
  inks: [],
  transactions: [],
  tickets: [],
  settings: {
    systemName: "Printer Toner & Ink Inventory Management System",
    reorderNotificationEmail: "admin@organization.com"
  },
  activeDeliveryTicket: null,
  activeReleaseTicket: null,
  activeDefectiveTxn: null,
  pendingRemoveTonerCode: null,
  selectedReleaseLocation: '',
  selectedReleaseDepartment: '',
  charts: {
    activity: null,
    brand: null,
    reportStatus: null,
    reportDept: null
  },
  filters: {
    inventorySearch: "",
    inventoryBrand: "ALL",
    inventoryColor: "ALL",
    inventoryStatus: "ALL",
    transactionSearch: "",
    transactionType: "RECEIVED",
    transactionBrand: "ALL",
    transactionDate: "ALL",
    transactionDateFrom: "",
    transactionDateTo: "",
    transactionDept: "ALL",
    reportDate: "ALL"
  },
  notifications: []
};

// ==========================================
// 3. STORAGE SERVICE
// ==========================================
// Centralized persistence interface. Future PHP/MySQL backend migration replaces
// these methods with standard Fetch API calls without altering calling business logic.

// ==========================================
// BACKEND API LAYER (PHP + MySQL)
// Falls back to localStorage if API is offline
// ==========================================
const API_BASE = (window.TONER_API_BASE || (function(){ var p=location.pathname.replace(/\/[^/]*$/, ''); return (p||'') + '/api'; })()).replace(/\/$/, '');
AppState.useBackend = false;


let _loadingDepth = 0;

function showGlobalLoading(message) {
  _loadingDepth++;
  const el = document.getElementById('global-loading');
  const txt = document.getElementById('global-loading-text');
  if (txt && message) txt.textContent = message;
  else if (txt && _loadingDepth === 1) txt.textContent = 'Processing…';
  if (el) {
    el.classList.remove('hidden');
    el.style.display = 'flex';
  }
}

function hideGlobalLoading() {
  _loadingDepth = Math.max(0, _loadingDepth - 1);
  if (_loadingDepth > 0) return;
  const el = document.getElementById('global-loading');
  if (el) {
    el.classList.add('hidden');
    el.style.display = 'none';
  }
}

function forceHideGlobalLoading() {
  _loadingDepth = 0;
  const el = document.getElementById('global-loading');
  if (el) {
    el.classList.add('hidden');
    el.style.display = 'none';
  }
}

async function apiRequest(path, options = {}) {
  const silent = !!options.silent;
  const loadingMsg = options.loadingMessage || 'Processing…';
  if (!silent) showGlobalLoading(loadingMsg);
  try {
    const base = (window.TONER_API_BASE || API_BASE || 'api').replace(/\/$/, '');
    const url = `${base}/${path.replace(/^\//, '')}`;
    console.info('[Toner] API', options.method || 'GET', url);
    const opts = {
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json', ...(options.headers || {}) },
      ...options,
    };
    delete opts.silent;
    delete opts.loadingMessage;
    if (opts.body && typeof opts.body === 'object') {
      opts.body = JSON.stringify(opts.body);
    }
    let res;
    try {
      res = await fetch(url, opts);
    } catch (networkErr) {
      const err = new Error('Cannot reach API at ' + url + ' — is Apache running?');
      err.cause = networkErr;
      throw err;
    }
    let data = null;
    try { data = await res.json(); } catch (_) { data = null; }
    if (!res.ok || (data && data.ok === false)) {
      const err = new Error((data && data.error) || `Request failed (${res.status}) at ${url}`);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  } finally {
    if (!silent) hideGlobalLoading();
  }
}

async function detectBackend() {
  try {
    const data = await apiRequest('health.php', { silent: true });
    AppState.useBackend = !!(data && data.ok);
  } catch (_) {
    AppState.useBackend = false;
  }
  return AppState.useBackend;
}

async function loadFromBackendSilent() {
  const [inv, tx] = await Promise.all([
    apiRequest('inventory.php', { silent: true }),
    apiRequest('transactions.php', { silent: true }),
  ]);
  AppState.inks = inv.items || [];
  AppState.transactions = tx.transactions || [];
  AppState.tickets = [];
  renderAlerts();
}

async function loadFromBackend() {
  const [inv, tx] = await Promise.all([
    apiRequest('inventory.php', { loadingMessage: 'Refreshing data…' }),
    apiRequest('transactions.php', { silent: true }),
  ]);
  AppState.inks = inv.items || [];
  AppState.transactions = tx.transactions || [];
  AppState.tickets = [];
  renderAlerts();
  // Quiet email check: still alerts admins if stock remains low (respects 12h cooldown)
  /* low-stock email on load optional - primary trigger is issuance */
  triggerLowStockEmailCheck(false).catch(() => {});
}

async function apiAddToner(payload) {
  return apiRequest('inventory.php', { method: 'POST', body: payload });
}
async function apiUpdateToner(payload) {
  return apiRequest('inventory.php', { method: 'PUT', body: payload });
}

async function apiRemoveToner(inkCode) {
  return apiRequest('inventory.php', {
    method: 'DELETE',
    body: { inkCode },
  });
}

async function apiRecordDelivery(payload) {
  return apiRequest('delivery.php', { method: 'POST', body: payload });
}

async function apiRecordRelease(payload, options = {}) {
  return apiRequest('release.php', {
    method: 'POST',
    body: payload,
    loadingMessage: options.loadingMessage || 'Issuing toner…',
    silent: !!options.silent,
  });
}

async function apiRecordDefective(payload) {
  return apiRequest('defective.php', { method: 'POST', body: payload });
}


const StorageService = {
  getInks() {
    // FUTURE BACKEND INTEGRATION:
    // Replace this LocalStorage operation with a Fetch API request
    // to a PHP backend endpoint: GET /api/inventory.php connected to MySQL.
    try {
      const data = localStorage.getItem(STORAGE_KEYS.INKS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getInks error:", e);
      return [];
    }
  },

  saveInks(inks) {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API PUT/POST to /api/inventory.php
    try {
      localStorage.setItem(STORAGE_KEYS.INKS, JSON.stringify(inks));
    } catch (e) {
      console.error("StorageService.saveInks error:", e);
    }
  },

  getTransactions() {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API GET /api/transactions.php
    try {
      const data = localStorage.getItem(STORAGE_KEYS.TRANSACTIONS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getTransactions error:", e);
      return [];
    }
  },

  saveTransactions(transactions) {
    // FUTURE BACKEND INTEGRATION:
    // Replace with Fetch API POST /api/transactions.php
    try {
      localStorage.setItem(STORAGE_KEYS.TRANSACTIONS, JSON.stringify(transactions));
    } catch (e) {
      console.error("StorageService.saveTransactions error:", e);
    }
  },

  getTickets() {
    // FUTURE BACKEND INTEGRATION:
    // Replace LocalStorage ticket lookup with a Fetch API request
    // to the PHP backend: GET /api/tickets.php
    try {
      const data = localStorage.getItem(STORAGE_KEYS.TICKETS);
      return data ? JSON.parse(data) : [];
    } catch (e) {
      console.error("StorageService.getTickets error:", e);
      return [];
    }
  },

  saveTickets(tickets) {
    try {
      localStorage.setItem(STORAGE_KEYS.TICKETS, JSON.stringify(tickets));
    } catch (e) {
      console.error("StorageService.saveTickets error:", e);
    }
  },

  getSettings() {
    try {
      const data = localStorage.getItem(STORAGE_KEYS.SETTINGS);
      return data ? JSON.parse(data) : null;
    } catch (e) {
      return null;
    }
  },

  saveSettings(settings) {
    try {
      localStorage.setItem(STORAGE_KEYS.SETTINGS, JSON.stringify(settings));
    } catch (e) {
      console.error("StorageService.saveSettings error:", e);
    }
  },

  resetAllDemoData() {
    localStorage.setItem(STORAGE_KEYS.INKS, JSON.stringify(DEMO_INITIAL_INVENTORY));
    localStorage.setItem(STORAGE_KEYS.TRANSACTIONS, JSON.stringify(DEMO_INITIAL_TRANSACTIONS));
    localStorage.setItem(STORAGE_KEYS.TICKETS, JSON.stringify(DEMO_APPROVED_TICKETS));
  }
};

// ==========================================
// 4. DATA INITIALIZATION
// ==========================================
function initializeDataSafely() {
  const existingInks = StorageService.getInks();
  if (!existingInks || existingInks.length === 0) {
    StorageService.saveInks(DEMO_INITIAL_INVENTORY);
  }

  const existingTransactions = StorageService.getTransactions();
  if (!existingTransactions || existingTransactions.length === 0) {
    StorageService.saveTransactions(DEMO_INITIAL_TRANSACTIONS);
  }

  // Always ensure demo tickets exist (merge by reference number)
  // so sample valid tickets keep working even with older localStorage data
  let existingTickets = StorageService.getTickets() || [];
  const byRef = new Map(existingTickets.map(t => [normalizeRefNumber(t.referenceNumber), t]));
  DEMO_APPROVED_TICKETS.forEach(demo => {
    const key = normalizeRefNumber(demo.referenceNumber);
    if (!byRef.has(key)) {
      existingTickets.push(demo);
      byRef.set(key, demo);
    }
  });
  StorageService.saveTickets(existingTickets);

  // Load into AppState
  AppState.inks = StorageService.getInks();
  AppState.transactions = StorageService.getTransactions();
  AppState.tickets = StorageService.getTickets();
}

// ==========================================
// 5. UTILITY FUNCTIONS
// ==========================================
function escapeHTML(str) {
  if (typeof str !== 'string') return String(str ?? '');
  return str.replace(/[&<>'"]/g, tag => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    "'": '&#39;',
    '"': '&quot;'
  }[tag] || tag));
}

/** Philippine Time (Asia/Manila) — 12-hour clock */
const APP_TIMEZONE = 'Asia/Manila';

function toManilaDate(input) {
  if (input == null || input === '') return null;
  if (input instanceof Date) return isNaN(input.getTime()) ? null : input;
  let s = String(input).trim();
  // Date-only (txn_date): treat as calendar day in PH
  if (/^\d{4}-\d{2}-\d{2}$/.test(s)) {
    return new Date(s + 'T12:00:00+08:00');
  }
  s = s.replace(' ', 'T');
  if (s.includes('.')) s = s.split('.')[0]; // drop fractional seconds
  // SQL UTC timestamps without Z → treat as UTC
  if (!/[zZ]$|[+-]\d{2}:?\d{2}$/.test(s)) {
    s += 'Z';
  }
  const d = new Date(s);
  return isNaN(d.getTime()) ? null : d;
}

function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  try {
    const d = toManilaDate(dateStr);
    if (!d) return String(dateStr);
    return d.toLocaleDateString('en-PH', {
      timeZone: APP_TIMEZONE,
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    });
  } catch (e) {
    return String(dateStr);
  }
}

function formatDateTime(dateStr) {
  if (!dateStr) return 'N/A';
  try {
    const d = toManilaDate(dateStr);
    if (!d) return String(dateStr);
    return d.toLocaleString('en-PH', {
      timeZone: APP_TIMEZONE,
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (e) {
    return String(dateStr);
  }
}


function normalizeRefNumber(ref) {
  return (ref || '').trim().toUpperCase();
}

// ==========================================
// 6. ID GENERATION
// ==========================================
function generateTransactionId() {
  const current = AppState.transactions.length + 1;
  return 'TXN-' + String(current).padStart(5, '0');
}

function generateInkId() {
  const current = AppState.inks.length + 1;
  return 'INK-' + String(current).padStart(3, '0');
}

// ==========================================
// 7. NAVIGATION
// ==========================================
function navigateTo(pageId) {
  const validPages = ['dashboard', 'inventory', 'transactions', 'locations', 'logs', 'email-config', 'users'];
  if (!validPages.includes(pageId)) pageId = 'dashboard';
  AppState.currentPage = pageId;
  try {
    localStorage.setItem('toner_ui_page', pageId);
    localStorage.setItem('toner_ui_txn_tab', AppState.filters.transactionType || 'RECEIVED');
    localStorage.setItem('toner_ui_txn_date', AppState.filters.transactionDate || 'ALL');
    localStorage.setItem('toner_ui_txn_dept', AppState.filters.transactionDept || 'ALL');
  } catch (_) {}

  // Toggle page sections
  document.querySelectorAll('.page-view').forEach(view => {
    view.classList.add('hidden');
  });
  const targetView = document.getElementById(`view-${pageId}`);
  if (targetView) targetView.classList.remove('hidden');

  // Update nav links styling
  document.querySelectorAll('.nav-link').forEach(link => {
    link.classList.remove('bg-blue-50', 'text-blue-700', 'font-semibold');
    link.classList.add('text-slate-600');
  });
  const activeLink = document.getElementById(`nav-${pageId}`);
  if (activeLink) {
    activeLink.classList.add('bg-blue-50', 'text-blue-700', 'font-semibold');
    activeLink.classList.remove('text-slate-600');
  }

  // Close mobile sidebar
  const sidebar = document.getElementById('sidebar');
  const backdrop = document.getElementById('sidebar-backdrop');
  if (sidebar && backdrop) {
    sidebar.classList.add('-translate-x-full');
    backdrop.classList.add('hidden');
  }

  // Refresh page data
  if (pageId === 'dashboard') {
    renderDashboard();
    renderCharts();
  } else if (pageId === 'inventory') {
    renderInventory();
  } else if (pageId === 'transactions') {
    renderTransactions();
  } else if (pageId === 'locations') {
    loadLocationsPage();
  } else if (pageId === 'logs') {
    loadSystemLogs();
  } else if (pageId === 'email-config') {
    loadEmailSettings();
  } else if (pageId === 'users') {
    loadUsers();
  } else if (pageId === 'reports') {
    renderReports();
  }

  renderAlerts();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ==========================================
// 8. DASHBOARD
// ==========================================
function renderDashboard() {
  syncDashCustomRangeUI();
  const inks = AppState.inks;
  // Inventory levels = current snapshot (not period-filtered)
  const totalSkus = inks.length;
  const totalStock = inks.reduce((acc, item) => acc + (Number(item.quantity) || 0), 0);

  let lowStockCount = 0;
  let outStockCount = 0;
  inks.forEach(item => {
    const status = getStockStatus(item.quantity, item.reorderLevel);
    if (status === STOCK_STATUS.OUT_OF_STOCK) outStockCount++;
    else if (status === STOCK_STATUS.LOW_STOCK) lowStockCount++;
  });

  // Movement KPIs / activity use selected dashboard period
  const transactions = getDashboardFilteredTransactions();
  const periodDeliveries = transactions.filter(t => t.type === 'RECEIVED');
  const periodReleases = transactions.filter(t => t.type === 'RELEASED');

  const todayDelUnits = periodDeliveries.reduce((sum, t) => sum + (Number(t.quantity) || 0), 0);
  const todayRelUnits = periodReleases.reduce((sum, t) => sum + (Number(t.quantity) || 0), 0);

  const uniqueDelTickets = new Set(periodDeliveries.map(t => t.referenceNumber)).size;
  const uniqueRelTickets = new Set(periodReleases.map(t => t.referenceNumber)).size;

  const totalTicketsProcessed = new Set(transactions.map(t => t.referenceNumber)).size;

  const latestTxn = transactions.length > 0
    ? [...transactions].sort((a, b) => String(b.date || b.createdAt).localeCompare(String(a.date || a.createdAt)))[0]
    : null;

  document.getElementById('kpi-total-skus').textContent = totalSkus;
  document.getElementById('kpi-total-stock').textContent = totalStock.toLocaleString();
  document.getElementById('kpi-low-stock').textContent = lowStockCount;
  document.getElementById('kpi-out-stock').textContent = outStockCount;
  document.getElementById('kpi-today-deliveries').textContent = `${todayDelUnits} units`;
  document.getElementById('kpi-today-del-tickets').textContent = `${uniqueDelTickets} ticket(s) in period`;
  document.getElementById('kpi-today-releases').textContent = `${todayRelUnits} units`;
  document.getElementById('kpi-today-rel-tickets').textContent = `${uniqueRelTickets} ticket(s) in period`;
  document.getElementById('kpi-total-tickets').textContent = totalTicketsProcessed;

  if (latestTxn) {
    document.getElementById('kpi-last-ticket').textContent = latestTxn.referenceNumber;
    document.getElementById('kpi-last-ticket-time').textContent = `${latestTxn.type}: ${latestTxn.quantity}x ${latestTxn.inkCode}`;
  } else {
    document.getElementById('kpi-last-ticket').textContent = 'None';
    document.getElementById('kpi-last-ticket-time').textContent = 'Awaiting first transaction';
  }

  // Movement totals (all time)
  let totalReceived = 0, totalReleased = 0;
  transactions.forEach(t => {
    const q = Number(t.quantity) || 0;
    if (t.type === 'RECEIVED') totalReceived += q;
    else if (t.type === 'RELEASED') totalReleased += q;
  });
  const net = totalReceived - totalReleased;
  const elRec = document.getElementById('dash-sum-received');
  const elRel = document.getElementById('dash-sum-released');
  const elNet = document.getElementById('dash-sum-net');
  if (elRec) elRec.textContent = '+' + totalReceived.toLocaleString();
  if (elRel) elRel.textContent = '-' + totalReleased.toLocaleString();
  if (elNet) {
    elNet.textContent = (net >= 0 ? '+' : '') + net.toLocaleString();
    elNet.className = 'text-3xl font-bold mt-2 ' + (net >= 0 ? 'text-blue-600' : 'text-amber-600');
  }

  // Attention table (aggregate by toner code)
  const attBody = document.getElementById('dash-attention-tbody');
  if (attBody) {
    const byCode = {};
    AppState.inks.forEach(item => {
      const code = (item.inkCode || '').trim();
      if (!code) return;
      const k = code.toUpperCase();
      if (!byCode[k]) {
        byCode[k] = { inkCode: code, quantity: 0, reorderLevel: 0, supplier: item.supplier || '—' };
      }
      byCode[k].quantity += Number(item.quantity) || 0;
      byCode[k].reorderLevel = Math.max(byCode[k].reorderLevel, Number(item.reorderLevel) || 0);
      if (item.supplier) byCode[k].supplier = item.supplier;
    });
    const lowOrOut = Object.values(byCode).filter(item => {
      const s = getStockStatus(item.quantity, item.reorderLevel);
      return s === STOCK_STATUS.OUT_OF_STOCK || s === STOCK_STATUS.LOW_STOCK;
    }).sort((a, b) => a.quantity - b.quantity);

    if (lowOrOut.length === 0) {
      attBody.innerHTML = `<tr><td colspan="5" class="text-center py-6 text-emerald-600 font-medium">All toners are currently well-stocked.</td></tr>`;
    } else {
      attBody.innerHTML = lowOrOut.map(item => {
        const isOut = item.quantity <= 0;
        const badge = isOut
          ? `<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-rose-100 text-rose-800">OUT OF STOCK</span>`
          : `<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-amber-100 text-amber-800">LOW STOCK</span>`;
        return `<tr class="hover:bg-slate-50">
          <td class="px-4 py-3 font-mono font-bold text-slate-900">${escapeHTML(item.inkCode)}</td>
          <td class="px-4 py-3 text-right font-mono font-bold ${isOut ? 'text-rose-600' : 'text-amber-600'}">${item.quantity}</td>
          <td class="px-4 py-3 text-right font-mono text-slate-500">${item.reorderLevel}</td>
          <td class="px-4 py-3">${badge}</td>
          <td class="px-4 py-3 text-xs text-slate-600">${escapeHTML(item.supplier)}</td>
        </tr>`;
      }).join('');
    }
  }

  renderCharts();


  // Render recent 5 transactions
  const recentTxns = [...transactions].reverse().slice(0, 5);
  const tbody = document.getElementById('dash-recent-txns-tbody');
  if (tbody) {
    if (recentTxns.length === 0) {
      tbody.innerHTML = `<tr><td colspan="7" class="text-center py-6 text-slate-400">No toner movements recorded yet.</td></tr>`;
    } else {
      tbody.innerHTML = recentTxns.map(t => {
        const isReceived = t.type === 'RECEIVED';
        const typeBadge = isReceived
          ? `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-blue-100 text-blue-800">RECEIVED</span>`
          : `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800">RELEASED</span>`;
        return `
          <tr class="hover:bg-slate-50/70 transition-colors">
            <td class="px-4 py-3 font-mono font-bold text-slate-900 text-xs">${escapeHTML(t.referenceNumber)}</td>
            <td class="px-4 py-3">${typeBadge}</td>
            <td class="px-4 py-3 font-semibold font-mono text-slate-900">${escapeHTML(t.inkCode)}</td>
            <td class="px-4 py-3 text-xs">${escapeHTML(t.department || '—')}</td>
            <td class="px-4 py-3 text-xs text-slate-600">${escapeHTML(t.location || t.supplier || '—')}</td>
            <td class="px-4 py-3 text-right font-bold ${isReceived ? 'text-blue-600' : 'text-emerald-600'}">${isReceived ? '+' : '-'}${t.quantity}</td>
            <td class="px-4 py-3 text-xs text-slate-500">${formatDate(t.date)}</td>
          </tr>
        `;
      }).join('');
    }
  }
}

// ==========================================
// 9. DATE FILTERING
// ==========================================
function parseLocalDate(dateStr) {
  if (!dateStr) return null;
  const s = String(dateStr).trim();
  // Prefer YYYY-MM-DD as local calendar date (avoid UTC shift that drops rows from "This Month")
  const m = s.match(/^(\d{4})-(\d{2})-(\d{2})/);
  if (m) {
    return new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]));
  }
  const d = new Date(s);
  if (Number.isNaN(d.getTime())) return null;
  return new Date(d.getFullYear(), d.getMonth(), d.getDate());
}

function isDateInFilter(dateStr, filterType, fromStrOverride, toStrOverride) {
  if (!filterType || filterType === 'ALL') return true;
  if (!dateStr) return true;

  const itemDay = parseLocalDate(dateStr);
  if (!itemDay) return true;

  const now = new Date();
  const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());

  if (filterType === 'TODAY') {
    return itemDay.getTime() === today.getTime();
  }

  if (filterType === 'WEEK') {
    const oneWeekAgo = new Date(today);
    oneWeekAgo.setDate(today.getDate() - 7);
    return itemDay >= oneWeekAgo && itemDay <= today;
  }

  if (filterType === 'MONTH') {
    return itemDay.getFullYear() === now.getFullYear() && itemDay.getMonth() === now.getMonth();
  }

  if (filterType === 'CUSTOM') {
    const fromStr = fromStrOverride !== undefined ? fromStrOverride : AppState.filters.transactionDateFrom;
    const toStr = toStrOverride !== undefined ? toStrOverride : AppState.filters.transactionDateTo;
    if (!fromStr && !toStr) return true;

    let ok = true;
    if (fromStr) {
      const fromDay = parseLocalDate(fromStr);
      ok = ok && fromDay && itemDay >= fromDay;
    }
    if (toStr) {
      const toDay = parseLocalDate(toStr);
      ok = ok && toDay && itemDay <= toDay;
    }
    return ok;
  }

  return true;
}

function getDashboardFilteredTransactions() {
  const filterType = AppState.filters.dashboardDate || 'MONTH';
  const from = AppState.filters.dashboardDateFrom || '';
  const to = AppState.filters.dashboardDateTo || '';
  return (AppState.transactions || []).filter(t =>
    isDateInFilter(t.date || t.createdAt, filterType, from, to)
  );
}

function syncDashCustomRangeUI() {
  const wrap = document.getElementById('dash-custom-range');
  const isCustom = AppState.filters.dashboardDate === 'CUSTOM';
  if (wrap) {
    if (isCustom) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
  const from = document.getElementById('filter-dash-from');
  const to = document.getElementById('filter-dash-to');
  if (from) from.value = AppState.filters.dashboardDateFrom || '';
  if (to) to.value = AppState.filters.dashboardDateTo || '';
  const sel = document.getElementById('filter-dash-date');
  if (sel && sel.value !== AppState.filters.dashboardDate) {
    sel.value = AppState.filters.dashboardDate || 'MONTH';
  }
  const label = document.getElementById('dash-period-label');
  if (label) {
    const map = { ALL: 'All time', TODAY: 'Today', WEEK: 'Last 7 days', MONTH: 'This month', CUSTOM: 'Custom range' };
    let text = map[AppState.filters.dashboardDate] || 'This month';
    if (AppState.filters.dashboardDate === 'CUSTOM' && (AppState.filters.dashboardDateFrom || AppState.filters.dashboardDateTo)) {
      text = (AppState.filters.dashboardDateFrom || '…') + ' → ' + (AppState.filters.dashboardDateTo || '…');
    }
    label.textContent = text;
  }
}

function syncTxnCustomRangeUI() {
  const wrap = document.getElementById('txn-custom-range');
  const isCustom = AppState.filters.transactionDate === 'CUSTOM';
  if (wrap) {
    if (isCustom) wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
  const from = document.getElementById('filter-txn-from');
  const to = document.getElementById('filter-txn-to');
  if (from && from.value !== (AppState.filters.transactionDateFrom || '')) {
    from.value = AppState.filters.transactionDateFrom || '';
  }
  if (to && to.value !== (AppState.filters.transactionDateTo || '')) {
    to.value = AppState.filters.transactionDateTo || '';
  }
}

// ==========================================
// 10. INVENTORY
// ==========================================
function resetInventoryFilters() {
  if (!AppState.filters) AppState.filters = {};
  AppState.filters.inventorySearch = '';
  AppState.filters.inventoryStatus = 'ALL';
  const invSearch = document.getElementById('filter-inv-search');
  const invStatus = document.getElementById('filter-inv-status');
  if (invSearch) {
    invSearch.value = '';
    invSearch.dispatchEvent(new Event('input', { bubbles: true }));
  }
  if (invStatus) {
    invStatus.value = 'ALL';
    invStatus.selectedIndex = 0; // All Statuses
    invStatus.dispatchEvent(new Event('change', { bubbles: true }));
  }
  // Force state again (in case input handlers re-read stale values)
  AppState.filters.inventorySearch = '';
  AppState.filters.inventoryStatus = 'ALL';
  renderInventory();
}

function renderInventory() {

  const tbody = document.getElementById('inventory-tbody');
  const emptyState = document.getElementById('inventory-empty-state');
  if (!tbody) return;

  const search = String(AppState.filters.inventorySearch || '').toLowerCase().trim();
  const statusFilter = AppState.filters.inventoryStatus || 'ALL';

  // Aggregate by toner code so each code appears only once
  const byCode = {};
  AppState.inks.forEach(item => {
    const code = (item.inkCode || '').trim();
    if (!code) return;
    const key = code.toUpperCase();
    if (!byCode[key]) {
      byCode[key] = {
        inkCode: code,
        description: item.description || '',
        brand: item.brand || '',
        printerModel: item.printerModel || '',
        supplier: item.supplier || '',
        quantity: 0,
        reorderLevel: Number(item.reorderLevel) || 0,
        printers: new Set(),
        suppliers: new Set()
      };
    }
    byCode[key].quantity += Number(item.quantity) || 0;
    if (!byCode[key].description && item.description) byCode[key].description = item.description;
    if (!byCode[key].brand && item.brand) byCode[key].brand = item.brand;
    // Use the highest reorder level among lines (safer alert threshold)
    byCode[key].reorderLevel = Math.max(byCode[key].reorderLevel, Number(item.reorderLevel) || 0);
    if (item.printerModel) {
      String(item.printerModel).split(/\s*·\s*/).map(s => s.trim()).filter(Boolean).forEach(p => byCode[key].printers.add(p));
    }
    if (item.supplier) byCode[key].suppliers.add(item.supplier);
  });

  let aggregated = Object.values(byCode).map(g => {
    const allPrinters = [];
    const seen = new Set();
    [...g.printers].forEach(p => {
      const norm = (p || '').trim();
      if (!norm) return;
      const key = norm.toUpperCase();
      if (seen.has(key)) return;
      seen.add(key);
      allPrinters.push(norm);
    });
    return {
      inkCode: g.inkCode,
      description: g.description || '',
      brand: g.brand || '',
      printerModel: allPrinters.length ? allPrinters.join(' · ') : (g.printerModel || '—'),
      printerList: allPrinters.length ? allPrinters : [],
      supplier: [...g.suppliers].join(', ') || g.supplier || '—',
      supplierList: [...g.suppliers],
      quantity: g.quantity,
      reorderLevel: g.reorderLevel || 3
    };
  });

  const filtered = aggregated.filter(item => {
    if (search) {
      const matchCode = (item.inkCode || '').toLowerCase().includes(search);
      const matchDesc = (item.description || '').toLowerCase().includes(search);
      const matchSupplier = (item.supplier || '').toLowerCase().includes(search);
      const matchPrinter = (item.printerModel || '').toLowerCase().includes(search);
      if (!matchCode && !matchDesc && !matchSupplier && !matchPrinter) return false;
    }
    const status = getStockStatus(item.quantity, item.reorderLevel);
    if (statusFilter === 'IN_STOCK' && status !== STOCK_STATUS.IN_STOCK) return false;
    if (statusFilter === 'LOW_STOCK' && status !== STOCK_STATUS.LOW_STOCK) return false;
    if (statusFilter === 'OUT_OF_STOCK' && status !== STOCK_STATUS.OUT_OF_STOCK) return false;
    return true;
  });

  // Sort by toner code
  filtered.sort((a, b) => (a.inkCode || '').localeCompare(b.inkCode || '', undefined, { sensitivity: 'base' }));

  if (filtered.length === 0) {
    tbody.innerHTML = '';
    emptyState.classList.remove('hidden');
    return;
  }

  emptyState.classList.add('hidden');
  tbody.innerHTML = filtered.map(item => {
    const status = getStockStatus(item.quantity, item.reorderLevel);
    let badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-200';
    if (status === STOCK_STATUS.OUT_OF_STOCK) {
      badgeClass = 'bg-rose-100 text-rose-800 border-rose-200 font-bold';
    } else if (status === STOCK_STATUS.LOW_STOCK) {
      badgeClass = 'bg-amber-100 text-amber-800 border-amber-200 font-bold';
    }

    return `
      <tr class="hover:bg-blue-50/60 transition-colors cursor-pointer inventory-row" data-ink-code="${escapeHTML(item.inkCode)}" title="View stock card / movement history">
        <td class="px-4 py-3.5 font-bold text-slate-900 font-mono whitespace-nowrap">
          <span class="inline-flex items-center gap-1.5">
            ${escapeHTML(item.inkCode)}
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </span>
        </td>
        <td class="px-4 py-3.5 text-slate-700 text-sm">
          ${escapeHTML(item.description || '—')}
        </td>
        <td class="px-4 py-3.5">
          <div class="flex flex-wrap gap-1.5">
            ${(!item.printerList || !item.printerList.length)
              ? `<span class="text-xs text-slate-400 italic">No printer listed</span>`
              : (item.printerList.length === 1
                  ? `<span class="text-sm text-slate-700" title="${escapeHTML(item.printerList[0])}">${escapeHTML(item.printerList[0])}</span>`
                  : item.printerList.map(p => `<span class="inline-flex items-center gap-1 max-w-full px-2 py-1 rounded-lg text-[11px] font-medium bg-indigo-50 text-indigo-800 border border-indigo-100 shadow-xs" title="${escapeHTML(p)}"><svg class="w-3 h-3 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2z"></path></svg><span class="truncate">${escapeHTML(p)}</span></span>`).join('')
                )}
          </div>
        </td>
        <td class="px-4 py-3.5">
          <div class="flex flex-wrap gap-1.5">
            ${(item.supplierList && item.supplierList.length
              ? item.supplierList.map(s => `<span class="inline-flex items-center px-2 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-700 border border-slate-200">${escapeHTML(s)}</span>`).join('')
              : `<span class="text-xs text-slate-400">—</span>`)}
          </div>
        </td>
        <td class="px-4 py-3.5 text-right font-bold font-mono text-base ${item.quantity === 0 ? 'text-rose-600' : (item.quantity <= item.reorderLevel ? 'text-amber-600' : 'text-slate-900')}">
          ${item.quantity}
        </td>
        <td class="px-4 py-3.5">
          <span class="inline-block px-2.5 py-1 text-xs rounded-full border ${badgeClass}">
            ${status}
          </span>
        </td>
        <td class="px-4 py-3.5 text-center">
          <button type="button" class="btn-remove-toner inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold rounded-lg text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 transition-colors" data-ink-code="${escapeHTML(item.inkCode)}" title="Remove this toner from inventory">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
            Remove
          </button>
        </td>
      </tr>
    `;
  }).join('');
}


// ==========================================
// STOCK CARD / TONER MOVEMENT HISTORY
// ==========================================

// ==========================================
// ADD / REMOVE TONER
// ==========================================
function renderAddPrinterRows(names) {
  const list = document.getElementById('add-toner-printer-list');
  if (!list) return;
  const values = (names && names.length) ? names : [''];
  list.innerHTML = values.map((name, idx) => `
    <div class="flex gap-2 items-center add-printer-row">
      <input type="text" class="add-printer-input flex-1 px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g. Canon MF237W" value="${escapeHTML(name || '')}">
      <button type="button" class="btn-remove-printer-row p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors ${values.length === 1 ? 'invisible' : ''}" title="Remove printer">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
  `).join('');

  list.querySelectorAll('.btn-remove-printer-row').forEach(btn => {
    btn.addEventListener('click', () => {
      const rows = getAddPrinterValues();
      const row = btn.closest('.add-printer-row');
      const inputs = [...list.querySelectorAll('.add-printer-input')];
      const idx = inputs.indexOf(row.querySelector('.add-printer-input'));
      const next = rows.filter((_, i) => i !== idx);
      renderAddPrinterRows(next.length ? next : ['']);
    });
  });
}

function getAddPrinterValues() {
  return [...document.querySelectorAll('#add-toner-printer-list .add-printer-input')]
    .map(el => (el.value || '').trim())
    .filter(Boolean);
}

function openAddTonerModal() {
  ['add-toner-code','add-toner-description'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.value = '';
  });
  const qty = document.getElementById('add-toner-qty');
  const re = document.getElementById('add-toner-reorder');
  if (qty) qty.value = '0';
  if (re) re.value = '3';
  renderAddPrinterRows(['']);
  const modal = document.getElementById('modal-add-toner');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => document.getElementById('add-toner-code')?.focus(), 80);
}

function closeAddTonerModal() {
  const modal = document.getElementById('modal-add-toner');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

function saveNewToner() {
  const code = (document.getElementById('add-toner-code')?.value || '').trim().toUpperCase();
  const description = (document.getElementById('add-toner-description')?.value || '').trim();
  const brand = '';
  const supplier = '';
  const qtyRaw = document.getElementById('add-toner-qty')?.value;
  const reorder = 3;

  if (!code) {
    showToast('Item code is required (same as MRR Item_code).', 'warning');
    document.getElementById('add-toner-code')?.focus();
    return;
  }
  if (!description) {
    showToast('Description is required (same as MRR Item_Desc).', 'warning');
    document.getElementById('add-toner-description')?.focus();
    return;
  }
  if (qtyRaw === '' || qtyRaw === null || qtyRaw === undefined) {
    showToast('Quantity is required (same as MRR_Qty).', 'warning');
    document.getElementById('add-toner-qty')?.focus();
    return;
  }

  const exists = AppState.inks.some(i => (i.inkCode || '').toUpperCase() === code);
  if (exists) {
    showToast(`Item ${code} already exists in inventory.`, 'warning');
    return;
  }

  const qty = Math.max(0, parseInt(qtyRaw, 10) || 0);
  const now = new Date().toISOString();
  const printerModelStored = '';

  const item = {
    id: generateInkId(),
    inkCode: code,
    brand: brand,
    description: description,
    printerModel: printerModelStored,
    color: 'Black',
    department: '',
    serialNumbers: [],
    quantity: qty,
    reorderLevel: reorder,
    supplier,
    location: '',
    createdAt: now,
    updatedAt: now
  };

  (async () => {
    try {
      // Always try MySQL API first (even if health check failed earlier)
      try {
        await apiAddToner({
          inkCode: code,
          itemCode: code,
          description,
          printerModel: '',
          supplier: '',
          quantity: qty,
          reorderLevel: 3
        });
        AppState.useBackend = true;
        await loadFromBackend();
        renderInventory();
        renderDashboard();
        renderAlerts();
        closeAddTonerModal();
        showToast(`Toner ${code} saved to database.`, 'success');
        pushNotification('success', 'Toner Added', `${code} was saved to MySQL inventory.`, { source: 'action' });
        return;
      } catch (apiErr) {
        console.error('[Toner] API add failed:', apiErr);
        // If explicitly offline mode preference after API failure, fall back with warning
        showToast((apiErr && apiErr.message) ? apiErr.message : 'Database save failed.', 'error');
        // Do NOT silently write only to localStorage — user expects DB
        return;
      }
    } catch (e) {
      showToast(e.message || 'Failed to add toner.', 'error');
    }
  })();
}

function openRemoveTonerModal(inkCode) {
  AppState.pendingRemoveTonerCode = inkCode;
  const label = document.getElementById('remove-toner-code-label');
  if (label) label.textContent = inkCode;
  const modal = document.getElementById('modal-remove-toner');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

function closeRemoveTonerModal() {
  const modal = document.getElementById('modal-remove-toner');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.pendingRemoveTonerCode = null;
}

async function confirmRemoveToner() {
  const ok = await appConfirm({ title: 'Remove toner', message: 'Remove this toner from inventory? This cannot be undone easily.', confirmText: 'Remove', danger: true });
  if (!ok) return;
  const code = AppState.pendingRemoveTonerCode;
  if (!code) return;
  const key = code.toUpperCase();
  const inks = AppState.inks.filter(i => (i.inkCode || '').toUpperCase() !== key);
  if (inks.length === AppState.inks.length) {
    showToast('Toner not found.', 'warning');
    closeRemoveTonerModal();
    return;
  }
  (async () => {
    try {
      if (AppState.useBackend) {
        await apiRemoveToner(code);
        await loadFromBackend();
      } else {
        StorageService.saveInks(inks);
        AppState.inks = inks;
      }
      renderInventory();
      renderDashboard();
      renderAlerts();
      closeRemoveTonerModal();
      showToast(`Toner ${code} removed from inventory.`, 'success');
      pushNotification('warning', 'Toner Removed', `${code} was removed from the master inventory.`, { source: 'action' });
    } catch (e) {
      showToast(e.message || 'Failed to remove toner.', 'error');
    }
  })();
}




function getLocationPrinterOptions() {
  const names = [];
  const seen = new Set();
  (AppState.releaseLocations || []).forEach(r => {
    const p = (r.printerName || '').trim();
    if (!p) return;
    const key = p.toUpperCase();
    if (seen.has(key)) return;
    seen.add(key);
    names.push(p);
  });
  return names.sort((a, b) => a.localeCompare(b));
}

function populateStockCardPrinterSelect(selectedList) {
  const box = document.getElementById('stock-card-printers-checkboxes');
  if (!box) return;
  const options = getLocationPrinterOptions();
  const selected = new Set((selectedList || []).map(s => String(s).trim().toUpperCase()).filter(Boolean));
  // Keep printers already on the item even if not in locations list
  (selectedList || []).forEach(p => {
    const name = String(p || '').trim();
    if (!name) return;
    if (!options.some(o => o.toUpperCase() === name.toUpperCase())) options.push(name);
  });
  options.sort((a, b) => a.localeCompare(b));

  if (!options.length) {
    box.innerHTML = '<p class="text-xs text-amber-700 bg-amber-50 border border-amber-100 rounded-lg px-2.5 py-2">No printers saved yet. Add printers under <strong>Locations &amp; Printers</strong>.</p>';
    return;
  }

  box.innerHTML = options.map((p, i) => {
    const id = 'sc-printer-cb-' + i;
    const checked = selected.has(p.toUpperCase()) ? ' checked' : '';
    return `<label for="${id}" class="flex items-center gap-2.5 cursor-pointer rounded-lg px-2 py-1.5 hover:bg-slate-50">
      <input type="checkbox" id="${id}" class="stock-card-printer-cb w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="${escapeHTML(p)}"${checked}>
      <span class="text-sm text-slate-800">${escapeHTML(p)}</span>
    </label>`;
  }).join('');
}

function getSelectedStockCardPrinters() {
  return [...document.querySelectorAll('.stock-card-printer-cb:checked')].map(cb => cb.value).filter(Boolean);
}

async function showStockCardEditPanel() {
  await loadReleaseLocations().catch(() => {});
  await loadSuppliers().catch(() => {});
  const code = (document.getElementById('stock-card-ink-code')?.value || '').trim();
  const item = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
  let printers = [];
  if (item) {
    if (Array.isArray(item.printerList) && item.printerList.length) printers = item.printerList;
    else if (item.printerModel) {
      printers = String(item.printerModel).split(/\s*·\s*/).map(s => s.trim()).filter(Boolean);
    }
  }
  populateStockCardPrinterSelect(printers);
  populateStockCardSupplierSelect(item ? (item.supplier || '') : '');
  const panel = document.getElementById('stock-card-edit-panel');
  if (panel) {
    panel.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
  }
  setTimeout(() => document.getElementById('stock-card-qty')?.focus(), 50);
}

function hideStockCardEditPanel() {
  const panel = document.getElementById('stock-card-edit-panel');
  if (panel) panel.classList.add('hidden');
  document.body.classList.remove('overflow-hidden');
}

async function saveStockCard() {
  const ok = await appConfirm({ title: 'Save stock card', message: 'Save changes to quantity, printers, and supplier?', confirmText: 'Save' });
  if (!ok) return;
  const code = (document.getElementById('stock-card-ink-code')?.value || document.getElementById('stock-card-code')?.textContent || '').trim();
  if (!code) return;
  const qty = Math.max(0, parseInt(document.getElementById('stock-card-qty')?.value, 10) || 0);
  const reorder = Math.max(0, parseInt(document.getElementById('stock-card-reorder')?.value, 10) || 0);
  // Description is read-only — keep existing master value
  const description = (document.getElementById('stock-card-description-input')?.value || '').trim();
  const printerList = getSelectedStockCardPrinters();
  const printerModel = printerList.join(' · ');
  const supplier = (document.getElementById('stock-card-supplier-input')?.value || '').trim();

  try {
    if (AppState.useBackend) {
      await apiUpdateToner({
        inkCode: code,
        itemCode: code,
        description,
        quantity: qty,
        reorderLevel: reorder,
        printerModel,
        supplier
      });
      await loadFromBackend();
    } else {
      const item = AppState.inks.find(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
      if (item) {
        item.quantity = qty;
        item.reorderLevel = reorder;
        item.printerModel = printerModel;
        item.supplier = supplier;
        item.description = description;
        item.updatedAt = new Date().toISOString();
        if (typeof StorageService !== 'undefined') StorageService.saveInks(AppState.inks);
      }
    }
    hideStockCardEditPanel();
    showToast(`Stock card for ${code} saved.`, 'success');
    renderInventory();
    renderDashboard();
    renderAlerts();
    openStockCard(code);
  } catch (e) {
    showToast(e.message || 'Failed to save stock card.', 'error');
  }
}

function closeStockCard() {
  const modal = document.getElementById('modal-stock-card');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}


function phTodayYmd() {
  try {
    return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Manila', year: 'numeric', month: '2-digit', day: '2-digit' }).format(new Date());
  } catch (_) {
    const d = new Date();
    return d.toISOString().slice(0, 10);
  }
}

function getStockCardDateBounds() {
  const period = document.getElementById('stock-card-period')?.value || 'ALL';
  const customFrom = document.getElementById('stock-card-from')?.value || '';
  const customTo = document.getElementById('stock-card-to')?.value || '';
  const today = phTodayYmd();
  let from = null;
  let to = null;
  if (period === 'TODAY') {
    from = today;
    to = today;
  } else if (period === 'WEEK') {
    const d = toManilaDate(today + 'T12:00:00+08:00') || new Date();
    d.setDate(d.getDate() - 6);
    from = d.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' });
    to = today;
  } else if (period === 'MONTH') {
    from = today.slice(0, 8) + '01';
    to = today;
  } else if (period === 'CUSTOM') {
    from = customFrom || null;
    to = customTo || null;
  }
  return { period, from, to };
}

function txnDateYmd(t) {
  const raw = (t.date || t.createdAt || '').toString();
  if (!raw) return '';
  const s = raw.replace(' ', 'T').split('T')[0];
  return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
}

function syncStockCardCustomRangeUI() {
  const period = document.getElementById('stock-card-period')?.value || 'ALL';
  const wrap = document.getElementById('stock-card-custom-range');
  if (wrap) {
    if (period === 'CUSTOM') wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
}

function renderStockCardMovements() {
  const code = (AppState._stockCardCode || document.getElementById('stock-card-ink-code')?.value || '').toUpperCase();
  const onHand = Number(AppState._stockCardOnHand != null ? AppState._stockCardOnHand : (document.getElementById('stock-card-onhand')?.textContent || 0)) || 0;
  if (!code) return;

  syncStockCardCustomRangeUI();
  const { from, to } = getStockCardDateBounds();

  const allTxns = (AppState.transactions || [])
    .filter(t => (t.inkCode || '').toUpperCase() === code)
    .slice()
    .sort((a, b) => {
      const da = new Date(a.createdAt || a.date || 0).getTime();
      const db = new Date(b.createdAt || b.date || 0).getTime();
      return da - db;
    });

  let netFromTxns = 0;
  allTxns.forEach(t => {
    if (t.type === 'RECEIVED') netFromTxns += Number(t.quantity) || 0;
    else if (t.type === 'RELEASED') netFromTxns -= Number(t.quantity) || 0;
  });
  let balance = onHand - netFromTxns;

  const tbody = document.getElementById('stock-card-tbody');
  const empty = document.getElementById('stock-card-empty');
  const rows = [];

  rows.push(`
    <tr class="bg-slate-50/80">
      <td class="px-3 py-2.5 text-xs text-slate-500">—</td>
      <td class="px-3 py-2.5 font-semibold text-slate-800">Beginning Balance</td>
      <td class="px-3 py-2.5 font-mono text-xs text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono text-slate-400">—</td>
      <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900">${balance}</td>
      <td class="px-3 py-2.5 text-xs text-slate-400">—</td>
    </tr>
  `);

  let shown = 0;
  allTxns.forEach(t => {
    const ymd = txnDateYmd(t);
    const inRange = (!from || (ymd && ymd >= from)) && (!to || (ymd && ymd <= to));

    const dateLabel = formatDate(t.date || (t.createdAt || '').toString().split('T')[0]);
    let label = t.type;
    let stockIn = '—';
    let stockOut = '—';
    let note = '';
    const qty = Number(t.quantity) || 0;

    if (t.type === 'RECEIVED') {
      label = 'Delivery Received';
      stockIn = String(qty);
      balance += qty;
      note = t.supplier || '';
    } else if (t.type === 'RELEASED') {
      label = t.defective ? 'Issued (later defective)' : 'Issued';
      stockOut = String(qty);
      balance -= qty;
      note = [t.department, t.location].filter(Boolean).join(' · ');
    } else if (t.type === 'DEFECTIVE') {
      label = 'Defective Return';
      note = t.purpose || 'Flagged defective';
    }

    if (!inRange) return;
    shown++;
    rows.push(`
      <tr class="hover:bg-slate-50">
        <td class="px-3 py-2.5 text-xs text-slate-600 whitespace-nowrap">${escapeHTML(dateLabel)}</td>
        <td class="px-3 py-2.5 font-medium text-slate-800">${escapeHTML(label)}</td>
        <td class="px-3 py-2.5 font-mono text-xs font-semibold text-slate-900">${escapeHTML(t.referenceNumber || '—')}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-blue-600">${stockIn === '—' ? '—' : '+' + stockIn}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-emerald-600">${stockOut === '—' ? '—' : '-' + stockOut}</td>
        <td class="px-3 py-2.5 text-right font-mono font-bold text-slate-900">${balance}</td>
        <td class="px-3 py-2.5 text-xs text-slate-500 truncate" title="${escapeHTML(note)}">${escapeHTML(note || '—')}</td>
      </tr>
    `);
  });

  if (tbody) tbody.innerHTML = rows.join('');
  if (empty) {
    if (shown === 0 && allTxns.length === 0) empty.classList.remove('hidden');
    else if (shown === 0) {
      empty.textContent = 'No movements in the selected date range.';
      empty.classList.remove('hidden');
    } else {
      empty.classList.add('hidden');
      empty.textContent = 'No movement history for this toner yet.';
    }
  }
}

function openStockCard(inkCode) {
  const code = (inkCode || '').trim();
  if (!code) return;
  hideStockCardEditPanel();

  const items = AppState.inks.filter(i => (i.inkCode || '').toUpperCase() === code.toUpperCase());
  const onHand = items.reduce((s, i) => s + (Number(i.quantity) || 0), 0);
  const sample = items[0] || {};
  const printer = [...new Set(items.flatMap(i => String(i.printerModel || '').split(/\s*·\s*/).map(s => s.trim()).filter(Boolean)))].join(' · ') || sample.printerModel || '—';
  const supplier = [...new Set(items.map(i => i.supplier).filter(Boolean))].join(', ') || sample.supplier || '—';

    document.getElementById('stock-card-code').textContent = code;
  document.getElementById('stock-card-meta').textContent = items.length > 1
    ? `${items.length} inventory lines · full movement history`
    : 'Complete stock movement history — editable master data';
  const codeEl = document.getElementById('stock-card-ink-code');
  if (codeEl) codeEl.value = code;
  const editCodeDisp = document.getElementById('stock-card-edit-code-display');
  if (editCodeDisp) editCodeDisp.textContent = code;
  const descInput = document.getElementById('stock-card-description-input');
  if (descInput) descInput.value = sample.description || '';
  const qtyEl = document.getElementById('stock-card-qty');
  const reorderEl = document.getElementById('stock-card-reorder');
  const printersInput = document.getElementById('stock-card-printers-input');
  const supplierInput = document.getElementById('stock-card-supplier-input');
  const onHandEl = document.getElementById('stock-card-onhand');
  if (onHandEl) onHandEl.textContent = onHand;
  if (qtyEl) qtyEl.value = onHand;
  if (reorderEl) reorderEl.value = Number(sample.reorderLevel) || 0;
  // Printers: show one per line for editing (split only on ·)
  const printerNames = (printer && printer !== '—')
    ? printer.split(/\s*·\s*/).map(s => s.trim()).filter(Boolean)
    : [];
  if (printersInput) printersInput.value = printerNames.join('\n');
  if (supplierInput) supplierInput.value = (supplier && supplier !== '—') ? supplier.split(',')[0].trim() : '';
  const printerEl = document.getElementById('stock-card-printer');
  const supplierEl = document.getElementById('stock-card-supplier');
  if (printerEl) {
    printerEl.innerHTML = printerNames.length
      ? printerNames.map(p => `<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-indigo-50 text-indigo-800 border border-indigo-100">${escapeHTML(p)}</span>`).join('')
      : '<span class="text-xs text-slate-400 italic">No printer listed</span>';
  }
  if (supplierEl) {
    const sup = (supplier && supplier !== '—') ? supplier : '—';
    supplierEl.innerHTML = sup === '—'
      ? '<span class="text-xs text-slate-400">—</span>'
      : `<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-white text-slate-700 border border-slate-200">${escapeHTML(sup)}</span>`;
  }


  AppState._stockCardCode = code;
  AppState._stockCardOnHand = onHand;
  renderStockCardMovements();

  const backdrop = document.getElementById('modal-backdrop');
  const modal = document.getElementById('modal-stock-card');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

function getColorSwatch(color) {
  const c = (color || '').toLowerCase();
  if (c.includes('black')) return 'bg-slate-900 border border-slate-700';
  if (c.includes('cyan')) return 'bg-cyan-400';
  if (c.includes('magenta')) return 'bg-fuchsia-500';
  if (c.includes('yellow')) return 'bg-amber-300';
  return 'bg-gradient-to-r from-cyan-400 via-fuchsia-500 to-amber-300';
}

// ==========================================
// 11. RECEIVE DELIVERY (TicketService Inbound)
// ==========================================
const TicketService = {
  getTickets() {
    return StorageService.getTickets();
  },

  saveTickets(tickets) {
    StorageService.saveTickets(tickets);
  },

  findByReferenceNumber(refNumber) {
    // FUTURE BACKEND INTEGRATION:
    // Replace LocalStorage ticket lookup with a Fetch API request
    // to the PHP backend: GET /api/tickets.php?ref=${encodeURIComponent(refNumber)}
    const normalized = normalizeRefNumber(refNumber);
    const tickets = (AppState.tickets && AppState.tickets.length)
      ? AppState.tickets
      : (StorageService.getTickets() || []);
    let found = tickets.find(t => normalizeRefNumber(t.referenceNumber) === normalized);
    // Fallback to hardcoded demo tickets so samples always work
    if (!found) {
      found = DEMO_APPROVED_TICKETS.find(t => normalizeRefNumber(t.referenceNumber) === normalized) || null;
    }
    return found || null;
  },

  isAlreadyProcessed(refNumber) {
    const normalized = normalizeRefNumber(refNumber);
    const txns = StorageService.getTransactions();
    return txns.some(t => normalizeRefNumber(t.referenceNumber) === normalized);
  },

  processDeliveryTicket(ticket) {
    if (!ticket || !ticket.referenceNumber) {
      showToast('Missing delivery reference.', 'error');
      return false;
    }

    const ref = normalizeRefNumber(ticket.referenceNumber);

    // Step 2: Check for duplicate execution
    if (this.isAlreadyProcessed(ref)) {
      openDuplicateModal(ref, 'DELIVERY');
      return false;
    }

    // Step 3: Loop through every ticket item and increase inventory stock
    const inks = [...AppState.inks];
    const newTxns = [];
    const executionTime = new Date().toISOString();

    ticket.items.forEach(item => {
      let matchedInk = inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());

      // If SKU doesn't exist, create it from ticket metadata
      if (!matchedInk) {
        matchedInk = {
          id: generateInkId(),
          inkCode: item.inkCode,
          brand: item.brand || 'Generic',
          printerModel: item.printerModel || 'Compatible Model',
          color: item.color || 'Black',
          serialNumbers: [],
          quantity: 0,
          reorderLevel: 5,
          supplier: ticket.supplier || 'Standard Supplier',
          location: 'Main Storage',
          createdAt: executionTime,
          updatedAt: executionTime
        };
        inks.push(matchedInk);
      }

      // Quantity calculation: New Stock = Current + Ticket Qty
      const prevQty = Number(matchedInk.quantity) || 0;
      const addQty = Number(item.quantity) || 0;
      matchedInk.quantity = prevQty + addQty;
      matchedInk.updatedAt = executionTime;

      // Track serial numbers
      if (item.serialNumber) {
        matchedInk.serialNumbers = matchedInk.serialNumbers || [];
        if (!matchedInk.serialNumbers.includes(item.serialNumber)) {
          matchedInk.serialNumbers.push(item.serialNumber);
        }
      }

      // Create one transaction record per item while maintaining the same Reference Number
      const txnRecord = {
        id: generateTransactionId() + '-' + Math.random().toString(36).substring(2, 5).toUpperCase(),
        type: 'RECEIVED',
        referenceNumber: ticket.referenceNumber,
        inkId: matchedInk.id,
        inkCode: matchedInk.inkCode,
        serialNumber: item.serialNumber || (matchedInk.serialNumbers[0] || 'N/A'),
        brand: matchedInk.brand,
        color: matchedInk.color,
        quantity: addQty,
        date: ticket.date || executionTime.split('T')[0],
        supplier: ticket.supplier || 'N/A',
        givenTo: '',
        department: 'Main Storage',
        purpose: 'Stock delivery',
        status: 'APPROVED',
        createdAt: executionTime
      };

      newTxns.push(txnRecord);
    });

    // Step 4: Persist inventory and transactions
    StorageService.saveInks(inks);
    AppState.inks = inks;

    const allTxns = [...AppState.transactions, ...newTxns];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    // Step 5: Refresh UI and show feedback
    renderDashboard();
    renderInventory();
    renderTransactions();
    renderReports();
    renderCharts();
    renderAlerts();

    showToast(`Delivery ${ticket.referenceNumber} processed successfully! Stock incremented.`, 'success');
    pushNotification('success', 'Delivery Received', `Ticket ${ticket.referenceNumber} processed. Stock incremented.`, { source: 'action' });
    return true;
  },

  processReleaseTicket(ticket) {
    if (!ticket || !ticket.referenceNumber) {
      showToast('Missing issuance reference.', 'error');
      return false;
    }

    const ref = normalizeRefNumber(ticket.referenceNumber);

    // Step 2: Check for duplicate execution
    if (this.isAlreadyProcessed(ref)) {
      openDuplicateModal(ref, 'RELEASE');
      return false;
    }

    // Step 3: ATOMIC PRE-VALIDATION: Check available stock for ALL items first!
    const inks = [...AppState.inks];
    let validationFailed = false;
    let failureDetail = '';

    // Business rule: 1 release ticket = 1 toner (use first item / primary toner on ticket)
    const items = ticket.items && ticket.items.length ? ticket.items : [{ inkCode: 'UNKNOWN', quantity: 1 }];
    // Only validate the primary toner line at qty 1
    const primary = items[0];
    {
      const matched = inks.find(i => i.inkCode.toUpperCase() === (primary.inkCode || '').toUpperCase());
      const available = matched ? (Number(matched.quantity) || 0) : 0;
      const requested = 1;

      if (!matched || available < requested) {
        validationFailed = true;
        failureDetail = `${primary.inkCode}: Need 1 unit, but only ${available} available in stock.`;
      }
    }

    if (validationFailed) {
      showToast(`Unable to process release: Insufficient stock. (${failureDetail})`, 'error');
      return false;
    }

    // Step 4: Atomic Execution — 1 ticket = 1 toner
    const newTxns = [];
    const executionTime = new Date().toISOString();
    const releaseItems = (ticket.items && ticket.items.length) ? [ticket.items[0]] : [];

    releaseItems.forEach(item => {
      const matched = inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
      // Business rule: 1 ticket = 1 toner unit
      const reqQty = 1;

      // Stock Decrement: New Stock = Current Stock - 1
      matched.quantity = (Number(matched.quantity) || 0) - reqQty;
      matched.updatedAt = executionTime;

      // Release serial number from active pool
      if (item.serialNumber && Array.isArray(matched.serialNumbers)) {
        matched.serialNumbers = matched.serialNumbers.filter(sn => sn !== item.serialNumber);
      }

      // Record transaction
      const txnRecord = {
        id: generateTransactionId() + '-' + Math.random().toString(36).substring(2, 5).toUpperCase(),
        type: 'RELEASED',
        referenceNumber: ticket.referenceNumber,
        inkId: matched.id,
        inkCode: matched.inkCode,
        serialNumber: item.serialNumber || 'N/A',
        brand: matched.brand,
        color: matched.color,
        quantity: reqQty,
        date: ticket.date || executionTime.split('T')[0],
        supplier: '',
        givenTo: ticket.givenTo || 'Authorized Staff',
        department: (AppState.selectedReleaseDepartment || ticket.department || 'General Department'),
        location: (AppState.selectedReleaseLocation || ''),
        purpose: ticket.purpose || 'Toner Release',
        status: 'APPROVED',
        createdAt: executionTime
      };

      newTxns.push(txnRecord);
    });

    // Step 5: Save updated state
    StorageService.saveInks(inks);
    AppState.inks = inks;

    const allTxns = [...AppState.transactions, ...newTxns];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    // Step 6: Refresh UI
    renderDashboard();
    renderInventory();
    renderTransactions();
    renderReports();
    renderCharts();
    renderAlerts();

    showToast(`Toner release ${ticket.referenceNumber} processed successfully! Stock decremented.`, 'success');
    pushNotification('success', 'Toner Released', `Ticket ${ticket.referenceNumber} processed. Toner stock decremented.`, { source: 'action' });
    return true;
  },

  /**
   * Flag a completed issuance ticket as defective return.
   * Does NOT restock usable inventory.
   */
  processDefectiveReturn(issuanceRef, notes) {
    const ref = normalizeRefNumber(issuanceRef);
    if (!ref) {
      showToast('Enter an issuance ticket number.', 'warning');
      return false;
    }

    // Must already be a processed RELEASE
    const released = AppState.transactions.filter(
      t => t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === ref
    );
    if (released.length === 0) {
      showToast('No completed issuance found for this ticket.', 'error');
      return false;
    }

    // Already flagged?
    const already = AppState.transactions.some(
      t => t.type === 'DEFECTIVE' && normalizeRefNumber(t.referenceNumber) === ref
    );
    if (already) {
      showToast('This issuance was already flagged as defective.', 'warning');
      return false;
    }

    const primary = released[0];
    const executionTime = new Date().toISOString();

    // Mark original release rows
    const updated = AppState.transactions.map(t => {
      if (t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === ref) {
        return { ...t, defective: true, defectiveAt: executionTime, defectiveNotes: notes || '' };
      }
      return t;
    });

    const defTxn = {
      id: (typeof generateTransactionId === 'function' ? generateTransactionId() : ('TXN-' + Date.now())) + '-DEF',
      type: 'DEFECTIVE',
      referenceNumber: primary.referenceNumber,
      inkId: primary.inkId || '',
      inkCode: primary.inkCode,
      serialNumber: primary.serialNumber || 'N/A',
      brand: primary.brand || '',
      color: primary.color || '',
      quantity: 1,
      date: executionTime.split('T')[0],
      supplier: '',
      givenTo: primary.givenTo || '',
      department: primary.department || '',
      location: primary.location || '',
      purpose: notes || 'Defective return',
      status: 'DEFECTIVE',
      defective: true,
      createdAt: executionTime
    };

    const allTxns = [...updated, defTxn];
    StorageService.saveTransactions(allTxns);
    AppState.transactions = allTxns;

    renderDashboard();
    renderInventory();
    renderTransactions();
    renderCharts();
    renderAlerts();

    showToast(`Issuance ${ref} flagged as defective.`, 'success');
    pushNotification('warning', 'Defective Return', `Ticket ${ref} (${primary.inkCode}) marked defective.`, { source: 'action' });
    return true;
  }
};


// ==========================================
// DEFECTIVE RETURN MODAL
// ==========================================
function resetDefectiveModal() {
  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.add('hidden');
  document.getElementById('defective-step-preview')?.classList.add('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
  AppState.activeDefectiveTxn = null;
  const notes = document.getElementById('modal-def-notes');
  if (notes) notes.value = '';
}

function openDefectiveModal() {
  resetDefectiveModal();
  const modal = document.getElementById('modal-defective');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  const input = document.getElementById('modal-def-ref');
  if (input) { input.value = ''; setTimeout(() => input.focus(), 80); }
}

function closeDefectiveModal() {
  const modal = document.getElementById('modal-defective');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeDefectiveTxn = null;
}

function showDefectiveError(ref, title, desc) {
  const er = document.getElementById('defective-error-ref');
  const et = document.getElementById('defective-error-title');
  const ed = document.getElementById('defective-error-desc');
  if (er) er.textContent = ref || '—';
  if (et) et.textContent = title || 'Not Found';
  if (ed) ed.textContent = desc || '';
  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.remove('hidden');
  document.getElementById('defective-step-preview')?.classList.add('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
}

function searchDefectiveIssuance(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter an issuance ticket number.', 'warning');
    return;
  }

  const released = AppState.transactions.filter(
    t => t.type === 'RELEASED' && normalizeRefNumber(t.referenceNumber) === normalized
  );

  if (released.length === 0) {
    showDefectiveError(
      normalized,
      'Issuance Not Found',
      'No completed stock issuance matches this ticket. Process the release first, then flag it.'
    );
    return;
  }

  const already = AppState.transactions.some(
    t => t.type === 'DEFECTIVE' && normalizeRefNumber(t.referenceNumber) === normalized
  );
  if (already) {
    showDefectiveError(
      normalized,
      'Already Flagged',
      'This issuance ticket was already marked as defective.'
    );
    return;
  }

  const primary = released[0];
  AppState.activeDefectiveTxn = primary;

  document.getElementById('m-def-ref').textContent = primary.referenceNumber;
  document.getElementById('m-def-code').textContent = primary.inkCode || '—';
  document.getElementById('m-def-dept').textContent = primary.department || '—';
  document.getElementById('m-def-loc').textContent = primary.location || '—';
  document.getElementById('m-def-qty').textContent = '1';
  document.getElementById('m-def-date').textContent = formatDateTime(primary.createdAt || primary.date);

  document.getElementById('defective-step-search')?.classList.remove('hidden');
  document.getElementById('defective-step-error')?.classList.add('hidden');
  document.getElementById('defective-step-preview')?.classList.remove('hidden');
  document.getElementById('defective-step-success')?.classList.add('hidden');
}

// ==========================================
// 12. RELEASE INK UI SEARCH & DISPLAY
// ==========================================
function searchAndDisplayDelivery(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a delivery reference number.', 'warning');
    return;
  }

  // Check if already processed
  if (TicketService.isAlreadyProcessed(normalized)) {
    openDuplicateModal(normalized, 'DELIVERY');
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'DELIVERY') {
    openNotFoundModal(normalized);
    return;
  }

  AppState.activeDeliveryTicket = ticket;

  // Render preview
  const previewBox = document.getElementById('del-ticket-preview');
  document.getElementById('del-preview-ref').textContent = ticket.referenceNumber;
  document.getElementById('del-preview-date').textContent = formatDate(ticket.date);
  document.getElementById('del-preview-supplier').textContent = ticket.supplier || 'N/A';
  document.getElementById('del-preview-status').textContent = ticket.status || 'APPROVED';

  const tbody = document.getElementById('del-preview-tbody');
  let totalQty = 0;

  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const currentQty = matched ? (Number(matched.quantity) || 0) : 0;
    const incomingQty = Number(item.quantity) || 0;
    const newQty = currentQty + incomingQty;
    totalQty += incomingQty;

    return `
      <tr class="hover:bg-slate-50">
        <td class="px-4 py-3 font-bold font-mono text-slate-900">${escapeHTML(item.inkCode)}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.brand || (matched ? matched.brand : 'HP'))}</td>
        <td class="px-4 py-3 text-slate-500 text-xs">${escapeHTML(item.printerModel || (matched ? matched.printerModel : 'Standard'))}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.color || (matched ? matched.color : 'Black'))}</td>
        <td class="px-4 py-3 font-mono text-xs text-slate-600">${escapeHTML(item.serialNumber || 'SN-AUTO')}</td>
        <td class="px-4 py-3 text-right font-mono text-slate-500">${currentQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-blue-600 text-base">+${incomingQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-700">${newQty}</td>
      </tr>
    `;
  }).join('');

  document.getElementById('del-preview-total-items').textContent = ticket.items.length;
  document.getElementById('del-preview-total-qty').textContent = totalQty;

  previewBox.classList.remove('hidden');
  previewBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function searchAndDisplayRelease(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a release reference number.', 'warning');
    return;
  }

  // Check if already processed
  if (TicketService.isAlreadyProcessed(normalized)) {
    openDuplicateModal(normalized, 'RELEASE');
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'RELEASE') {
    openNotFoundModal(normalized);
    return;
  }

  AppState.activeReleaseTicket = ticket;

  // Render preview details
  const previewBox = document.getElementById('rel-ticket-preview');
  document.getElementById('rel-preview-ref').textContent = ticket.referenceNumber;
  document.getElementById('rel-preview-date').textContent = formatDate(ticket.date);
  document.getElementById('rel-preview-dept').textContent = ticket.department || 'General';
  document.getElementById('rel-preview-givento').textContent = ticket.givenTo || 'Staff';
  document.getElementById('rel-preview-purpose').textContent = ticket.purpose || 'Toner replacement';

  const tbody = document.getElementById('rel-preview-tbody');
  const errorBox = document.getElementById('rel-stock-error-box');
  const errorMsg = document.getElementById('rel-stock-error-msg');
  const processBtn = document.getElementById('btn-process-release');
  const validationBadge = document.getElementById('rel-validation-status-badge');

  let totalQty = 0;
  let hasInsufficientStock = false;
  let insufficientSummary = [];

  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const availableQty = matched ? (Number(matched.quantity) || 0) : 0;
    const reqQty = Number(item.quantity) || 0;
    const postStock = availableQty - reqQty;
    totalQty += reqQty;

    const isShort = postStock < 0;
    if (isShort) {
      hasInsufficientStock = true;
      insufficientSummary.push(`${item.inkCode}: Needs ${reqQty}, only ${availableQty} in stock`);
    }

    const checkIcon = isShort
      ? `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-rose-100 text-rose-800">Deficit (-${Math.abs(postStock)})</span>`
      : `<span class="px-2 py-0.5 text-xs font-bold rounded-md bg-emerald-100 text-emerald-800">Available</span>`;

    return `
      <tr class="hover:bg-slate-50 ${isShort ? 'bg-rose-50/50' : ''}">
        <td class="px-4 py-3 font-bold font-mono text-slate-900">${escapeHTML(item.inkCode)}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.brand || (matched ? matched.brand : 'HP'))}</td>
        <td class="px-4 py-3 text-slate-700">${escapeHTML(item.color || (matched ? matched.color : 'Black'))}</td>
        <td class="px-4 py-3 text-right font-mono font-semibold ${availableQty === 0 ? 'text-rose-600' : 'text-slate-700'}">${availableQty}</td>
        <td class="px-4 py-3 text-right font-bold font-mono text-emerald-600 text-base">-${reqQty}</td>
        <td class="px-4 py-3 text-right font-mono font-bold ${isShort ? 'text-rose-600' : 'text-slate-900'}">${postStock}</td>
        <td class="px-4 py-3 text-center">${checkIcon}</td>
      </tr>
    `;
  }).join('');

  document.getElementById('rel-preview-total-items').textContent = ticket.items.length;
  document.getElementById('rel-preview-total-qty').textContent = totalQty;

  if (hasInsufficientStock) {
    errorBox.classList.remove('hidden');
    errorMsg.textContent = insufficientSummary.join(' | ');
    processBtn.disabled = true;
    processBtn.classList.add('opacity-50', 'cursor-not-allowed');
    validationBadge.textContent = 'Validation Failed: Insufficient Stock';
    validationBadge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-rose-200 text-rose-800';
  } else {
    errorBox.classList.add('hidden');
    processBtn.disabled = false;
    processBtn.classList.remove('opacity-50', 'cursor-not-allowed');
    validationBadge.textContent = 'Stock Verified & Sufficient';
    validationBadge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-200 text-emerald-800';
  }

  previewBox.classList.remove('hidden');
  previewBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// ==========================================
// 13. TRANSACTIONS
// ==========================================
function updateTxnTabUI() {
  const type = AppState.filters.transactionType;
  document.querySelectorAll('.txn-main-tab').forEach(btn => {
    const active = btn.getAttribute('data-txn-tab') === type;
    if (active) {
      if (type === 'RECEIVED') {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-blue-50 border-b-2 border-blue-600 transition-colors';
      } else if (type === 'DEFECTIVE') {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-rose-700 bg-rose-50 border-b-2 border-rose-600 transition-colors';
      } else {
        btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-emerald-700 bg-emerald-50 border-b-2 border-emerald-600 transition-colors';
      }
    } else {
      btn.className = 'txn-main-tab flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors';
    }
  });

  const deptTabs = document.getElementById('txn-dept-tabs');
  if (deptTabs) {
    if (type === 'RELEASED') {
      deptTabs.classList.remove('hidden');
      renderTxnDeptTabs();
    } else {
      deptTabs.classList.add('hidden');
    }
  }

  const receivedCount = AppState.transactions.filter(t => t.type === 'RECEIVED').length;
  const releasedCount = AppState.transactions.filter(t => t.type === 'RELEASED').length;
  const defectiveCount = AppState.transactions.filter(t => t.type === 'DEFECTIVE').length;
  const elR = document.getElementById('txn-tab-count-received');
  const elL = document.getElementById('txn-tab-count-released');
  const elD = document.getElementById('txn-tab-count-defective');
  if (elR) elR.textContent = receivedCount;
  if (elL) elL.textContent = releasedCount;
  if (elD) elD.textContent = defectiveCount;
}

function resolveTonerDescription(inkCode) {
  const code = (inkCode || '').toUpperCase().trim();
  if (!code) return '';
  const inv = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code);
  if (inv && inv.description) return String(inv.description).trim();
  return '';
}

function resolveTonerSupplier(inkCode) {
  const code = (inkCode || '').toUpperCase().trim();
  if (!code) return '';
  const inv = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === code);
  if (inv && inv.supplier) return String(inv.supplier).trim();
  return '';
}



let _appConfirmResolve = null;

function appConfirm({ title = 'Confirm', message = 'Are you sure?', confirmText = 'Confirm', cancelText = 'Cancel', danger = false } = {}) {
  return new Promise((resolve) => {
    _appConfirmResolve = resolve;
    const titleEl = document.getElementById('app-confirm-title');
    const msgEl = document.getElementById('app-confirm-message');
    const okBtn = document.getElementById('btn-app-confirm-ok');
    const cancelBtn = document.getElementById('btn-app-confirm-cancel');
    if (titleEl) titleEl.textContent = title;
    if (msgEl) msgEl.textContent = message;
    if (okBtn) {
      okBtn.textContent = confirmText;
      okBtn.className = danger
        ? 'px-4 py-2.5 text-sm font-semibold rounded-xl bg-rose-600 text-white hover:bg-rose-700'
        : 'px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black';
    }
    if (cancelBtn) cancelBtn.textContent = cancelText;
    const modal = document.getElementById('modal-app-confirm');
    if (modal) {
      modal.classList.remove('hidden');
      modal.style.display = 'flex';
    }
    document.body.classList.add('overflow-hidden');
  });
}

function closeAppConfirm(result) {
  const modal = document.getElementById('modal-app-confirm');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
  if (typeof _appConfirmResolve === 'function') {
    const r = _appConfirmResolve;
    _appConfirmResolve = null;
    r(!!result);
  }
}

async function sendDefectiveToSupplier(ref) {
  if (!ref) return;
  const ok = await appConfirm({
    title: 'Send to supplier',
    message: `Mark ${ref} as sent to the supplier for replacement?`,
    confirmText: 'Send to supplier',
    cancelText: 'Cancel'
  });
  if (!ok) return;
  try {
    await apiRequest('defective.php', {
      method: 'POST',
      body: { action: 'send_to_supplier', referenceNumber: ref }
    });
    await loadFromBackend();
    renderTransactions();
    renderInventory();
    renderDashboard();
    showToast(`${ref} marked as sent to supplier.`, 'success');
  } catch (e) {
    showToast(e.message || 'Failed to update.', 'error');
  }
}

async function openReceiveReplacementModal(ref, code) {
  await loadAdminUsersForIssuance().catch(() => {});
  document.getElementById('def-replace-ref').value = ref || '';
  document.getElementById('def-replace-code').value = code || '';
  document.getElementById('def-replace-ref-label').textContent = ref || '—';
  document.getElementById('def-replace-code-label').textContent = code || '—';
  document.getElementById('def-replace-desc-label').textContent = resolveTonerDescription(code) || '—';
  const me = AppState.currentUser;
  const rec = document.getElementById('def-replace-recorded-by');
  if (rec) rec.value = me ? (me.fullName ? `${me.fullName} (${me.username})` : me.username) : '';
  const sel = document.getElementById('def-replace-accepted-by');
  if (sel) {
    const users = AppState.adminUsers || [];
    const meUser = me?.username || '';
    sel.innerHTML = '<option value="">— Select admin —</option>' +
      users.map(u => {
        const label = u.fullName ? `${u.fullName} (${u.username})` : u.username;
        return `<option value="${escapeHTML(u.username)}">${escapeHTML(label)}</option>`;
      }).join('');
    if (meUser) sel.value = meUser;
  }
  const modal = document.getElementById('modal-defective-replace');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
}

function closeReceiveReplacementModal() {
  const modal = document.getElementById('modal-defective-replace');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

async function confirmReceiveReplacement() {
  const ref = document.getElementById('def-replace-ref')?.value || '';
  const acceptedBy = document.getElementById('def-replace-accepted-by')?.value || '';
  if (!ref) return;
  if (!acceptedBy) {
    showToast('Select the admin who accepted the replacement.', 'warning');
    return;
  }
  try {
    const btn = document.getElementById('btn-confirm-def-replace');
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    const res = await apiRequest('defective.php', {
      method: 'POST',
      body: { action: 'receive_replacement', referenceNumber: ref, acceptedBy }
    });
    closeReceiveReplacementModal();
    await loadFromBackend();
    renderTransactions();
    renderInventory();
    renderDashboard();
    renderCharts();
    showToast(res.message || 'Replacement received — stock +1.', 'success');
  } catch (e) {
    showToast(e.message || 'Failed to receive replacement.', 'error');
  } finally {
    const btn = document.getElementById('btn-confirm-def-replace');
    if (btn) { btn.disabled = false; btn.textContent = 'Confirm & add to stock'; }
  }
}


function getKnownDepartments() {
  const set = new Set();
  (AppState.releaseLocations || []).forEach(r => {
    const d = (r.department || '').toUpperCase().trim();
    if (d) set.add(d);
  });
  (AppState.transactions || []).forEach(t => {
    if (t.type !== 'RELEASED' && t.type !== 'DEFECTIVE') return;
    const d = (t.department || '').toUpperCase().trim();
    if (d) set.add(d);
  });
  return [...set].sort();
}

function renderTxnDeptTabs() {
  const wrap = document.getElementById('txn-dept-tabs');
  if (!wrap) return;
  const current = AppState.filters.transactionDept || 'ALL';
  const depts = getKnownDepartments();
  let html = `<button type="button" data-txn-dept="ALL" class="txn-dept-tab px-3 py-1.5 text-xs font-semibold rounded-lg ${current === 'ALL' ? 'bg-zinc-900 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'}">All Depts</button>`;
  depts.forEach(d => {
    const active = current === d;
    html += `<button type="button" data-txn-dept="${escapeHTML(d)}" class="txn-dept-tab px-3 py-1.5 text-xs font-semibold rounded-lg ${active ? 'bg-zinc-900 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-100'}">${escapeHTML(d)}</button>`;
  });
  wrap.innerHTML = html;
  wrap.querySelectorAll('.txn-dept-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      AppState.filters.transactionDept = btn.getAttribute('data-txn-dept') || 'ALL';
      renderTxnDeptTabs();
      renderTransactions();
    });
  });
}

function openDuplicateModal(ref, type) {
  const modal = document.getElementById('modal-duplicate');
  const refEl = document.getElementById('modal-dup-ref');
  if (refEl) refEl.textContent = ref || '—';
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
}

function closeDuplicateModal() {
  const modal = document.getElementById('modal-duplicate');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

function openKpiDetail(kind) {
  const titleEl = document.getElementById('kpi-detail-title');
  const subEl = document.getElementById('kpi-detail-sub');
  const bodyEl = document.getElementById('kpi-detail-body');
  if (!bodyEl) return;
  const periodTxns = typeof getDashboardFilteredTransactions === 'function' ? getDashboardFilteredTransactions() : (AppState.transactions || []);
  const inks = AppState.inks || [];
  let title = 'Details';
  let sub = '';
  let html = '';

  if (kind === 'skus' || kind === 'stock') {
    title = kind === 'skus' ? 'Toner SKUs' : 'Stock on hand';
    sub = 'Current inventory master list';
    html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Code</th><th class="px-3 py-2">Description</th><th class="px-3 py-2 text-right">Qty</th></tr></thead><tbody class="divide-y">` +
      inks.map(i => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(i.inkCode)}</td><td class="px-3 py-2">${escapeHTML(i.description || '—')}</td><td class="px-3 py-2 text-right font-mono">${Number(i.quantity)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'low' || kind === 'out') {
    title = kind === 'low' ? 'Low stock items' : 'Out of stock';
    sub = 'Based on reorder level';
    const rows = inks.filter(i => {
      const st = getStockStatus(i.quantity, i.reorderLevel);
      return kind === 'low' ? st === STOCK_STATUS.LOW_STOCK : st === STOCK_STATUS.OUT_OF_STOCK;
    });
    if (!rows.length) html = '<p class="text-slate-500">None in this category.</p>';
    else html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Code</th><th class="px-3 py-2">Description</th><th class="px-3 py-2 text-right">Qty</th><th class="px-3 py-2 text-right">Reorder</th></tr></thead><tbody class="divide-y">` +
      rows.map(i => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(i.inkCode)}</td><td class="px-3 py-2">${escapeHTML(i.description || '—')}</td><td class="px-3 py-2 text-right font-mono">${Number(i.quantity)||0}</td><td class="px-3 py-2 text-right font-mono">${Number(i.reorderLevel)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'deliveries' || kind === 'releases' || kind === 'tickets') {
    const type = kind === 'deliveries' ? 'RECEIVED' : (kind === 'releases' ? 'RELEASED' : null);
    title = kind === 'deliveries' ? 'Deliveries in period' : (kind === 'releases' ? 'Releases in period' : 'Tickets in period');
    sub = 'Filtered by dashboard period';
    let rows = periodTxns;
    if (type) rows = rows.filter(t => t.type === type);
    if (!rows.length) html = '<p class="text-slate-500">No transactions in this period.</p>';
    else html = `<div class="overflow-x-auto rounded-xl border border-slate-200"><table class="w-full text-sm text-left"><thead class="bg-slate-50 text-xs uppercase text-slate-500"><tr><th class="px-3 py-2">Ref</th><th class="px-3 py-2">Date</th><th class="px-3 py-2">Code</th><th class="px-3 py-2">Type</th><th class="px-3 py-2 text-right">Qty</th></tr></thead><tbody class="divide-y">` +
      [...rows].reverse().slice(0, 50).map(t => `<tr><td class="px-3 py-2 font-mono font-semibold">${escapeHTML(t.referenceNumber)}</td><td class="px-3 py-2 text-xs">${escapeHTML(formatDate(t.date||t.createdAt))}</td><td class="px-3 py-2 font-mono">${escapeHTML(t.inkCode)}</td><td class="px-3 py-2">${escapeHTML(t.type)}</td><td class="px-3 py-2 text-right font-mono">${Number(t.quantity)||0}</td></tr>`).join('') +
      `</tbody></table></div>`;
  } else if (kind === 'last') {
    title = 'Last processed ticket';
    const latest = periodTxns.length ? [...periodTxns].sort((a,b)=>String(b.date||b.createdAt).localeCompare(String(a.date||a.createdAt)))[0] : null;
    if (!latest) html = '<p class="text-slate-500">No transactions in this period.</p>';
    else {
      sub = latest.referenceNumber || '';
      html = `<dl class="space-y-2 text-sm">
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Reference</dt><dd class="font-mono font-semibold">${escapeHTML(latest.referenceNumber)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Type</dt><dd>${escapeHTML(latest.type)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Item</dt><dd class="font-mono">${escapeHTML(latest.inkCode)}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Description</dt><dd>${escapeHTML(resolveTonerDescription(latest.inkCode)||'—')}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Qty</dt><dd class="font-mono">${Number(latest.quantity)||0}</dd></div>
        <div class="flex justify-between gap-4 border-b border-slate-100 py-2"><dt class="text-slate-500">Date</dt><dd>${escapeHTML(formatDate(latest.date||latest.createdAt))}</dd></div>
        <div class="flex justify-between gap-4 py-2"><dt class="text-slate-500">Department</dt><dd>${escapeHTML(latest.department||'—')}</dd></div>
      </dl>`;
    }
  }
  if (titleEl) titleEl.textContent = title;
  if (subEl) subEl.textContent = sub;
  bodyEl.innerHTML = html;
  const modal = document.getElementById('modal-kpi-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

function closeKpiDetail() {
  const modal = document.getElementById('modal-kpi-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

async function openMailLogModal() {
  const modal = document.getElementById('modal-mail-log');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
  await refreshMailLog();
}

function closeMailLogModal() {
  const modal = document.getElementById('modal-mail-log');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

function summarizeMailPreview(text) {
  const raw = String(text || '').replace(/\s+/g, ' ').trim();
  if (!raw) return 'No message preview available.';
  // Strip technical bits and duplicate timestamps (time is shown once on the card)
  let s = raw
    .replace(/driver=\S+/gi, '')
    .replace(/to=\S+/gi, '')
    .replace(/subject=/gi, '')
    .replace(/SMTP ERROR:?/gi, '')
    .replace(/---/g, '')
    .replace(/\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}(:\d{2})?/g, '')
    .replace(/\b(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]* \d{1,2},? \d{4},? \d{1,2}:\d{2}\s*(?:AM|PM)?/gi, '')
    .replace(/\(Philippine Time\)/gi, '')
    .replace(/Philippine Time/gi, '')
    .replace(/\s{2,}/g, ' ')
    .trim();
  if (!s) s = 'Low-stock notification';
  if (s.length > 140) s = s.slice(0, 137) + '…';
  return s;
}

/** Mail log timestamps are written in Asia/Manila — format once in 12-hour PH time */
function formatMailLogTime(loggedAt) {
  if (!loggedAt) return '';
  try {
    let s = String(loggedAt).trim().replace(' ', 'T');
    // Drop fractional seconds but keep timezone if present
    s = s.replace(/(\.\d+)(?=[zZ]|[+-]\d{2}:?\d{2}$)/, '');
    if (!/[zZ]$|[+-]\d{2}:?\d{2}$/.test(s)) {
      // Bare stamp from our logger = already Asia/Manila wall clock (not UTC)
      s = s.split('.')[0] + '+08:00';
    }
    const d = new Date(s);
    if (isNaN(d.getTime())) return String(loggedAt);
    return d.toLocaleString('en-PH', {
      timeZone: 'Asia/Manila',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      hour12: true
    });
  } catch (_) {
    return String(loggedAt);
  }
}

function guessMailKind(entry) {
  const blob = ((entry.subject || '') + ' ' + (entry.bodyPreview || '') + ' ' + (entry.meta || '')).toLowerCase();
  if (blob.includes('low-stock') || blob.includes('low stock') || blob.includes('reorder')) return 'Low stock alert';
  if (blob.includes('failed') || blob.includes('error')) return 'Delivery issue';
  return 'System notification';
}

async function refreshMailLog() {
  const body = document.getElementById('mail-log-body');
  const countEl = document.getElementById('mail-log-count');
  if (!body) return;
  body.innerHTML = `
    <div class="flex flex-col items-center justify-center py-12 text-zinc-400">
      <div class="w-8 h-8 border-2 border-zinc-200 border-t-zinc-500 rounded-full animate-spin mb-3"></div>
      <p class="text-sm">Loading activity…</p>
    </div>`;
  try {
    const data = await apiRequest('mail_log.php?limit=80');
    const entries = data.entries || [];
    if (countEl) countEl.textContent = entries.length ? entries.length + (entries.length === 1 ? ' message' : ' messages') : '';
    if (!entries.length) {
      body.innerHTML = `
        <div class="flex flex-col items-center justify-center py-14 px-6 text-center">
          <div class="w-14 h-14 rounded-2xl bg-white border border-zinc-200 flex items-center justify-center text-zinc-300 mb-4 shadow-sm">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <p class="text-sm font-medium text-zinc-800">No emails yet</p>
          <p class="text-xs text-zinc-500 mt-1.5 max-w-[16rem] leading-relaxed">When low-stock alerts are sent, they will show up here with status and recipient.</p>
        </div>`;
      return;
    }
    body.innerHTML = entries.map((e, idx) => {
      const ok = !!e.ok;
      const kind = guessMailKind(e);
      const preview = summarizeMailPreview(e.bodyPreview || e.meta || '');
      const to = (e.to || '').trim() || 'Unknown recipient';
      const subject = (e.subject || '').trim() || kind;
      const driver = (e.driver || '').toUpperCase();
      const statusLabel = ok ? 'Delivered' : 'Failed';
      const statusClass = ok
        ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/15'
        : 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-600/15';
      const iconBg = ok ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600';
      const icon = ok
        ? '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>'
        : '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
      const whenLabel = formatMailLogTime(e.loggedAt || '');
      return `
        <article class="group bg-white rounded-2xl border border-zinc-200/80 shadow-sm hover:shadow-md hover:border-zinc-300 transition-all p-4">
          <div class="flex gap-3">
            <div class="w-9 h-9 rounded-xl ${iconBg} flex items-center justify-center shrink-0">${icon}</div>
            <div class="min-w-0 flex-1">
              <div class="flex flex-wrap items-center gap-2 justify-between">
                <h4 class="text-sm font-semibold text-zinc-900 tracking-tight truncate">${escapeHTML(subject)}</h4>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold tracking-wide ${statusClass}">${statusLabel}</span>
              </div>
              <p class="text-xs text-zinc-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                ${whenLabel ? `<span class="text-zinc-500">${escapeHTML(whenLabel)}</span><span class="text-zinc-300">·</span>` : ''}
                <span class="font-medium text-zinc-600">${escapeHTML(kind)}</span>
                <span class="text-zinc-300">·</span>
                <span class="truncate">${escapeHTML(to)}</span>
                ${driver ? `<span class="text-zinc-300">·</span><span class="text-[10px] uppercase tracking-wider text-zinc-400">${escapeHTML(driver)}</span>` : ''}
              </p>
              <p class="mt-2.5 text-xs text-zinc-600 leading-relaxed line-clamp-3">${escapeHTML(preview)}</p>
            </div>
          </div>
        </article>`;
    }).join('');
  } catch (err) {
    if (countEl) countEl.textContent = '';
    body.innerHTML = `
      <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-6 text-center">
        <p class="text-sm font-medium text-rose-800">Couldn’t load email activity</p>
        <p class="text-xs text-rose-600 mt-1">${escapeHTML(err.message || 'Unknown error')}</p>
      </div>`;
  }
}

async function clearMailLog() {
  if (!confirm('Clear the entire mail log file?')) return;
  try {
    await apiRequest('mail_log.php', { method: 'DELETE' });
    await refreshMailLog();
    showToast('Mail log cleared.', 'success');
  } catch (e) {
    showToast(e.message || 'Clear failed', 'error');
  }
}



function syncLogsCustomRangeUI() {
  const period = document.getElementById('filter-logs-period')?.value || 'MONTH';
  const wrap = document.getElementById('logs-custom-range');
  if (wrap) {
    if (period === 'CUSTOM') wrap.classList.remove('hidden');
    else wrap.classList.add('hidden');
  }
}

function populateLogsFilterMeta(meta, keepSelection) {
  const actionSel = document.getElementById('filter-logs-action');
  const actorSel = document.getElementById('filter-logs-actor');
  const prevAction = keepSelection ? (actionSel?.value || 'ALL') : 'ALL';
  const prevActor = keepSelection ? (actorSel?.value || 'ALL') : 'ALL';
  if (actionSel) {
    const actions = (meta && meta.actions) || [];
    actionSel.innerHTML = '<option value="ALL">All actions</option>' +
      actions.map(a => `<option value="${escapeHTML(a.key)}">${escapeHTML(a.label || a.key)}</option>`).join('');
    if ([...actionSel.options].some(o => o.value === prevAction)) actionSel.value = prevAction;
  }
  if (actorSel) {
    const actors = (meta && meta.actors) || [];
    actorSel.innerHTML = '<option value="ALL">All admins</option>' +
      actors.map(a => {
        const val = a.username || a.name || '';
        const label = a.name && a.username && a.name !== a.username
          ? `${a.name} (${a.username})` : (a.name || a.username || '');
        return `<option value="${escapeHTML(val)}">${escapeHTML(label)}</option>`;
      }).join('');
    if ([...actorSel.options].some(o => o.value === prevActor)) actorSel.value = prevActor;
  }
}

async function loadSystemLogs() {
  const tbody = document.getElementById('logs-tbody');
  const q = (document.getElementById('filter-logs-search')?.value || '').trim();
  const period = document.getElementById('filter-logs-period')?.value || 'MONTH';
  const action = document.getElementById('filter-logs-action')?.value || 'ALL';
  const actor = document.getElementById('filter-logs-actor')?.value || 'ALL';
  const from = document.getElementById('filter-logs-from')?.value || '';
  const to = document.getElementById('filter-logs-to')?.value || '';
  syncLogsCustomRangeUI();
  if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">Loading…</td></tr>';
  try {
    const params = new URLSearchParams();
    params.set('limit', '200');
    if (q) params.set('q', q);
    if (period && period !== 'ALL') params.set('period', period);
    if (period === 'CUSTOM') {
      if (from) params.set('from', from);
      if (to) params.set('to', to);
      params.set('period', 'CUSTOM');
    }
    if (action && action !== 'ALL') params.set('action', action);
    if (actor && actor !== 'ALL') params.set('actor', actor);
    const data = await apiRequest('logs.php?' + params.toString());
    populateLogsFilterMeta(data.meta, true);
    const logs = data.logs || [];
    if (!logs.length) {
      if (tbody) tbody.innerHTML = '<tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No logs match these filters.</td></tr>';
      return;
    }
    if (tbody) {
      tbody.innerHTML = logs.map(L => {
        const when = L.createdAt ? formatDateTime(L.createdAt) : '—';
        let admin = escapeHTML(L.actorName || L.actorUsername || '—');
        if (L.actorName && L.actorUsername && L.actorName !== L.actorUsername) {
          admin = `${escapeHTML(L.actorName)} <span class="text-xs text-slate-400">(${escapeHTML(L.actorUsername)})</span>`;
        }
        const parts = [];
        if (L.details) parts.push(escapeHTML(L.details));
        if (L.referenceNumber) parts.push('<span class="font-mono text-xs">' + escapeHTML(L.referenceNumber) + '</span>');
        if (L.itemCode) parts.push('<span class="font-mono text-xs">' + escapeHTML(L.itemCode) + '</span>');
        const details = parts.length ? parts.join(' · ') : '—';
        return `<tr class="hover:bg-slate-50">
          <td class="px-4 py-3 text-xs text-slate-600 whitespace-nowrap">${escapeHTML(when)}</td>
          <td class="px-4 py-3 font-semibold text-slate-900">${escapeHTML(L.actionLabel || L.actionKey || '—')}</td>
          <td class="px-4 py-3 text-sm text-slate-600">${details}</td>
          <td class="px-4 py-3 text-sm">${admin}</td>
        </tr>`;
      }).join('');
    }
  } catch (e) {
    if (tbody) tbody.innerHTML = `<tr><td colspan="4" class="px-4 py-8 text-center text-rose-600 text-sm">${escapeHTML(e.message || 'Failed to load logs')}</td></tr>`;
  }
}

function resetLogsFilters() {
  const s = document.getElementById('filter-logs-search');
  const p = document.getElementById('filter-logs-period');
  const a = document.getElementById('filter-logs-action');
  const u = document.getElementById('filter-logs-actor');
  const f = document.getElementById('filter-logs-from');
  const to = document.getElementById('filter-logs-to');
  if (s) s.value = '';
  if (p) p.value = 'MONTH';
  if (a) a.value = 'ALL';
  if (u) u.value = 'ALL';
  if (f) f.value = '';
  if (to) to.value = '';
  syncLogsCustomRangeUI();
  loadSystemLogs();
}

/* formatDateTime: use global PH 12-hour helper above */




async function fillAlertRecipientSelect(selected) {
  const sel = document.getElementById('cfg-alert-recipient');
  if (!sel) return;
  let users = AppState.adminUsers || [];
  if (!users.length) {
    try {
      const data = await apiRequest('users.php');
      users = (data.users || []).filter(u => u.isActive !== false);
      AppState.adminUsers = users;
    } catch (_) { users = []; }
  }
  const emails = users
    .map(u => String(u.username || '').trim().toLowerCase())
    .filter(e => e && e.includes('@'));
  const current = (selected || '').trim().toLowerCase();
  if (current && !emails.includes(current)) emails.unshift(current);
  sel.innerHTML = '<option value="">— Select registered admin email —</option>' +
    emails.map(e => {
      const u = users.find(x => String(x.username || '').toLowerCase() === e);
      const label = u && u.fullName ? `${u.fullName} (${e})` : e;
      return `<option value="${escapeHTML(e)}">${escapeHTML(label)}</option>`;
    }).join('');
  if (current) sel.value = current;
}

async function loadEmailSettings() {
  const status = document.getElementById('cfg-email-status');
  try {
    const data = await apiRequest('settings.php');
    const e = data.email || {};
    const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val ?? ''; };
    set('cfg-smtp-host', e.smtp_host || '');
    set('cfg-smtp-port', e.smtp_port != null ? e.smtp_port : 465);
    set('cfg-smtp-enc', (e.smtp_encryption || 'ssl').toLowerCase());
    set('cfg-smtp-user', e.smtp_user || '');
    await fillAlertRecipientSelect(e.alert_recipient || e.admin_email || '');
    set('cfg-smtp-pass', '');
    set('cfg-cooldown', e.cooldown_hours != null ? e.cooldown_hours : 12);
    const hint = document.getElementById('cfg-smtp-pass-hint');
    if (hint) {
      hint.textContent = e.smtp_pass_set
        ? 'SMTP password is stored encrypted in the database. Leave blank to keep it, or enter a new password to replace it.'
        : 'No SMTP password saved yet — enter the mailbox password; it will be encrypted in the database.';
    }
    if (status) status.textContent = '';
  } catch (err) {
    if (status) status.textContent = err.message || 'Failed to load settings';
  }
}

async function saveEmailSettings(ev) {
  if (ev) ev.preventDefault();
  const status = document.getElementById('cfg-email-status');
  const btn = document.getElementById('btn-save-email-settings');
  const smtpUser = (document.getElementById('cfg-smtp-user')?.value || '').trim();
  const payload = {
    email: {
      driver: 'smtp',
      smtp_host: document.getElementById('cfg-smtp-host')?.value || '',
      smtp_port: parseInt(document.getElementById('cfg-smtp-port')?.value || '465', 10),
      smtp_encryption: document.getElementById('cfg-smtp-enc')?.value || 'ssl',
      smtp_user: smtpUser,
      smtp_pass: document.getElementById('cfg-smtp-pass')?.value || '',
      from_email: smtpUser,
      from_name: 'Toner Inventory System',
      alert_recipient: (document.getElementById('cfg-alert-recipient')?.value || '').trim().toLowerCase(),
      admin_email: (document.getElementById('cfg-alert-recipient')?.value || '').trim().toLowerCase(),
      cooldown_hours: parseInt(document.getElementById('cfg-cooldown')?.value || '12', 10),
      subject_prefix: '[Toner Alert]',
    }
  };
  try {
    if (btn) { btn.disabled = true; btn.textContent = 'Saving…'; }
    await apiRequest('settings.php', { method: 'POST', body: payload });
    showToast('Email settings saved to database.', 'success');
    if (status) status.textContent = 'Saved to database. Outbound alerts will use this SMTP configuration.';
    document.getElementById('cfg-smtp-pass').value = '';
    await loadEmailSettings();
  } catch (err) {
    showToast(err.message || 'Save failed', 'error');
    if (status) status.textContent = err.message || 'Save failed';
  } finally {
    if (btn) { btn.disabled = false; btn.textContent = 'Save email settings'; }
  }
}

async function testEmailSettings() {
  const status = document.getElementById('cfg-email-status');
  try {
    if (status) status.textContent = 'Sending low-stock test using current SMTP settings…';
    const data = await apiRequest('check_low_stock.php?force=1');
    const r = data.result || {};
    if (r.sent) {
      showToast('Test/alert email sent.', 'success');
      if (status) status.textContent = 'Email sent to ' + (r.to || 'admins') + '. Check Mail log.';
    } else {
      showToast(r.error || r.reason || 'Check completed — see status', r.error ? 'error' : 'info');
      if (status) status.textContent = JSON.stringify(r).slice(0, 200);
    }
  } catch (err) {
    showToast(err.message || 'Test failed', 'error');
    if (status) status.textContent = err.message || 'Test failed';
  }
}


function updateSidebarUser() {
  const me = AppState.currentUser || {};
  const name = (me.fullName || me.name || '').trim();
  const user = (me.username || me.email || '').trim();
  const nameEl = document.getElementById('sidebar-user-name');
  const emailEl = document.getElementById('sidebar-user-email');
  const av = document.getElementById('sidebar-user-avatar');
  if (nameEl) nameEl.textContent = name || user || 'Admin';
  if (emailEl) emailEl.textContent = user && name ? user : (user || 'Signed in');
  if (av) {
    const label = name || user || 'A';
    const parts = label.split(/\s+/).filter(Boolean);
    let initials = parts.length >= 2
      ? (parts[0][0] + parts[1][0])
      : label.slice(0, 2);
    av.textContent = initials.toUpperCase();
  }
}


function parseDefectiveMeta(notes) {
  const raw = String(notes || '');
  const get = (key) => {
    const m = raw.match(new RegExp('\\[' + key + '\\]\\s*(.+)', 'i'));
    return m ? m[1].trim() : '';
  };
  const human = raw.split(/\n/).filter(line => !/^\s*\[(SENT_AT|RECV_AT|ACCEPTED_BY|RECORDED_BY)\]/i.test(line)).join('\n').trim();
  return { sentAt: get('SENT_AT'), receivedAt: get('RECV_AT'), acceptedByMeta: get('ACCEPTED_BY'), recordedByMeta: get('RECORDED_BY'), humanNotes: human };
}


function openReleaseDetailModal(ref, id) {
  const list = AppState.transactions || [];
  let t = null;
  if (id) t = list.find(x => x.type === 'RELEASED' && String(x.id) === String(id));
  if (!t) t = list.find(x => x.type === 'RELEASED' && normalizeRefNumber(x.referenceNumber) === normalizeRefNumber(ref));
  if (!t) { showToast('Issuance record not found.', 'warning'); return; }
  const desc = resolveTonerDescription(t.inkCode) || t.description || '';
  const refEl = document.getElementById('rel-detail-ref');
  if (refEl) refEl.textContent = t.referenceNumber || ref || '—';
  const body = document.getElementById('rel-detail-body');
  const row = (label, val) => `<div class="flex justify-between gap-4 border-b border-slate-100 py-2.5"><dt class="text-slate-500 shrink-0">${label}</dt><dd class="text-slate-900 font-medium text-right break-words">${val}</dd></div>`;
  const yieldVal = (t.actualYield != null && t.actualYield !== '' && Number(t.actualYield) > 0)
    ? Number(t.actualYield).toLocaleString() + ' pages'
    : '—';
  if (body) {
    body.innerHTML = `<dl>
      ${row('Date', escapeHTML(formatDate(t.date || t.createdAt) || '—'))}
      ${row('Item', escapeHTML((t.inkCode || '') + (desc ? ' — ' + desc : '')))}
      ${row('Department', escapeHTML(t.department || '—'))}
      ${row('Location', escapeHTML(t.location || '—'))}
      ${row('Printer assigned', escapeHTML(t.locationPrinter || '—'))}
      ${row('Actual yield', escapeHTML(yieldVal))}
      ${row('Issued by', escapeHTML(t.issuedBy || '—'))}
      ${row('Recorded by', escapeHTML(t.recordedBy || '—'))}
      ${row('Purpose', escapeHTML(t.purpose || 'Stock issuance'))}
      ${t.defective ? row('Flag', '<span class="text-rose-700 font-semibold">Later marked defective</span>') : ''}
    </dl>`;
  }
  const modal = document.getElementById('modal-release-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

function closeReleaseDetailModal() {
  const modal = document.getElementById('modal-release-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

function openDefectiveDetailModal(ref) {
  const t = (AppState.transactions || []).find(x => x.type === 'DEFECTIVE' && normalizeRefNumber(x.referenceNumber) === normalizeRefNumber(ref));
  if (!t) { showToast('Defective record not found.', 'warning'); return; }
  const st = String(t.status || 'DEFECTIVE').toUpperCase();
  const meta = parseDefectiveMeta(t.defectiveNotes || '');
  const desc = resolveTonerDescription(t.inkCode) || t.description || '';
  const statusLabel = st === 'SENT_TO_SUPPLIER' ? 'Sent to supplier' : st === 'REPLACED' ? 'Replaced (back in inventory)' : 'Defective';
  const refEl = document.getElementById('def-detail-ref');
  if (refEl) refEl.textContent = t.referenceNumber || ref;
  const body = document.getElementById('def-detail-body');
  const row = (label, val) => `<div class="flex justify-between gap-4 border-b border-slate-100 py-2.5"><dt class="text-slate-500 shrink-0">${label}</dt><dd class="text-slate-900 font-medium text-right break-words">${val}</dd></div>`;
  if (body) {
    body.innerHTML = `<dl>
      ${row('Status', escapeHTML(statusLabel))}
      ${row('Item', escapeHTML((t.inkCode || '') + (desc ? ' — ' + desc : '')))}
      ${row('Department', escapeHTML(t.department || '—'))}
      ${row('Location', escapeHTML(t.location || '—'))}
      ${row('Flagged on', escapeHTML(formatDateTime(t.defectiveAt || t.createdAt || t.date) || '—'))}
      ${row('Sent to supplier', escapeHTML(meta.sentAt ? formatDateTime(meta.sentAt) : (st === 'SENT_TO_SUPPLIER' || st === 'REPLACED' ? 'Recorded' : 'Not sent yet')))}
      ${row('Received back to inventory', escapeHTML(meta.receivedAt ? formatDateTime(meta.receivedAt) : (st === 'REPLACED' ? 'Recorded' : 'Not received yet')))}
      ${row('Accepted by', escapeHTML(t.issuedBy || meta.acceptedByMeta || '—'))}
      ${row('Transacted / recorded by', escapeHTML(t.recordedBy || meta.recordedByMeta || '—'))}
      ${row('Notes', escapeHTML(meta.humanNotes || t.purpose || '—'))}
    </dl>`;
  }
  const actions = document.getElementById('def-detail-actions');
  if (actions) {
    let html = '';
    if (st === 'DEFECTIVE') html = `<button type="button" id="def-detail-send" class="px-3 py-2 text-xs font-semibold rounded-xl border border-amber-200 text-amber-800 bg-amber-50" data-ref="${escapeHTML(t.referenceNumber)}">Send to supplier</button>`;
    else if (st === 'SENT_TO_SUPPLIER') html = `<button type="button" id="def-detail-recv" class="px-3 py-2 text-xs font-semibold rounded-xl border border-emerald-200 text-emerald-800 bg-emerald-50" data-ref="${escapeHTML(t.referenceNumber)}" data-code="${escapeHTML(t.inkCode)}">Receive replacement</button>`;
    actions.innerHTML = html;
    document.getElementById('def-detail-send')?.addEventListener('click', () => {
      closeDefectiveDetailModal();
      sendDefectiveToSupplier(t.referenceNumber);
    });
    document.getElementById('def-detail-recv')?.addEventListener('click', () => {
      closeDefectiveDetailModal();
      openReceiveReplacementModal(t.referenceNumber, t.inkCode || '');
    });
  }
  const modal = document.getElementById('modal-defective-detail');
  if (modal) { modal.classList.remove('hidden'); modal.style.display = 'flex'; }
  document.body.classList.add('overflow-hidden');
}

function closeDefectiveDetailModal() {
  const modal = document.getElementById('modal-defective-detail');
  if (modal) { modal.classList.add('hidden'); modal.style.display = 'none'; }
  document.body.classList.remove('overflow-hidden');
}

function renderTransactions() {
  const tbody = document.getElementById('txns-tbody');
  const emptyState = document.getElementById('txns-empty-state');
  if (!tbody) return;

  updateTxnTabUI();
  syncTxnCustomRangeUI();

  const search = AppState.filters.transactionSearch.toLowerCase().trim();
  const typeFilter = AppState.filters.transactionType; // RECEIVED | RELEASED
  const deptFilter = AppState.filters.transactionDept || 'ALL';
  const dateFilter = AppState.filters.transactionDate;

  const filtered = AppState.transactions.filter(t => {
    if (t.type !== typeFilter) return false;

    if (typeFilter === 'RELEASED' && deptFilter !== 'ALL') {
      const code = resolveDepartmentCode(t.department) || (t.department || '').toUpperCase();
      if (code !== deptFilter) return false;
    }

    if (search) {
      const matchRef = (t.referenceNumber || '').toLowerCase().includes(search);
      const matchInk = (t.inkCode || '').toLowerCase().includes(search);
      const matchLoc = (t.location || '').toLowerCase().includes(search);
      const matchSupplier = (t.supplier || '').toLowerCase().includes(search);
      const matchDept = (t.department || '').toLowerCase().includes(search);
      const matchPurpose = (t.purpose || '').toLowerCase().includes(search);
      const matchDesc = (resolveTonerDescription(t.inkCode) || t.description || '').toLowerCase().includes(search);
      const matchIssued = (t.issuedBy || '').toLowerCase().includes(search);
      const matchRecorded = (t.recordedBy || '').toLowerCase().includes(search);
      if (!matchRef && !matchInk && !matchLoc && !matchSupplier && !matchDept && !matchPurpose && !matchDesc && !matchIssued && !matchRecorded) return false;
    }

    if (!isDateInFilter(t.date || t.createdAt, dateFilter)) return false;
    return true;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '';
    emptyState.classList.remove('hidden');
    const msg = document.getElementById('txns-empty-msg');
    if (msg) {
      if (typeFilter === 'RECEIVED') msg.textContent = 'No incoming delivery transactions yet.';
      else if (typeFilter === 'DEFECTIVE') msg.textContent = 'No defective returns recorded yet.';
      else msg.textContent = deptFilter === 'ALL' ? 'No release transactions yet.' : `No releases for ${deptFilter} yet.`;
    }
    return;
  }

  emptyState.classList.add('hidden');
  const isReceived = typeFilter === 'RECEIVED';
  const theadRow = document.getElementById('txns-thead-row');

  // Equal-width columns; different headers per tab
  if (theadRow) {
    if (isReceived) {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5 text-center">Qty</th>
        <th class="px-4 py-3.5">Supplier</th>`;
    } else if (typeFilter === 'DEFECTIVE') {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5">Status</th>
        <th class="px-4 py-3.5">Action</th>`;
    } else {
      theadRow.innerHTML = `
        <th class="px-4 py-3.5">Ticket Ref</th>
        <th class="px-4 py-3.5">Date</th>
        <th class="px-4 py-3.5">Toner Code</th>
        <th class="px-4 py-3.5">Description</th>
        <th class="px-4 py-3.5">Department</th>
        <th class="px-4 py-3.5">Action</th>`;
    }
  }

  const isDefective = typeFilter === 'DEFECTIVE';

  tbody.innerHTML = [...filtered].reverse().map(t => {
    if (isReceived) {
      const desc = resolveTonerDescription(t.inkCode) || t.description || '';
      // Prefer current inventory (stock card) supplier so edits show immediately;
      // fall back to what was stored on the transaction (e.g. old ADJ rows).
      const supplier = resolveTonerSupplier(t.inkCode) || (t.supplier || '').trim() || '';
      return `
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3.5 font-mono font-bold text-blue-700">${escapeHTML(t.referenceNumber)}</td>
          <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
          <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
          <td class="px-4 py-3.5 text-center font-mono font-bold text-blue-600">+${t.quantity}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(supplier || '—')}</td>
        </tr>`;
    }
        if (isDefective) {
      const desc = resolveTonerDescription(t.inkCode) || t.description || '';
      const st = String(t.status || 'DEFECTIVE').toUpperCase();
      let statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-100 text-rose-800 border border-rose-200">DEFECTIVE</span>';
      if (st === 'SENT_TO_SUPPLIER') {
        statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-100 text-amber-900 border border-amber-200">SENT TO SUPPLIER</span>';
      } else if (st === 'REPLACED') {
        statusBadge = '<span class="inline-flex px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-100 text-emerald-800 border border-emerald-200">REPLACED</span>';
      }
      const actions = `<button type="button" class="btn-def-view text-xs font-semibold text-blue-700 hover:underline" data-ref="${escapeHTML(t.referenceNumber)}">View</button>`;
      return `
        <tr class="hover:bg-slate-50 transition-colors">
          <td class="px-4 py-3.5 font-mono font-bold text-rose-700">${escapeHTML(t.referenceNumber)}</td>
          <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
          <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
          <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
          <td class="px-4 py-3.5">${statusBadge}</td>
          <td class="px-4 py-3.5">${actions}</td>
        </tr>`;
    }
    const desc = resolveTonerDescription(t.inkCode) || t.description || '';
    const defBadge = t.defective
      ? ' <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold rounded bg-rose-100 text-rose-700">DEFECTIVE</span>'
      : '';
    return `
      <tr class="hover:bg-slate-50 transition-colors">
        <td class="px-4 py-3.5 font-mono font-bold text-emerald-700">${escapeHTML(t.referenceNumber)}${defBadge}</td>
        <td class="px-4 py-3.5 text-xs text-slate-600">${formatDate(t.date || t.createdAt)}</td>
        <td class="px-4 py-3.5 font-mono font-semibold text-slate-900">${escapeHTML(t.inkCode)}</td>
        <td class="px-4 py-3.5 text-sm text-slate-700">${escapeHTML(desc || '—')}</td>
        <td class="px-4 py-3.5">
          <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-md bg-slate-100 text-slate-700 border border-slate-200">${escapeHTML(t.department || '—')}</span>
        </td>
        <td class="px-4 py-3.5">
          <button type="button" class="btn-rel-view text-xs font-semibold text-blue-700 hover:underline" data-ref="${escapeHTML(t.referenceNumber)}" data-id="${escapeHTML(t.id || '')}">View</button>
        </td>
      </tr>`;
  }).join('');
}

function getFilteredTransactionsForExport() {
  const typeFilter = AppState.filters.transactionType || 'RECEIVED';
  const search = (AppState.filters.transactionSearch || '').toLowerCase().trim();
  const dateFilter = AppState.filters.transactionDate || 'ALL';
  const deptFilter = AppState.filters.transactionDept || 'ALL';

  return (AppState.transactions || []).filter(t => {
    if (typeFilter === 'RECEIVED' && t.type !== 'RECEIVED') return false;
    if (typeFilter === 'RELEASED' && t.type !== 'RELEASED') return false;
    if (typeFilter === 'DEFECTIVE' && t.type !== 'DEFECTIVE') return false;

    if (!isDateInFilter(t.date || t.createdAt, dateFilter)) return false;

    if (typeFilter === 'RELEASED' && deptFilter !== 'ALL') {
      if ((t.department || '').toUpperCase() !== deptFilter.toUpperCase()) return false;
    }

    if (search) {
      const hay = [
        t.referenceNumber, t.inkCode, t.supplier, t.department, t.location, t.purpose, t.givenTo
      ].join(' ').toLowerCase();
      if (!hay.includes(search)) return false;
    }
    return true;
  });
}

function exportTransactionsToCSV() {
  const txns = getFilteredTransactionsForExport();
  if (!txns || txns.length === 0) {
    showToast('No transactions match the current filters.', 'warning');
    return;
  }

  const headers = [
    'Transaction ID', 'Reference Number', 'Type', 'Date', 'Toner Code', 'Description',
    'Quantity', 'Supplier', 'Department', 'Location', 'Issued By', 'Recorded By', 'Purpose', 'Status', 'Defective'
  ];
  const rows = txns.map(t => [
    t.id,
    t.referenceNumber,
    t.type,
    t.date || (t.createdAt || '').split('T')[0],
    t.inkCode,
    resolveTonerDescription(t.inkCode) || t.description || '',
    t.quantity,
    resolveTonerSupplier(t.inkCode) || (t.supplier || '').trim() || '',
    t.department || '',
    t.location || '',
    t.issuedBy || '',
    t.recordedBy || '',
    t.purpose || '',
    t.status || 'RECORDED',
    t.defective ? 'YES' : ''
  ]);

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(val => `"${String(val ?? '').replace(/"/g, '""')}"`).join(','))
  ].join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  const dateTag = new Date().toISOString().split('T')[0];
  const filterTag = (AppState.filters.transactionDate || 'ALL').toLowerCase();
  link.setAttribute('href', url);
  link.setAttribute('download', `transactions_${filterTag}_${dateTag}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
  showToast(`Exported ${txns.length} filtered transaction(s).`, 'success');
}

// ==========================================
// 14. REPORTS
// ==========================================
function renderReports() {
  // Reports page removed — analytics live on Dashboard
  if (typeof renderDashboard === 'function' && AppState.currentPage === 'dashboard') {
    /* no-op; dashboard already refreshes */
  }
}

function getStockStatus(quantity, reorderLevel) {
  const qty = Number(quantity) || 0;
  const reorder = Number(reorderLevel) || 0;
  if (qty <= 0) return STOCK_STATUS.OUT_OF_STOCK;
  if (qty <= reorder) return STOCK_STATUS.LOW_STOCK;
  return STOCK_STATUS.IN_STOCK;
}

// ==========================================
// 16. ALERTS
// ==========================================
// In-memory notification store (also regenerated from stock on each render)
AppState.notifications = AppState.notifications || [];

function pushNotification(type, title, message, meta = {}) {
  AppState.notifications.unshift({
    id: 'n-' + Date.now() + '-' + Math.random().toString(36).slice(2, 7),
    type, // 'warning' | 'danger' | 'success' | 'info'
    title,
    message,
    time: new Date().toISOString(),
    read: false,
    ...meta
  });
  // Keep last 30
  if (AppState.notifications.length > 30) AppState.notifications = AppState.notifications.slice(0, 30);
  renderNotifications();
}

function renderAlerts() {
  // Rebuild stock-based alerts; keep action/success notifications
  const lowOrOut = AppState.inks.filter(item => {
    const s = getStockStatus(item.quantity, item.reorderLevel);
    return s === STOCK_STATUS.OUT_OF_STOCK || s === STOCK_STATUS.LOW_STOCK;
  });

  AppState.notifications = (AppState.notifications || []).filter(n => n.source !== 'stock');

  lowOrOut.forEach(item => {
    const isOut = Number(item.quantity) <= 0;
    const code = item.inkCode || '';
    const desc = (item.description || '').trim();
    const label = desc ? `${code} — ${desc}` : code;
    AppState.notifications.push({
      id: 'stock-' + code,
      type: isOut ? 'danger' : 'warning',
      title: isOut ? 'Out of stock' : 'Low stock alert',
      message: isOut
        ? `${label} has 0 units. Order replacement soon.`
        : `${label} is low (${item.quantity} on hand; reorder at ${item.reorderLevel}).`,
      time: new Date().toISOString(),
      read: false,
      source: 'stock',
      inkCode: code
    });
  });

  AppState.notifications.sort((a, b) => {
    if (a.read !== b.read) return a.read ? 1 : -1;
    return new Date(b.time) - new Date(a.time);
  });

  renderNotifications();
}

/** Email admins if inventory is still low/out (server cooldown applies unless force). */
async function triggerLowStockEmailCheck(force = false) {
  try {
    const q = force ? 'check_low_stock.php?force=1' : 'check_low_stock.php';
    const data = await apiRequest(q);
    const r = data.result || {};
    if (r.sent) {
      showToast('Low-stock email sent to admins.', 'success');
      pushNotification('warning', 'Low-stock email sent', `Alerted ${r.alerted || 0} item(s).`, { source: 'action' });
    } else if ((r.low_count || 0) + (r.out_count || 0) === 0) {
      if (force) showToast('Stock levels are OK — no alert needed.', 'info');
    } else if (r.reason === 'no_recipients') {
      showToast(r.error || 'No admin emails configured.', 'warning');
    } else if (r.error) {
      showToast(r.error, 'error');
    } else if (force) {
      showToast('Alert checked (may be in cooldown or already notified).', 'info');
    }
  } catch (e) {
    if (force) showToast(e.message || 'Email check failed', 'error');
    console.warn('[Toner] low-stock email check', e);
  }
}


function renderNotifications() {
  const list = document.getElementById('notif-list');
  const badge = document.getElementById('notif-badge');
  const empty = document.getElementById('notif-empty');
  if (!list) return;

  const notifs = AppState.notifications || [];
  const unread = notifs.filter(n => !n.read).length;

  if (badge) {
    if (unread > 0) {
      badge.textContent = unread > 99 ? '99+' : String(unread);
      badge.classList.remove('hidden');
    } else {
      badge.classList.add('hidden');
    }
  }

  if (notifs.length === 0) {
    list.innerHTML = '<div class="p-6 text-center text-sm text-slate-400" id="notif-empty">No notifications yet</div>';
    return;
  }

  list.innerHTML = notifs.map(n => {
    const colors = {
      danger: 'bg-rose-50 text-rose-600',
      warning: 'bg-amber-50 text-amber-600',
      success: 'bg-emerald-50 text-emerald-600',
      info: 'bg-blue-50 text-blue-600'
    };
    const iconBg = colors[n.type] || colors.info;
    const timeStr = formatDateTime(n.time);
    return `
      <div class="p-3.5 hover:bg-slate-50 transition-colors ${n.read ? 'opacity-70' : ''}" data-notif-id="${n.id}">
        <div class="flex gap-3">
          <div class="w-9 h-9 rounded-lg ${iconBg} flex items-center justify-center shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
          </div>
          <div class="flex-1 min-w-0">
            <div class="flex items-start justify-between gap-2">
              <p class="text-sm font-semibold text-slate-900">${escapeHTML(n.title)}</p>
              <span class="text-[10px] text-slate-400 whitespace-nowrap">${timeStr}</span>
            </div>
            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">${escapeHTML(n.message)}</p>
          </div>
        </div>
      </div>`;
  }).join('');
}


// ==========================================
// 17. CHARTS (Chart.js Integration)
// ==========================================
function renderCharts() {
  if (typeof Chart === 'undefined') return;
  renderDepartmentDemandChart();
  renderStockStatusChart();
  renderAvgYieldChart();
}

function renderAvgYieldChart() {
  const canvas = document.getElementById('chart-avg-yield');
  if (!canvas) return;
  if (AppState.charts.avgYield) {
    try { AppState.charts.avgYield.destroy(); } catch (_) {}
  }

  const periodTxns = (typeof getDashboardFilteredTransactions === 'function')
    ? getDashboardFilteredTransactions()
    : (AppState.transactions || []);

  // Group RELEASED with positive actualYield by toner code (or description)
  const buckets = {}; // code -> { sum, n, label }
  let totalSum = 0, totalN = 0;
  periodTxns.forEach(t => {
    if (t.type !== 'RELEASED') return;
    const y = t.actualYield != null ? Number(t.actualYield) : (t.yield != null ? Number(t.yield) : NaN);
    if (!Number.isFinite(y) || y <= 0) return;
    const code = (t.inkCode || '').toUpperCase() || 'UNKNOWN';
    const desc = (typeof resolveTonerDescription === 'function' ? resolveTonerDescription(code) : '') || code;
    if (!buckets[code]) buckets[code] = { sum: 0, n: 0, label: desc };
    buckets[code].sum += y;
    buckets[code].n += 1;
    totalSum += y;
    totalN += 1;
  });

  const kpi = document.getElementById('kpi-avg-yield');
  if (kpi) {
    kpi.textContent = totalN > 0
      ? `Overall avg: ${Math.round(totalSum / totalN).toLocaleString()} pages (${totalN} issuance${totalN === 1 ? '' : 's'})`
      : 'Avg: — (no yield data in period)';
  }

  const entries = Object.entries(buckets)
    .map(([code, b]) => ({ code, avg: b.sum / b.n, label: b.label, n: b.n }))
    .sort((a, b) => b.avg - a.avg)
    .slice(0, 12);

  if (entries.length === 0) {
    AppState.charts.avgYield = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: ['No yield data'],
        datasets: [{ label: 'Avg pages', data: [0], backgroundColor: '#cbd5e1', borderRadius: 6 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          title: { display: true, text: 'Record yields on issuance to populate this chart', font: { size: 12 } }
        },
        scales: { y: { beginAtZero: true, max: 1000 } }
      }
    });
    return;
  }

  const colors = ['#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#14b8a6'];
  AppState.charts.avgYield = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: entries.map(e => e.label.length > 28 ? e.label.slice(0, 26) + '…' : e.label),
      datasets: [{
        label: 'Average pages',
        data: entries.map(e => Math.round(e.avg)),
        backgroundColor: entries.map((_, i) => colors[i % colors.length]),
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            afterLabel: (ctx) => {
              const e = entries[ctx.dataIndex];
              return e ? `From ${e.n} issuance(s) · ${e.code}` : '';
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: { display: true, text: 'Pages', font: { size: 11 } }
        },
        x: {
          ticks: { maxRotation: 45, minRotation: 0, font: { size: 10 } }
        }
      }
    }
  });
}


function renderStockStatusChart() {
  const canvas = document.getElementById('chart-stock-status');
  if (!canvas) return;
  if (AppState.charts.brand) {
    AppState.charts.brand.destroy();
  }

  // Aggregate unique toner codes
  const byCode = {};
  AppState.inks.forEach(item => {
    const code = (item.inkCode || '').trim();
    if (!code) return;
    const k = code.toUpperCase();
    if (!byCode[k]) byCode[k] = { quantity: 0, reorderLevel: 0 };
    byCode[k].quantity += Number(item.quantity) || 0;
    byCode[k].reorderLevel = Math.max(byCode[k].reorderLevel, Number(item.reorderLevel) || 0);
  });

  let inStock = 0, lowStock = 0, outStock = 0;
  Object.values(byCode).forEach(item => {
    const s = getStockStatus(item.quantity, item.reorderLevel);
    if (s === STOCK_STATUS.IN_STOCK) inStock++;
    else if (s === STOCK_STATUS.LOW_STOCK) lowStock++;
    else outStock++;
  });

  AppState.charts.brand = new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels: ['In Stock', 'Low Stock', 'Out of Stock'],
      datasets: [{
        data: [inStock, lowStock, outStock],
        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
        hoverOffset: 6
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 }, padding: 12 } }
      }
    }
  });
}

function renderDepartmentDemandChart() {
  const canvas = document.getElementById('chart-activity');
  if (!canvas) return;

  if (AppState.charts.activity) {
    AppState.charts.activity.destroy();
  }

  // Frequency of toner need = units RELEASED per department (dashboard period)
  const periodTxns = (typeof getDashboardFilteredTransactions === 'function')
    ? getDashboardFilteredTransactions()
    : (AppState.transactions || []);
  const demand = {};
  periodTxns
    .filter(t => t.type === 'RELEASED')
    .forEach(t => {
      const d = t.department || 'Other';
      demand[d] = (demand[d] || 0) + (Number(t.quantity) || 0);
    });

  const releaseEvents = {};
  const seen = new Set();
  periodTxns
    .filter(t => t.type === 'RELEASED')
    .forEach(t => {
      const d = t.department || 'Other';
      const ref = normalizeRefNumber(t.referenceNumber);
      const key = d + '::' + ref;
      if (!seen.has(key)) {
        seen.add(key);
        releaseEvents[d] = (releaseEvents[d] || 0) + 1;
      }
    });

  // Include departments from Locations master even with 0 releases (new locations)
  getKnownDepartments().forEach(d => {
    if (demand[d] === undefined) demand[d] = 0;
  });
  // Sort by units issued (most frequent demand first)
  const labels = Object.keys(demand).sort((a, b) => demand[b] - demand[a]);
  const data = labels.map(l => demand[l]);
  const eventData = labels.map(l => releaseEvents[l] || 0);

  // If no release history yet, show empty state message on chart
  if (labels.length === 0) {
    AppState.charts.activity = new Chart(canvas, {
      type: 'bar',
      data: {
        labels: ['No release data yet'],
        datasets: [{ label: 'Units issued', data: [0], backgroundColor: '#cbd5e1', borderRadius: 6 }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 5 } }
      }
    });
    return;
  }

  const colors = ['#ef4444', '#f59e0b', '#3b82f6', '#8b5cf6', '#10b981', '#06b6d4', '#ec4899', '#84cc16'];

  AppState.charts.activity = new Chart(canvas, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        {
          label: 'Units issued',
          data: data,
          backgroundColor: labels.map((_, i) => colors[i % colors.length]),
          borderRadius: 6
        }
      ]
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: (ctx) => {
              const dept = ctx.label;
              const units = ctx.parsed.x;
              const events = releaseEvents[dept] || 0;
              return [
                ` Units issued: ${units}`,
                ` Release tickets: ${events}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { stepSize: 1 },
          title: { display: true, text: 'Toner units issued', font: { size: 11 } }
        },
        y: {
          ticks: { font: { size: 12, weight: '600' } }
        }
      }
    }
  });
}

function renderReportCharts(filteredTxns) {
  if (typeof Chart === 'undefined') return;

  // 1. Status Doughnut Chart
  const statusCanvas = document.getElementById('chart-report-status');
  if (statusCanvas) {
    if (AppState.charts.reportStatus) AppState.charts.reportStatus.destroy();

    let inStock = 0, lowStock = 0, outStock = 0;
    AppState.inks.forEach(item => {
      const s = getStockStatus(item.quantity, item.reorderLevel);
      if (s === STOCK_STATUS.IN_STOCK) inStock++;
      else if (s === STOCK_STATUS.LOW_STOCK) lowStock++;
      else if (s === STOCK_STATUS.OUT_OF_STOCK) outStock++;
    });

    AppState.charts.reportStatus = new Chart(statusCanvas, {
      type: 'pie',
      data: {
        labels: ['In Stock', 'Low Stock', 'Out of Stock'],
        datasets: [{
          data: [inStock, lowStock, outStock],
          backgroundColor: ['#10b981', '#f59e0b', '#ef4444']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  }

  // 2. Department Consumption Chart
  const deptCanvas = document.getElementById('chart-report-dept');
  if (deptCanvas) {
    if (AppState.charts.reportDept) AppState.charts.reportDept.destroy();

    const deptCounts = {};
    filteredTxns.filter(t => t.type === 'RELEASED').forEach(t => {
      const dept = t.department || 'Other';
      deptCounts[dept] = (deptCounts[dept] || 0) + (Number(t.quantity) || 0);
    });

    const deptLabels = Object.keys(deptCounts);
    const deptData = Object.values(deptCounts);

    AppState.charts.reportDept = new Chart(deptCanvas, {
      type: 'polarArea',
      data: {
        labels: deptLabels.length > 0 ? deptLabels : ['No releases in period'],
        datasets: [{
          data: deptData.length > 0 ? deptData : [0],
          backgroundColor: ['#3b82f6', '#8b5cf6', '#ec4899', '#f97316', '#10b981']
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { position: 'right' }
        }
      }
    });
  }
}

// ==========================================
// 18. MODALS
// ==========================================
/* openDuplicateModal replaced below */
function __dup_placeholder(){}

function openNotFoundModal(refNumber) {
  const modal = document.getElementById('modal-not-found');
  if (!modal) return;
  const nfRef = document.getElementById('modal-notfound-ref') || document.getElementById('modal-nf-ref');
  if (nfRef) nfRef.textContent = refNumber;
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  modal.classList.remove('hidden');
}

function closeNotFoundModal() {
  const modal = document.getElementById('modal-not-found');
  if (modal) modal.classList.add('hidden');
  const backdrop = document.getElementById('modal-backdrop');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

function openResetModal() {
  const resetModal = document.getElementById('modal-reset-confirm') || document.getElementById('modal-reset');
  if (!resetModal) return;
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  resetModal.classList.remove('hidden');
}

function closeResetModal() {
  const resetModal = document.getElementById('modal-reset-confirm') || document.getElementById('modal-reset');
  if (resetModal) resetModal.classList.add('hidden');
  const backdrop = document.getElementById('modal-backdrop');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}

// ==========================================
// 19. TOAST NOTIFICATIONS
// ==========================================
function showToast(message, type = 'info') {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const toast = document.createElement('div');
  toast.className = `p-4 rounded-xl shadow-lg border text-sm flex items-start gap-3 transform transition-all duration-300 pointer-events-auto max-w-sm ${
    type === 'success' ? 'bg-white border-emerald-200 text-emerald-900 shadow-emerald-100' :
    type === 'error' ? 'bg-white border-rose-200 text-rose-900 shadow-rose-100' :
    type === 'warning' ? 'bg-white border-amber-200 text-amber-900 shadow-amber-100' :
    'bg-white border-blue-200 text-blue-900 shadow-blue-100'
  }`;

  const icon = type === 'success' 
    ? `<svg class="w-5 h-5 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    : type === 'error'
    ? `<svg class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
    : `<svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`;

  toast.innerHTML = `
    ${icon}
    <div class="flex-1 text-xs font-medium leading-relaxed">${escapeHTML(message)}</div>
    <button type="button" class="text-slate-400 hover:text-slate-600 ml-2" onclick="this.parentElement.remove()">
      &times;
    </button>
  `;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 4500);
}


// ==========================================
// ACTION MODALS (Receive / Release)
// ==========================================

function openLogoutModal() {
  const modal = document.getElementById('modal-logout');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

function closeLogoutModal() {
  const modal = document.getElementById('modal-logout');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}


function getUniqueTonerCodes() {
  const map = {};
  (AppState.inks || []).forEach(i => {
    const c = (i.inkCode || i.itemCode || '').trim();
    if (!c) return;
    const k = c.toUpperCase();
    if (!map[k]) {
      map[k] = {
        code: c,
        description: (i.description || '').trim(),
        qty: 0
      };
    }
    if (!map[k].description && i.description) {
      map[k].description = String(i.description).trim();
    }
    map[k].qty += Number(i.quantity) || 0;
  });
  return Object.values(map).sort((a, b) => {
    const da = (a.description || a.code).toLowerCase();
    const db = (b.description || b.code).toLowerCase();
    return da.localeCompare(db);
  });
}

function populateTonerSelect(selectId, includeStock) {
  const sel = document.getElementById(selectId);
  if (!sel) return;
  const current = sel.value;
  const list = getUniqueTonerCodes();
  // Label = description (easier to distinguish); value stays item_code for posting
  sel.innerHTML = '<option value="">— Select item —</option>' +
    list.map(t => {
      const label = t.description
        ? `${t.description}`
        : t.code;
      const stock = includeStock ? ` (${t.qty} on hand)` : '';
      const title = t.description ? `${t.description} · ${t.code}` : t.code;
      return `<option value="${escapeHTML(t.code)}" data-qty="${t.qty}" title="${escapeHTML(title)}">${escapeHTML(label)}${escapeHTML(stock)}</option>`;
    }).join('');
  if (current) sel.value = current;
}



function recordManualDelivery() {
  const inputRef = normalizeRefNumber(document.getElementById('modal-del-ref')?.value);
  const supplier = (document.getElementById('modal-del-supplier')?.value || '').trim();
  const mrrData = AppState.activeMrrLookup || null;

  // Prefer MRR from successful lookup, then input field
  const ref = normalizeRefNumber(
    (mrrData && (mrrData.mrr || mrrData.referenceNumber)) || inputRef || ''
  );

  if (!ref) {
    showToast('Enter an MRR number and click Search MRR first.', 'warning');
    document.getElementById('modal-del-ref')?.focus();
    return;
  }
  if (!mrrData || !Array.isArray(mrrData.lines) || !mrrData.lines.length) {
    showToast('Search the MRR first so ERP lines can load, then confirm.', 'warning');
    return;
  }
  if (mrrData.alreadyRecorded) {
    showToast('This MRR was already recorded.', 'error');
    return;
  }

  const lines = mrrData.lines.map(L => ({
    itemCode: L.itemCode || L.inkCode,
    inkCode: L.itemCode || L.inkCode,
    quantity: L.quantity,
    date: L.date || mrrData.mrrDate || new Date().toISOString().slice(0, 10),
    description: L.description || ''
  }));

  (async () => {
    try {
      const btn = document.getElementById('btn-modal-process-del');
      if (btn) { btn.disabled = true; btn.textContent = 'Posting…'; }

      // Send mrr + referenceNumber + lines so API never says "reference required"
      const okDel = await appConfirm({ title: 'Confirm delivery', message: 'Post this delivery and add stock to inventory?', confirmText: 'Post to stock' });
        if (!okDel) return;
        await apiRecordDelivery({
        mrr: ref,
        mrrNo: ref,
        referenceNumber: ref,
        supplier: supplier,
        lines: lines
      });

      await loadFromBackend();
      // Show in Transaction History (Incoming) without date filter hiding MRR date
      AppState.filters.transactionType = 'RECEIVED';
      AppState.filters.transactionDate = 'ALL';
      AppState.filters.transactionDateFrom = '';
      AppState.filters.transactionDateTo = '';
      const dateSel = document.getElementById('filter-txn-date');
      if (dateSel) dateSel.value = 'ALL';
      renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
      const successRef = document.getElementById('m-del-success-ref');
      if (successRef) successRef.textContent = ref;
      document.getElementById('receive-step-form')?.classList.add('hidden');
      document.getElementById('receive-step-success')?.classList.remove('hidden');
      showToast(`MRR ${ref} posted — check Transaction History → Incoming.`, 'success');
      pushNotification('success', 'Delivery Received', `MRR ${ref} processed from ERP.`, { source: 'action' });
      AppState.activeMrrLookup = null;
    } catch (e) {
      if (e.data && e.data.duplicate) openDuplicateModal(ref, 'DELIVERY');
      else showToast(e.message || 'Failed to record delivery.', 'error');
    } finally {
      const btn = document.getElementById('btn-modal-process-del');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Confirm & Post Stock';
      }
    }
  })();
}

async function searchMrrLookup() {
  const ref = normalizeRefNumber(document.getElementById('modal-del-ref')?.value);
  const status = document.getElementById('mrr-lookup-status');
  const preview = document.getElementById('mrr-preview');
  const tbody = document.getElementById('mrr-preview-tbody');
  const postBtn = document.getElementById('btn-modal-process-del');
  const already = document.getElementById('mrr-already-warn');

  if (!ref) {
    showToast('Enter an MRR number (e.g. MG009105).', 'warning');
    return;
  }
  AppState.activeMrrLookup = null;
  if (postBtn) postBtn.disabled = true;
  if (preview) preview.classList.add('hidden');
  if (already) already.classList.add('hidden');
  if (status) {
    status.classList.remove('hidden');
    status.className = 'text-sm px-3 py-2 rounded-xl border border-blue-200 bg-blue-50 text-blue-800';
    status.textContent = 'Looking up MRR in ERP…';
  }

  try {
    const data = await apiRequest('mrr_lookup.php?mrr=' + encodeURIComponent(ref));
    AppState.activeMrrLookup = data;

    if (status) {
      status.className = 'text-sm px-3 py-2 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800';
      status.textContent = `Found ${data.lineCount || (data.lines || []).length} line(s) for MRR ${data.mrr}.`;
    }

    document.getElementById('mrr-preview-no').textContent = data.mrr || ref;
    document.getElementById('mrr-preview-date').textContent = data.mrrDate ? formatDate(data.mrrDate) : '—';
    document.getElementById('mrr-preview-lines').textContent = String(data.lineCount || (data.lines || []).length);
    document.getElementById('mrr-preview-qty').textContent = String(data.totalQty || 0);

    if (tbody) {
      tbody.innerHTML = (data.lines || []).map(L => `
        <tr class="hover:bg-slate-50">
          <td class="px-3 py-2 font-mono text-xs font-semibold text-slate-900">${escapeHTML(L.itemCode)}</td>
          <td class="px-3 py-2 text-slate-700 text-xs">${escapeHTML(L.description || '—')}</td>
          <td class="px-3 py-2 text-right font-mono font-bold">${L.quantity}</td>
          <td class="px-3 py-2 text-right font-mono text-slate-600">${L.currentStock}</td>
          <td class="px-3 py-2 text-right font-mono text-emerald-700 font-semibold">${L.projectedStock}</td>
        </tr>
      `).join('');
    }

    if (preview) preview.classList.remove('hidden');
    if (data.alreadyRecorded) {
      if (already) already.classList.remove('hidden');
      if (postBtn) postBtn.disabled = true;
    } else if (postBtn) {
      postBtn.disabled = false;
    }
  } catch (e) {
    if (status) {
      status.className = 'text-sm px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 text-rose-800';
      status.textContent = (e.message || 'MRR lookup failed.') + (e.status ? ' (HTTP ' + e.status + ')' : '');
    }
    if (preview) preview.classList.add('hidden');
    if (postBtn) postBtn.disabled = true;
  }
}

function recordManualIssuance() {
  const ref = normalizeRefNumber(document.getElementById('modal-rel-ref')?.value);
  const toner = (document.getElementById('modal-rel-toner')?.value || '').trim();
  const dept = (document.getElementById('modal-rel-dept')?.value || '').trim();
  const locSelect = document.getElementById('modal-rel-location');
  const location = (locSelect?.value || '').trim();
  const locationPrinter = (document.getElementById('modal-rel-printer')?.value || '').trim();
  const yieldRaw = document.getElementById('modal-rel-yield')?.value;
  const issuedBy = (document.getElementById('modal-rel-issued-by')?.value || '').trim();
  // Issuance date is always the current day
  const date = new Date().toISOString().split('T')[0];
  const dateInput = document.getElementById('modal-rel-date');
  if (dateInput) dateInput.value = date;

  if (!ref) { showToast('Issuance reference is required.', 'warning'); return; }
  if (!toner) { showToast('Select an item.', 'warning'); return; }
  if (!dept) { showToast('Select a department.', 'warning'); return; }
  if (!location) { showToast('Select a location.', 'warning'); return; }
  const hasYield = !!document.getElementById('modal-rel-has-yield')?.checked;
  let actualYield = null;
  if (hasYield) {
    if (yieldRaw === '' || yieldRaw === null || yieldRaw === undefined) {
      showToast('Enter actual yield (pages before change).', 'warning');
      document.getElementById('modal-rel-yield')?.focus();
      return;
    }
    actualYield = Math.max(0, parseInt(yieldRaw, 10) || 0);
  }
  if (!issuedBy) { showToast('Select who issued this toner.', 'warning'); return; }

  if (TicketService.isAlreadyProcessed(ref)) {
    openDuplicateModal(ref, 'RELEASE');
    return;
  }

  const totalQty = AppState.inks
    .filter(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase())
    .reduce((s, i) => s + (Number(i.quantity) || 0), 0);
  if (totalQty < 1) {
    showToast(`Insufficient stock for ${toner} (0 on hand).`, 'error');
    return;
  }

  const matched = AppState.inks.find(i =>
    (i.inkCode || '').toUpperCase() === toner.toUpperCase() && (Number(i.quantity) || 0) > 0
  ) || AppState.inks.find(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase());

  AppState.selectedReleaseLocation = location;
  AppState.selectedReleaseDepartment = dept;

  const ticket = {
    referenceNumber: ref,
    type: 'RELEASE',
    status: 'RECORDED',
    date,
    givenTo: '',
    department: dept,
    purpose: 'Stock issuance',
    items: [{
      inkCode: toner,
      brand: matched?.brand || '',
      printerModel: matched?.printerModel || '',
      color: matched?.color || 'Black',
      quantity: 1,
      serialNumber: ''
    }]
  };

  (async () => {
    try {
      if (AppState.useBackend) {
        const okIssue = await appConfirm({
          title: 'Confirm stock issuance',
          message: 'Record this toner issuance and deduct 1 unit from inventory?',
          confirmText: 'Issue toner'
        });
        if (!okIssue) return;

        // Keep loading visible for the whole issuance + refresh (email can take a while)
        showGlobalLoading('Issuing toner… sending alerts if needed');
        try {
          await apiRecordRelease({
            referenceNumber: ref,
            inkCode: toner,
            itemCode: toner,
            department: dept,
            location,
            locationPrinter: locationPrinter === '—' ? '' : locationPrinter,
            actualYield,
            issuedBy
          }, { silent: true, loadingMessage: 'Issuing toner…' });
          await loadFromBackendSilent();
        } finally {
          forceHideGlobalLoading();
        }
        renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
        document.getElementById('m-rel-success-ref').textContent = ref;
        document.getElementById('release-step-form')?.classList.add('hidden');
        document.getElementById('release-step-success')?.classList.remove('hidden');
        showToast(`Issuance ${ref} recorded.`, 'success');
        // If API reported low-stock email, surface a soft reminder in UI
        try {
          const invItem = (AppState.inks || []).find(i => (i.inkCode || '').toUpperCase() === toner.toUpperCase());
          const qty = invItem ? Number(invItem.quantity) : null;
          const reorder = invItem ? Number(invItem.reorderLevel) : 3;
          if (qty !== null && qty <= reorder) {
            pushNotification('warning', 'Low stock after issuance',
              `${toner} is still low (${qty} left; reorder at ${reorder}). Admins were emailed if configured.`,
              { source: 'action' });
          }
        } catch (_) {}
        pushNotification('success', 'Toner Released', `Ticket ${ref} processed. Toner stock decremented.`, { source: 'action' });
      } else {
        const ok = TicketService.processReleaseTicket(ticket);
        if (ok) {
          document.getElementById('m-rel-success-ref').textContent = ref;
          document.getElementById('release-step-form')?.classList.add('hidden');
          document.getElementById('release-step-success')?.classList.remove('hidden');
        }
      }
    } catch (e) {
      if (e.data && e.data.duplicate) openDuplicateModal(ref, 'RELEASE');
      else showToast(e.message || 'Failed to record issuance.', 'error');
    }
  })();
}

function openReceiveModal() {
  document.getElementById('receive-step-form')?.classList.remove('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');
  AppState.activeMrrLookup = null;
  const ref = document.getElementById('modal-del-ref');
  const sup = document.getElementById('modal-del-supplier');
  const status = document.getElementById('mrr-lookup-status');
  const preview = document.getElementById('mrr-preview');
  const already = document.getElementById('mrr-already-warn');
  const postBtn = document.getElementById('btn-modal-process-del');
  if (ref) ref.value = '';
  if (sup) sup.value = '';
  if (status) { status.classList.add('hidden'); status.textContent = ''; }
  if (preview) preview.classList.add('hidden');
  if (already) already.classList.add('hidden');
  if (postBtn) { postBtn.disabled = true; postBtn.textContent = 'Confirm & Post Stock'; }
  const modal = document.getElementById('modal-receive');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => ref?.focus(), 80);
}

function closeReceiveModal() {
  const modal = document.getElementById('modal-receive');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeDeliveryTicket = null;
}

function resetReceiveModal() {
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.add('hidden');
  document.getElementById('receive-step-preview')?.classList.add('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');
  AppState.activeDeliveryTicket = null;
}

async function openReleaseModal() {
  document.getElementById('release-step-form')?.classList.remove('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  populateTonerSelect('modal-rel-toner', true);
  await loadReleaseLocations();
  await loadAdminUsersForIssuance();
  populateDeptSelect();
  populateIssuedBySelect();
  const ref = document.getElementById('modal-rel-ref');
  const dept = document.getElementById('modal-rel-dept');
  const date = document.getElementById('modal-rel-date');
  const hint = document.getElementById('modal-rel-stock-hint');
  const hasYieldEl = document.getElementById('modal-rel-has-yield');
  if (hasYieldEl) hasYieldEl.checked = false;
  const yieldWrap = document.getElementById('modal-rel-yield-wrap');
  if (yieldWrap) yieldWrap.classList.add('hidden');
  const yieldEl = document.getElementById('modal-rel-yield');
  const printerEl = document.getElementById('modal-rel-printer');
  if (ref) ref.value = '';
  if (dept) dept.value = '';
  if (date) date.value = new Date().toISOString().split('T')[0];
  if (hint) hint.textContent = '';
  if (yieldEl) yieldEl.value = '';
  if (printerEl) printerEl.value = '';
  fillLocationsForDept('', 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
  const modal = document.getElementById('modal-release');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
  setTimeout(() => ref?.focus(), 80);
}

function closeReleaseModal() {
  const modal = document.getElementById('modal-release');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  AppState.activeReleaseTicket = null;
}

function resetReleaseModal() {
  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.classList.add('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  AppState.activeReleaseTicket = null;
  AppState.selectedReleaseLocation = '';
  AppState.selectedReleaseDepartment = '';
  const loc = document.getElementById('modal-rel-location');
  if (loc) loc.value = '';
}

function showReceiveError(ref, title, desc) {
  const errRef = document.getElementById('receive-error-ref');
  const errTitle = document.getElementById('receive-error-title');
  const errDesc = document.getElementById('receive-error-desc');
  if (errRef) errRef.textContent = ref || '—';
  if (errTitle) errTitle.textContent = title || 'Ticket Not Found';
  if (errDesc) errDesc.textContent = desc || 'No approved ticket matches this reference in the registry.';

  // Keep search visible; show error in front below it
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.remove('hidden');
  document.getElementById('receive-step-preview')?.classList.add('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');

  // Scroll error into view inside modal
  document.getElementById('receive-step-error')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function searchAndDisplayDeliveryModal(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a delivery reference number.', 'warning');
    return;
  }

  // Keep search visible area but switch steps based on result
  if (TicketService.isAlreadyProcessed(normalized)) {
    showReceiveError(
      normalized,
      'Ticket Already Processed',
      'This delivery ticket was already executed. Duplicate stock movements are blocked.'
    );
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  if (!ticket || ticket.type !== 'DELIVERY') {
    showReceiveError(
      normalized,
      'Ticket Not Found',
      'No approved delivery ticket matches this reference in the registry.'
    );
    return;
  }

  AppState.activeDeliveryTicket = ticket;

  // Fill validated preview
  document.getElementById('m-del-ref').textContent = ticket.referenceNumber;
  document.getElementById('m-del-date').textContent = formatDate(ticket.date);
  document.getElementById('m-del-supplier').textContent = ticket.supplier || '—';

  let totalQty = 0;
  const tbody = document.getElementById('m-del-tbody');
  tbody.innerHTML = ticket.items.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const currentQty = matched ? (Number(matched.quantity) || 0) : 0;
    const incomingQty = Number(item.quantity) || 0;
    const newQty = currentQty + incomingQty;
    totalQty += incomingQty;
    return `<tr>
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(item.inkCode)}</td>
      <td class="px-3 py-2">${escapeHTML(item.brand || matched?.brand || '—')}</td>
      <td class="px-3 py-2">${escapeHTML(item.color || matched?.color || '—')}</td>
      <td class="px-3 py-2 text-right font-mono text-slate-500">${currentQty}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-blue-600">+${incomingQty}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-emerald-700">${newQty}</td>
    </tr>`;
  }).join('');

  document.getElementById('m-del-total-items').textContent = ticket.items.length;
  document.getElementById('m-del-total-qty').textContent = totalQty;

  // Keep search visible; show validated preview in front below it
  document.getElementById('receive-step-search')?.classList.remove('hidden');
  document.getElementById('receive-step-error')?.classList.add('hidden');
  document.getElementById('receive-step-preview')?.classList.remove('hidden');
  document.getElementById('receive-step-success')?.classList.add('hidden');

  document.getElementById('receive-step-preview')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}


function resolveDepartmentCode(ticketDept) {
  const d = (ticketDept || '').toUpperCase().trim();
  if (!d) return null;
  if (d === 'ACCT' || d.includes('ACCOUNT') || d.includes('ACCTG')) return 'ACCT';
  if (d === 'BD' || d.includes('BUSINESS DEV')) return 'BD';
  if (d === 'BMS') return 'BMS';
  if (d.includes('LOGISTIC') || d.includes('AMEC ICT') || d === 'LOGISTICS') return 'LOGISTICS';
  if (d.includes('PRODUCT') || d.includes('PACKAG')) return 'PRODUCTION';
  if (d.includes('PURCHAS')) return 'PURCHASING';
  if (d === 'QC' || d.includes('QUALITY') || d.includes('LAB')) return 'QC';
  // Exact department code match
  const codes = [...new Set(RELEASE_LOCATIONS.map(r => r.department))];
  if (codes.includes(d)) return d;
  return null;
}

function populateReleaseLocationsForDepartment(ticketDept) {
  const select = document.getElementById('modal-rel-location');
  const hint = document.getElementById('m-rel-location-hint');
  const autoBox = document.getElementById('m-rel-location-auto');
  const autoText = document.getElementById('m-rel-location-auto-text');
  const help = document.getElementById('m-rel-location-help');
  if (!select) return;

  const code = resolveDepartmentCode(ticketDept);
  select.innerHTML = '<option value="">— Select location —</option>';
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (hint) {
    hint.classList.add('hidden');
    hint.classList.remove('text-rose-600');
  }

  let options = [];
  if (code) {
    options = RELEASE_LOCATIONS.filter(r => r.department === code);
  }

  if (options.length === 0) {
    if (hint) {
      hint.textContent = ticketDept
        ? `No mapped locations for department “${ticketDept}”. Check the ticket department or master list.`
        : 'Ticket has no department. Cannot select a location.';
      hint.classList.remove('hidden');
      hint.classList.add('text-rose-600');
    }
    select.disabled = true;
    select.value = '';
    if (help) help.classList.remove('hidden');
    return;
  }

  // Only one office for this department → auto-apply, hide dropdown
  if (options.length === 1) {
    const only = options[0];
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = only.location;
    opt.setAttribute('data-dept', only.department);
    opt.textContent = `${only.department} — ${only.location}`;
    select.appendChild(opt);
    select.value = only.location;
    select.classList.add('hidden');
    select.disabled = false;
    if (autoBox) autoBox.classList.remove('hidden');
    if (autoText) autoText.textContent = `${only.department} — ${only.location}`;
    if (help) help.classList.add('hidden');
    if (hint) hint.classList.add('hidden');
    AppState.selectedReleaseLocation = only.location;
    AppState.selectedReleaseDepartment = only.department;
    return;
  }

  // Multiple locations → show dropdown to choose
  options.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.location;
    opt.setAttribute('data-dept', r.department);
    opt.textContent = `${r.department} — ${r.location}`;
    select.appendChild(opt);
  });
  select.disabled = false;
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (help) {
    help.textContent = `Select where the toner will be issued for ${code}.`;
    help.classList.remove('hidden');
  }
  if (hint) {
    hint.textContent = `${options.length} locations for ${code}. Choose one.`;
    hint.classList.remove('hidden', 'text-rose-600');
  }
  AppState.selectedReleaseLocation = '';
  AppState.selectedReleaseDepartment = code || '';
}

function showReleaseError(ref, title, desc) {
  const errRef = document.getElementById('release-error-ref');
  const errTitle = document.getElementById('release-error-title');
  const errDesc = document.getElementById('release-error-desc');
  if (errRef) errRef.textContent = ref || '—';
  if (errTitle) errTitle.textContent = title || 'Ticket Not Found';
  if (errDesc) errDesc.textContent = desc || 'No approved release ticket matches this reference.';

  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.remove('hidden');
  document.getElementById('release-step-preview')?.classList.add('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  AppState.activeReleaseTicket = null;

  document.getElementById('release-step-error')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function searchAndDisplayReleaseModal(refNumber) {
  const normalized = normalizeRefNumber(refNumber);
  if (!normalized) {
    showToast('Please enter a release reference number.', 'warning');
    return;
  }

  if (TicketService.isAlreadyProcessed(normalized)) {
    showReleaseError(
      normalized,
      'Ticket Already Processed',
      'This release ticket was already executed. Duplicate stock movements are blocked.'
    );
    return;
  }

  const ticket = TicketService.findByReferenceNumber(normalized);
  // Not found, wrong type, or not approved → error, no process button
  if (!ticket || ticket.type !== 'RELEASE' || ticket.status !== 'APPROVED') {
    showReleaseError(
      normalized,
      'Ticket Not Found',
      'This ticket was not found or is not approved yet. Confirm & Process is unavailable.'
    );
    return;
  }

  AppState.activeReleaseTicket = ticket;

  document.getElementById('m-rel-ref').textContent = ticket.referenceNumber;
  document.getElementById('m-rel-date').textContent = formatDate(ticket.date);
  document.getElementById('m-rel-dept').textContent = ticket.department || 'General';
  document.getElementById('m-rel-givento').textContent = ticket.givenTo || 'Staff';
  document.getElementById('m-rel-purpose').textContent = ticket.purpose || '';

  // Only show locations that belong to this ticket's department
  populateReleaseLocationsForDepartment(ticket.department);

  let totalQty = 0;
  let hasInsufficient = false;
  const tbody = document.getElementById('m-rel-tbody');
  // 1 ticket = 1 toner: only show primary item at quantity 1
  const displayItems = (ticket.items && ticket.items.length) ? [ticket.items[0]] : [];
  tbody.innerHTML = displayItems.map(item => {
    const matched = AppState.inks.find(i => i.inkCode.toUpperCase() === item.inkCode.toUpperCase());
    const available = matched ? (Number(matched.quantity) || 0) : 0;
    const req = 1;
    const after = available - req;
    totalQty += req;
    const isShort = after < 0;
    if (isShort) hasInsufficient = true;
    const statusBadge = isShort
      ? '<span class="text-xs font-bold px-2 py-0.5 rounded bg-rose-100 text-rose-700">Insufficient</span>'
      : '<span class="text-xs font-bold px-2 py-0.5 rounded bg-emerald-100 text-emerald-700">OK</span>';
    return `<tr class="${isShort ? 'bg-rose-50' : ''}">
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(item.inkCode)}</td>
      <td class="px-3 py-2">${escapeHTML(item.brand || matched?.brand || '—')}</td>
      <td class="px-3 py-2 text-right font-mono">${available}</td>
      <td class="px-3 py-2 text-right font-bold font-mono text-emerald-600">-${req}</td>
      <td class="px-3 py-2 text-right font-mono ${isShort ? 'text-rose-600 font-bold' : ''}">${after}</td>
      <td class="px-3 py-2 text-center">${statusBadge}</td>
    </tr>`;
  }).join('');

  document.getElementById('m-rel-total-items').textContent = 1;
  document.getElementById('m-rel-total-qty').textContent = 1;

  const errorBox = document.getElementById('m-rel-stock-error');
  const badge = document.getElementById('m-rel-validation-badge');
  const processBtn = document.getElementById('btn-modal-process-rel');
  const tryAnotherBtn = document.getElementById('btn-release-try-another');
  const validatedBanner = document.getElementById('m-rel-validated-banner');

  if (hasInsufficient) {
    if (errorBox) {
      errorBox.textContent = 'Insufficient stock for one or more items. Cannot process this release.';
      errorBox.classList.remove('hidden');
    }
    if (badge) {
      badge.textContent = 'Stock Insufficient';
      badge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-rose-100 text-rose-800';
    }
    // Hide Confirm & Process — show Try another ticket instead
    if (processBtn) processBtn.classList.add('hidden');
    if (tryAnotherBtn) tryAnotherBtn.classList.remove('hidden');
    if (validatedBanner) {
      validatedBanner.className = 'flex items-center gap-2 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800';
      validatedBanner.innerHTML = `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div><div class="text-sm font-bold">Cannot Process — Stock Insufficient</div>
        <div class="text-xs opacity-80">Inventory must be replenished before this release can be executed.</div></div>`;
    }
    // Preview panel border red
    const preview = document.getElementById('release-step-preview');
    if (preview) {
      preview.classList.remove('border-emerald-200');
      preview.classList.add('border-rose-200');
    }
  } else {
    if (errorBox) errorBox.classList.add('hidden');
    if (badge) {
      badge.textContent = 'Stock Verified';
      badge.className = 'text-xs px-2.5 py-1 font-bold rounded-md bg-emerald-100 text-emerald-800';
    }
    if (processBtn) {
      processBtn.classList.remove('hidden');
      processBtn.disabled = false;
    }
    if (tryAnotherBtn) tryAnotherBtn.classList.add('hidden');
    if (validatedBanner) {
      validatedBanner.className = 'flex items-center gap-2 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800';
      validatedBanner.innerHTML = `<svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <div><div class="text-sm font-bold">Ticket Validated</div>
        <div class="text-xs opacity-80">Approved release ticket found. Review stock impact below, then confirm to decrement inventory.</div></div>`;
    }
    const preview = document.getElementById('release-step-preview');
    if (preview) {
      preview.classList.remove('border-rose-200');
      preview.classList.add('border-emerald-200');
    }
  }

  document.getElementById('release-step-search')?.classList.remove('hidden');
  document.getElementById('release-step-error')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.classList.remove('hidden');
  document.getElementById('release-step-success')?.classList.add('hidden');
  document.getElementById('release-step-preview')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}


// ==========================================
// 20. EVENT LISTENERS
// ==========================================
function setupEventListeners() {
  // ---------- Sidebar Navigation ----------
  document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
      const pageId = link.id.replace('nav-', '');
      if (['dashboard', 'inventory', 'transactions', 'reports'].includes(pageId)) {
        navigateTo(pageId);
      }
    });
  });

  // ---------- Mobile Sidebar Toggle ----------
  const btnMobileMenu = document.getElementById('mobile-menu-toggle');
  const sidebar = document.getElementById('sidebar');
  const sidebarBackdrop = document.getElementById('sidebar-backdrop');

  if (btnMobileMenu && sidebar && sidebarBackdrop) {
    btnMobileMenu.addEventListener('click', () => {
      sidebar.classList.remove('-translate-x-full');
      sidebarBackdrop.classList.remove('hidden');
    });
    sidebarBackdrop.addEventListener('click', () => {
      sidebar.classList.add('-translate-x-full');
      sidebarBackdrop.classList.add('hidden');
    });
  }

  // ---------- Dashboard Quick Action Buttons → open modals ----------
  const quickReceive = document.getElementById('quick-btn-receive');
  const quickRelease = document.getElementById('quick-btn-release');
  const quickDefective = document.getElementById('quick-btn-defective');
  if (quickReceive) quickReceive.addEventListener('click', openReceiveModal);
  if (quickRelease) quickRelease.addEventListener('click', openReleaseModal);
  if (quickDefective) quickDefective.addEventListener('click', openDefectiveModal);

  // Defective modal controls
  const btnCloseDef = document.getElementById('btn-close-defective-modal');
  const btnCancelDef = document.getElementById('btn-modal-cancel-def');
  const btnSearchDef = document.getElementById('btn-modal-search-def');
  const modalDefRef = document.getElementById('modal-def-ref');
  const btnProcessDef = document.getElementById('btn-modal-process-def');
  const btnDefDone = document.getElementById('btn-defective-done');
  const btnDefErrBack = document.getElementById('btn-defective-error-back');
  if (btnCloseDef) btnCloseDef.addEventListener('click', closeDefectiveModal);
  if (btnCancelDef) btnCancelDef.addEventListener('click', closeDefectiveModal);
  if (btnDefDone) btnDefDone.addEventListener('click', () => {
    closeDefectiveModal();
    AppState.filters.transactionType = 'DEFECTIVE';
    navigateTo('transactions');
  });
  if (btnDefErrBack) btnDefErrBack.addEventListener('click', () => {
    resetDefectiveModal();
    const input = document.getElementById('modal-def-ref');
    if (input) { input.value = ''; input.focus(); }
  });
  if (btnSearchDef && modalDefRef) {
    btnSearchDef.addEventListener('click', () => searchDefectiveIssuance(modalDefRef.value));
    modalDefRef.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); searchDefectiveIssuance(modalDefRef.value); }
    });
  }
  document.querySelectorAll('.sample-def-ref').forEach(btn => {
    btn.addEventListener('click', () => {
      const ref = btn.getAttribute('data-ref');
      const input = document.getElementById('modal-def-ref');
      if (input) input.value = ref;
      searchDefectiveIssuance(ref);
    });
  });
  if (btnProcessDef) {
    btnProcessDef.addEventListener('click', () => {
      if (!AppState.activeDefectiveTxn) return;
      const notes = document.getElementById('modal-def-notes')?.value || '';
      const refNum = AppState.activeDefectiveTxn.referenceNumber;
      (async () => {
        try {
          if (AppState.useBackend) {
            await apiRecordDefective({ action: 'flag', referenceNumber: refNum, notes });
            await loadFromBackend();
            renderDashboard(); renderInventory(); renderTransactions(); renderCharts(); renderAlerts();
            showToast(`Issuance ${refNum} flagged as defective.`, 'success');
            pushNotification('warning', 'Defective Return', `Ticket ${refNum} marked defective.`, { source: 'action' });
          } else {
            const ok = TicketService.processDefectiveReturn(refNum, notes);
            if (!ok) return;
          }
          document.getElementById('m-def-success-ref').textContent = refNum;
          document.getElementById('defective-step-search')?.classList.add('hidden');
          document.getElementById('defective-step-error')?.classList.add('hidden');
          document.getElementById('defective-step-preview')?.classList.add('hidden');
          document.getElementById('defective-step-success')?.classList.remove('hidden');
        } catch (e) {
          showToast(e.message || 'Failed to flag defective.', 'error');
        }
      })();
    });
  }

  // ---------- Notification bell ----------
  const btnNotif = document.getElementById('btn-notifications');
  const notifPanel = document.getElementById('notif-panel');
  const btnClearNotifs = document.getElementById('btn-clear-notifs');

  if (btnNotif && notifPanel) {
    btnNotif.addEventListener('click', (e) => {
      e.stopPropagation();
      const isHidden = notifPanel.classList.contains('hidden');
      notifPanel.classList.toggle('hidden');
      if (isHidden) {
        // Mark all as read when opened
        (AppState.notifications || []).forEach(n => { n.read = true; });
        renderNotifications();
      }
    });
    // Close when clicking outside
    document.addEventListener('click', (e) => {
      if (!notifPanel.classList.contains('hidden') && !notifPanel.contains(e.target) && e.target !== btnNotif && !btnNotif.contains(e.target)) {
        notifPanel.classList.add('hidden');
      }
    });
  }
  if (btnClearNotifs) {
    btnClearNotifs.addEventListener('click', () => {
      AppState.notifications = [];
      renderNotifications();
    });
  }
  // ---------- Logout ----------
  const btnLogout = document.getElementById('btn-logout');
  const btnCancelLogout = document.getElementById('btn-cancel-logout');
  const btnConfirmLogout = document.getElementById('btn-confirm-logout');
  if (btnLogout) btnLogout.addEventListener('click', openLogoutModal);
  if (btnCancelLogout) btnCancelLogout.addEventListener('click', closeLogoutModal);
  if (btnConfirmLogout) {
    btnConfirmLogout.addEventListener('click', () => {
      closeLogoutModal();
      showToast('Signing out…', 'info');
      window.location.href = 'logout.php';
    });
  }


  // ---------- Sample reference clicks (works in modals too) ----------
  document.addEventListener('click', (e) => {
    const el = e.target.closest('.sample-ref');
    if (!el) return;
    e.preventDefault();
    e.stopPropagation();
    const ref = el.getAttribute('data-ref');
    if (!ref) return;
    if (ref.startsWith('DEL-')) {
      const receiveModal = document.getElementById('modal-receive');
      const alreadyOpen = receiveModal && !receiveModal.classList.contains('hidden');
      if (!alreadyOpen) openReceiveModal();
      const input = document.getElementById('modal-del-ref');
      if (input) input.value = ref;
      // slight delay so modal is painted if just opened
      setTimeout(() => searchAndDisplayDeliveryModal(ref), alreadyOpen ? 0 : 50);
    } else if (ref.startsWith('REL-')) {
      const releaseModal = document.getElementById('modal-release');
      const alreadyOpen = releaseModal && !releaseModal.classList.contains('hidden');
      if (!alreadyOpen) openReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) input.value = ref;
      setTimeout(() => searchAndDisplayReleaseModal(ref), alreadyOpen ? 0 : 50);
    }
  });

  // ---------- Receive Modal ----------
  const btnCloseReceive = document.getElementById('btn-close-receive-modal');
  const btnModalSearchDel = document.getElementById('btn-modal-search-del');
  const modalDelRef = document.getElementById('modal-del-ref');
  const btnModalProcessDel = document.getElementById('btn-modal-process-del');
  const btnModalCancelDel = document.getElementById('btn-modal-cancel-del');
  const btnReceiveDone = document.getElementById('btn-receive-done');

  if (btnCloseReceive) btnCloseReceive.addEventListener('click', closeReceiveModal);
  if (btnModalCancelDel) btnModalCancelDel.addEventListener('click', closeReceiveModal);
  if (btnReceiveDone) btnReceiveDone.addEventListener('click', () => {
    closeReceiveModal();
    navigateTo('dashboard');
  });


  const btnReceiveErrorBack = document.getElementById('btn-receive-error-back');
  if (btnReceiveErrorBack) {
    btnReceiveErrorBack.addEventListener('click', () => {
      resetReceiveModal();
      const input = document.getElementById('modal-del-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }

  // Legacy ticket search button removed — MRR search uses btn-modal-search-mrr
  if (btnModalSearchDel) {
    btnModalSearchDel.addEventListener('click', () => searchMrrLookup());
  }

  if (btnModalProcessDel) {
    btnModalProcessDel.addEventListener('click', recordManualDelivery);
  }
  // MRR search (Receive Delivery)
  const btnSearchMrr = document.getElementById('btn-modal-search-mrr');
  if (btnSearchMrr) {
    btnSearchMrr.addEventListener('click', (e) => {
      e.preventDefault();
      searchMrrLookup();
    });
  }
  if (modalDelRef) {
    modalDelRef.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchMrrLookup();
      }
    });
  }

  // ---------- Release Modal ----------
  const btnCloseRelease = document.getElementById('btn-close-release-modal');
  const btnModalSearchRel = document.getElementById('btn-modal-search-rel');
  const modalRelRef = document.getElementById('modal-rel-ref');
  const btnModalProcessRel = document.getElementById('btn-modal-process-rel');
  const btnModalCancelRel = document.getElementById('btn-modal-cancel-rel');
  const btnReleaseDone = document.getElementById('btn-release-done');

  if (btnCloseRelease) btnCloseRelease.addEventListener('click', closeReleaseModal);
  if (btnModalCancelRel) btnModalCancelRel.addEventListener('click', closeReleaseModal);
  if (btnReleaseDone) btnReleaseDone.addEventListener('click', () => {
    closeReleaseModal();
    navigateTo('dashboard');
  });


  const btnReleaseErrorBack = document.getElementById('btn-release-error-back');
  if (btnReleaseErrorBack) {
    btnReleaseErrorBack.addEventListener('click', () => {
      resetReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }
  const btnReleaseTryAnother = document.getElementById('btn-release-try-another');
  if (btnReleaseTryAnother) {
    btnReleaseTryAnother.addEventListener('click', () => {
      resetReleaseModal();
      const input = document.getElementById('modal-rel-ref');
      if (input) { input.value = ''; input.focus(); }
    });
  }

  if (btnModalSearchRel && modalRelRef) {
    btnModalSearchRel.addEventListener('click', () => searchAndDisplayReleaseModal(modalRelRef.value));
    modalRelRef.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayReleaseModal(modalRelRef.value);
      }
    });
  }

  if (btnModalProcessRel) {
    btnModalProcessRel.addEventListener('click', recordManualIssuance);
  const hasYieldCb = document.getElementById('modal-rel-has-yield');
  if (hasYieldCb) {
    hasYieldCb.addEventListener('change', () => {
      const wrap = document.getElementById('modal-rel-yield-wrap');
      if (wrap) wrap.classList.toggle('hidden', !hasYieldCb.checked);
      if (!hasYieldCb.checked) {
        const y = document.getElementById('modal-rel-yield');
        if (y) y.value = '';
      } else {
        document.getElementById('modal-rel-yield')?.focus();
      }
    });
  }


  const relDept = document.getElementById('modal-rel-dept');
  if (relDept) {
    relDept.addEventListener('change', () => {
      fillLocationsForDept(relDept.value, 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
    });
  }
  const relLoc = document.getElementById('modal-rel-location');
  if (relLoc) relLoc.addEventListener('change', onReleaseLocationChange);
  const btnCloseLoc = document.getElementById('btn-close-locations');
  if (btnCloseLoc) btnCloseLoc.addEventListener('click', closeLocationsModal);
  const btnLocSave = document.getElementById('btn-loc-save');
  if (btnLocSave) btnLocSave.addEventListener('click', saveLocationRow);
  const btnLocClear = document.getElementById('btn-loc-clear');
  if (btnLocClear) btnLocClear.addEventListener('click', () => {
    document.getElementById('loc-edit-id').value = '';
    document.getElementById('loc-edit-dept').value = '';
    document.getElementById('loc-edit-location').value = '';
    document.getElementById('loc-edit-printer').value = '';
  });

  }

  const modalRelDept = document.getElementById('modal-rel-dept');
  if (modalRelDept) {
    modalRelDept.addEventListener('change', () => {
      fillLocationsForDept(modalRelDept.value, 'modal-rel-location', 'm-rel-location-auto', 'm-rel-location-auto-text');
    });
  }
  const modalRelToner = document.getElementById('modal-rel-toner');
  if (modalRelToner) {
    modalRelToner.addEventListener('change', () => {
      const opt = modalRelToner.options[modalRelToner.selectedIndex];
      const hint = document.getElementById('modal-rel-stock-hint');
      if (hint && opt && opt.value) {
        const q = opt.getAttribute('data-qty') || '0';
        hint.textContent = `${opt.value}: ${q} unit(s) on hand · issuance uses 1 unit`;
      } else if (hint) hint.textContent = '';
    });
  }



  // Close action modals when clicking the shared backdrop (but not the cards themselves)
  // Backdrop is visual only — not clickable. Modals close only via their buttons.
  const modalBackdrop = document.getElementById('modal-backdrop');
  if (modalBackdrop) {
    modalBackdrop.addEventListener('click', (e) => {
      // Intentionally do nothing when clicking the gray/blurred background
      e.stopPropagation();
    });
  }

  // Delivery Search
  const delSearchInput = document.getElementById('input-del-ref');
  const btnDelSearch = document.getElementById('btn-search-delivery');
  if (btnDelSearch && delSearchInput) {
    btnDelSearch.addEventListener('click', () => {
      searchAndDisplayDelivery(delSearchInput.value);
    });
    delSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayDelivery(delSearchInput.value);
      }
    });
  }

  // Delivery Demo Quick Buttons
  ['btn-demo-del-1', 'btn-demo-del-2', 'btn-demo-del-3'].forEach(btnId => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', () => {
        const ref = btn.getAttribute('data-ref');
        delSearchInput.value = ref;
        searchAndDisplayDelivery(ref);
      });
    }
  });

  // Delivery Actions
  const btnProcessDel = document.getElementById('btn-process-delivery');
  if (btnProcessDel) {
    btnProcessDel.addEventListener('click', () => {
      if (!AppState.activeDeliveryTicket) return;
      const success = TicketService.processDeliveryTicket(AppState.activeDeliveryTicket);
      if (success) {
        document.getElementById('del-ticket-preview').classList.add('hidden');
        delSearchInput.value = '';
        AppState.activeDeliveryTicket = null;
      }
    });
  }

  const btnDelCancel = document.getElementById('btn-cancel-del');
  if (btnDelCancel) {
    btnDelCancel.addEventListener('click', () => {
      document.getElementById('del-ticket-preview').classList.add('hidden');
      delSearchInput.value = '';
      AppState.activeDeliveryTicket = null;
    });
  }

  // Release Search
  const relSearchInput = document.getElementById('input-rel-ref');
  const btnRelSearch = document.getElementById('btn-search-release');
  if (btnRelSearch && relSearchInput) {
    btnRelSearch.addEventListener('click', () => {
      searchAndDisplayRelease(relSearchInput.value);
    });
    relSearchInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        searchAndDisplayRelease(relSearchInput.value);
      }
    });
  }

  // Release Demo Quick Buttons
  ['btn-demo-rel-1', 'btn-demo-rel-2', 'btn-demo-rel-3'].forEach(btnId => {
    const btn = document.getElementById(btnId);
    if (btn) {
      btn.addEventListener('click', () => {
        const ref = btn.getAttribute('data-ref');
        relSearchInput.value = ref;
        searchAndDisplayRelease(ref);
      });
    }
  });

  // Release Actions
  const btnProcessRel = document.getElementById('btn-process-release');
  if (btnProcessRel) {
    btnProcessRel.addEventListener('click', () => {
      if (!AppState.activeReleaseTicket) return;
      const success = TicketService.processReleaseTicket(AppState.activeReleaseTicket);
      if (success) {
        document.getElementById('rel-ticket-preview').classList.add('hidden');
        relSearchInput.value = '';
        AppState.activeReleaseTicket = null;
      }
    });
  }

  const btnRelCancel = document.getElementById('btn-cancel-rel');
  if (btnRelCancel) {
    btnRelCancel.addEventListener('click', () => {
      document.getElementById('rel-ticket-preview').classList.add('hidden');
      relSearchInput.value = '';
      AppState.activeReleaseTicket = null;
    });
  }

  // Inventory Filters (IDs must match the HTML: filter-inv-search / filter-inv-status)
  const invSearch = document.getElementById('filter-inv-search');
  const invStatus = document.getElementById('filter-inv-status');
  const btnInvClear = document.getElementById('btn-clear-inv-filters') || document.getElementById('btn-inv-clear');

  if (invSearch) {
    invSearch.addEventListener('input', (e) => {
      AppState.filters.inventorySearch = e.target.value || '';
      renderInventory();
    });
    invSearch.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        invSearch.value = '';
        AppState.filters.inventorySearch = '';
        renderInventory();
      }
    });
  }
  if (invStatus) {
    invStatus.addEventListener('change', (e) => {
      AppState.filters.inventoryStatus = e.target.value || 'ALL';
      renderInventory();
    });
  }
  if (btnInvClear) {
    btnInvClear.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      resetInventoryFilters();
    });
  }
  // Safety: also bind by id in case DOM was re-rendered
  document.getElementById('btn-clear-inv-filters')?.addEventListener('click', (e) => {
    e.preventDefault();
    resetInventoryFilters();
  });


  // Stock card: click inventory row
  const invTbody = document.getElementById('inventory-tbody');
  if (invTbody) {
    invTbody.addEventListener('click', (e) => {
      const removeBtn = e.target.closest('.btn-remove-toner');
      if (removeBtn) {
        e.preventDefault();
        e.stopPropagation();
        const code = removeBtn.getAttribute('data-ink-code');
        if (code) openRemoveTonerModal(code);
        return;
      }
      const row = e.target.closest('tr.inventory-row');
      if (!row) return;
      const code = row.getAttribute('data-ink-code');
      if (code) openStockCard(code);
    });
  }
  const btnAddToner = document.getElementById('btn-add-toner');
  if (btnAddToner) btnAddToner.addEventListener('click', openAddTonerModal);

  const navLocations = document.getElementById('nav-locations');
  if (navLocations) navLocations.addEventListener('click', () => navigateTo('locations'));
  const navLogs = document.getElementById('nav-logs');
  if (navLogs) navLogs.addEventListener('click', () => navigateTo('logs'));
  const navSettings = document.getElementById('nav-email-config');
  if (navSettings) navSettings.addEventListener('click', () => navigateTo('email-config'));
  const navUsers = document.getElementById('nav-users');
  if (navUsers) navUsers.addEventListener('click', () => navigateTo('users'));
  const formEmailSettings = document.getElementById('form-email-settings');
  if (formEmailSettings) formEmailSettings.addEventListener('submit', saveEmailSettings);
  const btnTestEmail = document.getElementById('btn-test-email-settings');
  if (btnTestEmail) btnTestEmail.addEventListener('click', testEmailSettings);
  const btnRefreshLogs = document.getElementById('btn-refresh-logs');
  if (btnRefreshLogs) btnRefreshLogs.addEventListener('click', loadSystemLogs);

  const btnApplyLogs = document.getElementById('btn-apply-logs-filter');
  if (btnApplyLogs) btnApplyLogs.addEventListener('click', loadSystemLogs);
  const btnClearLogs = document.getElementById('btn-clear-logs-filter');
  if (btnClearLogs) btnClearLogs.addEventListener('click', resetLogsFilters);
  const logsPeriod = document.getElementById('filter-logs-period');
  if (logsPeriod) logsPeriod.addEventListener('change', () => {
    syncLogsCustomRangeUI();
    if (logsPeriod.value !== 'CUSTOM') loadSystemLogs();
  });
  const logsAction = document.getElementById('filter-logs-action');
  if (logsAction) logsAction.addEventListener('change', loadSystemLogs);
  const logsActor = document.getElementById('filter-logs-actor');
  if (logsActor) logsActor.addEventListener('change', loadSystemLogs);

  const filterLogs = document.getElementById('filter-logs-search');
  if (filterLogs) {
    let logTimer;
    filterLogs.addEventListener('input', () => {
      clearTimeout(logTimer);
      logTimer = setTimeout(loadSystemLogs, 300);
    });
  }
  document.querySelectorAll('.loc-sup-tab').forEach(btn => {
    btn.addEventListener('click', () => switchLocSupTab(btn.getAttribute('data-loc-tab')));
  });
  const btnPageLocSave = document.getElementById('btn-page-loc-save');
  if (btnPageLocSave) btnPageLocSave.addEventListener('click', savePageLocationRow);
  const btnPageSupAdd = document.getElementById('btn-page-supplier-add');
  if (btnPageSupAdd) btnPageSupAdd.addEventListener('click', addPageSupplier);
  const pageSupInput = document.getElementById('page-supplier-name');
  if (pageSupInput) pageSupInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); addPageSupplier(); } });

  const btnCloseEditLoc = document.getElementById('btn-close-edit-location');
  const btnCancelEditLoc = document.getElementById('btn-cancel-edit-location');
  const btnSaveEditLoc = document.getElementById('btn-save-edit-location');
  const editLocBackdrop = document.getElementById('modal-edit-location-backdrop');
  if (btnCloseEditLoc) btnCloseEditLoc.addEventListener('click', closeEditLocationModal);
  if (btnCancelEditLoc) btnCancelEditLoc.addEventListener('click', closeEditLocationModal);
  if (btnSaveEditLoc) btnSaveEditLoc.addEventListener('click', saveEditLocationModal);
  if (editLocBackdrop) editLocBackdrop.addEventListener('click', closeEditLocationModal);

  const btnPageLocClear = document.getElementById('btn-page-loc-clear');
  const filterLocSearch = document.getElementById('filter-locations-search');
  if (filterLocSearch) {
    let locSearchTimer;
    filterLocSearch.addEventListener('input', () => {
      clearTimeout(locSearchTimer);
      locSearchTimer = setTimeout(() => renderPageLocationsTable(), 200);
    });
  }
  const btnLocSearchClear = document.getElementById('btn-locations-search-clear');
  if (btnLocSearchClear) {
    btnLocSearchClear.addEventListener('click', () => {
      const inp = document.getElementById('filter-locations-search');
      if (inp) inp.value = '';
      renderPageLocationsTable();
    });
  }

  if (btnPageLocClear) btnPageLocClear.addEventListener('click', () => {
    document.getElementById('page-loc-edit-id').value = '';
    document.getElementById('page-loc-dept').value = '';
    document.getElementById('page-loc-location').value = '';
    document.getElementById('page-loc-printer').value = '';
  });
  const btnAddUser = document.getElementById('btn-add-user');
  if (btnAddUser) btnAddUser.addEventListener('click', () => openUserModal(null));
  const btnCloseUser = document.getElementById('btn-close-user-modal');
  const btnCancelUser = document.getElementById('btn-cancel-user');
  const btnSaveUser = document.getElementById('btn-save-user');
  if (btnCloseUser) btnCloseUser.addEventListener('click', closeUserModal);
  if (btnCancelUser) btnCancelUser.addEventListener('click', closeUserModal);
  if (btnSaveUser) btnSaveUser.addEventListener('click', saveUser);
  const btnCloseUserSaved = document.getElementById('btn-close-user-saved');
  const userSavedBackdrop = document.getElementById('modal-user-saved-backdrop');
  if (btnCloseUserSaved) btnCloseUserSaved.addEventListener('click', closeUserSavedModal);
  if (userSavedBackdrop) userSavedBackdrop.addEventListener('click', closeUserSavedModal);

  const btnAddPrinterRow = document.getElementById('btn-add-printer-row');
  if (btnAddPrinterRow) {
    btnAddPrinterRow.addEventListener('click', () => {
      const current = [...document.querySelectorAll('#add-toner-printer-list .add-printer-input')].map(el => el.value || '');
      current.push('');
      renderAddPrinterRows(current);
      const inputs = document.querySelectorAll('#add-toner-printer-list .add-printer-input');
      inputs[inputs.length - 1]?.focus();
    });
  }
  const btnCloseAddToner = document.getElementById('btn-close-add-toner');
  const btnCancelAddToner = document.getElementById('btn-cancel-add-toner');
  const btnSaveAddToner = document.getElementById('btn-save-add-toner');
  if (btnCloseAddToner) btnCloseAddToner.addEventListener('click', closeAddTonerModal);
  if (btnCancelAddToner) btnCancelAddToner.addEventListener('click', closeAddTonerModal);
  if (btnSaveAddToner) btnSaveAddToner.addEventListener('click', saveNewToner);
  const btnCancelRemoveToner = document.getElementById('btn-cancel-remove-toner');
  const btnConfirmRemoveToner = document.getElementById('btn-confirm-remove-toner');
  if (btnCancelRemoveToner) btnCancelRemoveToner.addEventListener('click', closeRemoveTonerModal);
  if (btnConfirmRemoveToner) btnConfirmRemoveToner.addEventListener('click', confirmRemoveToner);
  const btnCloseRelDetail = document.getElementById('btn-close-release-detail');
  const btnRelDetailDone = document.getElementById('btn-release-detail-done');
  const relDetailBackdrop = document.getElementById('modal-release-detail-backdrop');
  if (btnCloseRelDetail) btnCloseRelDetail.addEventListener('click', closeReleaseDetailModal);
  if (btnRelDetailDone) btnRelDetailDone.addEventListener('click', closeReleaseDetailModal);
  if (relDetailBackdrop) relDetailBackdrop.addEventListener('click', closeReleaseDetailModal);
  const btnCloseDefDetail = document.getElementById('btn-close-defective-detail');
  const btnDefDetailDone = document.getElementById('btn-defective-detail-done');
  const defDetailBackdrop = document.getElementById('modal-defective-detail-backdrop');
  if (btnCloseDefDetail) btnCloseDefDetail.addEventListener('click', closeDefectiveDetailModal);
  if (btnDefDetailDone) btnDefDetailDone.addEventListener('click', closeDefectiveDetailModal);
  if (defDetailBackdrop) defDetailBackdrop.addEventListener('click', closeDefectiveDetailModal);
  const btnAppConfirmOk = document.getElementById('btn-app-confirm-ok');
  const btnAppConfirmCancel = document.getElementById('btn-app-confirm-cancel');
  const appConfirmBackdrop = document.getElementById('modal-app-confirm-backdrop');
  if (btnAppConfirmOk) btnAppConfirmOk.addEventListener('click', () => closeAppConfirm(true));
  if (btnAppConfirmCancel) btnAppConfirmCancel.addEventListener('click', () => closeAppConfirm(false));
  if (appConfirmBackdrop) appConfirmBackdrop.addEventListener('click', () => closeAppConfirm(false));
  const btnCloseStock = document.getElementById('btn-close-stock-card');
  
  const btnStockEdit = document.getElementById('btn-stock-card-edit');
  if (btnStockEdit) btnStockEdit.addEventListener('click', showStockCardEditPanel);

  const stockEditBackdrop = document.getElementById('stock-card-edit-backdrop');
  if (stockEditBackdrop) {
    stockEditBackdrop.addEventListener('click', hideStockCardEditPanel);
  }

  const btnStockEditCancel = document.getElementById('btn-stock-card-edit-cancel');
  const btnStockEditCancel2 = document.getElementById('btn-stock-card-edit-cancel-2');
  if (btnStockEditCancel) btnStockEditCancel.addEventListener('click', hideStockCardEditPanel);
  if (btnStockEditCancel2) btnStockEditCancel2.addEventListener('click', hideStockCardEditPanel);

  const btnStockDone = document.getElementById('btn-stock-card-done');
  const btnStockSave = document.getElementById('btn-stock-card-save');
  if (btnStockSave) btnStockSave.addEventListener('click', saveStockCard);
  if (btnCloseStock) btnCloseStock.addEventListener('click', closeStockCard);
  if (btnStockDone) btnStockDone.addEventListener('click', closeStockCard);

  const stockCardPeriod = document.getElementById('stock-card-period');
  if (stockCardPeriod) stockCardPeriod.addEventListener('change', () => {
    syncStockCardCustomRangeUI();
    if (stockCardPeriod.value !== 'CUSTOM') renderStockCardMovements();
  });
  const btnStockCardApplyDates = document.getElementById('btn-stock-card-apply-dates');
  if (btnStockCardApplyDates) btnStockCardApplyDates.addEventListener('click', renderStockCardMovements);
  const btnStockCardResetDates = document.getElementById('btn-stock-card-reset-dates');
  if (btnStockCardResetDates) btnStockCardResetDates.addEventListener('click', () => {
    const p = document.getElementById('stock-card-period');
    const f = document.getElementById('stock-card-from');
    const toEl = document.getElementById('stock-card-to');
    if (p) p.value = 'ALL';
    if (f) f.value = '';
    if (toEl) toEl.value = '';
    syncStockCardCustomRangeUI();
    renderStockCardMovements();
  });


  // Transactions Filters, Tabs & Export
  document.querySelectorAll('.txn-main-tab').forEach(btn => {
    btn.addEventListener('click', () => {
      AppState.filters.transactionType = btn.getAttribute('data-txn-tab');
      if (AppState.filters.transactionType === 'RECEIVED') {
        AppState.filters.transactionDept = 'ALL';
      }
      try {
        localStorage.setItem('toner_ui_txn_tab', AppState.filters.transactionType || 'RECEIVED');
        localStorage.setItem('toner_ui_txn_dept', AppState.filters.transactionDept || 'ALL');
      } catch (_) {}
      renderTransactions();
    });
  });
  // Dept tabs are rebuilt dynamically — use delegated click
  const deptTabsHost = document.getElementById('txn-dept-tabs');
  if (deptTabsHost && !deptTabsHost._deptBound) {
    deptTabsHost._deptBound = true;
    deptTabsHost.addEventListener('click', (e) => {
      const btn = e.target.closest('.txn-dept-tab');
      if (!btn) return;
      AppState.filters.transactionDept = btn.getAttribute('data-txn-dept') || 'ALL';
      try { localStorage.setItem('toner_ui_txn_dept', AppState.filters.transactionDept); } catch (_) {}
      renderTransactions();
    });
  }

  
  const txnsTbody = document.getElementById('txns-tbody');
  if (txnsTbody && !txnsTbody._defActionsBound) {
    txnsTbody._defActionsBound = true;
    txnsTbody.addEventListener('click', (e) => {
      const relViewBtn = e.target.closest('.btn-rel-view');
      if (relViewBtn) {
        openReleaseDetailModal(relViewBtn.getAttribute('data-ref'), relViewBtn.getAttribute('data-id'));
        return;
      }
      const viewBtn = e.target.closest('.btn-def-view');
      if (viewBtn) {
        openDefectiveDetailModal(viewBtn.getAttribute('data-ref'));
        return;
      }
      const sendBtn = e.target.closest('.btn-def-send');
      if (sendBtn) {
        e.preventDefault();
        sendDefectiveToSupplier(sendBtn.getAttribute('data-ref'));
        return;
      }
      const recvBtn = e.target.closest('.btn-def-receive');
      if (recvBtn) {
        e.preventDefault();
        openReceiveReplacementModal(recvBtn.getAttribute('data-ref'), recvBtn.getAttribute('data-code'));
      }
    });
  }
  const btnCloseDefRep = document.getElementById('btn-close-def-replace');
  const btnCancelDefRep = document.getElementById('btn-cancel-def-replace');
  const btnConfirmDefRep = document.getElementById('btn-confirm-def-replace');
  const defRepBackdrop = document.getElementById('modal-defective-replace-backdrop');
  if (btnCloseDefRep) btnCloseDefRep.addEventListener('click', closeReceiveReplacementModal);
  if (btnCancelDefRep) btnCancelDefRep.addEventListener('click', closeReceiveReplacementModal);
  if (btnConfirmDefRep) btnConfirmDefRep.addEventListener('click', confirmReceiveReplacement);
  if (defRepBackdrop) defRepBackdrop.addEventListener('click', closeReceiveReplacementModal);

  
  const dashDate = document.getElementById('filter-dash-date');
  if (dashDate) {
    dashDate.addEventListener('change', (e) => {
      AppState.filters.dashboardDate = e.target.value || 'MONTH';
      if (AppState.filters.dashboardDate !== 'CUSTOM') {
        AppState.filters.dashboardDateFrom = '';
        AppState.filters.dashboardDateTo = '';
      }
      syncDashCustomRangeUI();
      renderDashboard();
      renderCharts();
    });
  }
  const dashFrom = document.getElementById('filter-dash-from');
  const dashTo = document.getElementById('filter-dash-to');
  const btnDashApply = document.getElementById('btn-dash-apply-range');
  if (dashFrom) dashFrom.addEventListener('change', () => {
    AppState.filters.dashboardDateFrom = dashFrom.value || '';
  });
  if (dashTo) dashTo.addEventListener('change', () => {
    AppState.filters.dashboardDateTo = dashTo.value || '';
  });
  if (btnDashApply) {
    btnDashApply.addEventListener('click', () => {
      AppState.filters.dashboardDate = 'CUSTOM';
      AppState.filters.dashboardDateFrom = document.getElementById('filter-dash-from')?.value || '';
      AppState.filters.dashboardDateTo = document.getElementById('filter-dash-to')?.value || '';
      const sel = document.getElementById('filter-dash-date');
      if (sel) sel.value = 'CUSTOM';
      syncDashCustomRangeUI();
      renderDashboard();
      renderCharts();
    });
  }

  
  document.querySelectorAll('.kpi-card').forEach(card => {
    card.addEventListener('click', () => openKpiDetail(card.getAttribute('data-kpi')));
  });
  const btnCloseKpi = document.getElementById('btn-close-kpi-detail');
  const kpiBackdrop = document.getElementById('modal-kpi-detail-backdrop');
  if (btnCloseKpi) btnCloseKpi.addEventListener('click', closeKpiDetail);
  if (kpiBackdrop) kpiBackdrop.addEventListener('click', closeKpiDetail);
  const btnCloseDup = document.getElementById('btn-close-dup-modal');
  const dupBackdrop = document.getElementById('modal-duplicate-backdrop');
  if (btnCloseDup) btnCloseDup.addEventListener('click', closeDuplicateModal);
  if (dupBackdrop) dupBackdrop.addEventListener('click', closeDuplicateModal);
  const btnMailLog = document.getElementById('btn-open-mail-log');
  if (btnMailLog) btnMailLog.addEventListener('click', openMailLogModal);
  const btnCloseMail = document.getElementById('btn-close-mail-log');
  const btnRefreshMail = document.getElementById('btn-refresh-mail-log');
  const btnClearMail = document.getElementById('btn-clear-mail-log');
  const mailBackdrop = document.getElementById('modal-mail-log-backdrop');
  if (btnCloseMail) btnCloseMail.addEventListener('click', closeMailLogModal);
  if (btnRefreshMail) btnRefreshMail.addEventListener('click', refreshMailLog);
  if (btnClearMail) btnClearMail.addEventListener('click', clearMailLog);
  if (mailBackdrop) mailBackdrop.addEventListener('click', closeMailLogModal);

  const txnSearch = document.getElementById('filter-txn-search');
  const txnDate = document.getElementById('filter-txn-date');
  const btnTxnExport = document.getElementById('btn-export-txns');
  const btnClearTxnFilters = document.getElementById('btn-clear-txn-filters');
  if (btnClearTxnFilters) {
    btnClearTxnFilters.addEventListener('click', () => {
      AppState.filters.transactionSearch = '';
      AppState.filters.transactionDate = 'MONTH';
      AppState.filters.transactionDateFrom = '';
      AppState.filters.transactionDateTo = '';
      AppState.filters.transactionDept = 'ALL';
      const s = document.getElementById('filter-txn-search');
      const d = document.getElementById('filter-txn-date');
      const f = document.getElementById('filter-txn-from');
      const to = document.getElementById('filter-txn-to');
      if (s) s.value = '';
      if (d) d.value = 'MONTH';
      if (f) f.value = '';
      if (to) to.value = '';
      if (typeof syncTxnCustomRangeUI === 'function') syncTxnCustomRangeUI();
      if (typeof updateTxnTabUI === 'function') updateTxnTabUI();
      renderTransactions();
    });
  }


  if (txnSearch) {
    const applyTxnSearch = () => {
      AppState.filters.transactionSearch = txnSearch.value || '';
      renderTransactions();
    };
    txnSearch.addEventListener('input', applyTxnSearch);
    txnSearch.addEventListener('keyup', applyTxnSearch);
    txnSearch.addEventListener('search', applyTxnSearch);
  }
  if (txnDate) {
    txnDate.addEventListener('change', (e) => {
      AppState.filters.transactionDate = e.target.value;
      if (e.target.value !== 'CUSTOM') {
        AppState.filters.transactionDateFrom = '';
        AppState.filters.transactionDateTo = '';
      }
      try { localStorage.setItem('toner_ui_txn_date', AppState.filters.transactionDate || 'ALL'); } catch (_) {}
      renderTransactions();
    });
  }
  const txnFrom = document.getElementById('filter-txn-from');
  const txnTo = document.getElementById('filter-txn-to');
  const btnTxnApplyRange = document.getElementById('btn-txn-apply-range');
  if (txnFrom) {
    txnFrom.addEventListener('change', () => {
      AppState.filters.transactionDateFrom = txnFrom.value || '';
    });
  }
  if (txnTo) {
    txnTo.addEventListener('change', () => {
      AppState.filters.transactionDateTo = txnTo.value || '';
    });
  }
  if (btnTxnApplyRange) {
    btnTxnApplyRange.addEventListener('click', () => {
      AppState.filters.transactionDate = 'CUSTOM';
      AppState.filters.transactionDateFrom = document.getElementById('filter-txn-from')?.value || '';
      AppState.filters.transactionDateTo = document.getElementById('filter-txn-to')?.value || '';
      const sel = document.getElementById('filter-txn-date');
      if (sel) sel.value = 'CUSTOM';
      if (!AppState.filters.transactionDateFrom && !AppState.filters.transactionDateTo) {
        showToast('Select a From and/or To date for the custom range.', 'warning');
        return;
      }
      if (AppState.filters.transactionDateFrom && AppState.filters.transactionDateTo
          && AppState.filters.transactionDateFrom > AppState.filters.transactionDateTo) {
        showToast('From date cannot be after To date.', 'warning');
        return;
      }
      renderTransactions();
    });
  }
  if (btnTxnExport) {
    btnTxnExport.addEventListener('click', exportTransactionsToCSV);
  }

  // Reports
  const reportFilterDate = document.getElementById('report-date-filter') || document.getElementById('report-filter-date');
  const btnPrintReport = document.getElementById('btn-print-report');
  if (reportFilterDate) {
    reportFilterDate.addEventListener('change', (e) => {
      AppState.filters.reportDate = e.target.value;
      renderReports();
    });
  }
  if (btnPrintReport) {
    btnPrintReport.addEventListener('click', () => {
      window.print();
    });
  }

  // Modal Buttons (duplicate modal wired with blur backdrop elsewhere)

  const btnCloseNf = document.getElementById('btn-close-notfound-modal') || document.getElementById('btn-close-nf-modal');
  if (btnCloseNf) btnCloseNf.addEventListener('click', closeNotFoundModal);

  // Reset Demo Modal
  const btnResetDemo = document.getElementById('btn-reset-demo');
  const btnCancelReset = document.getElementById('btn-cancel-reset');
  const btnConfirmReset = document.getElementById('btn-confirm-reset');

  if (btnResetDemo) btnResetDemo.addEventListener('click', openResetModal);
  if (btnCancelReset) btnCancelReset.addEventListener('click', closeResetModal);
  if (btnConfirmReset) {
    btnConfirmReset.addEventListener('click', () => {
      StorageService.resetAllDemoData();
      initializeDataSafely();
      closeResetModal();
      navigateTo('dashboard');
      showToast('System inventory, tickets, and transactions reset to original demo state!', 'success');
    });
  }
}

// ==========================================
// 21. APPLICATION INITIALIZATION
// ==========================================

// ---------- User Management (API: users.php) ----------
async function loadUsers() {
  const tbody = document.getElementById('users-tbody');
  try {
    const data = await apiRequest('users.php');
    AppState.users = data.users || [];
    renderUsers();
  } catch (e) {
    if (tbody) {
      tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-rose-600 text-sm">${escapeHTML(e.message || 'Failed to load users')}</td></tr>`;
    }
  }
}

function renderUsers() {
  const tbody = document.getElementById('users-tbody');
  if (!tbody) return;
  const users = AppState.users || [];
  if (!users.length) {
    tbody.innerHTML = `<tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">No admin users yet. Click Add Admin.</td></tr>`;
    return;
  }
  tbody.innerHTML = users.map(u => {
    const status = u.isActive
      ? '<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-emerald-100 text-emerald-800">Active</span>'
      : '<span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-slate-100 text-slate-600">Inactive</span>';
    return `<tr class="hover:bg-slate-50">
      <td class="px-4 py-3 font-mono text-sm font-semibold text-slate-900">${escapeHTML(u.username)}</td>
      <td class="px-4 py-3 text-slate-700">${escapeHTML(u.fullName || '—')}</td>
      <td class="px-4 py-3">${status}</td>
      <td class="px-4 py-3">
        <div class="flex flex-wrap gap-2">
          <button type="button" class="btn-edit-user text-xs font-semibold text-blue-600 hover:underline" data-id="${u.id}">Edit</button>
          <button type="button" class="btn-delete-user text-xs font-semibold text-rose-600 hover:underline" data-id="${u.id}" data-username="${escapeHTML(u.username)}">Delete</button>
        </div>
      </td>
    </tr>`;
  }).join('');

  tbody.querySelectorAll('.btn-edit-user').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const u = (AppState.users || []).find(x => x.id === id);
      if (u) openUserModal(u);
    });
  });
  tbody.querySelectorAll('.btn-delete-user').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const name = btn.getAttribute('data-username') || '';
      if (!confirm(`Delete admin "${name}"? They will no longer be able to sign in.`)) return;
      try {
        await apiRequest('users.php', { method: 'DELETE', body: { id } });
        showToast('User deleted.', 'success');
        await loadUsers();
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

function openUserModal(user) {
  const modal = document.getElementById('modal-user');
  const backdrop = document.getElementById('modal-backdrop');
  const title = document.getElementById('modal-user-title');
  const idEl = document.getElementById('user-edit-id');
  const userEl = document.getElementById('user-username');
  const nameEl = document.getElementById('user-fullname');
  const passEl = document.getElementById('user-password');
  const activeWrap = document.getElementById('user-active-wrap');
  const activeEl = document.getElementById('user-active');
  const passHint = document.getElementById('user-pass-hint');
  const passReq = document.getElementById('user-pass-req');

  if (user) {
    title.textContent = 'Edit Admin';
    idEl.value = user.id;
    userEl.value = user.username;
    userEl.disabled = true;
    nameEl.value = user.fullName || '';
    passEl.value = '';
    passHint.classList.remove('hidden');
    passReq.classList.add('hidden');
    activeWrap.classList.remove('hidden');
    activeEl.checked = !!user.isActive;
  } else {
    title.textContent = 'Add Admin';
    idEl.value = '';
    userEl.value = '';
    userEl.disabled = false;
    nameEl.value = '';
    passEl.value = '';
    passHint.classList.add('hidden');
    passReq.classList.remove('hidden');
    activeWrap.classList.add('hidden');
  }
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

function closeUserModal() {
  const modal = document.getElementById('modal-user');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
}


function openUserSavedModal({ isNew, fullName, username, isActive }) {
  const title = document.getElementById('user-saved-title');
  const msg = document.getElementById('user-saved-message');
  const nameEl = document.getElementById('user-saved-name');
  const emailEl = document.getElementById('user-saved-email');
  const statusEl = document.getElementById('user-saved-status');
  if (title) title.textContent = isNew ? 'Admin created' : 'Admin updated';
  if (msg) {
    msg.textContent = isNew
      ? 'The new admin account was saved successfully.'
      : 'The admin details were updated successfully.';
  }
  if (nameEl) nameEl.textContent = fullName || '—';
  if (emailEl) emailEl.textContent = username || '—';
  if (statusEl) statusEl.textContent = isActive === false ? 'Inactive' : 'Active';
  // Close edit modal/backdrop so only success modal is highlighted
  try { closeUserModal(); } catch (_) {}
  const sharedBackdrop = document.getElementById('modal-backdrop');
  if (sharedBackdrop) sharedBackdrop.classList.add('hidden');
  const modal = document.getElementById('modal-user-saved');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
    modal.style.zIndex = '10080';
  }
  document.body.classList.add('overflow-hidden');
}

function closeUserSavedModal() {
  const modal = document.getElementById('modal-user-saved');
  if (modal) {
    modal.classList.add('hidden');
    modal.style.display = 'none';
  }
  document.body.classList.remove('overflow-hidden');
}

async function saveUser() {
  const id = document.getElementById('user-edit-id')?.value;
  const ok = await appConfirm({
    title: id ? 'Update admin' : 'Create admin',
    message: id ? 'Save changes to this admin account?' : 'Create this new admin account?',
    confirmText: 'Save'
  });
  if (!ok) return;
  const username = (document.getElementById('user-username')?.value || '').trim().toLowerCase();
  const fullName = (document.getElementById('user-fullname')?.value || '').trim();
  const password = document.getElementById('user-password')?.value || '';
  const isActive = document.getElementById('user-active')?.checked;

  try {
    let isNew = false;
    if (id) {
      const body = { id: parseInt(id, 10), fullName, isActive };
      if (password) body.password = password;
      await apiRequest('users.php', { method: 'PUT', body });
    } else {
      if (!username) { showToast('Email is required.', 'warning'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(username)) { showToast('Enter a valid email address.', 'warning'); return; }
      if (!password || password.length < 6) { showToast('Password must be at least 6 characters.', 'warning'); return; }
      await apiRequest('users.php', { method: 'POST', body: { username, password, fullName, role: 'admin' } });
      isNew = true;
    }
    closeUserModal();
    await loadUsers();
    openUserSavedModal({
      isNew,
      fullName: fullName || '—',
      username: username || '—',
      isActive: isActive !== false
    });
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}



// ---------- Locations (editable dept / location / printer) ----------
async function loadReleaseLocations() {
  try {
    const data = await apiRequest('locations.php');
    AppState.releaseLocations = data.locations || [];
  } catch (e) {
    console.warn('[Toner] locations load failed, using static list', e);
    AppState.releaseLocations = (typeof RELEASE_LOCATIONS !== 'undefined' ? RELEASE_LOCATIONS : []).map((r, i) => ({
      id: -(i + 1),
      department: r.department,
      location: r.location,
      printerName: r.printerName || '',
      isActive: true
    }));
  }
}

async function loadAdminUsersForIssuance() {
  try {
    const data = await apiRequest('users.php');
    AppState.adminUsers = (data.users || []).filter(u => u.isActive !== false);
  } catch (e) {
    AppState.adminUsers = [];
  }
  try {
    const me = await apiRequest('me.php', { silent: true });
    AppState.currentUser = me;
  } catch (e) {
    AppState.currentUser = { username: 'admin', fullName: 'Admin' };
  }
  updateSidebarUser();
}

function populateDeptSelect() {
  const sel = document.getElementById('modal-rel-dept');
  if (!sel) return;
  const current = sel.value;
  const depts = [...new Set((AppState.releaseLocations || []).map(r => (r.department || '').toUpperCase()).filter(Boolean))].sort();
  sel.innerHTML = '<option value="">— Select department —</option>' +
    depts.map(d => `<option value="${escapeHTML(d)}">${escapeHTML(d)}</option>`).join('');
  if (current && depts.includes(current)) sel.value = current;
}

function populateIssuedBySelect() {
  const sel = document.getElementById('modal-rel-issued-by');
  const recorded = document.getElementById('modal-rel-recorded-by');
  if (recorded) {
    const me = AppState.currentUser;
    recorded.value = me ? (me.fullName ? `${me.fullName} (${me.username})` : (me.username || '')) : '';
  }
  if (!sel) return;
  const users = AppState.adminUsers || [];
  const meUser = AppState.currentUser?.username || '';
  sel.innerHTML = '<option value="">— Select admin —</option>' +
    users.map(u => {
      const label = u.fullName ? `${u.fullName} (${u.username})` : u.username;
      return `<option value="${escapeHTML(u.username)}">${escapeHTML(label)}</option>`;
    }).join('');
  if (meUser) sel.value = meUser;
}

function fillLocationsForDept(deptCode, selectId, autoWrapId, autoTextId) {
  const select = document.getElementById(selectId);
  const autoBox = document.getElementById(autoWrapId);
  const autoText = document.getElementById(autoTextId);
  const printerEl = document.getElementById('modal-rel-printer');
  if (!select) return;
  select.innerHTML = '<option value="">— Select location —</option>';
  select.classList.remove('hidden');
  if (autoBox) autoBox.classList.add('hidden');
  if (printerEl) printerEl.value = '';
  const code = (deptCode || '').toUpperCase();
  const options = (AppState.releaseLocations || []).filter(r => (r.department || '').toUpperCase() === code);
  if (!code) {
    select.innerHTML = '<option value="">— Select department first —</option>';
    return;
  }
  if (options.length === 0) {
    select.innerHTML = '<option value="">— No locations — add under Locations & Suppliers —</option>';
    return;
  }
  if (options.length === 1) {
    const only = options[0];
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = only.location;
    opt.textContent = only.location;
    opt.dataset.printer = only.printerName || '';
    select.appendChild(opt);
    select.value = only.location;
    select.classList.add('hidden');
    if (autoBox) autoBox.classList.remove('hidden');
    if (autoText) autoText.textContent = only.department + ' — ' + only.location;
    if (printerEl) printerEl.value = only.printerName || '—';
    return;
  }
  options.forEach(r => {
    const opt = document.createElement('option');
    opt.value = r.location;
    opt.textContent = r.location;
    opt.dataset.printer = r.printerName || '';
    select.appendChild(opt);
  });
}

function onReleaseLocationChange() {
  const locSel = document.getElementById('modal-rel-location');
  const printerEl = document.getElementById('modal-rel-printer');
  if (!locSel || !printerEl) return;
  const opt = locSel.options[locSel.selectedIndex];
  printerEl.value = (opt && opt.dataset.printer) ? opt.dataset.printer : '—';
}

function renderLocationsTable() {
  const tbody = document.getElementById('locations-tbody');
  if (!tbody) return;
  const rows = AppState.releaseLocations || [];
  if (!rows.length) {
    tbody.innerHTML = '<tr><td colspan="4" class="px-3 py-6 text-center text-slate-400">No locations yet. Add one above.</td></tr>';
    return;
  }
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-3 py-2 font-mono font-semibold">${escapeHTML(r.department)}</td>
      <td class="px-3 py-2">${escapeHTML(r.location)}</td>
      <td class="px-3 py-2 text-slate-600">${escapeHTML(r.printerName || '—')}</td>
      <td class="px-3 py-2 space-x-2">
        <button type="button" class="btn-loc-edit text-xs font-semibold text-blue-600 hover:underline" data-id="${r.id}">Edit</button>
        <button type="button" class="btn-loc-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Delete</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-loc-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const row = (AppState.releaseLocations || []).find(x => x.id === id);
      if (!row) return;
      document.getElementById('loc-edit-id').value = row.id;
      document.getElementById('loc-edit-dept').value = row.department;
      document.getElementById('loc-edit-location').value = row.location;
      document.getElementById('loc-edit-printer').value = row.printerName || '';
    });
  });
  tbody.querySelectorAll('.btn-loc-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this location from issuance options?')) return;
      try {
        await apiRequest('locations.php', { method: 'DELETE', body: { id } });
        await loadReleaseLocations();
        renderLocationsTable();
        populateDeptSelect();
        showToast('Location removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

async function openLocationsModal() {
  await loadReleaseLocations();
  renderLocationsTable();
  document.getElementById('loc-edit-id').value = '';
  document.getElementById('loc-edit-dept').value = '';
  document.getElementById('loc-edit-location').value = '';
  document.getElementById('loc-edit-printer').value = '';
  const modal = document.getElementById('modal-locations');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop) backdrop.classList.remove('hidden');
  if (modal) modal.classList.remove('hidden');
}

function closeLocationsModal() {
  const modal = document.getElementById('modal-locations');
  if (modal) modal.classList.add('hidden');
  const anyOpen = document.querySelector('.modal-card:not(.hidden)');
  const backdrop = document.getElementById('modal-backdrop');
  if (backdrop && !anyOpen) backdrop.classList.add('hidden');
  populateDeptSelect();
}

async function saveLocationRow() {
  const id = document.getElementById('loc-edit-id')?.value;
  const department = (document.getElementById('loc-edit-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('loc-edit-location')?.value || '').trim();
  const printerName = (document.getElementById('loc-edit-printer')?.value || '').trim();
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  try {
    if (id) {
      await apiRequest('locations.php', { method: 'PUT', body: { id: parseInt(id, 10), department, location, printerName } });
      showToast('Location updated.', 'success');
    } else {
      await apiRequest('locations.php', { method: 'POST', body: { department, location, printerName } });
      showToast('Location added.', 'success');
    }
    await loadReleaseLocations();
    renderLocationsTable();
    document.getElementById('loc-edit-id').value = '';
    document.getElementById('loc-edit-dept').value = '';
    document.getElementById('loc-edit-location').value = '';
    document.getElementById('loc-edit-printer').value = '';
    populateDeptSelect();
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}




async function loadSuppliers() {
  try {
    const data = await apiRequest('suppliers.php');
    AppState.suppliers = data.suppliers || [];
  } catch (e) {
    console.warn('[Toner] suppliers load failed', e);
    AppState.suppliers = AppState.suppliers || [];
  }
}

function populateStockCardSupplierSelect(current) {
  const sel = document.getElementById('stock-card-supplier-input');
  if (!sel) return;
  const list = (AppState.suppliers || []).map(s => s.name).filter(Boolean);
  const cur = (current || '').trim();
  if (cur && !list.some(n => n.toUpperCase() === cur.toUpperCase())) list.push(cur);
  list.sort((a, b) => a.localeCompare(b));
  sel.innerHTML = '<option value="">— Select supplier —</option>' +
    list.map(n => {
      const selAttr = cur && n.toUpperCase() === cur.toUpperCase() ? ' selected' : '';
      return `<option value="${escapeHTML(n)}"${selAttr}>${escapeHTML(n)}</option>`;
    }).join('');
}

function renderPageSuppliersTable() {
  const tbody = document.getElementById('page-suppliers-tbody');
  const empty = document.getElementById('page-suppliers-empty');
  const countEl = document.getElementById('page-supplier-count');
  if (!tbody) return;
  const rows = AppState.suppliers || [];
  if (countEl) countEl.textContent = rows.length + (rows.length === 1 ? ' supplier' : ' suppliers');
  if (!rows.length) {
    tbody.innerHTML = '';
    if (empty) empty.classList.remove('hidden');
    return;
  }
  if (empty) empty.classList.add('hidden');
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-semibold text-slate-900">${escapeHTML(r.name)}</td>
      <td class="px-5 py-3">
        <button type="button" class="btn-page-sup-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Remove</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-page-sup-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this supplier from the list?')) return;
      try {
        await apiRequest('suppliers.php', { method: 'DELETE', body: { id } });
        await loadSuppliers();
        renderPageSuppliersTable();
        showToast('Supplier removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Remove failed', 'error');
      }
    });
  });
}

async function addPageSupplier() {
  const name = (document.getElementById('page-supplier-name')?.value || '').trim();
  if (!name) {
    showToast('Supplier name is required.', 'warning');
    return;
  }
  try {
    await apiRequest('suppliers.php', { method: 'POST', body: { name } });
    document.getElementById('page-supplier-name').value = '';
    await loadSuppliers();
    renderPageSuppliersTable();
    showToast('Supplier added.', 'success');
  } catch (e) {
    showToast(e.message || 'Add failed', 'error');
  }
}


function switchLocSupTab(tab) {
  tab = tab === 'suppliers' ? 'suppliers' : 'locations';
  try { localStorage.setItem('toner_ui_loc_tab', tab); } catch (_) {}
  const isLoc = tab === 'locations';
  const panelLoc = document.getElementById('panel-locations');
  const panelSup = document.getElementById('panel-suppliers');
  const tabLoc = document.getElementById('tab-locations');
  const tabSup = document.getElementById('tab-suppliers');
  if (panelLoc) panelLoc.classList.toggle('hidden', !isLoc);
  if (panelSup) panelSup.classList.toggle('hidden', isLoc);
  const active = 'flex-1 px-4 py-3 text-sm font-semibold text-blue-700 bg-white border-b-2 border-blue-600 transition-colors';
  const idle = 'flex-1 px-4 py-3 text-sm font-semibold text-slate-500 hover:text-slate-800 hover:bg-slate-50 border-b-2 border-transparent transition-colors';
  if (tabLoc) tabLoc.className = 'loc-sup-tab ' + (isLoc ? active : idle);
  if (tabSup) tabSup.className = 'loc-sup-tab ' + (!isLoc ? active : idle);
  if (!isLoc) renderPageSuppliersTable();
  else renderPageLocationsTable();
}

async function loadLocationsPage() {
  await loadReleaseLocations();
  await loadSuppliers();
  let tab = 'locations';
  try {
    const saved = localStorage.getItem('toner_ui_loc_tab');
    if (saved === 'suppliers' || saved === 'locations') tab = saved;
  } catch (_) {}
  switchLocSupTab(tab);
  renderPageLocationsTable();
  renderPageSuppliersTable();
}

function renderPageLocationsTable() {
  const tbody = document.getElementById('page-locations-tbody');
  const empty = document.getElementById('page-locations-empty');
  const countEl = document.getElementById('page-loc-count');
  if (!tbody) return;
  const q = (document.getElementById('filter-locations-search')?.value || '').trim().toLowerCase();
  const allRows = AppState.releaseLocations || [];
  const rows = !q ? allRows : allRows.filter(r => {
    const blob = [r.department, r.location, r.printerName, r.printer]
      .map(x => String(x || '').toLowerCase())
      .join(' ');
    return blob.includes(q);
  });
  if (countEl) {
    countEl.textContent = q
      ? `${rows.length} of ${allRows.length} location${allRows.length === 1 ? '' : 's'}`
      : (allRows.length + (allRows.length === 1 ? ' location' : ' locations'));
  }
  if (!allRows.length) {
    tbody.innerHTML = '';
    if (empty) {
      empty.textContent = 'No locations yet. Add department, location, and optional printer above.';
      empty.classList.remove('hidden');
    }
    return;
  }
  if (!rows.length) {
    tbody.innerHTML = '';
    if (empty) {
      empty.textContent = 'No locations match your search.';
      empty.classList.remove('hidden');
    }
    return;
  }
  if (empty) empty.classList.add('hidden');
  tbody.innerHTML = rows.map(r => `
    <tr class="hover:bg-slate-50">
      <td class="px-5 py-3 font-mono font-semibold text-slate-900">${escapeHTML(r.department)}</td>
      <td class="px-5 py-3">${escapeHTML(r.location)}</td>
      <td class="px-5 py-3 text-slate-600">${escapeHTML(r.printerName || '—')}</td>
      <td class="px-5 py-3 space-x-3">
        <button type="button" class="btn-page-loc-edit text-xs font-semibold text-blue-600 hover:underline" data-id="${r.id}">Edit</button>
        <button type="button" class="btn-page-loc-del text-xs font-semibold text-rose-600 hover:underline" data-id="${r.id}">Delete</button>
      </td>
    </tr>
  `).join('');
  tbody.querySelectorAll('.btn-page-loc-edit').forEach(btn => {
    btn.addEventListener('click', () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      const row = (AppState.releaseLocations || []).find(x => x.id === id);
      if (!row) return;
      openEditLocationModal(row);
    });
  });
  tbody.querySelectorAll('.btn-page-loc-del').forEach(btn => {
    btn.addEventListener('click', async () => {
      const id = parseInt(btn.getAttribute('data-id'), 10);
      if (!confirm('Remove this location from issuance options?')) return;
      try {
        await apiRequest('locations.php', { method: 'DELETE', body: { id } });
        await loadReleaseLocations();
        renderPageLocationsTable();
        if (typeof renderLocationsTable === 'function') renderLocationsTable();
        populateDeptSelect();
        showToast('Location removed.', 'success');
      } catch (e) {
        showToast(e.message || 'Delete failed', 'error');
      }
    });
  });
}

async function savePageLocationRow() {
  const ok = await appConfirm({ title: 'Save location', message: 'Add or update this location and printer assigned?', confirmText: 'Save' });
  if (!ok) return;

  const department = (document.getElementById('page-loc-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('page-loc-location')?.value || '').trim();
  const printerName = (document.getElementById('page-loc-printer')?.value || '').trim();
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  try {
    await apiRequest('locations.php', { method: 'POST', body: { department, location, printerName } });
    showToast('Location added.', 'success');
    document.getElementById('page-loc-edit-id').value = '';
    document.getElementById('page-loc-dept').value = '';
    document.getElementById('page-loc-location').value = '';
    document.getElementById('page-loc-printer').value = '';
    await loadReleaseLocations();
    renderPageLocationsTable();
    if (typeof renderCharts === "function") renderCharts();
    if (typeof renderTxnDeptTabs === "function") renderTxnDeptTabs();
    if (typeof populateDeptSelect === "function") populateDeptSelect();
    if (typeof renderLocationsTable === 'function') renderLocationsTable();
    populateDeptSelect();
  } catch (e) {
    showToast(e.message || 'Save failed', 'error');
  }
}

function openEditLocationModal(row) {
  if (!row) return;
  document.getElementById('edit-loc-id').value = String(row.id);
  document.getElementById('edit-loc-dept').value = row.department || '';
  document.getElementById('edit-loc-location').value = row.location || '';
  document.getElementById('edit-loc-printer').value = row.printerName || '';
  const modal = document.getElementById('modal-edit-location');
  const dialog = document.getElementById('modal-edit-location-dialog');
  if (modal) {
    modal.classList.remove('hidden');
    modal.style.display = 'flex';
  }
  document.body.classList.add('overflow-hidden');
  // Pop-in animation
  requestAnimationFrame(() => {
    if (dialog) {
      dialog.style.transform = 'scale(1)';
      dialog.style.opacity = '1';
    }
  });
  setTimeout(() => document.getElementById('edit-loc-dept')?.focus(), 80);
}

function closeEditLocationModal() {
  const modal = document.getElementById('modal-edit-location');
  const dialog = document.getElementById('modal-edit-location-dialog');
  if (dialog) {
    dialog.style.transform = 'scale(0.96)';
    dialog.style.opacity = '0';
  }
  setTimeout(() => {
    if (modal) {
      modal.classList.add('hidden');
      modal.style.display = 'none';
    }
    document.body.classList.remove('overflow-hidden');
  }, 150);
}

async function saveEditLocationModal() {
  const id = parseInt(document.getElementById('edit-loc-id')?.value || '0', 10);
  const department = (document.getElementById('edit-loc-dept')?.value || '').trim().toUpperCase();
  const location = (document.getElementById('edit-loc-location')?.value || '').trim();
  const printerName = (document.getElementById('edit-loc-printer')?.value || '').trim();
  if (!id) { showToast('Missing location id.', 'error'); return; }
  if (!department || !location) {
    showToast('Department and location are required.', 'warning');
    return;
  }
  try {
    await apiRequest('locations.php', { method: 'PUT', body: { id, department, location, printerName } });
    showToast('Location updated.', 'success');
    closeEditLocationModal();
    await loadReleaseLocations();
    renderPageLocationsTable();
    if (typeof renderLocationsTable === 'function') renderLocationsTable();
    populateDeptSelect();
    if (typeof renderCharts === 'function') renderCharts();
    if (typeof renderTxnDeptTabs === 'function') renderTxnDeptTabs();
  } catch (e) {
    showToast(e.message || 'Update failed', 'error');
  }
}



document.addEventListener('DOMContentLoaded', async () => {
  setupEventListeners();
  loadReleaseLocations().catch(() => {});
  loadSuppliers().catch(() => {});
  loadAdminUsersForIssuance().catch(() => {});
  const online = await detectBackend();
  if (online) {
    try {
      await loadFromBackend();
      console.info('[Toner] Connected to PHP/SQL Server backend');
    } catch (e) {
      console.warn('[Toner] Backend load failed, using local demo', e);
      AppState.useBackend = false;
      initializeDataSafely();
    }
  } else {
    console.warn('[Toner] Backend offline — localStorage demo mode. Open api/health.php to debug.');
    showToast('Database offline — changes will NOT save to SQL Server.', 'warning');
    initializeDataSafely();
  }
  try {
    const savedPage = localStorage.getItem('toner_ui_page');
    const savedTab = localStorage.getItem('toner_ui_txn_tab');
    const savedDate = localStorage.getItem('toner_ui_txn_date');
    const savedDept = localStorage.getItem('toner_ui_txn_dept');
    if (savedTab && ['RECEIVED','RELEASED','DEFECTIVE'].includes(savedTab)) {
      AppState.filters.transactionType = savedTab;
    }
    if (savedDate) AppState.filters.transactionDate = savedDate;
    if (savedDept) AppState.filters.transactionDept = savedDept;
    // Sync date select UI
    const dateSel = document.getElementById('filter-txn-date');
    if (dateSel && savedDate) dateSel.value = savedDate;
    if (savedPage && ['dashboard','inventory','transactions','locations','logs','email-config','users'].includes(savedPage)) {
      navigateTo(savedPage);
    } else {
      navigateTo('dashboard');
    }
  } catch (_) { navigateTo('dashboard'); }

  renderAlerts();
});


  </script>



    
    <!-- EDIT LOCATION MODAL — full-screen pop-out with blur -->
    <div id="modal-edit-location" class="hidden" style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;">
      <div id="modal-edit-location-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
      <div id="modal-edit-location-dialog" style="position:relative;z-index:1;width:100%;max-width:28rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;overflow:hidden;transform:scale(0.96);opacity:0;transition:transform 0.15s ease,opacity 0.15s ease;">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between" style="background:linear-gradient(to right,#eff6ff,#ffffff);">
          <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-md">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            </div>
            <div class="min-w-0">
              <h3 class="text-base font-bold text-slate-900">Edit location</h3>
              <p class="text-xs text-slate-500">Update department, location, or printer</p>
            </div>
          </div>
          <button type="button" id="btn-close-edit-location" class="p-2 rounded-xl text-slate-400 hover:bg-white hover:text-slate-700 border border-transparent hover:border-slate-200">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="px-5 py-4 space-y-3">
          <input type="hidden" id="edit-loc-id" value="">
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-dept">Department <span class="text-rose-500">*</span></label>
            <input id="edit-loc-dept" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 uppercase font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-location">Location <span class="text-rose-500">*</span></label>
            <input id="edit-loc-location" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="edit-loc-printer">Printer assigned</label>
            <input id="edit-loc-printer" type="text" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
          </div>
        </div>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2">
          <button type="button" id="btn-cancel-edit-location" class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-xl hover:bg-white">Cancel</button>
          <button type="button" id="btn-save-edit-location" class="px-5 py-2 text-sm font-semibold bg-blue-600 text-white rounded-xl hover:bg-blue-700 shadow-sm">Save changes</button>
        </div>
      </div>
    </div>

    <!-- RECEIVE DEFECTIVE REPLACEMENT -->
    <div id="modal-defective-replace" class="hidden" style="position:fixed;inset:0;z-index:9999;display:none;align-items:center;justify-content:center;padding:1rem;">
      <div id="modal-defective-replace-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
      <div id="modal-defective-replace-dialog" style="position:relative;z-index:1;width:100%;max-width:28rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e2e8f0;overflow:hidden;">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3" style="background:linear-gradient(to right,#fff1f2,#ffffff);">
          <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-base font-bold text-slate-900">Receive supplier replacement</h3>
            <p class="text-xs text-slate-500">Stock will increase by 1 for this item</p>
          </div>
          <button type="button" id="btn-close-def-replace" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
          </button>
        </div>
        <div class="px-5 py-4 space-y-3">
          <input type="hidden" id="def-replace-ref" value="">
          <input type="hidden" id="def-replace-code" value="">
          <div class="rounded-xl bg-slate-50 border border-slate-100 px-3.5 py-2.5 space-y-1 text-sm">
            <div><span class="text-slate-500">Reference:</span> <span id="def-replace-ref-label" class="font-mono font-bold text-rose-700"></span></div>
            <div><span class="text-slate-500">Item:</span> <span id="def-replace-code-label" class="font-mono font-semibold"></span></div>
            <div><span class="text-slate-500">Description:</span> <span id="def-replace-desc-label" class="text-slate-800"></span></div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1">Recorded by (logged in)</label>
            <input id="def-replace-recorded-by" type="text" readonly class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-100 text-slate-600 cursor-not-allowed">
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-800 mb-1" for="def-replace-accepted-by">Accepted by (admin) <span class="text-rose-500">*</span></label>
            <select id="def-replace-accepted-by" class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-rose-500">
              <option value="">— Select admin —</option>
            </select>
            <p class="text-[11px] text-slate-500 mt-1">Admin who received the good unit from the supplier.</p>
          </div>
        </div>
        <div class="px-5 py-3.5 border-t border-slate-100 bg-slate-50 flex justify-end gap-2">
          <button type="button" id="btn-cancel-def-replace" class="px-4 py-2 text-sm font-medium text-slate-700 border border-slate-200 rounded-xl hover:bg-white">Cancel</button>
          <button type="button" id="btn-confirm-def-replace" class="px-5 py-2 text-sm font-semibold bg-rose-600 text-white rounded-xl hover:bg-rose-700">Confirm &amp; add to stock</button>
        </div>
      </div>
    </div>


<!-- Ticket Already Processed — full-screen blur -->
<div id="modal-duplicate" class="hidden" style="position:fixed;inset:0;z-index:10050;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-duplicate-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:26rem;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);border:1px solid #e4e4e7;overflow:hidden;">
    <div class="p-6">
      <div class="flex items-center gap-3 p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 mb-4">
        <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        <div>
          <div class="text-sm font-bold" id="modal-duplicate-title">Ticket Already Processed</div>
          <div class="text-xs opacity-80">This reference was already executed. Duplicate stock moves are blocked.</div>
        </div>
      </div>
      <p class="text-sm text-slate-600" id="modal-duplicate-desc">This ticket reference has already been used. Stock cannot be modified twice.</p>
      <div class="mt-4 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs font-mono text-slate-700" id="modal-duplicate-details">
        Reference Number: <span id="modal-dup-ref" class="font-bold text-slate-900"></span>
      </div>
      <div class="mt-6 flex justify-end">
        <button id="btn-close-dup-modal" type="button" class="px-4 py-2 text-sm font-semibold bg-zinc-900 text-white hover:bg-black rounded-xl transition-colors">Understood</button>
      </div>
    </div>
  </div>
</div>

<!-- KPI detail modal -->
<div id="modal-kpi-detail" class="hidden" style="position:fixed;inset:0;z-index:10040;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-kpi-detail-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.5);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:36rem;max-height:85vh;background:#fff;border-radius:1rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.3);border:1px solid #e4e4e7;overflow:hidden;display:flex;flex-direction:column;">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between shrink-0">
      <div>
        <h3 id="kpi-detail-title" class="text-base font-bold text-slate-900">Details</h3>
        <p id="kpi-detail-sub" class="text-xs text-slate-500 mt-0.5"></p>
      </div>
      <button type="button" id="btn-close-kpi-detail" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div id="kpi-detail-body" class="px-5 py-4 overflow-y-auto text-sm text-slate-700 flex-1"></div>
  </div>
</div>

<!-- Mail log modal — modern inbox-style -->
<div id="modal-mail-log" class="hidden" style="position:fixed;inset:0;z-index:10040;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-mail-log-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:32rem;max-height:88vh;background:#fafafa;border-radius:1.25rem;border:1px solid #e4e4e7;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">
    <div class="px-5 pt-5 pb-4 bg-white border-b border-zinc-100 shrink-0">
      <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3 min-w-0">
          <div class="w-11 h-11 rounded-2xl bg-zinc-900 text-white flex items-center justify-center shrink-0 shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
          </div>
          <div class="min-w-0">
            <h3 class="text-lg font-semibold text-zinc-900 tracking-tight">Email activity</h3>
            <p class="text-xs text-zinc-500 mt-0.5">Low-stock alerts and system notifications</p>
          </div>
        </div>
        <button type="button" id="btn-close-mail-log" class="p-2 rounded-xl text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 transition-colors" title="Close">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
      </div>
      <div class="mt-4 flex items-center gap-2">
        <button type="button" id="btn-refresh-mail-log" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full bg-zinc-100 text-zinc-700 hover:bg-zinc-200 transition-colors">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
          Refresh
        </button>
        <button type="button" id="btn-clear-mail-log" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium rounded-full text-rose-600 hover:bg-rose-50 transition-colors">
          Clear history
        </button>
        <span id="mail-log-count" class="ml-auto text-[11px] font-medium text-zinc-400"></span>
      </div>
    </div>
    <div id="mail-log-body" class="px-4 py-4 overflow-y-auto flex-1 space-y-3"></div>
  </div>
</div>


<!-- Admin saved — pop-out with blurred background -->
<div id="modal-user-saved" class="hidden" style="position:fixed;inset:0;z-index:10080;display:none;align-items:center;justify-content:center;padding:1.25rem;">
  <div id="modal-user-saved-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.6);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);"></div>
  <div id="modal-user-saved-card" style="position:relative;z-index:1;width:100%;max-width:24rem;background:#fff;border-radius:1.25rem;border:1px solid rgba(255,255,255,0.8);box-shadow:0 25px 60px -12px rgba(0,0,0,0.45), 0 0 0 1px rgba(15,23,42,0.06);overflow:hidden;transform:scale(1);animation:userSavedPop 0.22s ease-out;">
    <div class="p-6 text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-4 ring-8 ring-emerald-50/80">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
      </div>
      <h3 id="user-saved-title" class="text-lg font-bold text-slate-900 tracking-tight">Admin updated</h3>
      <p id="user-saved-message" class="text-sm text-slate-500 mt-2 leading-relaxed"></p>
      <div class="mt-4 rounded-xl bg-slate-50 border border-slate-100 px-4 py-3 text-left text-sm space-y-1.5">
        <div class="flex justify-between gap-3"><span class="text-slate-500">Name</span><span id="user-saved-name" class="font-semibold text-slate-900 text-right"></span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Email</span><span id="user-saved-email" class="font-mono text-xs text-slate-800 text-right break-all"></span></div>
        <div class="flex justify-between gap-3"><span class="text-slate-500">Status</span><span id="user-saved-status" class="font-semibold text-slate-900"></span></div>
      </div>
      <button type="button" id="btn-close-user-saved" class="mt-5 w-full px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black transition-colors">Done</button>
    </div>
  </div>
</div>
<style>
@keyframes userSavedPop {
  from { opacity: 0; transform: scale(0.94) translateY(8px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
</style>


<div id="modal-defective-detail" class="hidden" style="position:fixed;inset:0;z-index:10070;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-defective-detail-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:28rem;max-height:90vh;overflow:auto;background:#fff;border-radius:1.25rem;border:1px solid #e4e4e7;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">
    <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 sticky top-0 bg-white z-10">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Defective return details</h3>
        <p class="text-xs text-slate-500 mt-0.5 font-mono" id="def-detail-ref">—</p>
      </div>
      <button type="button" id="btn-close-defective-detail" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div class="p-5 space-y-1 text-sm" id="def-detail-body"></div>
    <div class="px-5 py-4 border-t border-slate-100 flex flex-wrap gap-2 justify-end sticky bottom-0 bg-white">
      <div id="def-detail-actions" class="flex flex-wrap gap-2 mr-auto"></div>
      <button type="button" id="btn-defective-detail-done" class="px-4 py-2 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Close</button>
    </div>
  </div>
</div>


<!-- App confirm modal (replaces browser confirm) -->
<div id="modal-app-confirm" class="hidden" style="position:fixed;inset:0;z-index:10090;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-app-confirm-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:24rem;background:#fff;border-radius:1.25rem;border:1px solid #e4e4e7;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);overflow:hidden;">
    <div class="p-6">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mb-4">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
      </div>
      <h3 id="app-confirm-title" class="text-lg font-bold text-slate-900">Confirm</h3>
      <p id="app-confirm-message" class="text-sm text-slate-600 mt-2 leading-relaxed"></p>
      <div class="mt-6 flex gap-2 justify-end">
        <button type="button" id="btn-app-confirm-cancel" class="px-4 py-2.5 text-sm font-medium rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50">Cancel</button>
        <button type="button" id="btn-app-confirm-ok" class="px-4 py-2.5 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Confirm</button>
      </div>
    </div>
  </div>
</div>


<div id="modal-release-detail" class="hidden" style="position:fixed;inset:0;z-index:10070;display:none;align-items:center;justify-content:center;padding:1rem;">
  <div id="modal-release-detail-backdrop" style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px);"></div>
  <div style="position:relative;z-index:1;width:100%;max-width:28rem;max-height:90vh;overflow:auto;background:#fff;border-radius:1.25rem;border:1px solid #e4e4e7;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);">
    <div class="px-5 py-4 border-b border-slate-100 flex items-start justify-between gap-3 sticky top-0 bg-white z-10">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Issuance details</h3>
        <p class="text-xs text-slate-500 mt-0.5 font-mono" id="rel-detail-ref">—</p>
      </div>
      <button type="button" id="btn-close-release-detail" class="p-2 rounded-xl text-slate-400 hover:bg-slate-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
      </button>
    </div>
    <div class="p-5 text-sm" id="rel-detail-body"></div>
    <div class="px-5 py-4 border-t border-slate-100 flex justify-end sticky bottom-0 bg-white">
      <button type="button" id="btn-release-detail-done" class="px-4 py-2 text-sm font-semibold rounded-xl bg-zinc-900 text-white hover:bg-black">Close</button>
    </div>
  </div>
</div>


<div id="global-loading" class="hidden" style="position:fixed;inset:0;z-index:2147483000;display:none;align-items:center;justify-content:center;padding:1rem;" aria-live="polite" aria-busy="true">
  <div style="position:absolute;inset:0;background:rgba(15,23,42,0.55);backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);"></div>
  <div style="position:relative;z-index:1;background:#fff;border-radius:1.25rem;padding:1.75rem 2rem;box-shadow:0 25px 50px -12px rgba(0,0,0,0.4);border:1px solid #e4e4e7;min-width:16rem;text-align:center;">
    <div style="width:2.5rem;height:2.5rem;margin:0 auto 0.75rem;border:3px solid #e4e4e7;border-top-color:#18181b;border-radius:9999px;animation:toner-spin 0.75s linear infinite;"></div>
    <div id="global-loading-text" class="text-sm font-semibold text-slate-800">Processing…</div>
    <div class="text-xs text-slate-500 mt-1">Please wait — do not close this window</div>
  </div>
</div>
<style>@keyframes toner-spin{to{transform:rotate(360deg)}}</style>

</body>
</html>