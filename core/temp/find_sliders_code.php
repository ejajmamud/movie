<?php
$content = file_get_contents('app/Http/Controllers/SiteController.php');
$lines = explode("\n", $content);
foreach ($lines as $i => $line) {
    if (stripos($line, 'slider') !== false) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
