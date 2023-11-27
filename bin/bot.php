<?php

use Dotenv\Dotenv;
use Psr\Http\Message\ResponseInterface;
use Ratchet\Client\Connector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Loop;
use React\Http\Browser;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$loop = Loop::get();
$connector = new Connector($loop);
$client = new Browser($loop);
$connector('wss://gateway.discord.gg/?v=10&encoding=json')->then(function(WebSocket $conn) use ($loop, $client) {
    $sequence = null;
    $identified = false;
    $conn->on('message', function(MessageInterface $msg) use ($conn, $loop, $client, &$sequence, &$identified) {
        echo $msg . PHP_EOL;
        $parsedMsg = json_decode($msg, true, 512, JSON_THROW_ON_ERROR);

        if ($parsedMsg['s']) {
            $sequence = $parsedMsg['s'];
        }

        switch ($parsedMsg['op']) {
            case 10: // Hello event
                $hbInterval = $parsedMsg['d']['heartbeat_interval'] / 1000;

                $conn->send(json_encode(['op' => 1, 'd' => null], JSON_THROW_ON_ERROR));
                $loop->addPeriodicTimer($hbInterval, function () use ($conn, $sequence) {
                    echo 'Sending heartbeat.' . PHP_EOL;
                    $conn->send(json_encode(['op' => 1, 'd' => $sequence], JSON_THROW_ON_ERROR));
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
                                'browser' => 'een-voorbeeld-bot',
                                'device' => 'een-voorbeeld-bot',
                            ],
                            'intents' => 34304,
                        ],
                    ], JSON_THROW_ON_ERROR));
                }
                break;
            case 0:
                switch ($parsedMsg['t']) {
                    case 'READY':
                        $identified = true;
                        break;
                    case 'MESSAGE_CREATE':
                        $message = $parsedMsg['d']['content'];
                        
                        if (!str_contains(strtolower($message), 'hello')) {
                            break;
                        }
                        
                        $channelId = $parsedMsg['d']['channel_id'];
                        $messageId = $parsedMsg['d']['id'];

                        $client->put('https://discord.com/api/channels/' . $channelId . '/messages/' . $messageId . '/reactions/👋/@me', [
                            'Authorization' => 'Bot ' . $_ENV['TOKEN']
                        ])->then(static function (ResponseInterface $response) {
                            echo 'Putting reaction: ' . $response->getStatusCode() . ' ' . $response->getBody() . PHP_EOL;
                        }, static function (Exception $e) {
                            echo 'Failed putting reaction: ' . $e->getMessage();
                        });

                        break;
                }
                break;
        }
    });
}, function ($e) use ($loop) {
    echo "Could not connect: {$e->getMessage()}\n";
    $loop->stop();
});
