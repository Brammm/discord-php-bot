<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use Closure;
use DiscordPhpBot\EventHandler\Event;
use DiscordPhpBot\EventHandler\OpCode;
use DiscordPhpBot\EventHandler\Payload;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use RuntimeException;

use function json_decode;
use function json_encode;

use const JSON_THROW_ON_ERROR;

final class Connection
{
    private WebSocket|null $socket = null;

    public function __construct(private LoopInterface $loop)
    {
    }

    public function connect(): PromiseInterface
    {
        $deferred = new Deferred();

        $connector = new Connector($this->loop);

        $connector('wss://gateway.discord.gg/?v=10&encoding=json')->then(
            function (WebSocket $socket) use ($deferred): void {
                $this->socket = $socket;
                $deferred->resolve($this);
            },
        );

        return $deferred->promise();
    }

    /** @param array{op: int, d: array<mixed>} $data */
    public function send(array $data): void
    {
        if (! $this->socket) {
            throw new RuntimeException('Not connected yet!');
        }

        $this->socket->send(json_encode($data, JSON_THROW_ON_ERROR));
    }

    /** @param Closure(Payload): void $callback */
    public function onEvent(Closure $callback): void
    {
        if (! $this->socket) {
            throw new RuntimeException('Not connected yet!');
        }

        $this->socket->on('message', static function (MessageInterface $msg) use ($callback): void {
            $decodedMsg = json_decode((string) $msg, true, 512, JSON_THROW_ON_ERROR);

            $payload = new Payload(
                OpCode::tryFrom($decodedMsg['op']),
                $decodedMsg['t'] ? Event::tryFrom($decodedMsg['t']) : null,
                $decodedMsg['s'],
                $decodedMsg['d'] ?? [],
            );

            $callback($payload);
        });
    }
}
