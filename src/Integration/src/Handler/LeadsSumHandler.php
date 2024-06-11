<?php

declare(strict_types=1);

namespace Integration\Handler;

use CrmApi\Auth\AuthClient;
use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Integration\Filter\LeadsFilter;
use Integration\Helpers\ValidateParamsHelper;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Integration\Exception\FilterException;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Integration\Wrapper\MemcachedWrapper;
use Integration\Repository\LeadsRepository;

class LeadsSumHandler implements RequestHandlerInterface
{
    private LeadsFilter $leadsFilter;
    private TemplateRendererInterface $twig;
    private MemcachedWrapper $memcachedWrapper;
    private AuthClient $authClient;
    private leadsRepository $leadsRepository;

    public function __construct(LeadsRepository $leadsRepository, LeadsFilter $leadsFilter, TemplateRendererInterface $twig)
    {
        $this->leadsFilter = $leadsFilter;
        $this->twig = $twig;
        $this->memcachedWrapper = new MemcachedWrapper();
        $this->authClient = new AuthClient();
        $this->leadsRepository = $leadsRepository;
    }

    /**
     * @throws FilterException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $filter = $params['filter'] ?? '';
        $accountId = $params['accountId'] ?? '';

        if (!ValidateParamsHelper::validateParam($accountId)) {
            return new JsonResponse(
                [
                    'message' => 'Invalid account ID',
                    'error code' => StatusCodeInterface::STATUS_BAD_REQUEST
                ],
            );
        } else {
            $accountId = intval($accountId);
            $this->authClient->authorizationByAccountId($accountId);
        }

        try {
            $queryFilter = [];
            if (!empty($filter)) {
                $queryFilter = $this->leadsFilter->buildFilter($filter, $accountId);
            }
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'code' => StatusCodeInterface::STATUS_BAD_REQUEST,
                    'message' => $e->getMessage()
                ]
            );
        }
        $hashFilterKey = $this->memcachedWrapper->buildHashKey($queryFilter);
        $sumFromCache = $this->memcachedWrapper->getMemcached()->get((string)$accountId) ?: [];

        if ($sumFromCache !== false && isset($sumFromCache[$hashFilterKey])) {
            $leadsBudget = $sumFromCache[$hashFilterKey];
        } else {
            $leadsBudget = $this->leadsRepository->getLeadsBudget($accountId, $queryFilter);

            $sumFromCache[$hashFilterKey] = $leadsBudget;
            $this->memcachedWrapper->set((string)$accountId, $sumFromCache);
        }
        $renderData = ['sum' => $leadsBudget];

        return new HtmlResponse($this->twig->render('@integration/leads_sum', $renderData));
    }
}
