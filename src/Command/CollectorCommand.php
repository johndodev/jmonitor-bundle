<?php

namespace Johndodev\JmonitorBundle\Command;

use Johndodev\JmonitorBundle\Exceptions\ResponseException;
use Johndodev\JmonitorBundle\Jmonitor\Client;
use Johndodev\JmonitorBundle\Jmonitor\Jmonitor;
use Johndodev\JmonitorBundle\Model\Context;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('jmonitor:collect')]
class CollectorCommand extends Command
{
    private Jmonitor $jmonitor;
    private Client $client;
    private ?LoggerInterface $logger;
    private CacheItemPoolInterface $cache;

    private Context $context;
    private CacheItemInterface $contextItem;

    public function __construct(Jmonitor $jmonitor, CacheItemPoolInterface $cache, Client $client, ?LoggerInterface $logger = null)
    {
        parent::__construct();

        $this->jmonitor = $jmonitor;
        $this->client = $client;
        $this->logger = $logger ?: new NullLogger();
        $this->cache = $cache;
    }

    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->contextItem = $this->cache->getItem('JMONITOR_CONTEXT');
        $this->context = new Context(json_decode($this->contextItem->get() ?: '{}', true));

        $this->logger->debug('Context loaded', ['context nb consecutive failures' => $this->context->getNbConsecutiveFailures()]);

        $this->context->setNbDelayedExecution($this->context->getNbDelayedExecution() - 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->context->getNbDelayedExecution() > 0) {
            $this->logger->info('Execution delayed', ['remain delayed execution' => $this->context->getNbDelayedExecution()]);

            $this->saveContext();

            return Command::SUCCESS;
        }

        try {
            $metrics = $this->jmonitor->collectMetrics();
        } catch (\Throwable $e) {
            throw $e;
            $this->logger->error('Error while collecting metrics', [
                'exception' => $e,
            ]);

            return Command::FAILURE;
        }

        $this->logger->debug('Metrics collected', ['metrics' => $metrics]);

        try {
            $this->client->sendMetrics($metrics);
        } catch (ResponseException $e) {
            $this->logger->error('Error while sending metrics, next send delayed', [
                'exception' => $e,
                'response' => $e->getResponse(),
            ]);

            return $this->handleFailure();
        } catch (ClientExceptionInterface $e) {
            $this->logger->error('Error while sending metrics, next send delayed', ['exception' => $e]);

            return $this->handleFailure();
        }

        $this->handleSuccess();
        // TODO handle specific error response

        return Command::SUCCESS;
    }

    private function handleFailure(): int
    {
        $this->context->setNbConsecutiveFailures($this->context->getNbConsecutiveFailures() + 1);

        if ($this->context->getNbConsecutiveFailures() === 1) {
            $this->context->setNbDelayedExecution(1);
        } else {
            $this->context->setNbDelayedExecution(min(40, $this->context->getNbConsecutiveFailures() * 3));
        }

        $this->saveContext();

        return Command::FAILURE;
    }

    private function handleSuccess(): int
    {
        $this->context->setNbDelayedExecution(0);
        $this->context->setNbConsecutiveFailures(0);

        $this->saveContext();

        return Command::SUCCESS;
    }

    private function saveContext(): void
    {
        $this->cache->save($this->contextItem->set(json_encode($this->context)));
    }
}
