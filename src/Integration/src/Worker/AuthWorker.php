<?php

declare(strict_types=1);

namespace Integration\Worker;

use CrmApi\Auth\AuthClient;
use Exception;
use Monolog\Logger;
use Pheanstalk\Pheanstalk;
use Symfony\Component\Console\Output\OutputInterface;
use Integration\Wrapper\LoggerWrapper;
use Integration\Wrapper\PheanstalkWrapper;
use Integration\Handler\LeadWebhookHandler;
use Integration\Repository\LeadsRepository;
use CrmApi\Auth\Repository\TokenRepository;

class AuthWorker
{
    private Pheanstalk $pheanstalk;
    private OutputInterface $output;
    private Logger $logger;
    private LeadsRepository $leadsRepository;
    private TokenRepository $tokenRepository;
    private authClient $authClient;

    public function __construct(
        PheanstalkWrapper $pheanstalkWrapper,
        LeadsRepository $leadsRepository,
        OutputInterface $output,
    )
    {
        $this->output = $output;
        $this->pheanstalk = $pheanstalkWrapper->getPheanstalk();
        $this->pheanstalk->watch(PheanstalkWrapper::AUTH_QUEUE);
        $this->logger = (new LoggerWrapper(LoggerWrapper::SYNC_LEADS_LOG))->getLogger();
        $this->leadsRepository = $leadsRepository;
        $this->tokenRepository = new TokenRepository();
        $this->authClient = new AuthClient();
    }

    public function run(): void
    {
        $job = $this->pheanstalk->reserve();
        $this->output->writeln(sprintf('Sync job id: %s - Data: %s', $job->getId(), $job->getData()));

        $jobData = json_decode($job->getData(), true);
        $action = $jobData['action'];
        $accountId = $jobData['account_id'];

        switch ($action) {
            case LeadWebhookHandler::HOOK_ACTION_UPDATE:
                try {
                    $this->authClient->setAccessToken(
                        $this->tokenRepository->getOAuthTokenByAccountId($accountId)->toAccessToken(),
                        $this->tokenRepository->getOAuthTokenByAccountId($accountId)->getBaseDomain()
                    );

                    $leads = $this->authClient->getApiClient()->leads()->get()->toArray();
                    foreach ($leads as $lead) {
                        $this->leadsRepository->updateLead($lead);
                    }
                    $this->logger->info(sprintf('Leads for account %d was sync', $accountId), $leads);
                } catch (Exception) {
                    $this->logger->error(sprintf('Error. Not sync for account %d', $accountId));
                }
                break;
            default:
                $this->logger->info(sprintf('Unknown action %s', $action));
                break;
        }
        $this->pheanstalk->delete($job);
    }
}
