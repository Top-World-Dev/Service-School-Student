<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Cycle\Annotated;
use Cycle\Schema;
use Spiral\Database;
use Spiral\Migrations;
use Spiral\Tokenizer;

class MigrationsGenerate extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Database';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'migrations:generate';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Generate migrations from entities based on CycleORM';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'migrations:generate [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        // Delete old migrations
        helper('filesystem');
        delete_files(__DIR__ . '/../Database/Migrations/');

        $config = new \Config\CycleORM();
        $dbal = new Database\DatabaseManager($config->getDBConfig());

        $config = new Migrations\Config\MigrationConfig([
            'directory' => __DIR__ . '/../Database/Migrations/',
            'table'     => 'migrations'
        ]);

        $migrator = new Migrations\Migrator($config, $dbal, new Migrations\FileRepository($config));

        // Init migration table
        $migrator->configure();

         // Class locator
        $cl = (new Tokenizer\Tokenizer(new Tokenizer\Config\TokenizerConfig([
            'directories' => [__DIR__ . '/../Entities'],
        ])))->classLocator();

        $schema = (new Schema\Compiler())->compile(new Schema\Registry($dbal), [
            new Schema\Generator\ResetTables(),
            new Annotated\Embeddings($cl),
            new Annotated\Entities($cl),
            new Annotated\MergeColumns(),
            new Schema\Generator\GenerateRelations(),
            new Schema\Generator\ValidateEntities(),
            new Schema\Generator\RenderTables(),
            new Schema\Generator\RenderRelations(),
            new Annotated\MergeIndexes(),
            new \Cycle\Migrations\GenerateMigrations($migrator->getRepository(), $migrator->getConfig()),
            new Schema\Generator\GenerateTypecast(),
        ]);

        //  Run all outstanding migrations:
        while($migrator->run() !== null) { }
    }
}
