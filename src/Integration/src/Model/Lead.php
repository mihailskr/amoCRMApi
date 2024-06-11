<?php

declare(strict_types=1);

namespace Integration\Model;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    /** Наименование таблицы leads */
    public const TABLE = 'leads';
    protected $table = self::TABLE;
    public $timestamps = false;

    /**
     * Получение id сделки
     * @return int
     */
    public function getLeadId(): int
    {
        return (int)$this->getAttributeFromArray('amo_lead_id');
    }

    /**
     * Установка значения id сделки
     * @param int $leadId
     * @return self
     */
    public function setLeadId(int $leadId): self
    {
        return $this->setAttribute('amo_lead_id', $leadId);
    }

    /**
     * Получение id аккаунта
     * @return int
     */
    public function getAccountId(): int
    {
        return (int)$this->getAttributeFromArray('account_id');
    }

    /**
     * Установка значени id аккаунт
     * @param int $accountId
     * @return self
     */
    public function setAccountId(int $accountId): self
    {
        return $this->setAttribute('account_id', $accountId);
    }

    /**
     * Получение наименования сделки
     * @return string
     */
    public function getName(): string
    {
        return $this->getAttributeFromArray('name');
    }

    /**
     * Установка значения наименования сделки
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        return $this->setAttribute('name', $name);
    }

    /**
     * Получение значения поля бюджет
     * @return int
     */
    public function getBudget(): int
    {
        return (int)$this->getAttributeFromArray('budget');
    }

    /**
     * Установка значения поля бюджет
     * @param int $budget
     * @return self
     */
    public function setBudget(int $budget): self
    {
        return $this->setAttribute('budget', $budget);
    }

    /**
     * Получеие значение поля id ответственного пользователя
     * @return int
     */
    public function getResponsibleUserId(): int
    {
        return (int)$this->getAttributeFromArray('responsible_user_id');
    }

    /**
     * Установка значения поля id ответственного пользователя
     * @param int $responsibleUserId
     * @return self
     */
    public function setResponsibleUserId(int $responsibleUserId): self
    {
        return $this->setAttribute('responsible_user_id', $responsibleUserId);
    }

    /**
     * Получение id статуса сделки
     * @return int
     */
    public function getStatusId(): int
    {
        return (int)$this->getAttributeFromArray('status_id');
    }

    /**
     * Установка значения статуса сделки
     * @param int $statusId
     * @return self
     */
    public function setStatusId(int $statusId): self
    {
        return $this->setAttribute('status_id', $statusId);
    }
}

