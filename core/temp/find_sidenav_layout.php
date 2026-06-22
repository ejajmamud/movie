<?php
$lines = file('resources/views/admin/layouts/app.blade.php');
foreach ($lines as $i => $line) {
    if (stripos($line, 'sidenav') !== false) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
