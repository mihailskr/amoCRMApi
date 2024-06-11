<?php

declare(strict_types=1);

namespace CrmApi\Auth;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use CrmApi\Auth\Model\Token;
use AmoCRM\Client\AmoCRMApiClient;
use CrmApi\Auth\Repository\TokenRepository;
use League\OAuth2\Client\Token\AccessToken;
use Illuminate\Database\Eloquent\Collection;

class AuthClient
{
    private AmoCRMApiClient $apiClient;
    private TokenRepository $tokenRepositoryr;
    private OAuthConfig $oAuthConfig;
    /** Количество секунд в неделе */
    public const WEEK_IN_SECONDS = 604800;

    public function __construct()
    {
        $this->oAuthConfig = new OAuthConfig();
        $this->apiClient = new AmoCRMApiClient(
            $this->oAuthConfig->getIntegrationId(),
            $this->oAuthConfig->getSecretKey(),
            $this->oAuthConfig->getRedirectDomain());
        $this->tokenRepositoryr = new TokenRepository();
    }

    /**
     * Устанавка значения субдомена аккаунта
     * @param string $domain
     * @return void
     */
    public function setAccountBaseDomain(string $domain): void
    {
        $this->apiClient->setAccountBaseDomain($domain);
    }

    /**
     * Получение ссылки на авторизацию
     * @param string $state
     * @return string
     */
    public function getAuthorizeUrl(string $state): string
    {
        return $this->apiClient->getOAuthClient()->getAuthorizeUrl([
            'state' => $state,
            'mode' => 'post_message',
        ]);
    }

    /**
     * Обмен authCode на accessToken
     * @param string $authCode
     * @return AccessToken
     * @throws AmoCRMoAuthApiException
     */
    public function getAccessTokenByCode(string $authCode): AccessToken
    {
        return $this->apiClient->getOAuthClient()->getAccessTokenByCode($authCode);
    }

    /**
     * Установка значения токена
     * @param AccessToken $token
     * @param string $subdomain
     * @return void
     */
    public function setAccessToken(AccessToken $token, string $subdomain)
    {
        $this->apiClient
            ->setAccessToken($token)
            ->setAccountBaseDomain($subdomain);
    }

    /**
     * Обмен refreshToken на accessToken
     * @param Token $token
     * @return void
     * @throws AmoCRMoAuthApiException
     */
    public function updateTokenByRefresh(Token $token): void
    {
        $this->authorizationByAccountId($token->getAccountId());
        $newAccessToken = $this->apiClient->getOAuthClient()
            ->getAccessTokenByRefreshToken(
                $token->toAccessToken()
            );
        $token->setAccessToken($newAccessToken->getToken());
        $token->setRefreshToken($newAccessToken->getRefreshToken());
        $token->setExpiresAt($newAccessToken->getExpires());
        $token->save();
    }

    /**
     * Возвращаем токены, время жизни которых осталось меньше недели
     * @return Collection|null
     */
    public function refreshTokensIsWeekAgo(): ?Collection
    {
        return $this->tokenRepositoryr->getTokensForUpdate(self::WEEK_IN_SECONDS);
    }

    /**
     * Получене apiClient
     * @return AmoCRMApiClient
     */
    public function getApiClient(): AmoCRMApiClient
    {
        return $this->apiClient;
    }

    /**
     * Метод проверки авторизации пользователя в системе.
     * Если токен есть, сетим значение в клиент и возвращаем
     * если токена нет в бд, то редиректим на страницу авторизации.
     * @param int $accountId
     * @return AmoCRMApiClient
     */
    public function authorizationByAccountId(int $accountId): AmoCRMApiClient
    {
        $token = $this->tokenRepositoryr->getOAuthTokenByAccountId($accountId);
        if (is_null($token)) {
            header('Location: ' . getenv('REDIRECT_URI'));
            exit;
        }

        return $this->apiClient->setAccessToken($token->toAccessToken())
            ->setAccountBaseDomain($token->getBaseDomain());
    }
}

