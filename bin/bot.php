<?php

use Dotenv\Dotenv;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$loop = Loop::get();
$connector = new Connector($loop);
$connector('wss://gateway.discord.gg/?v=10&encoding=json')->then(function(WebSocket $conn) use ($loop) {
    $sequence = null;
    $identified = false;
    $conn->on('message', function(MessageInterface $msg) use ($conn, $loop, &$sequence, &$identified) {
        echo $msg . PHP_EOL;
        $parsedMsg = json_decode($msg, true);

        if ($parsedMsg['s']) {
            $sequence = $parsedMsg['s'];
        }

        switch ($parsedMsg['op']) {
            case 10: // Hello event
                $hbInterval = $parsedMsg['d']['heartbeat_interval'] / 1000;

                $conn->send(json_encode(['op' => 1, 'd' => null]));
                $loop->addPeriodicTimer($hbInterval, function () use ($conn, $sequence) {
                    echo 'Sending heartbeat.' . PHP_EOL;
                    $conn->send(json_encode(['op' => 1, 'd' => $sequence]));
                });
                break;
            case 11: // Heartbeat Ack
                if (!$identified) {
                    echo 'Sending identify' . PHP_EOL;
                    $conn->send(json_encode([
                        'op' => 2,
                        'd' => [
                            'token' => $_ENV['TOKEN'],
                            'properties' => [
                                'os' => 'macos',
                                'browser' => 'recreatief-tellen',
                                'device' => 'recreatief-tellen',
                            ],
                            'intents' => 3136,
                        ],
                    ]));
                }
                break;
            case 0: // Ready
                $identified = true;
                break;

        }
    });
}, function ($e) use ($loop) {
    echo "Could not connect: {$e->getMessage()}\n";
    $loop->stop();
});
