<?php

declare(strict_types=1);

namespace Integration\Worker;

use Exception;
use Integration\Service\LeadService;
use Monolog\Logger;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Output\OutputInterface;
use Integration\Wrapper\LoggerWrapper;
use Integration\Wrapper\PheanstalkWrapper;
use Integration\Handler\LeadWebhookHandler;
use Integration\Repository\LeadsRepository;

class LeadsWorker
{
    private Pheanstalk $pheanstalk;
    private LeadService $leadService;
    private OutputInterface $output;
    private Logger $logger;
    private LeadsRepository $leadsRepository;

    public function __construct(
        LeadService $leadService,
        PheanstalkWrapper $pheanstalkWrapper,
        OutputInterface $output
    )
    {
        $this->leadService = $leadService;
        $this->output = $output;
        $this->pheanstalk = $pheanstalkWrapper->getPheanstalk();
        $this->pheanstalk->watch(PheanstalkWrapper::HOOK_QUEUE);
        $this->logger = (new LoggerWrapper(LoggerWrapper::HOOK_WORKER_LOG))->getLogger();
        $this->leadsRepository = new LeadsRepository();
    }

    public function run(): void
    {
        $job = $this->pheanstalk->reserve();
        $this->output->writeln(sprintf('Job id: %s - Data: %s', $job->getId(), $job->getData()));

        $leadData = json_decode($job->getData(), true);
        $action = $leadData['action'];

        switch ($action) {
            case LeadWebhookHandler::HOOK_ACTION_ADD:
                try {
                    $this->leadService->saveNewLead($leadData);
                    $this->logger->info('Lead was saved', $leadData);
                } catch (Exception) {
                    $this->logger->error('Error. Lead was not saved', $leadData);
                }
                break;
            case LeadWebhookHandler::HOOK_ACTION_UPDATE:
                try {
                    $this->leadsRepository->updateLead($leadData);
                    $this->logger->info('Lead was updated', $leadData);
                } catch (Exception) {
                    $this->logger->error('Error. Lead was not updated', $leadData);
                }
                break;
            default:
                $this->logger->info(sprintf('Unknown action %s', $action), $leadData);
                break;
        }
        $this->pheanstalk->delete($job);
    }
}
