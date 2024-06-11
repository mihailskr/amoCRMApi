<?php

declare(strict_types=1);

namespace Integration\Command;

use Integration\Repository\LeadsRepository;
use Integration\Service\LeadService;
use Integration\Worker\AuthWorker;
use Integration\Worker\LeadsWorker;
use Integration\Wrapper\PheanstalkWrapper;
use Laminas\ServiceManager\ServiceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunLeadsWorkerCommand extends Command
{
    /** Наименование команды */
    public const NAME = 'worker:leads.' . PheanstalkWrapper::HOOK_QUEUE;

    public function setContainer(ServiceManager $container)
    {
        $this->container = $container;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName(self::NAME);
        $this->setDescription('Worker for adding, updating and sync leads is active');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $leadWorker = new LeadsWorker(
            $this->container->get(LeadService::class),
            $this->container->get(PheanstalkWrapper::class),
            $output,
        );

        $authWorker = new AuthWorker(
            $this->container->get(PheanstalkWrapper::class),
            $this->container->get(LeadsRepository::class),
            $output,
        );

        while (true) {
            $leadWorker->run();
            $authWorker->run();
        }
    }
}
