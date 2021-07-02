<?php

namespace Migration;

use Helper\PDOFactory;

/**
 * Class Migration
 * @package Migration
 */
class Migration extends MigrationCore
{
    /**
     * propriété contenant la config
     * @var MigrationConfig
     */
    protected $config;

    /**
     * Migration constructor.
     * @param MigrationConfig $config
     */
    public function __construct(MigrationConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Exécute le process de migration
     * @throws \Exception
     */
    public function run(): void
    {
        # connexion
        $this->connexion();
        # setup & migrate
        parent::run();
    }

    /**
     * Initialise la connexion
     * @throws \Exception
     */
    private function connexion(): void
    {
        $this->setMigrationDirectory($this->config->migration_directory);
        switch ($this->config->provider) {
            case 'mysql':
                $this->setProvider($this->config->provider);
                $this->setPdo(PDOFactory::mysql(
                    $this->config->host,
                    $this->config->name,
                    $this->config->user,
                    $this->config->pass
                ));
                break;
            case 'sqlite':
                $this->setProvider($this->config->provider);
                $this->setPdo(PDOFactory::sqlite($this->config->name));
                break;
            case 'postgres':
            case 'postgresql':
                $this->setProvider('postgres');
                $this->setPdo(PDOFactory::pgsql(
                    $this->config->name,
                    $this->config->host,
                    $this->config->user,
                    $this->config->pass,
                    $this->config->port ?? 5432
                ));
                break;
            default:
                throw new \RuntimeException("Le provider {$this->config->provider} est inconnue!");
        }
    }
}
