<?php

namespace Johndodev\JmonitorBundle\Command;

use Johndodev\JmonitorBundle\Jmonitor\Client;
use Johndodev\JmonitorBundle\Jmonitor\Jmonitor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'jmonitor:collect')]
class CollectorCommand extends Command
{
    private Jmonitor $jmonitor;
    private Client $client;

    public function __construct(Jmonitor $jmonitor, Client $client)
    {
        parent::__construct();

        $this->jmonitor = $jmonitor;
        $this->client = $client;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->client->sendMetrics($this->jmonitor->collectMetrics());

        // todo handle response (async si possible ?)
        // si project invalide ou autre, délai croissant
        // si bug, ..

        return Command::SUCCESS;
    }
}
