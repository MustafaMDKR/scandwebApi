<?php
namespace API\core;

use PDO;

class Database
{
    public function __construct(
        private $driver,
        private $host,
        private $name,
        private $user,
        private $pass
    )
    {}


    public function getConnection():PDO
    {
        $dsn = "{$this->driver}:host={$this->host};dbname={$this->name}";
        return new PDO($dsn, $this->user, $this->pass, [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
    }
}