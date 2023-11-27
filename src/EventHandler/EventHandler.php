<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

interface EventHandler
{
    public function handlesEvent(Payload $payload): bool;

    public function handle(Payload $payload): void;
}
