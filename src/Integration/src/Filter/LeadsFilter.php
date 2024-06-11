<?php

declare(strict_types=1);

namespace Integration\Filter;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use CrmApi\ApiClient;
use Integration\Exception\FilterException;
use Integration\Helpers\ValidateParamsHelper;

class LeadsFilter
{
    private ApiClient $apiClient;
    public const FILTERS = [
        'status',
        'responsible'
    ];

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Метод обработки фильтра по get параметрам
     * @param array $filter
     * @param int $accountId
     * @return array
     * @throws FilterException
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     */
    public function buildFilter(array $filter, int $accountId): array
    {
        $filter = ValidateParamsHelper::validateFilter($filter);

        $queryFilter = [];
        if (isset($filter['status'])) {
            $statusesFilter = is_array($filter['status'])
                ? array_map('intval', $filter['status'])
                : [(int)$filter['status']];
            $amoStatusesIds = $this->apiClient->getAllAccountStatusesIds($accountId);
            if (array_diff($statusesFilter, $amoStatusesIds)) {
                throw new FilterException('Status filter contains invalid data or no pipelines');
            }
            $queryFilter['status'] = $statusesFilter;
        }

        if (isset($filter['responsible'])) {
            $responsiblesFilter = is_array($filter['responsible'])
                ? array_map('intval', $filter['responsible'])
                : [(int)$filter['responsible']];
            $amoUsersIds = $this->apiClient->getAllAccountUsersIds($accountId);

            if (array_diff($responsiblesFilter, $amoUsersIds)) {
                throw new FilterException('Responsible users filter contains invalid data');
            }
            $queryFilter['responsible'] = $responsiblesFilter;
        }

        return $queryFilter;
    }

    /**
     * @param array $filter
     * @return array
     */
    public function validateFilter(array $filter): array
    {
        $validFilter = [];

        foreach (self::FILTERS as $allowedFilter) {
            if (isset($filter[$allowedFilter])) {
                $validFilter[$allowedFilter] = $filter[$allowedFilter];
            }
        }

        return $validFilter;
    }
}
