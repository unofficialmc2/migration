<?php

/**
 * User: fabien.sanchez
 * Date: 14/09/2018
 * Time: 09:35
 */

namespace Migration;

/**
 * Class MigrationConfig
 * @package Migration
 */
class MigrationConfig
{
    /**
     * chemin vers les fichiers de migration
     * @var string
     */
    public $migration_directory;
    /**
     * provider
     * @var string
     */
    public $provider;
    /**
     * host
     * @var string
     */
    public $host;
    /**
     * port
     * @var int
     */
    public $port;
    /**
     * nom
     * @var string
     */
    public $name;
    /**
     * utilisateur
     * @var string
     */
    public $user;
    /**
     * mot de passe
     * @var string
     */
    public $pass;

    /**
     * MigrationConfig constructor.
     * @param string $migration_directory
     * @param string $provider
     * @param string $host
     * @param int $port
     * @param string $name
     * @param string $user
     * @param string $pass
     */
    public function __construct(string $migration_directory, string $provider, string $host, int $port, string $name, string $user, string $pass)
    {
        $migration_directory = str_replace('\\', '/', $migration_directory);
        $migration_directory = substr($migration_directory, -1) === '/'
            ? $migration_directory
            : $migration_directory . '/';
        $this->migration_directory = $migration_directory;
        $this->provider = $provider;
        $this->host = $host;
        $this->port = $port;
        $this->name = $name;
        $this->user = $user;
        $this->pass = $pass;
    }
}
