<?php

declare(strict_types=1);

namespace CrmApi;

use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Exceptions\AmoCRMMissedTokenException;
use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use AmoCRM\Models\Leads\Pipelines\PipelineModel;
use CrmApi\Auth\AuthClient;
use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\OAuth2\Client\Provider\AmoCRMException;
use Exception;
use Integration\Wrapper\MemcachedWrapper;

class ApiClient
{
    private AmoCRMApiClient $amoApiClient;
    private AuthClient $authClient;
    private MemcachedWrapper $memcachedWrapper;

    public function __construct()
    {
        $this->authClient = new AuthClient();
        $this->amoApiClient = $this->authClient->getApiClient();
        $this->memcachedWrapper = new MemcachedWrapper();
    }

    /**
     * Метод получения id всех доступных пользователей в аккаунте
     * @param int $accountId
     * @return array
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     * @throws Exception
     */
    public function getAllAccountUsersIds(int $accountId): array
    {
        $this->authClient->authorizationByAccountId($accountId);

        $cacheKey = $this->memcachedWrapper->buildHashKey(['usersIds']);
        $cacheData = $this->memcachedWrapper->getMemcached()->get((string)$accountId) ?: [];

        if ($cacheData !== false && isset($cacheData[$cacheKey])) {
            $usersIds = $cacheData[$cacheKey];
        } else {
            try {
                $usersIds = $this->amoApiClient->users()->get()->pluck('id');
                $cacheData[$cacheKey] = $usersIds;
                $this->memcachedWrapper->set((string)$accountId, $cacheData);
            } catch (AmoCRMException $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $usersIds;
    }

    /**
     * Метод получения id всех доступных статусов сделок в аккаунте
     * @param int $accountId
     * @return array
     * @throws AmoCRMApiException
     * @throws AmoCRMMissedTokenException
     * @throws AmoCRMoAuthApiException
     * @throws Exception
     */
    public function getAllAccountStatusesIds(int $accountId): array
    {
        $this->authClient->authorizationByAccountId($accountId);

        $cacheKey = $this->memcachedWrapper->buildHashKey(['statusesIds']);
        $cacheData = $this->memcachedWrapper->getMemcached()->get((string)$accountId) ?: [];

        if ($cacheData !== false && isset($cacheData[$cacheKey])) {
            $statusesIds = $cacheData[$cacheKey];
        } else {
            try {
                /** @var PipelineModel $pipeline */
                $pipeline = $this->amoApiClient->pipelines()->get()->first();

                if ($pipeline === null) {
                   return [];
                }
                $statusesIds = $this->amoApiClient->statuses($pipeline->getId())->get()->pluck('id');

                $cacheData[$cacheKey] = $statusesIds;
                $this->memcachedWrapper->set((string)$accountId, $cacheData);
            } catch (AmoCRMException $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $statusesIds;
    }
}

