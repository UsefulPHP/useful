<?php declare(strict_types=1);


namespace Useful\Common\Configuration;


use Useful\Common\Configuration\Exception\EnvironmentException;
use Useful\Common\Configuration\Importer\ImporterInterface;

class Environment
{
    private static array $variables = [];

    public static function has(string $key): bool
    {
        return array_key_exists($key, static::$variables);
    }

    public static function isImportant(string $key): bool
    {
        if (!static::has($key)) {
            throw new EnvironmentException('ENV_VAR_NOT_FOUND');
        }
        return static::$variables[$key]['important'];
    }

    private static function validateKey(string $key): bool
    {
        if (preg_match('/^([A-Z1-9_]*)$/u', $key)) {
            return true;
        }
        return false;
    }

    public static function set(string $key, $value, bool $important = false): void
    {
        $key = strtoupper($key);
        if (static::has($key)) {
            if (static::isImportant($key)) {
                throw new EnvironmentException('CANNOT_OVERWRITE_IMPORTANT_ENV');
            }
        }
        if (!static::validateKey($key)) {
            throw new EnvironmentException('INVALID_ENV_KEY');
        }
        static::$variables[$key]['important'] = $important;
        static::$variables[$key]['value'] = $value;
    }

    public static function get(string $key, $default = null)
    {
        if (static::has($key)) {
            return static::$variables[$key]['value'];
        }
        return $default;
    }

    public static function import(ImporterInterface $importer, bool $important = false): void
    {
        $vars = $importer->toArray();
        foreach ($vars as $key => $value) {
            static::set($key, $value, $important);
        }
    }


}
