<?php

declare(strict_types=1);

namespace CrmApi\Auth;

use AmoCRM\OAuth\OAuthConfigInterface;

class OAuthConfig implements OAuthConfigInterface
{
    public function getIntegrationId(): string
    {
        return getenv('AMOCRM_INTEGRATION_ID');
    }

    public function getSecretKey(): string
    {
        return getenv('AMOCRM_SECRET_KEY');
    }

    public function getRedirectDomain(): string
    {
        return getenv('REDIRECT_URI');
    }
}

