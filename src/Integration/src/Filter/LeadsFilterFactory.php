<?php

declare(strict_types=1);

namespace Integration\Filter;

use CrmApi\ApiClient;
use Psr\Container\ContainerInterface;

class LeadsFilterFactory
{
    public function __invoke(ContainerInterface $container): LeadsFilter
    {
        $apiClient = $container->get(ApiClient::class);

        return new LeadsFilter($apiClient);
    }
}
