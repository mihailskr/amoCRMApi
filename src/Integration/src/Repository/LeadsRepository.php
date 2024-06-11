<?php

declare(strict_types=1);

namespace Integration\Repository;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Integration\Model\Lead;
use Integration\Wrapper\MemcachedWrapper;

class LeadsRepository
{
    public const QUERY_LIMIT = 500;
    private MemcachedWrapper $memcachedWrapper;

    public function __construct()
    {
        $this->memcachedWrapper = new MemcachedWrapper();
    }


    /**
     * Метод получения сделок по id аккаунта и фильтру
     * @param int $accountIdParam
     * @param array $filter
     * @return Builder
     */
    public function getLeadsBuilder(int $accountIdParam, array $filter = []): Builder
    {
        $query = Lead::query()
            ->where('account_id', $accountIdParam);

        if (isset($filter['status'])) {
            $query->whereIn('status_id', $filter['status']);
        }

        if (isset($filter['responsible'])) {
            $query->whereIn('responsible_user_id', $filter['responsible']);
        }

        return $query;
    }


    /**
     * @param int $accountId
     * @param array $queryFilter
     * @param int $limit
     * @param int $offset
     * @return Collection
     */
    public function getLeads(
        int   $accountId,
        array $queryFilter,
        int   $limit = self::QUERY_LIMIT,
        int   $offset = 0
    ): Collection
    {
        return $this->getLeadsBuilder($accountId,$queryFilter)
            ->limit($limit)
            ->offset($offset)
            ->get();
    }

    /**
     * @param int $accountId
     * @param array $queryFilter
     * @return int
     */
    public function getLeadsBudget(int $accountId, array $queryFilter): int
    {
        return (int)$this->getLeadsBuilder($accountId, $queryFilter)->sum('budget');
    }
    /**
     * Метод обновления сделок
     * @param array $leadData
     * @return bool
     */
    public function updateLead(array $leadData): bool
    {
        $needToClearCache = false;

        $lead = Lead::query()
            ->where('amo_lead_id', $leadData['lead_id'] ?? $leadData['id'])
            ->where('account_id', $leadData['account_id'])
            ->first();

        if (is_null($lead)) {
            $leadToSave = new Lead();
            $leadToSave->setName($leadData['name']);
            // пришлось идти на такие проверки, из-за различий данных в хуке и response api/v4/leads
            $leadToSave->setBudget(isset($leadData['budget'])
                ? (int)$leadData['budget']
                : ($leadData['price'])
            );
            $leadToSave->setStatusId((int)$leadData['status_id']);
            $leadToSave->setResponsibleUserId((int)$leadData['responsible_user_id']);
            $leadToSave->setLeadId(isset($leadData['lead_id'])
                ? (int)$leadData['lead_id']
                : $leadData['id']
            );
            $leadToSave->setAccountId((int)$leadData['account_id']);

            if ($leadToSave->save()) {
                if ($leadToSave->getBudget() > 0) {
                    $needToClearCache = true;
                }
                $result = true;

            } else {
                $result = false;
            }
        } else {
            /** @var Lead $leadToUpdate */
            $leadToUpdate = $lead;
            $oldBudget = $leadToUpdate->getBudget();
            $leadToUpdate->setName($leadData['name']);
            $leadToUpdate->setBudget(isset($leadData['budget']) ? (int)$leadData['budget'] : ($leadData['price']));
            $leadToUpdate->setStatusId((int)$leadData['status_id']);
            $leadToUpdate->setResponsibleUserId((int)$leadData['responsible_user_id']);

            if ($leadToUpdate->update()) {
                if ($leadToUpdate->getBudget() !== $oldBudget) {
                    $needToClearCache = true;
                }
                $result = true;
            } else {
                $result = false;
            }
        }

        if ($needToClearCache) {
            $this->memcachedWrapper->getMemcached()->delete((string)$leadData['account_id']);
        }

        return $result;
    }
}
