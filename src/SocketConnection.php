<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use Ratchet\Client\WebSocket;

final readonly class SocketConnection
{
    public function __construct(private WebSocket $socket) {}

    /** @param array{op: int, d: array} $data */
    public function send(array $data): void
    {
        $this->socket->send(json_encode($data, JSON_THROW_ON_ERROR));
    }
}
