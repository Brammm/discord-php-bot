<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use DI\Attribute\Inject;
use DiscordPhpBot\EventHandler\EventHandler;
use DiscordPhpBot\EventHandler\Payload;

final class Bot
{
    #[Inject]
    private Connection $connection;

    /** @var EventHandler[] */
    #[Inject('eventHandlers')]
    private array $eventHandlers;

    public function run(): void
    {
        $this->connection->connect()->then(function (Connection $connection): void {
            $connection->onEvent(function (Payload $payload): void {
                foreach ($this->eventHandlers as $eventHandler) {
                    if (! $eventHandler->handlesEvent($payload)) {
                        continue;
                    }

                    $eventHandler->handle($payload);
                }
            });
        });
    }
}
