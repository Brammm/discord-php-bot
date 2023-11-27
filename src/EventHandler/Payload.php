<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

final readonly class Payload
{
    public function __construct(
        public OpCode $opCode,
        public Event|null $event,
        public int|null $sequence,
        public array $data,
    ) {
    }
}
