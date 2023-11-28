<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

use DiscordPhpBot\Connection;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;

final class Heartbeat implements EventHandler
{
    private static int $lastSequence = 0;

    public function __construct(
        private readonly LoopInterface $loop,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handlesEvent(Payload $payload): bool
    {
        return true;
    }

    public function handle(Payload $payload): void
    {
        if ($payload->sequence) {
            self::$lastSequence = $payload->sequence;
        }

        if ($payload->opCode !== OpCode::Hello) {
            return;
        }

        // Respond immediately with a first pong
        $this->connection->send(['op' => OpCode::Heartbeat, 'd' => null]);

        // Set up a periodic timer to send periodic heartbeats.
        $hbInterval = $payload->data['heartbeat_interval'] / 1000;
        $this->loop->addPeriodicTimer($hbInterval, function (): void {
            $this->logger->debug('Sending heartbeat ping');
            $this->connection->send(['op' => OpCode::Heartbeat, 'd' => self::$lastSequence]);
        });
    }
}
