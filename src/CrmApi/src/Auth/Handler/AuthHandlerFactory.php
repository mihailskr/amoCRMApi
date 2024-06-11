<?php

declare(strict_types=1);

namespace CrmApi\Auth\Handler;

use CrmApi\Auth\AuthClient;
use Integration\Wrapper\PheanstalkWrapper;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class AuthHandlerFactory
{
    public function __invoke(ContainerInterface $container): AuthHandler
    {
        $authService = $container->get(AuthClient::class);
        $pheanstalkWrapper = $container->get(PheanstalkWrapper::class);
        $twig = $container->get(TemplateRendererInterface::class);

        return new AuthHandler($authService, $pheanstalkWrapper, $twig);
    }
}
