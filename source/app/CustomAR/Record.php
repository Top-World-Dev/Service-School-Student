<?php
namespace App\CustomAR;

use Cycle\ORM;
use Cycle\ORM\Iterator;
use Cycle\ORM\Select;
use Cycle\ORM\Exception\ParserException;
use Spiral\Database\Exception\DatabaseException;
use Spiral\Database\Injection\Parameter;
use Spiral\Database\Injection\Expression;

/**
 * Custom Active Record.
 */
abstract class Record
{
    /** @var ORM\ORMInterface */
    private static $orm;

    /** @var array */
    private $data = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->__setData($data);
    }

    /**
     * Set data from array
     *
     * @param array $data
     */
    public function __setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get data as array
     *
     * @return array
     */
    public function __getData(): array
    {
        return $this->data;
    }

    /**
     * Get a value from data array
     *
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return empty($this->data[$name]) ? null: $this->data[$name];
    }

    /**
     * Set a value
     *
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * Save a record
     *
     * @param bool $saveChildren
     */
    public function save(bool $saveChildren = false)
    {
        $tr = new ORM\Transaction(self::getORM());

        // in case of the happended database exceptions like no connection and concurrency operations, etc, retry persisting.
        try {
            $tr->persist(
                $this,
                $saveChildren ? ORM\Transaction::MODE_CASCADE : ORM\Transaction::MODE_ENTITY_ONLY
            );
            $tr->run();
        } catch (DatabaseException $e) {
            sleep(1);
            // retry
            // $tr->persist(
            //     $this,
            //     $saveChildren ? ORM\Transaction::MODE_CASCADE : ORM\Transaction::MODE_ENTITY_ONLY
            // );
            // $tr->run();
        }
    }

    /**
     * Delete a record
     */
    public function delete()
    {
        $tr = new ORM\Transaction(self::getORM());
        $tr->delete($this);
        $tr->run();
    }

    /**
     * Get cached entity's iterator
     *
     * @return Iterator
     */
    public static function get(): Iterator
    {
        $data = self::$orm->getRepository(static::class)
                             ->select()
                             ->fetchData();

        return self::getIterator(static::class, $data);
    }

    /**
     * Get cached entity's iterator with lazy loading relation.
     *
     * @return Iterator
     */
    public static function getWith($relations): Iterator
    {
        $data = self::$orm->getRepository(static::class)->select()
                             ->load($relations)
                             ->fetchData();

        return self::getIterator(static::class, $data);
    }

    /**
     * Get a entity
     *
     * @param int $id
     * @param array $relations
     * @return self|null
     */
    public static function getOneByID(int $id, array $relations = []): ?self
    {
        // Check if the entity is already cached into heap.
        // $entity = self::getORM()->getHeap()->find(static::class, ['id' => $id]);
        // if (!empty($entity)) {
        //     return $entity;
        // }

        // self::getORM()->getHeap()->clean();

        return self::getORM()->getRepository(static::class)
                    ->select()
                    ->load($relations)
                    ->wherePK($id)->fetchOne();

        // return self::getORM()->getRepository(static::class)->findByPK($id);
    }

    /**
     * Analyze query criteria
     *
     * @param array $criteria - query criteria (e.g. ['group_id' => $groupData['id']])
     * @param int|null $limit - query result limit
     * @return Select
     */
    public static function analyzeQueryCriteria(array $criteria, ?int $limit = null): Select
    {
        $select = self::select();

        if ($limit) {
            $select->limit($limit);
        }

        if ($criteria) {
            foreach ($criteria as $key => $value) {
                if (!is_array($value)) {
                    $select->where($key, $value);
                } else {
                    if (self::isAssoc($value)) {
                        $select->where(function(Select\QueryBuilder $qb) use ($key, $value) {
                            $index = 0;
                            foreach ($value as $operator => $val) {
                                if ($index == 0) {
                                    switch ($operator) {
                                        case 'lowerCase':
                                            $expression = "LOWER(".$key.")";
                                            if (!is_array($val))
                                                $qb->where(new Expression($expression), $val);
                                            else
                                                $qb->where(new Expression($expression), 'in', new Parameter($val));
                                            break;
                                        case 'noSpecialInputChar':
                                            $lowerCase = "LOWER(".$key.")";
                                            if (!is_array($val)) {
                                                $chars = str_split(preg_replace('/[^A-Za-z0-9]/', '', $val));
                                                $regExp = '';
                                                foreach($chars as $i => $char) {
                                                    if (($i + 1) < count($chars)) {
                                                        $regExp .= '[' . $char . ']\W?';
                                                    } else {
                                                        $regExp .= '[' . $char . ']';
                                                    }
                                                }
                                                $qb->where(new Expression($lowerCase), 'REGEXP', $regExp);
                                            } else {
                                                foreach($val as $i => $el) {
                                                    $chars = str_split(preg_replace('/[^A-Za-z0-9]/', '', $el));
                                                    $regExp = '';
                                                    foreach($chars as $k => $char) {
                                                        if (($k + 1) < count($chars)) {
                                                            $regExp .= '[' . $char . ']\W?';
                                                        } else {
                                                            $regExp .= '[' . $char . ']';
                                                        }
                                                    }
                                                    if ($i == 0) {
                                                        $qb->where(new Expression($lowerCase), 'REGEXP', $regExp);
                                                    } else if ($el){
                                                        $qb->orWhere(new Expression($lowerCase), 'REGEXP', $regExp);
                                                    } else {
                                                        $qb->orWhere($key, null);
                                                    }
                                                }
                                            }
                                            break;
                                        case 'year':
                                            $expression = "YEAR(".$key.")";
                                            $qb->orWhere(new Expression($expression), $val);
                                        case (preg_match('/^year\.[gtl]{2}$/', $operator) ? true : false):
                                            $expression = "YEAR(".$key.")";
                                            $pieces = explode('.', $operator);
                                            $symbol = @$pieces[1];
                                            if ($symbol && !is_array($val)) {
                                                if ($symbol === 'gt')
                                                    $qb->where(new Expression($expression), '>', $val);
                                                elseif ($symbol === 'lt')
                                                    $qb->where(new Expression($expression), '<', $val);
                                                else
                                                    throw new ParserException("Unrecognized comparison operator for: 'year'");
                                            } elseif (!$symbol && is_array($val) && !self::isAssoc($val)) {
                                                $qb->where(new Expression($expression), 'in', new Parameter($val));
                                            } elseif (!$symbol && !is_array($val)) {
                                                $qb->where(new Expression($expression), $val);
                                            } else {
                                                throw new ParserException("Unrecognized comparison value for key: 'year'");
                                            }
                                            break;
                                        default:
                                            $qb->where($key, $operator, $val);
                                            break;
                                    }
                                } else if ($val) {
                                    switch ($operator) {
                                        case 'lowerCase':
                                            $expression = "LOWER(".$key.")";
                                            if (!is_array($val))
                                                $qb->orWhere(new Expression($expression), $val);
                                            else
                                                $qb->orWhere(new Expression($expression), 'in', new Parameter($val));
                                            break;
                                        case 'noSpecialInputChar':
                                            $lowerCase = "LOWER(".$key.")";
                                            if (!is_array($val)) {
                                                $chars = str_split(preg_replace('/[^A-Za-z0-9]/', '', $val));
                                                $regExp = '';
                                                foreach($chars as $i => $char) {
                                                    if (($i + 1) < count($chars)) {
                                                        $regExp .= '[' . $char . ']\W?';
                                                    } else {
                                                        $regExp .= '[' . $char . ']';
                                                    }
                                                }
                                                $qb->orWhere(new Expression($lowerCase), 'REGEXP', $regExp);
                                            } else {
                                                foreach($val as $i => $el) {
                                                    $chars = str_split(preg_replace('/[^A-Za-z0-9]/', '', $el));
                                                    $regExp = '';
                                                    foreach($chars as $k => $char) {
                                                        if (($k + 1) < count($chars)) {
                                                            $regExp .= '[' . $char . ']\W?';
                                                        } else {
                                                            $regExp .= '[' . $char . ']';
                                                        }
                                                    }
                                                    if ($el){
                                                        $qb->orWhere(new Expression($lowerCase), 'REGEXP', $regExp);
                                                    } else {
                                                        $qb->orWhere($key, null);
                                                    }
                                                }
                                            }
                                            break;
                                        case 'year':
                                            $expression = "YEAR(".$key.")";
                                            $qb->orWhere(new Expression($expression), $val);
                                        case (preg_match('/^year\.[gtl]{2}$/', $operator) ? true : false):
                                            $expression = "YEAR(".$key.")";
                                            $pieces = explode('.', $operator);
                                            $symbol = @$pieces[1];
                                            if ($symbol && !is_array($val)) {
                                                if ($symbol === 'gt')
                                                    $qb->orWhere(new Expression($expression), '>', $val);
                                                elseif ($symbol === 'lt')
                                                    $qb->orWhere(new Expression($expression), '<', $val);
                                                else
                                                    throw new ParserException("Unrecognized comparison operator for: 'year'");
                                            } elseif (!$symbol && is_array($val) && !self::isAssoc($val)) {
                                                $qb->orWhere(new Expression($expression), 'in', new Parameter($val));
                                            } elseif (!$symbol && !is_array($val)) {
                                                $qb->orWhere(new Expression($expression), $val);
                                            } else {
                                                throw new ParserException("Unrecognized comparison value for key: 'year'");
                                            }
                                            break;
                                        default:
                                            $qb->orWhere($key, $operator, $val);
                                            break;
                                    }
                                } else { // if value is null
                                    $qb->orWhere($key, null);
                                }
                                $index++;
                            }
                        });
                    } else {
                        // check if value contains null.
                        $result = array_filter($value);
                        if (count($result) == count($value)) {
                            $select->where($key, 'in', new Parameter($value));
                        } else {
                            $select->where(function(Select\QueryBuilder $qb) use ($key, $result) {
                                $qb->where($key, 'in', new Parameter($result))
                                   ->orWhere($key, null);
                            });
                        }
                    }
                }
            }
        }
        return $select;
    }

    /**
     * Get an entity object by criteria
     *
     * @param array $criteria
     * @param array $relations - if not empty then includes matching results from table(s) in $relations array
     * @return self|null - returns a single entity object or null
     *
     * Examples:
     * $withRelations = ['discipline', 'level', 'subject'];
     * (1) Values in $criteria array are NOT associative arrays themselves.
     *      $queryResult = Entity::findOne(['group_id' => $groupId, 'user_id' => $userId], $withRelations);
     *      $queryResult = Entity::findOne(['group_id' => [1, 2, 3], 'user_id' => $userId]);
     * (2) Some values in $criteria array ARE associative arrays themselves. Keys must be normal SQL operators (e.g. '>', '<=', etc...) or explicitely defined (see self::analyzeQueryCriteria()).
     *   When multiple array key operators are specified, they are bundled exclusively (i.e. using the conditional 'OR') from left to right and the query search stops upon the first match.
     *   Arrays are not currently accepted as values for operator keys (will be added soon - just a matter of further customizing the self::analyzeQueryCriteria() method).
     *      $queryResult = Entity::findOne(['grade_value' => ['=' => 100, '>' => 95], 'user_id' => $userId]); // keys are normal SQL operators - illustrative example only - could have used only the '>=' SQL operator.
     *      $queryResult = Entity::findOne(['name' => ['lowerCase' => 'Carl']], $withRelations); // key is a case-sensitive custom-defined operator
     */
    public static function findOne(array $criteria, array $relations = []): ?self
    {
        $select = self::analyzeQueryCriteria($criteria);

        return $select->load($relations)->fetchOne();
    }

    /**
     * Get all matching entity objects by criteria
     *
     * @param array $criteria
     * @param array|null $relations - if not empty then includes matching results from table(s) in $relations array
     * @param int|null $limit
     * @return Iterator - returns an iterator over all entity objects matching the $criteria
     *
     * Examples:
     * $withRelations = ['discipline', 'level', 'subject'];
     * (1) Values in $criteria array are NOT associative arrays themselves.
     *      $queryResult = Entity::findAll(['group_id' => $groupId]);
     *      $queryResult = Entity::findAll(['group_id' => [1, 2, 3], 'user_id' => $userId], $withRelations);
     * (2) Some values in $criteria array ARE associative arrays themselves. Keys must be normal SQL operators (e.g. '>', '<=', etc...) or explicitely defined (see self::analyzeQueryCriteria()).
     *   When multiple array key operators are specified, they are bundled exclusively (i.e. using the conditional 'OR') from left to right and the query search stops upon the first match.
     *   Arrays are not currently accepted as values for operator keys (will be added soon - just a matter of further customizing the self::analyzeQueryCriteria() method).
     *      $queryResult = Entity::findAll(['grade_value' => ['=' => 100, '>' => 95], 'user_id' => $userId], $withRelations); // keys are normal SQL operators - illustrative example only - could have used only the '>=' SQL operator.
     *      $queryResult = Entity::findAll(['name' => ['lowerCase' => 'Carl']]); // key is a case-sensitive custom-defined operator
     */
    public static function findAll(array $criteria, ?array $relations = [], ?int $limit = null): Iterator
    {
        $select = self::analyzeQueryCriteria($criteria, $limit);

        $data = $select->load($relations)->fetchData();

        return self::getIterator(static::class, $data);
    }

    /**
     * Check if array is associative or sequential
     *
     * @param array $data
     *
     * @return bool
     */
    private static function isAssoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * Get iterable data from array.
     *
     * @param string $class
     * @param array $data
     *
     * @return Iterator
     */
    public static function getIterator(string $class, array $data): Iterator
    {
        return new Iterator(self::$orm, $class, $data);
    }

    /**
     * Get ORM interface
     *
     * @return ORM\ORMInterface
     */
    public static function getORM(): ORM\ORMInterface
    {
        return self::$orm;
    }

    /**
     * Set ORM interface
     *
     * @param ORM\ORMInterface $orm
     */
    public static function setORM(ORM\ORMInterface $orm)
    {
        self::$orm = $orm;
    }

    /**
     * Get ORM Select
     *
     * @return \Cycle\ORM\Select
     */
    public static function select() {
        return self::getORM()->getRepository(static::class)->select()->constrain(new NotDeletedConstrain());
    }
}
