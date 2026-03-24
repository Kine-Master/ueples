<?php
// backend/config/env.php

if (!function_exists('loadEnvFile')) {
    function loadEnvFile(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || $trimmed[0] === '#') {
                continue;
            }

            if (stripos($trimmed, 'export ') === 0) {
                $trimmed = trim(substr($trimmed, 7));
            }

            $pos = strpos($trimmed, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $pos));
            $value = trim(substr($trimmed, $pos + 1));
            if ($key === '') {
                continue;
            }

            if (
                (strlen($value) >= 2) &&
                (($value[0] === '"' && $value[strlen($value) - 1] === '"') ||
                 ($value[0] === "'" && $value[strlen($value) - 1] === "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $value = str_replace(['\\n', '\\r', '\\t'], ["\n", "\r", "\t"], $value);

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

