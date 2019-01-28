<?php

namespace Migration;

/**
 * Class Migration
 * @package Migration
 */
class MigrationInit
{
    /**
     * config_file
     * @var string
     */
    private $config_file;

    /**
     * MigrationInit constructor.
     * @param string $config_file
     * @throws \Exception
     */
    public function __construct(string $config_file)
    {
        if (empty($config_file)) {
            throw new \Exception("Impossible d'initialiser le fichier de configuration.");
        }
        $this->config_file = $config_file;
    }

    /**
     * Lance la commande
     */
    public function run(): void
    {
        if (!is_file($this->config_file)) {
            touch($this->config_file);
            $structure = [
                'migration_directory' => "./db/migration",
                'config_extern' => [
                    "file" => "",
                    'array_path' => "",
                    "provider" => "provider",
                    "host" => "host",
                    "port" => "port",
                    "name" => "name",
                    "user" => "user",
                    "pass" => "pass"
                ],
                'config_intern' => [
                    "provider" => "",
                    "host" => "",
                    "port" => 0,
                    "name" => "",
                    "user" => "",
                    "pass" => ""
                ]
            ];
            file_put_contents($this->config_file, json_encode($structure, JSON_PRETTY_PRINT));
        } else {
            throw new \Exception
            ("Impossible d'initialiser le fichier de configuration car ce fichier existe déjà.");
        }
    }
}
