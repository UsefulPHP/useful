<?php
declare(strict_types = 1);
/**
 * PHPImporter.php
 *
 * PHP Config File Importer Strategy
 *
 * @category Common
 * @package Configuration
 * @author Lauren Black
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html
 */
namespace Useful\Common\Configuration\Importer;

use M1\Env\Parser;

/**
 * Imports a PHP Config File
 *
 * @package Useful\Common\Configuration\Importer
 */
class EnvFileImporter implements ImporterInterface
{

    /**
     * Store the parsed configuration as an array
     *
     * @var array|mixed $config
     */
    private array $config;

    /**
     * PHPImporter constructor.
     *
     * @param string $file
     */
    public function __construct(string $file)
    {
        $config = Parser::parse(file_get_contents($file));

        $this->config = (array) $config;
    }

    /**
     *
     * @inheritDoc
     */
    final public function toArray(): array
    {
        return $this->config;
    }
}
