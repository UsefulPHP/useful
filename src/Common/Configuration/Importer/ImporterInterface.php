<?php declare(strict_types=1);
/**
 * ImporterInterface.php
 *
 * Importer Strategy Interface
 *
 * @category   Common
 * @package    Configuration
 * @author     Lauren Black
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html
 */

namespace Useful\Common\Configuration\Importer;

/**
 * Interface ImporterInterface
 * @package Useful\Common\Configuration\Importer
 */
interface ImporterInterface
{
    /**
     * Transform a set of data to an array
     * @return array
     */
    public function toArray(): array;
}
