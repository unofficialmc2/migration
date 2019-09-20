<?php

namespace Migration;

use Helper\PDOFactory;

/**
 * Class Migration
 * @package Migration
 */
class MigrationCore
{
    /**
     * provider
     * @var string
     */
    private $provider;
    /**
     * migrationDirectory
     * @var string
     */
    private $migrationDirectory;
    /**
     * connexion PDO
     * @var \PDO
     */
    private $pdo;
    /**
     * historique des migration
     * @var array
     */
    private $story;

    /**
     * Exécute le process de migration
     * @throws \Exception
     */
    public function run(): void
    {
        # setup
        $this->setup();
        # migration
        $this->migrate();
    }

    /**
     * Initilise la base pour les migration
     * @throws \Exception
     */
    private function setup(): void
    {
        try {
            $stm = $this->pdo->query('select * from migration_story');
        } catch (\PDOException $ex) {
            $stm = false;
        }
        if ($stm === false) {
            foreach ($this->getQuerySetupFiles() as $filename) {
                $this->executeQueryFile($filename);
            }
            echo 'migration : setup migration' . PHP_EOL;
            try {
                $stm = $this->pdo->query('select * from migration_story');
            } catch (\PDOException $ex) {
                throw new \RuntimeException(
                    "Impossible d'initialiser l'historique des migration dans la base"
                );
            }
        }
        $this->story = $stm->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le fichiers de setup pour un provider
     * @return array|false
     */
    private function getQuerySetupFiles()
    {
        $query_files = glob(__DIR__ . '/../setup/' . $this->provider . '*.sql');
        $query_files = array_map(
            'realpath',
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
    private function executeQueryFile(string $filename): void
    {
        $file_lines = file($filename);
        $current_request = '';
        $index = 0;
        $start = 0;
        $end = 0;
        foreach ($file_lines as $line) {
            $index++;
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (strpos($line, '---') === 0) {
                if ($current_request !== '') {
                    $this->executeQuery($current_request, "$filename($start-$end)");
                }
                $current_request = '';
                $start = 0;
                $end = 0;
            } else {
                $start = $start > 0 ? $start : $index;
                $end = $index;
                $current_request .= $current_request !== '' ? PHP_EOL : '';
                $current_request .= $line;
            }
        }
        if ($current_request !== '') {
            $this->executeQuery($current_request, "$filename($start-$end)");
        }
    }

    /**
     * execute une requete
     * @param string $query
     * @param string $info
     * @throws \Exception
     */
    private function executeQuery(string $query, string $info = ''): void
    {
        $query = $this->cleanQuery($query);
        try {

            if ($this->pdo->exec($query) === false) {
                $error = $this->pdo->errorInfo();
                throw new \RuntimeException("[{$error[1]}] {$error[2]}]");
            }
        } catch (\Exception $ex) {
            throw new \RuntimeException("Impossible d'executer la requete $info.\n{$ex->getMessage()}");
        }
    }

    /**
     * netoie une requète, supprime le point virgule de fin
     * @param string $query
     * @return boolean|string
     */
    private function cleanQuery(string $query)
    {
        trim($query);
        if (substr($query, -1) === ';') {
            $query = substr($query, 0, -1);
        }
        return $query;
    }

    /**
     * exécute la migration
     * @throws \Exception
     */
    private function migrate(): void
    {
        foreach ($this->getQueryFiles() as $filename) {
            if (!$this->controlMigrationFilePassed($filename)) {
                $this->executeQueryFile($filename);
                $this->storeMigration($filename);
            }
        }
    }

    /**
     * liste les fichiers de migration d'un provider
     * @return array|false
     * @throws \DomainException
     */
    private function getQueryFiles()
    {
        $dbDir = realpath($this->migrationDirectory);
        if (!is_dir($dbDir)) {
            throw new \DomainException("Le dossier {$dbDir} n'a pas été trouvé");
        }
        $query_files = glob($dbDir . $this->provider . '/????????-??-*.sql');
        $query_files = array_map(
            'realpath',
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
    private function controlMigrationFilePassed(string $filename): bool
    {
        $file = basename(dirname($filename)) . '/' . basename($filename);
        $migration = array_filter($this->story, static function ($story) use ($file) {
            return ($story['FILE'] === $file);
        });
        return !(count($migration) === 0);
    }

    /**
     * Enregistre la migration en base
     * @param string $filename
     */
    private function storeMigration(string $filename): void
    {
        $file = basename(dirname($filename)) . '/' . basename($filename);
        $content = file_get_contents($filename);
        $checksum = sha1_file($filename);
        $stm = $this->pdo->prepare('INSERT INTO migration_story (file, content, checksum) VALUES (?, ?, ?)');
        $stm->execute([$file, $content, $checksum]);
        echo "migration : $file" . PHP_EOL;
    }

    /**
     * @param string $provider
     * @return static
     */
    public function setProvider(string $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @param string $migrationDirectory
     * @return static
     */
    public function setMigrationDirectory(string $migrationDirectory)
    {
        $this->migrationDirectory =
            preg_replace(
                "/[\/\\]+/",
                DIRECTORY_SEPARATOR,
                $migrationDirectory . DIRECTORY_SEPARATOR
            );
        return $this;
    }

    /**
     * @param \PDO $pdo
     * @return static
     */
    public function setPdo(\PDO $pdo)
    {
        $this->pdo = $pdo;
        return $this;
    }
}
