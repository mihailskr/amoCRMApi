<?php

declare(strict_types=1);

namespace Integration\Handler;

use Psr\Container\ContainerInterface;
use Integration\Wrapper\PheanstalkWrapper;

class LeadWebhookHandlerFactory
{
    public function __invoke(ContainerInterface $container): LeadWebhookHandler
    {
        $pheanstalkWrapper = $container->get(PheanstalkWrapper::class);

        return new LeadWebhookHandler($pheanstalkWrapper);
    }
}
