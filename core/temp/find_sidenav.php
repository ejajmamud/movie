<?php
$dir = new RecursiveDirectoryIterator('app');
foreach (new RecursiveIteratorIterator($dir) as $file) {
    if ($file->isFile() && $file->getExtension() == 'php') {
        $content = file_get_contents($file->getPathname());
        if (stripos($content, 'sidenav.json') !== false || stripos($content, "'sidenav'") !== false) {
            echo $file->getPathname() . "\n";
        }
    }
}
