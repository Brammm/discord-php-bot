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
    
    #[Inject('eventHandlers')]
    /** @param EventHandler[] */
    private array $eventHandlers;

    public function run(): void
    {
        $this->connection->connect()->then(function (Connection $connection) {
            $connection->on(function (Payload $payload) {
                foreach ($this->eventHandlers as $eventHandler) {
                    if ($eventHandler->handlesEvent($payload)) {
                        $eventHandler->handle($payload);
                    }
                }
            });
        });
    }
}
