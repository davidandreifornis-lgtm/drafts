<?php
/**
 * Front controller — injects API base path and serves the SPA.
 */
$html = file_get_contents(__DIR__ . '/index.html');
$apiBase = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
if ($apiBase === '' || $apiBase === '.') {
    $apiBase = '/api';
} else {
    $apiBase .= '/api';
}
$inject = '<script>window.TONER_API_BASE=' . json_encode($apiBase) . ';</script>';
if (stripos($html, 'TONER_API_BASE') === false) {
    $html = preg_replace('/<head([^>]*)>/i', '<head$1>' . $inject, $html, 1);
}
header('Content-Type: text/html; charset=UTF-8');
echo $html;