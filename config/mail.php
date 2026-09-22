<?php
/**
 * Emergency defaults only — live settings come from dbo.toner_email_settings.
 * Used when the table is empty or unavailable.
 */
return [
    'admin_email'    => '',
    'from_email'     => '',
    'from_name'      => 'Toner Inventory System',
    'subject_prefix' => '[Toner Alert]',
    'cooldown_hours' => 12,
    'cooldown_file'  => __DIR__ . '/../storage/low_stock_alerts.json',
    'driver'         => 'smtp',
    'smtp_host'      => '',
    'smtp_port'      => 465,
    'smtp_encryption'=> 'ssl',
    'smtp_user'      => '',
    'smtp_pass'      => '',
];
