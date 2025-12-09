<?php
/**
 * Simple .env file loader
 * Loads environment variables from .env file
 */

function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        return false;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Set as environment variable
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }

    return true;
}

/**
 * Get environment variable
 */
function env($key, $default = null) {
    $value = getenv($key);
    return $value !== false ? $value : $default;
}

// Auto-load .env when this file is included
loadEnv();
?>
