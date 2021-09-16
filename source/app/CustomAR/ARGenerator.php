<?php
namespace App\CustomAR;

use App\CustomAR\Mappers\ARMapper;
use Cycle\Schema\GeneratorInterface;
use Cycle\Schema\Registry;
use Cycle\Schema\Definition;
use Cycle\Schema\Definition\Relation;
use Spiral\Prototype\Traits\PrototypeTrait;
use Spiral\Tokenizer\ClassesInterface;

class ARGenerator implements GeneratorInterface
{
    /** @var ClassesInterface */
    private $classLocator;

    /**
     * Constructor
     *
     * @param ClassesInterface $classLocator
     */
    public function __construct(ClassesInterface $classLocator)
    {
        $this->classLocator = $classLocator;
    }

    /**
     * Run generator over given registry.
     *
     * @param Registry $registry
     * @return Registry
     */
    public function run(Registry $registry): Registry
    {
        foreach ($this->classLocator->getClasses(Record::class) as $entity) {
            if ($entity->isAbstract()) {
                continue;
            }

            $this->declareEntity(
                $registry,
                $entity->getName(),
                $entity->getConstant('TABLE'),
                $entity->getConstant('RELATIONS')
            );
        }

        return $registry;
    }

    /**
     * Declare entity from table schema.
     *
     * @param Registry $registry
     * @param string $class
     * @param string $table
     * @param array $relations
     */
    private function declareEntity(Registry $registry, string $class, string $table, array $relations)
    {
        $e = new Definition\Entity();
        $e->setRole($class);
        $e->setClass($class);
        $e->setMapper(ARMapper::class);

        $registry->register($e);
        $registry->linkTable($e, 'default', $table);

        $schema = $registry->getTableSchema($e);

        foreach ($schema->getColumns() as $column) {
            $field = new Definition\Field();
            $field->setColumn($column->getName());

            if (in_array($column->getName(), $schema->getPrimaryKeys())) {
                $field->setPrimary(true);
            }

            $e->getFields()->set($column->getName(), $field);
        }

        if (!empty($relations)) {
            foreach ($relations as $type => $relation) {
                foreach ($relation as $name => $target) {
                    $e->getRelations()->set(
                        $name,
                        (new Relation())->setTarget($target)->setType($type)
                    );
                }
            }
        }
    }
}