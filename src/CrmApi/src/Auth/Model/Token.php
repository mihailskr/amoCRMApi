<?php

declare(strict_types=1);

namespace CrmApi\Auth\Model;

use Illuminate\Database\Eloquent\Model;
use League\OAuth2\Client\Token\AccessToken;

class Token extends Model
{
    /** Наименование таблицы с токенами */
    public const TABLE = 'oauth_token';
    protected $table = self::TABLE;
    public $timestamps = false;

    protected $fillable = [
        'access_token',
        'refresh_token',
        'expires_at',
        'account_id',
        'subdomain',
    ];

    /**
     * Получение токена
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->getAttribute('access_token');
    }

    /**
     * Установка значения поля access_token
     * @param string $token
     * @return void
     */
    public function setAccessToken(string $token): void
    {

        $this->setAttribute('access_token', $token);
    }

    /**
     * Получение значения expiresAt - срок жизни токена
     * @return int
     */
    public function getExpiresAt(): int
    {
        return $this->getAttribute('expires_at');
    }

    /**
     * Установка значения поля expiresAt - срок жизни токена
     * @param int $expiresAt
     * @return void
     */
    public function setExpiresAt(int $expiresAt): void
    {
        $this->setAttribute('expires_at', $expiresAt);
    }

    /**
     * Получение рефреш токена
     * @return string|null
     */
    public function getRefreshToken(): ?string
    {
        return $this->getAttribute('refresh_token');
    }

    /**
     * Установка значения поля рефреш токена
     * @param string $token
     * @return void
     */
    public function setRefreshToken(string $token): void
    {
        $this->setAttribute('refresh_token', $token);
    }

    /**
     * Получение значения поля account_id
     * @return int
     */
    public function getAccountId(): int
    {
        return $this->getAttribute('account_id');
    }

    /**
     * Установка значения поля account_id
     * @param int $accountId
     * @return void
     */
    public function setAccountId(int $accountId): void
    {
        $this->setAttribute('account_id', $accountId);
    }

    /**
     * Установка значения поля subdomain
     * @param string $baseDomain
     * @return void
     */
    public function setBaseDomain(string $baseDomain): void
    {
        $this->setAttribute('subdomain', $baseDomain);
    }

    /**
     * Установка значения поля subdomain
     * @return string
     */
    public function getBaseDomain(): string
    {
        return $this->getAttribute('subdomain');
    }

    /**
     * Метод создания AccessToken из Token.
     * AcсessToken - необходим для работы с amo api
     * @return AccessToken
     */
    public function toAccessToken(): AccessToken
    {
        return new AccessToken([
            'access_token' => $this->getAccessToken(),
            'expires_in' => $this->getExpiresAt(),
            'refresh_token' => $this->getRefreshToken(),
        ]);
    }
}

