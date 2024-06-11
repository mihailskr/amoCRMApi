<?php

declare(strict_types=1);

namespace Integration\Handler;

use Exception;
use Fig\Http\Message\StatusCodeInterface;
use Integration\Filter\LeadsFilter;
use Integration\Helpers\ValidateParamsHelper;
use Integration\Model\Lead;
use Integration\Service\LeadService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Mezzio\Template\TemplateRendererInterface;
use CrmApi\Auth\AuthClient;
use Integration\Repository\LeadsRepository;

class LeadsListHandler implements RequestHandlerInterface
{
    /** Номер страницы по умолчанию */
    public const DEFAULT_PAGE_NUMBER = 1;
    /** Максимальное количетсво сделок на странице */
    public const MAX_PAGE_SIZE = 100;
    private LeadService $leadService;
    private LeadsFilter $leadsFilter;
    private TemplateRendererInterface $twig;
    private AuthClient $authClient;
    private LeadsRepository $leadsRepository;

    public function __construct(
        LeadService $leadService,
        LeadsRepository $leadsRepository,
        LeadsFilter $leadsFilter,
        TemplateRendererInterface $twig
    )
    {
        $this->leadService = $leadService;
        $this->leadsFilter = $leadsFilter;
        $this->twig = $twig;
        $this->authClient = new AuthClient();
        $this->leadsRepository = $leadsRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getQueryParams();
        $filter = $params['filter'] ?? '';
        $accountId = $params['accountId'] ?? '';
        $page = $params['page'] ?? '';
        $size = $params['size'] ?? '';

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

        $pageNumber = ValidateParamsHelper::validateParam($page)
            ? $params['page']
            : self::DEFAULT_PAGE_NUMBER;

        $pageSize = ValidateParamsHelper::validateParam($size)
            ? min($params['size'], self::MAX_PAGE_SIZE)
            : self::MAX_PAGE_SIZE;

        try {
            $queryFilter = [];
            if (!empty($filter)) {
                $queryFilter = $this->leadsFilter->buildFilter($filter, $accountId);
            }
            $leads = $this->leadsRepository->getLeads(
                $accountId,
                $queryFilter,
                self::MAX_PAGE_SIZE,
                ($pageNumber - 1) * self::MAX_PAGE_SIZE
            );
        } catch (Exception $e) {
            return new JsonResponse(
                [
                    'code' => StatusCodeInterface::STATUS_BAD_REQUEST,
                    'message' => $e->getMessage()
                ]
            );
        }

        if ($leads->isEmpty()) {
            return new JsonResponse(
                [
                    'code' => StatusCodeInterface::STATUS_BAD_REQUEST,
                    'message' => 'No Leads found!'
                ]
            );
        }

        $subdomain = $this->authClient->getApiClient()->getAccountBaseDomain();

        $renderData['pageSize'] = $pageSize;
        $renderData['pageNumber'] = $pageNumber;
        $renderData['leads'] = [];

        /** @var Lead $lead */
        foreach ($leads as $lead) {
            $renderData['leads'][] = [
                'name' => $lead->getName() ?? '',
                'budget' => $lead->getBudget() ?? '',
                'status' => $lead->getStatusId() ?? '',
                'responsible' => $lead->getResponsibleUserId() ?? '',
                'leadId' => $lead->getLeadId() ?? '',
                'accountId' => $lead->getAccountId() ?? '',
                'leadLink' => $this->leadService->buildAmoLeadLink($lead->getLeadId(), $subdomain),
            ];
        }

        return new HtmlResponse($this->twig->render('@integration/leads_list', $renderData));
    }
}
