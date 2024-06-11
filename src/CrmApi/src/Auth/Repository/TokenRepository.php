<?php

declare(strict_types=1);

namespace CrmApi\Auth\Repository;

use CrmApi\Auth\Model\Token;
use Illuminate\Support\Carbon;
use League\OAuth2\Client\Token\AccessToken;
use Illuminate\Database\Eloquent\Collection;

class TokenRepository
{
    /**
     * Метод сохранения токена в бд.
     * @param AccessToken $accessToken
     * @param int $accountId
     * @param string $baseDomain
     * @return void
     */
    public function saveOAuthToken(AccessToken $accessToken, int $accountId, string $baseDomain): void
    {
        $oldToken = self::getOAuthTokenByAccountId($accountId);

        $tokenModel = $oldToken ?? new Token();
        $tokenModel->setAccessToken($accessToken->getToken());
        $tokenModel->setExpiresAt($accessToken->getExpires());
        $tokenModel->setRefreshToken($accessToken->getRefreshToken());
        $tokenModel->setAccountId($accountId);
        $tokenModel->setBaseDomain($baseDomain);
        $tokenModel->save();
    }

    /**
     * Получение токена по id аккаунта
     * @param int $accountId
     * @return Token|null
     */
    public function getOAuthTokenByAccountId(int $accountId): ?Token
    {
        /** @var Token $oAuthToken */
        $oAuthToken = Token::query()
            ->where('account_id', '=', $accountId)
            ->first();

        return $oAuthToken;
    }

    /**
     * Получает колличество токенов, которые попадают под обновление
     * @param int $expiresTime Временной интервал (в секундах)
     * @param int $limit
     * @return Collection|null
     */
    public function getTokensForUpdate(int $expiresTime, int $limit = 500): ?Collection
    {
        $result = new Collection();

        Token::query()
            ->where('expires_at', '<', Carbon::now()->modify('- ' . $expiresTime . ' seconds')->getTimestamp())
            ->chunkById(
                $limit,
                function (Collection $tokens) use ($result) {
                    $result->merge($tokens);
                }
            );

        return $result->isNotEmpty() ? $result : null;
    }
}
