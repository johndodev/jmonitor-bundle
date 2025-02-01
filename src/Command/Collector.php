<?php

namespace Johndodev\JmonitorBundle\Command;

use Johndodev\JmonitorBundle\Jmonitor\Jmonitor;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'jmonitor:collect')]
class Collector extends Command
{
    private Jmonitor $jmonitor;

    public function __construct(Jmonitor $jmonitor)
    {
        parent::__construct();

        $this->jmonitor = $jmonitor;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->jmonitor->collect();

        dd($result);

        // return $this->client->post('', $results);
        return Command::SUCCESS;
    }
}
