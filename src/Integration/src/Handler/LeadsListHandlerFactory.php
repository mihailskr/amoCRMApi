<?php

declare(strict_types=1);

namespace Integration\Handler;

use Integration\Filter\LeadsFilter;
use Integration\Repository\LeadsRepository;
use Integration\Service\LeadService;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;

class LeadsListHandlerFactory
{
    public function __invoke(ContainerInterface $container): LeadsListHandler
    {
        $leadService = $container->get(LeadService::class);
        $leadsFilter = $container->get(LeadsFilter::class);
        $twig = $container->get(TemplateRendererInterface::class);
        $leadsRepository = $container->get(leadsRepository::class);

        return new LeadsListHandler($leadService,$leadsRepository, $leadsFilter, $twig);
    }
}
