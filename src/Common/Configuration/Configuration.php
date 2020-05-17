<?php declare(strict_types=1);
/**
 * Configuration.php
 *
 * Basic Configuration Container
 *
 * @category   Common
 * @package    Configuration
 * @author     Lauren Black
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html
 */
namespace Useful\Common\Configuration;

use Useful\Common\Configuration\Importer\ImporterInterface;

/**
 * Configuration
 * @package Useful\Common\Configuration
 */
class Configuration
{
    /**
     * @var Configuration $instance
     */
    private static Configuration $instance;

    /**
     * @var array $configuration
     */
    private array $configuration = [];

    /**
     * Singleton Constructor
     */
    private function __construct()
    {
    }

    /**
     * @return Configuration Singleton Instance
     */
    public static function instance(): self
    {
        if(empty(static::$instance)){
            static::$instance = new Configuration();
        }
        return static::$instance;
    }

    /**
     * Import a file using a given importer strategy
     * @param string $section
     * @param ImporterInterface $importer
     * @return $this
     */
    final public function import(string $section, ImporterInterface $importer): self
    {
        $this->configuration = array_merge($this->configuration, [$section => $importer->toArray()]);
        return $this;
    }

    /**
     * Get a configuration value
     * @param string $section
     * @param string $value
     * @param null $default
     * @return mixed|null
     */
    final public function get(string $section, string $value, $default = null)
    {
        if (!isset($this->configuration[$section][$value])) {
            return $default;
        }

        return $this->configuration[$section][$value];
    }

}

