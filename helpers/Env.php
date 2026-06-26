<?php
/**
 * Environment Configuration Loader Helper
 */

class Env {
    private static $loaded = false;
    private static $variables = [];

    /**
     * Load environment variables from a file
     */
    public static function load($path) {
        if (!file_exists($path)) {
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            // Split key and value by first '='
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);

                // Remove surrounding quotes if they exist
                if ((strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)) {
                    $value = substr($value, 1, -1);
                }

                // Convert representation of booleans and null
                $lowerVal = strtolower($value);
                if ($lowerVal === 'true') {
                    $value = true;
                } elseif ($lowerVal === 'false') {
                    $value = false;
                } elseif ($lowerVal === 'null') {
                    $value = null;
                }

                self::$variables[$key] = $value;
                
                // Add to standard globals if not present
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
                if (!array_key_exists($key, $_SERVER)) {
                    $_SERVER[$key] = $value;
                }
                
                // Put in environment (ensure string representation)
                $envStr = $value;
                if ($value === true) $envStr = 'true';
                if ($value === false) $envStr = 'false';
                if ($value === null) $envStr = 'null';
                putenv("$key=$envStr");
            }
        }
        self::$loaded = true;
        return true;
    }

    /**
     * Get environment configuration parameter
     */
    public static function get($key, $default = null) {
        if (array_key_exists($key, self::$variables)) {
            return self::$variables[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            if ($value === 'true') return true;
            if ($value === 'false') return false;
            if ($value === 'null') return null;
            return $value;
        }

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }
        if (isset($_SERVER[$key])) {
            return $_SERVER[$key];
        }

        return $default;
    }
}
