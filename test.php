<?php

echo '<pre>';

echo "PHP Version: " . PHP_VERSION . "\n\n";

echo "PDO Drivers:\n";
print_r(PDO::getAvailableDrivers());

echo "\nSQLSRV extension: ";
var_dump(extension_loaded('sqlsrv'));

echo "PDO_SQLSRV extension: ";
var_dump(extension_loaded('pdo_sqlsrv'));

echo '</pre>';