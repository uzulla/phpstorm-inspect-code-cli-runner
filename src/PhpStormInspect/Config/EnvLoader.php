<?php

declare(strict_types=1);

namespace PhpStormInspect\Config;

class EnvLoader
{
    /**
     * Loads environment variables from .env file
     */
    public static function load(string $rootDir): void
    {
        $envFile = $rootDir . '/.env';
        
        if (!file_exists($envFile)) {
            return;
        }
        
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }
        
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }
            
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }
            
            $name = trim($parts[0]);
            $value = trim($parts[1]);
            
            // Remove quotes if present
            if (preg_match('/^([\'"])(.*)\1$/', $value, $matches)) {
                $value = $matches[2];
            }
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
    
    /**
     * Gets an environment variable
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $value = getenv($key);
        
        if ($value === false) {
            return $default;
        }
        
        return $value;
    }
}
