<?php

namespace Migration;

use Helper\PDOFactory;

/**
 * Class Migration
 * @package Migration
 */
class Migration
{
    /**
     * propriété contenant la config
     * @var MigrationConfig
     */
    protected $config;
    /**
     * connexion PDO
     * @var \PDO
     */
    protected $pdo;
    /**
     * historique des migration
     * @var array
     */
    protected $story;

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
    public function run()
    {
        # connexion
        $this->connexion();
        # setup
        $this->setup();
        # migration
        $this->migrate();
    }

    /**
     * Initialise la connexion
     * @throws \Exception
     */
    private function connexion()
    {
        $provider = $this->config->provider;
        switch ($provider) {
            case 'sqlite':
                $this->pdo = PDOFactory::sqlite($this->config->name);
                break;
            case 'postgres':
            case 'postgresql':
                $this->config->provider = 'postgres';
                $this->pdo = PDOFactory::pgsql(
                    $this->config->name,
                    $this->config->host,
                    $this->config->user,
                    $this->config->pass
                );
                break;
            default:
                throw new \Exception("Le provider $provider est inconnue!");
        }
    }

    /**
     * Initilise la base pour les migration
     * @throws \Exception
     */
    private function setup()
    {
        $provider = $this->config->provider;
        try {
            $stm = $this->pdo->query('select * from migration_story');
        } catch (\PDOException $ex) {
            $stm = false;
        }
        if ($stm === false) {
            foreach ($this->getQuerySetupFiles($provider) as $filename) {
                $this->executeQueryFile($filename);
            }
            echo "migration : setup migration" . PHP_EOL;
            try {
                $stm = $this->pdo->query('select * from migration_story');
            } catch (\PDOException $ex) {
                throw new \Exception(
                    "Impossible d'initialiser l'historique des migration dans la base"
                );
            }
        }
        $this->story = $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le fichiers de setup pour un provider
     * @param string $provider
     * @return array|false
     */
    private function getQuerySetupFiles(string $provider)
    {
        $query_files = glob(__DIR__ . '/../setup/' . $provider . '*.sql');
        $query_files = array_map(
            function ($filename) {
                return \realpath($filename);
            },
            $query_files
        );
        sort($query_files);
        return $query_files;
    }

    /**
     * execute un fichier de requète
     * @param string $filename
     * @throws \Exception
     */
    private function executeQueryFile(string $filename)
    {
        $file_lines = file($filename);
        $current_request = "";
        $index = 0;
        $start = 0;
        $end = 0;
        foreach ($file_lines as $line) {
            $index++;
            $line = trim($line);
            if ($line == "") continue;
            if (substr($line, 0, 3) == '---') {
                if (strlen($current_request) > 0) {
                    $this->executeQuery($current_request, "$filename($start-$end)");
                }
                $current_request = "";
                $start = 0;
                $end = 0;
            } else {
                $start = $start > 0 ? $start : $index;
                $end = $index;
                $current_request .= strlen($current_request) > 0 ? PHP_EOL : "";
                $current_request .= $line;
            }
        }
        if (strlen($current_request) > 0) {
            $this->executeQuery($current_request, "$filename($start-$end)");
        }
    }

    /**
     * execute une requete
     * @param string $query
     * @param string $info
     * @throws \Exception
     */
    private function executeQuery(string $query, string $info = "")
    {
        $query = $this->cleanQuery($query);
        try {

            if ($this->pdo->exec($query) === false) {
                $error = $this->pdo->errorInfo();
                throw new \Exception("[{$error[1]}] {$error[2]}]");
            }
        } catch (\Exception $ex) {
            throw new \Exception("Impossible d'executer la requete $info.\n{$ex->getMessage()}");
        }
    }

    /**
     * netoie une requète, supprime le point virgule de fin
     * @param string $query
     * @return bool|string
     */
    private function cleanQuery(string $query)
    {
        trim($query);
        if (substr($query, -1) == ';') {
            $query = substr($query, 0, -1);
        }
        return $query;
    }

    /**
     * exécute la migration
     * @throws \Exception
     */
    private function migrate()
    {
        $provider = $this->config->provider;
        foreach ($this->getQueryFiles($provider) as $filename) {
            if (!$this->controlMigrationFilePassed($filename)) {
                $this->executeQueryFile($filename);
                $this->storeMigration($filename);
            }
        }
    }

    /**
     * liste les fichiers de migration d'un provider
     * @param string $provider
     * @return array|false
     * @throws \DomainException
     */
    private function getQueryFiles(string $provider)
    {
        $dbDir = $this->config->migration_directory;
        if (!is_dir($dbDir)) {
            throw new \DomainException("Le dossier {$dbDir} n'a pas été trouvé");
        }
        $query_files = glob($dbDir . $provider . '/????????-??-*.sql');
        $query_files = array_map(
            function ($filename) {
                return \realpath($filename);
            },
            $query_files
        );
        sort($query_files);
        return $query_files;
    }

    /**
     * control si un fichier de migration a déjà été passé.
     * @param string $filename
     * @return bool
     */
    private function controlMigrationFilePassed(string $filename)
    {
        $file = basename(dirname($filename)) . '/' . basename($filename);
        $migration = array_filter($this->story, function ($story) use ($file) {
            return ($story['FILE'] == $file);
        });
        if (count($migration) == 0) {
            return false;
        }
        return true;
    }

    /**
     * Enregistre la migration en base
     * @param string $filename
     */
    private function storeMigration(string $filename)
    {
        $file = basename(dirname($filename)) . '/' . basename($filename);
        $content = file_get_contents($filename);
        $checksum = sha1_file($filename);
        $stm = $this->pdo->prepare('INSERT INTO migration_story (file, content, checksum) VALUES (?, ?, ?)');
        $stm->execute([$file, $content, $checksum]);
        echo "migration : $file" . PHP_EOL;
    }

}
