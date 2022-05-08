<?php

namespace Migration;

use Helper\DbQuickUse;
use Helper\PDOFactory;
use PDO;

class DbTestCase extends TestCase
{
    /** @var ?\PDO instance d'acces à la base */
    private $pdo = null;

    protected const DBPROVIDER = "sqlite";
    protected const DBFILE = "./data.sqlite";

    /**
     *
     */
    protected function putMigrationConfigFile(): void
    {
        $config = [
            'migration_directory' => __DIR__ . "/migration",
            "config_intern" => [
                "provider" => self::DBPROVIDER,
                "name" => self::DBFILE
            ]
        ];
        if (!is_dir(__DIR__ . "/migration")) {
            if (!mkdir($concurrentDirectory = __DIR__ . "/migration") && !is_dir($concurrentDirectory)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
            }
        }
        file_put_contents(self::CONFIGFILE, json_encode($config, JSON_PRETTY_PRINT));
    }

    protected function createEmptyDbFile(): void
    {
        file_put_contents(self::DBFILE, '');
    }

    /**
     * @return \PDO
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            if (!is_file(self::DBFILE)) {
                $this->createEmptyDbFile();
            }
            $this->pdo = PDOFactory::sqlite(self::DBFILE);
        }
        return $this->pdo;
    }

    /**
     * @return \Helper\DbQuickUse
     */
    public function query(): DbQuickUse
    {
        $pdo = $this->getPdo();
        return new DbQuickUse($pdo);
    }

    protected function deleteDbFile(): void
    {
        if (is_file(self::DBFILE)) {
            unlink(self::DBFILE);
        }
    }
}
