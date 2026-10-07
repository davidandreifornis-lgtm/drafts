<?php
require_once __DIR__ . '/config/auth_lib.php';
auth_require_login();

/**
 * Toner Inventory — thin shell (layout + includes only)
 */
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$scriptDir = rtrim($scriptDir, '/');
$apiBase = ($scriptDir === '' || $scriptDir === '.') ? '/api' : ($scriptDir . '/api');
$assetBase = ($scriptDir === '' || $scriptDir === '.') ? '' : $scriptDir;

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
  <script src="https://cdn.tailwindcss.com"></script>
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
              50: '#f8fafc', 100: '#f1f5f9', 500: '#0f172a',
              600: '#0f172a', 700: '#020617', 800: '#020617', 900: '#020617'
            }
          },
          boxShadow: {
            soft: '0 1px 2px rgba(15, 23, 42, 0.04), 0 4px 12px rgba(15, 23, 42, 0.04)',
            lift: '0 8px 30px rgba(15, 23, 42, 0.08)',
          },
          borderRadius: { xl: '0.875rem', '2xl': '1rem' }
        }
      }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>/assets/css/app.css?v=<?php echo (int)@filemtime(__DIR__ . '/assets/css/app.css'); ?>">
  <link rel="stylesheet" href="<?php echo htmlspecialchars($assetBase); ?>/assets/css/theme.css?v=<?php echo (int)@filemtime(__DIR__ . '/assets/css/theme.css'); ?>">
</head>
<body class="bg-[#fafafa] text-slate-900 font-sans antialiased min-h-screen flex flex-col">

  <?php require __DIR__ . '/partials/layout/header.php'; ?>

  <div class="h-14 shrink-0" aria-hidden="true"></div>

  <div class="flex-1 flex max-w-7xl w-full mx-auto lg:pl-64">
    <?php require __DIR__ . '/partials/layout/sidebar.php'; ?>
    <?php require __DIR__ . '/partials/layout/sidebar-backdrop.php'; ?>

    <main id="main-area" class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
      <div id="global-alerts-container" class="mb-6 space-y-2 hidden"></div>

      <?php require __DIR__ . '/partials/views/dashboard.php'; ?>
      <?php require __DIR__ . '/partials/views/inventory.php'; ?>
      <?php require __DIR__ . '/partials/views/receive.php'; ?>
      <?php require __DIR__ . '/partials/views/release.php'; ?>
      <?php require __DIR__ . '/partials/views/transactions.php'; ?>
      <?php require __DIR__ . '/partials/views/locations.php'; ?>
      <?php require __DIR__ . '/partials/views/logs.php'; ?>
      <?php require __DIR__ . '/partials/views/email-config.php'; ?>
      <?php require __DIR__ . '/partials/views/users.php'; ?>
    </main>
  </div>

  <?php require __DIR__ . '/partials/modals/_backdrop-shell.php'; ?>
  <?php require __DIR__ . '/partials/modals/edit-location.php'; ?>
  <?php require __DIR__ . '/partials/modals/defective-replace.php'; ?>
  <?php require __DIR__ . '/partials/modals/duplicate.php'; ?>
  <?php require __DIR__ . '/partials/modals/lifespan-detail.php'; ?>
  <?php require __DIR__ . '/partials/modals/kpi-detail.php'; ?>
  <?php require __DIR__ . '/partials/modals/mail-log.php'; ?>
  <?php require __DIR__ . '/partials/modals/user-saved.php'; ?>
  <?php require __DIR__ . '/partials/modals/defective-detail.php'; ?>
  <?php require __DIR__ . '/partials/modals/app-confirm.php'; ?>
  <?php require __DIR__ . '/partials/modals/release-detail.php'; ?>

  <?php require __DIR__ . '/partials/layout/toast.php'; ?>
  <?php require __DIR__ . '/partials/layout/global-loading.php'; ?>

  <script type="module" src="<?php echo htmlspecialchars($assetBase); ?>/assets/js/main.js?v=<?php echo (int)@filemtime(__DIR__ . '/assets/js/main.js'); ?>"></script>
</body>
</html>
