<?php

/**
 * User: fabien.sanchez
 * Date: 14/09/2018
 * Time: 09:35
 */

namespace Migration;

use RuntimeException;

/**
 * Class MigrationConfig
 * @package Migration
 */
class MigrationConfigFile extends MigrationConfig
{

    /**
     * config du fichier
     * @var array
     */
    private $config;

    /**
     * MigrationConfigFile constructor.
     * @param string $config_filename
     */
    public function __construct(string $config_filename)
    {
        parent::__construct('', '', '', 0, '', '', '');
        if (!is_file($config_filename)) {
            throw new \RuntimeException("le fichier $config_filename n'a pas été trouvé.");
        }
        $this->config = json_decode(file_get_contents($config_filename));
        $this->migration_directory = $this->config->migration_directory;
        if (isset($this->config->config_extern) && is_file($this->config->config_extern->file ?? '')) {
            try {
                $this->initExternPhp();
            } catch (\Throwable $er) {
                throw new \RuntimeException(
                    "Le fichier de configuration externe n'est pas exploitable. "
                        . $er->getMessage()
                );
            }
        } elseif(isset($this->config->config_intern)) {
            $this->initIntern();
        } else {
            throw new RuntimeException("Le fichier de configuration n'est pas valide")
        }
    }

    /**
     * Initialise la config avec un fichier PHP extern
     */
    private function initExternPhp(): void
    {
        $fichier = require (string) $this->config->config_extern->file;
        $config = (array) $fichier;
        if (!empty($this->config->config_extern->array_path)) {
            $arrayPath = explode('/', $this->config->config_extern->array_path);
            foreach ($arrayPath as $path) {
                if (isset($config[$path])) {
                    $config = $config[$path];
                } else {
                    throw new \DomainException(
                        'La structure ne correspond pas au chemin indiqué dans array_path.'
                    );
                }
            }
        }
        $this->provider = $config[$this->config->config_extern->provider ?: 'provider'] ?: '';
        $this->host = $config[$this->config->config_extern->host ?: 'host'] ?: '';
        $this->port = $config[$this->config->config_extern->port ?: 'port'] ?: 0;
        $this->name = $config[$this->config->config_extern->name ?: 'name'] ?: '';
        $this->user = $config[$this->config->config_extern->user ?: 'user'] ?: '';
        $this->pass = $config[$this->config->config_extern->pass ?: 'pass'] ?: '';
    }

    /**
     * Initialise la config avec un le fichier de config
     */
    private function initIntern(): void
    {
        $this->provider = $this->config->config_intern->provider ?: '';
        $this->host = $this->config->config_intern->host ?: '';
        $this->port = $this->config->config_intern->port ?: 0;
        $this->name = $this->config->config_intern->name ?: '';
        $this->user = $this->config->config_intern->user ?: '';
        $this->pass = $this->config->config_intern->pass ?: '';
    }
}
