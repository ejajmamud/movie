<?php
function searchDir($dir) {
    $it = new RecursiveDirectoryIterator($dir);
    foreach (new RecursiveIteratorIterator($it) as $file) {
        if ($file->isFile() && $file->getExtension() == 'php') {
            $name = $file->getPathname();
            if (strpos($name, 'vendor') !== false || strpos($name, 'storage') !== false) {
                continue;
            }
            $content = file_get_contents($name);
            if (stripos($content, 'sidenav.json') !== false || stripos($content, 'sidenav') !== false) {
                echo $name . "\n";
            }
        }
    }
}
searchDir('app');
searchDir('config');
searchDir('bootstrap');
