<?php

declare(strict_types=1);

namespace Integration;

/**
 * The configuration provider for the Integration module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'templates' => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Service\LeadService::class => Service\LeadService::class,
                Worker\LeadsWorker::class => Worker\LeadsWorker::class,
                Repository\LeadsRepository::class => Repository\LeadsRepository::class,
                Wrapper\PheanstalkWrapper::class => Wrapper\PheanstalkWrapper::class,
            ],
            'factories' => [
                Filter\LeadsFilter::class => Filter\LeadsFilterFactory::class,
                Handler\LeadWebhookHandler::class => Handler\LeadWebhookHandlerFactory::class,
                Handler\LeadsSumHandler::class => Handler\LeadsSumHandlerFactory::class,
                Handler\LeadsListHandler::class => Handler\LeadsListHandlerFactory::class,
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
    public function getTemplates(): array
    {
        return [
            'paths' => [
                'integration' => [__DIR__ . '/../templates/'],
            ],
        ];
    }
}
