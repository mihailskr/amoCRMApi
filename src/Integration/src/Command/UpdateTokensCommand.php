<?php

declare(strict_types=1);

namespace Integration\Command;

use AmoCRM\Exceptions\AmoCRMoAuthApiException;
use CrmApi\Auth\AuthClient;
use CrmApi\Auth\Model\Token;
use CrmApi\Exception\AuthClientException;
use Laminas\ServiceManager\ServiceManager;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Integration\Wrapper\LoggerWrapper;

class UpdateTokensCommand extends Command
{
    private AuthClient $authClient;
    protected ServiceManager $container;
    private Logger $logger;

    public function setContainer(ServiceManager $container)
    {
        $this->container = $container;
    }

    protected function configure(): void
    {
        parent::configure();

        $this->setName('integration:token:update');
        $this->setDescription('Updates token if it was created 7 days ago');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->authClient = $this->container->get(AuthClient::class);
        $this->logger = (new LoggerWrapper(LoggerWrapper::UPDATE_TOKENS_COMMAND_LOG))->getLogger();

        try {
            $tokens = $this->authClient->refreshTokensIsWeekAgo();
            if ($tokens) {
                $output->writeln('Token is a 7 days ago, now updates');

                try {
                    $tokens->each(
                        function (Token $token) use (&$accountId) {
                            $accountId = $token->getAccountId();
                            $this->authClient->updateTokenByRefresh($token);
                        }
                    );
                } catch (
                AmoCRMoAuthApiException $e) {
                    $this->logger->error(printf('Failed to update token for account %d: %s', $accountId, $e->getMessage()));
                }
                $this->logger->info('Token update command executed successfully');

                return Command::SUCCESS;
            }
        } catch (AuthClientException $e) {
            $this->logger->error('Auth client error: ' . $e->getMessage());

            return Command::FAILURE;
        } catch (\Exception $e) {
            $this->logger->error('PHP error: ' . $e->getMessage());

            return Command::FAILURE;
        }
        $this->logger->info('No tokens found for refresh');

        return Command::SUCCESS;
    }
}

