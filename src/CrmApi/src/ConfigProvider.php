<?php

declare(strict_types=1);

namespace CrmApi;

use Integration\Worker\LeadsWorker;
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Auth\AuthClient::class => Auth\AuthClient::class,
                ApiClient::class => ApiClient::class,
                LeadsWorker::class => LeadsWorker::class,
            ],
            'factories' => [
                Auth\Handler\AuthHandler::class => Auth\Handler\AuthHandlerFactory::class,
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'error' => [__DIR__ . '/../templates/'],
                '404' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
