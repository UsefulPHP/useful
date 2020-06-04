<?php
declare(strict_types = 1);

use Useful\DataStore\Driver\PdoDriver;

class AbstractPdoRepository
{

    /**
     *
     * @var PdoDriver $driver Contains the PDO Driver Instance.
     */
    private PdoDriver $driver;

    /**
     * AbstractPDORepository constructor.
     *
     * @param PdoDriver $driver
     */
    public function __construct(PdoDriver $driver)
    {
        $this->pdoDriver = $driver;
    }
}
