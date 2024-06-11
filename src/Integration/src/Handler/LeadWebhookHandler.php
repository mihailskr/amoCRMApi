<?php

declare(strict_types=1);

namespace Integration\Handler;

use Fig\Http\Message\StatusCodeInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Integration\Wrapper\LoggerWrapper;
use Monolog\Logger;
use Pheanstalk\Pheanstalk;
use Integration\Wrapper\PheanstalkWrapper;

class LeadWebhookHandler implements RequestHandlerInterface
{
    private Logger $logger;
    protected Pheanstalk $pheanstalk;
    /** action update вебхука */
    public const HOOK_ACTION_UPDATE = 'update';
    /** action add вебхука */
    public const HOOK_ACTION_ADD = 'add';

    public function __construct(PheanstalkWrapper $pheanstalkWrapper)
    {
        $this->logger = (new LoggerWrapper(LoggerWrapper::HOOK_WORKER_LOG))->getLogger();
        $this->pheanstalk = $pheanstalkWrapper->getPheanstalk();
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $hookData = $request->getParsedBody();
        $leadData = [];

        if (isset($hookData['leads']['add'])) {
            $leadData['action'] = self::HOOK_ACTION_ADD;
            $hookData = reset($hookData['leads']['add']);
        } elseif (isset($hookData['leads']['update'])) {
            $leadData['action'] = self::HOOK_ACTION_UPDATE;
            $hookData = reset($hookData['leads']['update']);
        } else {
            $this->logger->error('Unknown action or invalid hook data ', $hookData);

            return new JsonResponse(
                ['code' => StatusCodeInterface::STATUS_OK]
            );
        }

        $leadData['name'] = $hookData['name'] ?? '';
        $leadData['budget'] = $hookData['price'] ?? '';
        $leadData['status_id'] = $hookData['status_id'] ?? '';
        $leadData['responsible_user_id'] = $hookData['responsible_user_id'] ?? '';
        $leadData['lead_id'] = $hookData['id'] ?? '';
        $leadData['account_id'] = $hookData['account_id'] ?? '';

        if (!empty($leadData)) {
            $this->pheanstalk
                ->useTube(PheanstalkWrapper::HOOK_QUEUE)
                ->put(json_encode($leadData));
        }

        return new JsonResponse(
            ['code' => StatusCodeInterface::STATUS_OK]
        );
    }
}
