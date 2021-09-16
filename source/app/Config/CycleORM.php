<?php namespace Config;

use App\CustomAR\ARGenerator;
use Spiral\Database;
use Cycle\Schema;
use Cycle\Annotated;
use Spiral\Tokenizer;

/**
 * CycleORM Configuration
 *
 * @package Config
 */

class CycleORM
{

    /**
     * Database Hostname
     *
     * @var string
     */
    private $hostname;

    /**
     * Database name
     *
     * @var string
     */
    private $database;

    /**
     * Database user name
     *
     * @var string
     */
    private $username;

    /**
     * Database password
     *
     * @var string
     */
    private $password;

    public function __construct()
    {
        $this->hostname = getenv('database.default.hostname');
        $this->database = getenv('database.default.database');
        $this->username = getenv('database.default.username');
        $this->password = getenv('database.default.password');
    }

    /**
     * A method to return the database configuration for cycle ORM schema.
     *
     * @return Database\Config\DatabaseConfig
     */
    public function getDBConfig() {
        return new Database\Config\DatabaseConfig([
            'default'     => 'default',
            'databases'   => [
                'default' => ['connection' => 'mysql']
            ],
            'connections' => [
                'mysql' => [
                    'driver'  => Database\Driver\MySQL\MySQLDriver::class,
                    'connection' => 'mysql:host=' . $this->hostname . ';dbname=' . $this->database,
                    'username'   => $this->username,
                    'password'   => $this->password,
                ]
            ]
        ]);
    }

    /**
     * A method to return the database configuration for cycle ORM schema.
     *
     * @param Database\DatabaseManager $dbal
     *
     * @return array $schema
     */
    public function loadSchema1($dbal) {
        // Class locator
        $cl = (new Tokenizer\Tokenizer(new Tokenizer\Config\TokenizerConfig([
            'directories' => [__DIR__ . '/../Entities'],
        ])))->classLocator();

        $schema = (new Schema\Compiler())->compile(new Schema\Registry($dbal), [
            new Annotated\Embeddings($cl),            // register embeddable entities
            new Annotated\Entities($cl),              // register annotated entities
            new Schema\Generator\ResetTables(),       // re-declared table schemas (remove columns)
            new Annotated\MergeColumns(),             // copy column declarations from all related classes (@Table annotation)
            new Schema\Generator\GenerateRelations(), // generate entity relations
            new Schema\Generator\ValidateEntities(),  // make sure all entity schemas are correct
            new Schema\Generator\RenderTables(),      // declare table schemas
            new Schema\Generator\RenderRelations(),   // declare relation keys and indexes
            new Annotated\MergeIndexes(),             // copy index declarations from all related classes (@Table annotation)
            // new Schema\Generator\SyncTables(),        // sync table changes to database
            new Schema\Generator\GenerateTypecast(),  // typecast non string columns
        ]);

        return $schema;
    }

    /**
     * A method to return the database configuration for cycle ORM schema.
     *
     * @param Database\DatabaseManager $orm
     *
     * @return array $schema
     */
    public function loadSchema($orm) {
        // Class locator
        $cl = (new Tokenizer\Tokenizer(new Tokenizer\Config\TokenizerConfig([
            'directories' => [__DIR__ . '/../Models'],
        ])))->classLocator();

        $schema = (new Schema\Compiler())->compile(
            new Schema\Registry($orm->getFactory()),
            [
                new ARGenerator($cl),
                new Schema\Generator\ValidateEntities(),
                new Schema\Generator\GenerateTypecast(),
                new Schema\Generator\GenerateRelations(),
            ]
        );

        return $schema;
    }
}