<?php
declare(strict_types=1);

namespace CrmApi\Auth\Handler;

use AmoCRM\Models\WebhookModel;
use CrmApi\Auth\AuthClient;
use CrmApi\Auth\Repository\TokenRepository;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Integration\Handler\LeadWebhookHandler;
use Integration\Wrapper\PheanstalkWrapper;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Pheanstalk\Pheanstalk;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Template\TemplateRendererInterface;

class AuthHandler implements RequestHandlerInterface
{
    private AuthClient $authClient;
    private TokenRepository $tokenRepository;
    private Pheanstalk $pheanstalk;

    private const AMOCRM_DOMAIN = 'amocrm.ru';
    public const WEBHOOK_ACTIONS = [
        'add_lead',
        'update_lead',
    ];
    private TemplateRendererInterface $twig;

    public function __construct(AuthClient $authClient, PheanstalkWrapper $pheanstalkWrapper, TemplateRendererInterface $twig)
    {
        $this->authClient = $authClient;
        $this->twig = $twig;
        $this->pheanstalk = $pheanstalkWrapper->getPheanstalk();
        $this->authClient->setAccountBaseDomain(self::AMOCRM_DOMAIN);
        $this->tokenRepository = new TokenRepository();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            session_start();
            $params = $request->getQueryParams();
            $authCode = $params['code'] ?? null;
            $referer = $params['referer'] ?? null;

            if (isset($params['state'])) {
                $state = $params['state'];
            }

            if ($referer !== null) {
                $this->authClient->setAccountBaseDomain($referer);
            }

            if ($authCode !== null && $state == $_SESSION['state']) {
                $accessToken = $this->authClient->getAccessTokenByCode($authCode);

                $baseDomain = $this->authClient->getApiClient()->getAccountBaseDomain();
                $this->authClient->setAccessToken($accessToken, $baseDomain);
                $accountId = $this->authClient->getApiClient()->account()->getCurrent()->getId();
                $this->tokenRepository->saveOAuthToken($accessToken, $accountId, $baseDomain);

                //Подписка на хук
                $webhookModel = new WebhookModel();
                $webhookModel->setDestination(getenv('HOST_URI') . '/webhooks/lead/add');
                $webhookModel->setSettings(self::WEBHOOK_ACTIONS);
                $this->authClient->getApiClient()->webhooks()->subscribe($webhookModel);

                $syncLeads['action'] = LeadWebhookHandler::HOOK_ACTION_UPDATE;
                $syncLeads['account_id'] = $accountId;
                $this->pheanstalk
                    ->useTube(PheanstalkWrapper::AUTH_QUEUE)
                    ->put(json_encode($syncLeads));

                $renderData = [
                    'accountId' => $accountId,
                ];

                return new HtmlResponse($this->twig->render('@integration/authorization', $renderData));
            }

            if (!isset($_SESSION['state'])) {
                $state = bin2hex(random_bytes(16));
                $_SESSION['state'] = $state;
            }
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'code' => StatusCodeInterface::STATUS_BAD_REQUEST,
                    'message' => $e->getMessage()
                ]
            );
        }
        $authorization_url = $this->authClient->getAuthorizeUrl($_SESSION['state']);

        return new RedirectResponse($authorization_url);
    }
}

