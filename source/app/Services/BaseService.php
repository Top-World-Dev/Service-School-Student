<?php declare(strict_types=1);

namespace App\Services;

use Cycle\ORM\Iterator;

/**
 * Base Service
 */
class BaseService
{
    /**
     * Convert iterator to array
     *
     * @param Iterator $iterator
     * @return array
     */
    protected static function iteratorToArray(Iterator $iterator): array
    {
        $array = [];
        foreach ($iterator as $item) {
            array_push($array, $item->getFields());
        }
        return $array;
    }

    /**
     * Map array
     *
     * @param array $data
     * @param string $key
     * @return array
     */
    protected function mapArray(array $data, string $key): array
    {
        return array_map(function($item) use($key) {
            return $item[$key];
        }, $data);
    }
}
