<?php

declare(strict_types=1);

namespace Integration\Helpers;

use Integration\Filter\LeadsFilter;

class ValidateParamsHelper
{
    /**
     * @param array $filter
     * @return array
     */
    public static function validateFilter(array $filter): array
    {
        $validFilter = [];

        foreach (LeadsFilter::FILTERS as $allowedFilter) {
            if (isset($filter[$allowedFilter])) {
                $validFilter[$allowedFilter] = $filter[$allowedFilter];
            }
        }

        return $validFilter;
    }

    /**
     * @param string $param
     * @return bool
     */
    public static function validateParam(string $param): bool
    {
        if (empty($param) || (int)$param <= 0) {
            return false;
        } else {
            return true;
        }
    }

}
