<?php

declare(strict_types=1);

namespace Integration\Service;

use Integration\Model\Lead;
use Integration\Wrapper\MemcachedWrapper;

class LeadService
{
    /** Шаблон ссылки на сделку */
    private const AMOCRM_LEAD_LINK = 'https://%s/leads/detail/%d';
    private MemcachedWrapper $memcachedWrapper;

    public function __construct()
    {
        $this->memcachedWrapper = new MemcachedWrapper();
    }

    /**
     * Метод сохранения новой сделки
     * @param array $leadData
     * @return bool
     */
    public function saveNewLead(array $leadData): bool
    {
        $lead = new Lead();

        $lead->setName($leadData['name']);
        $lead->setBudget((int)$leadData['budget']);
        $lead->setStatusId((int)$leadData['status_id']);
        $lead->setResponsibleUserId((int)$leadData['responsible_user_id']);
        $lead->setLeadId((int)$leadData['lead_id']);
        $lead->setAccountId((int)$leadData['account_id']);

        if ($lead->save()) {
            if ($lead->getBudget() > 0) {
                $this->memcachedWrapper->getMemcached()->delete((string)$lead->getAccountId());
            }
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Метод создания ссылки для сделки
     * @param int $accountId
     * @param string $subdomain
     * @return string
     */
    public function buildAmoLeadLink(int $accountId, string $subdomain): string
    {
        return sprintf(self::AMOCRM_LEAD_LINK, $subdomain, $accountId);
    }
}

