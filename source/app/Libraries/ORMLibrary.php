<?php

namespace App\Libraries;

use App\CustomAR\Record;
use Config\Services;
use Cycle\ORM;
use Cycle\ORM\Transaction;
use Spiral\Database;

class ORMLibrary {

    /**
     * @var ORM\ORM
     */
    private $orm;

    /**
     * @var Cycle\ORM\Transaction
     */
    private $transaction;

    /**
     * The constructor
     */
    public function __construct()
    {
        $config = new \Config\CycleORM();
        $dbal = new Database\DatabaseManager($config->getDBConfig());

        $this->orm = new ORM\ORM(new ORM\Factory($dbal));

        $schema = $config->loadSchema($this->orm);
        $this->orm = $this->orm->withSchema(new ORM\Schema($schema));
        Record::setORM($this->orm);
    }

    /**
     * Get Cycle ORM interface
     *
     * @return ORM\ORM
     */
    public function getORM() {
        return $this->orm;
    }

    /**
     * Get Cycle ORM Transaction instance
     *
     * @return Transaction
     */
    public function getTransaction() {
        return new Transaction($this->orm);
    }

    /**
     * Get Cycle ORM Repository instance
     *
     * @return Repository
     */
    public function getRepository(strin $className) {
        return $this->orm->getRepository($className);
    }
}