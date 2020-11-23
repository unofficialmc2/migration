<?php


namespace Migration;

class BinTest extends DbTastCase
{
    /** @var string  */
    protected $cmd = __DIR__ . '/../bin/migrate';

    /**
     * @param string[] $param
     */
    protected function runMigrate(array $param = []): void
    {
        array_unshift($param, 'migrate');
        $argv = $param;
        $argc = count($argv);
        ob_start();
        include $this->cmd;
        $content = ob_get_clean();
        $content = str_replace(["#!/usr/bin/env php\r\n", "#!/usr/bin/env php\n"], '', ltrim($content));
        if (!empty($content)) {
            echo($content);
        }
    }

    /**
     * test de migrateInitCommand
     */
    public function testMigrateInitCommand(): void
    {
        $this->deleteConfigFile();
        $this->deleteDbFile();
        $this->runMigrate(['-i']);
        self::assertFileExists(self::CONFIGFILE);
    }


    /**
     * test de migrateInitCommand
     */
    public function testMigrateWithNothink(): void
    {
        $this->deleteConfigFile();
        $this->deleteDbFile();
        $this->expectOutputRegex("/Impossible de trouver le fichier de configuration \.\/migration-config\.json/");
        $this->runMigrate();
    }


    /**
     * test de migrateInitCommand
     */
    public function testMigrateWithConfig(): void
    {
        $this->expectOutputRegex("/Le fichier \.\/data.sqlite n'a pas été trouvé!/");
        $this->putMigrationConfigFile();
        $this->deleteDbFile();
        $this->runMigrate();
    }


    /**
     * test de migrateInitCommand
     */
    public function testMigrateWithConfigAndDbfile(): void
    {
        $this->expectOutputRegex("/migration : setup migration/");
        $this->putMigrationConfigFile();
        $this->createEmptyDbFile();
        $this->runMigrate();
        $nbStory = $this->query()->countElement('migration_story');
        self::assertEquals(0, $nbStory);
    }
}
