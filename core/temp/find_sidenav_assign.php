<?php
function searchAll($dir) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isFile()) {
            $name = $file->getPathname();
            if (strpos($name, 'vendor') !== false || strpos($name, 'storage') !== false || strpos($name, '.git') !== false || strpos($name, 'node_modules') !== false) {
                continue;
            }
            $content = file_get_contents($name);
            if (stripos($content, 'sidenav.json') !== false) {
                echo "FOUND IN: " . $name . "\n";
            }
        }
    }
}
searchAll('.');
