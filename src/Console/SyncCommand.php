<?php
declare(strict_types=1);

namespace PHPSu\Console;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class SyncCommand extends AbstractCommand
{

    private const DIRECTION_LEFT = 0;
    private const DIRECTION_RIGHT = 1;

    private $allowedCharacters = [
        self::DIRECTION_RIGHT => [
            '→',
            '->',
            ':=',
            ':-',
        ],
        self::DIRECTION_LEFT => [
            '←',
            '<-',
            '=:',
            '-:',
        ]
    ];

    protected function configure(): void
    {
        $this->setName('sync')
            ->setDescription('Sync Utility written in PHP')
            ->setHelp('Deploys locally or on the server')
            ->addOption('dry-run')
            ->addArgument('direction', InputArgument::IS_ARRAY, 'this is the sync direction. eg prod->local', ['production', 'to', 'local']);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $result = $this->parseDirection($input->getArgument('direction'));
        if ($result['direction'] === self::DIRECTION_RIGHT) {
            $this->output->writeln('<info>PHPSu is going to synchronize from ' . $result['systems'][0] . ' to ' . $result['systems'][1] . '</info>');
            return 0;
        }
        $this->output->writeln('<info>PHPSu is going to synchronize from ' . $result['systems'][1] . ' to ' . $result['systems'][0] . '</info>');
        return 0;
    }

    protected function parseDirection(array $direction): array
    {
        switch (\count($direction)) {
            case 1:
                $parsedDirectionArray = $this->parseDirectionSymbol($direction[0]);
                if (empty($parsedDirectionArray)) {
                    throw new \InvalidArgumentException('Direction of synchronisation not detectable');
                }
                return [
                    'systems' => explode($parsedDirectionArray['symbol'], $direction[0]),
                    'direction' => $parsedDirectionArray['direction']
                ];
                break;
            case 2:
                throw new \InvalidArgumentException('amount of arguments are not satisfying the command, cannot determine target, destination and direction');
            case 3:
                if (!empty($this->parseDirectionSymbol($direction[0])) && !empty($this->parseDirectionSymbol($direction[2]))) {
                    throw new \InvalidArgumentException('Target and Destination not detectable');
                }
                if ($direction[1] === 'to') {
                    return [
                        'systems' => [$direction[0], $direction[2]],
                        'direction' => self::DIRECTION_RIGHT
                    ];
                }
                $parsedDirectionArray = $this->parseDirectionSymbol($direction[1]);
                if (empty($parsedDirectionArray)) {
                    throw new \InvalidArgumentException('Direction of synchronisation not detectable');
                }
                return [
                    'systems' => [$direction[0], $direction[2]],
                    'direction' => $parsedDirectionArray['direction']
                ];
                break;
            default:
                throw new \InvalidArgumentException('Direction of synchronisation not detectable');
        }
    }

    private function parseDirectionSymbol(string $direction): array
    {
        foreach ($this->allowedCharacters as $key => $allowedCharacters) {
            foreach ($allowedCharacters as $symbol) {
                if (\strpos($direction, $symbol) !== false) {
                    return [
                        'direction' => $key,
                        'symbol' => $symbol
                    ];
                }
            }
        }
        return [];
    }
}
