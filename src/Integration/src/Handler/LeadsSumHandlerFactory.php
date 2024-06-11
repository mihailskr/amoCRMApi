<?php

declare(strict_types=1);

namespace Integration\Handler;

use Integration\Filter\LeadsFilter;
use Psr\Container\ContainerInterface;
use Mezzio\Template\TemplateRendererInterface;
use Integration\Repository\LeadsRepository;

class LeadsSumHandlerFactory
{
    public function __invoke(ContainerInterface $container): LeadsSumHandler
    {
        $leadsRepository = $container->get(LeadsRepository::class);
        $leadsFilter = $container->get(LeadsFilter::class);
        $twig = $container->get(TemplateRendererInterface::class);

        return new LeadsSumHandler($leadsRepository, $leadsFilter, $twig);
    }
}
