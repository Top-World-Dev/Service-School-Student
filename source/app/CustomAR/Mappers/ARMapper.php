<?php
namespace App\CustomAR\Mappers;

use Cycle\ORM;
use Cycle\ORM\Command\ContextCarrierInterface;
use Cycle\ORM\Command\Database\Update;
use Cycle\ORM\Context\ConsumerInterface;
use Cycle\ORM\Heap\Node;
use Cycle\ORM\Heap\State;

/**
 * Provide the ability to carry data over the specific class instances. 
 */
class ARMapper extends ORM\Mapper\DatabaseMapper
{
    /**
     * @inheritdoc
     */
    public function init(array $data): array
    {
        $class = $this->orm->getSchema()->define($this->role, ORM\Schema::ENTITY);

        // return empty entity and prepared data
        return [new $class, $data];
    }

    /**
     * @inheritdoc
     */
    public function hydrate($entity, array $data)
    {
        /** @var Record $entity */
        $entity->__setData($data);

        return $entity;
    }

    /**
     * @inheritdoc
     */
    public function extract($entity): array
    {
        /** @var Record $entity */
        return $entity->__getData();
    }

    /**
     * Get entity columns.
     *
     * @param object $entity
     * @return array
     */
    protected function fetchFields($entity): array
    {
        // ignore properties which are not declated in schema
        $columns = array_intersect_key(
            $this->extract($entity),
            array_flip($this->columns)
        );

        return $columns;
    }

    /**
     * Automatically set created_at and updated_at column values on entity create
     */
    public function queueCreate($entity, Node $node, State $state): ContextCarrierInterface
    {
        $cmd = parent::queueCreate($entity, $node, $state);

        $state->register('created_at', new \DateTimeImmutable(), true);
        $cmd->register('created_at', new \DateTimeImmutable(), true);

        $state->register('updated_at', new \DateTimeImmutable(), true);
        $cmd->register('updated_at', new \DateTimeImmutable(), true);

        return $cmd;
    }

    /**
     * Automatically set created_at and updated_at column values on entity update
     */
    public function queueUpdate($entity, Node $node, State $state): ContextCarrierInterface
    {
        /** @var Update $cmd */
        $cmd = parent::queueUpdate($entity, $node, $state);

        $state->register('updated_at', new \DateTimeImmutable(), true);
        $cmd->registerAppendix('updated_at', new \DateTimeImmutable());

        return $cmd;
    }

    public function queueDelete($entity, Node $node, State $state): ContextCarrierInterface
    {
        // identify entity as being "deleted"
        $state->setStatus(Node::SCHEDULED_DELETE);
        $state->decClaim();

        $cmd = new Update(
            $this->source->getDatabase(),
            $this->source->getTable(),
            ['deleted_at' => new \DateTimeImmutable()]
        );

        // forward primaryKey value from entity state
        // this sequence is only required if the entity is created and deleted 
        // within one transaction
        $cmd->waitScope($this->primaryColumn);
        $state->forward(
            $this->primaryKey,
            $cmd,
            $this->primaryColumn,
            true,
            ConsumerInterface::SCOPE
        );

        return $cmd;
    }
}