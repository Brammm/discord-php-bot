<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

use DiscordPhpBot\Connection;
use Psr\Log\LoggerInterface;

final class Identify implements EventHandler
{
    private static bool $identified = false;

    public function __construct(
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handlesEvent(Payload $payload): bool
    {
        return $payload->opCode === OpCode::HeartbeatAck || $payload->event === Event::Ready;
    }

    public function handle(Payload $payload): void
    {
        if (self::$identified) {
            return;
        }

        if ($payload->event === Event::Ready) {
            self::$identified = true;
            $this->logger->debug('Identified');

            return;
        }

        $this->connection->send([
            'op' => OpCode::Identify,
            'd' => [
                'token' => $_ENV['TOKEN'],
                'properties' => [
                    'os' => 'macos',
                    'browser' => 'an-example-bot',
                    'device' => 'an-example-bot',
                ],
                'intents' => 34304,
            ],
        ]);
    }
}
