<?php
namespace App\CustomAR;

use Cycle\ORM\Select\ConstrainInterface;
use Cycle\ORM\Select\QueryBuilder;

class NotDeletedConstrain implements ConstrainInterface
{
    public function apply(QueryBuilder $query)
    {
        $query->where('deleted_at', '=', null);
    }
}