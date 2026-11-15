
<?php
error_reporting(E_ALL);
ini_set("display_errors", "1");

header("Content-Type: text/plain");

echo "PHP is running.\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Temporary directory: " . sys_get_temp_dir() . "\n";
echo "Temporary directory writable: ";
var_export(is_writable(sys_get_temp_dir()));
echo "\n";

echo "Headers already sent: ";
var_export(headers_sent($file, $line));

if (headers_sent($file, $line)) {
    echo "\nOutput started at: " . $file . ":" . $line;
}

echo "\nSession start: ";
var_export(session_start());

echo "\nSession status: " . session_status();
