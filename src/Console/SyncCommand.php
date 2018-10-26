<?php
declare(strict_types=1);

namespace PHPSu\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends Command
{

    const DIRECTION_LEFT = 0;
    const DIRECTION_RIGHT = 1;

    protected function configure()
    {
        $this->setName('sync')
            ->setDescription('Sync Utility written in PHP')
            ->setHelp('Deploys locally or on the server')
            ->addOption('dry-run')
            ->addArgument('direction', InputArgument::REQUIRED,'this is the sync direction. eg prod->local');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write($this->parseDirection($input->getArgument('direction')));
        return 0;
    }

    protected function parseDirection(string $direction): array
    {
        if (substr_count($direction, '<-') > 1 || substr_count($direction, '->') > 1) {
            throw new \Exception('Direction of synchronisation not detectable');
        }
        return [];
    }
}
