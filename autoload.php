<?php
// Autoloader PSR-4 sederhana untuk namespace SMKApp
spl_autoload_register(function ($class) {
    $prefix = 'SMKApp\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    // Jika kelas bukan di namespace SMKApp, abaikan
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Ambil nama kelas relatif lalu ganti \ dengan /
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
?>
