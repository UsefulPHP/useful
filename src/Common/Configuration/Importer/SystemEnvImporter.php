<?php declare(strict_types=1);
/**
 * PHPImporter.php
 *
 * PHP Config File Importer Strategy
 *
 * @category   Common
 * @package    Configuration
 * @author     Lauren Black
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Useful\Common\Configuration\Importer;

/**
 * Imports a PHP Config File
 * @package Useful\Common\Configuration\Importer
 */
class SystemEnvImporter implements ImporterInterface
{

    /**
     * PHPImporter constructor.
     * @param string $file
     */
    public function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    final public function toArray(): array
    {
        return $_SERVER;
    }
}
