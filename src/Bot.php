<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use DiscordPhpBot\EventHandler\Event;
use DiscordPhpBot\EventHandler\EventHandler;
use DiscordPhpBot\EventHandler\Heartbeat;
use DiscordPhpBot\EventHandler\Identify;
use DiscordPhpBot\EventHandler\MessageCreated;
use DiscordPhpBot\EventHandler\OpCode;
use DiscordPhpBot\EventHandler\Payload;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;

final class Bot
{
    private LoopInterface $loop;

    public function __construct()
    {
        $this->loop = Loop::get();
    }

    public function run(): void
    {
        $connector = new Connector($this->loop);

        $connector('wss://gateway.discord.gg/?v=10&encoding=json')->then(function (WebSocket $conn) {
            $socketConnection = new SocketConnection($conn);
            
            /** @var EventHandler[] $eventHandlers */
            $eventHandlers = [
                new Heartbeat($this->loop, $socketConnection),
                new Identify($socketConnection),
                new MessageCreated($this->loop),
            ];

            $conn->on('message', function(MessageInterface $msg) use ($eventHandlers) {
                echo $msg . PHP_EOL;
                $decodedMsg = json_decode((string) $msg, true, 512, JSON_THROW_ON_ERROR);
                
                $payload = new Payload(
                    OpCode::tryFrom($decodedMsg['op']),
                    $decodedMsg['t'] ? Event::tryFrom($decodedMsg['t']) : null,
                    $decodedMsg['s'],
                    $decodedMsg['d'] ?? [],
                );

                foreach ($eventHandlers as $eventHandler) {
                    if ($eventHandler->handlesEvent($payload)) {
                        $eventHandler->handle($payload);
                    }
                }
            });
        });
    }
}
