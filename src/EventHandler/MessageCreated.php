<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

use Psr\Http\Message\ResponseInterface;
use React\Http\Browser;

final class MessageCreated implements EventHandler
{
    private const string REACTION = '👋';
    
    public function __construct(private readonly Browser $browser)
    {
    }
    
    public function handlesEvent(Payload $payload): bool
    {
        return $payload->event === Event::MessageCreate;
    }

    public function handle(Payload $payload): void
    {
        $message = $payload->data['content'];

        if (! str_contains(strtolower($message), 'hello')) {
            return;
        }

        $this->browser->put(
            sprintf(
                'channels/%s/messages/%s/reactions/%s/@me',
                $payload->data['channel_id'],
                $payload->data['id'], 
                self::REACTION
            )
        )->then(static function (ResponseInterface $response) {
            echo 'Put reaction: ' . $response->getStatusCode() . ' ' . $response->getBody() . PHP_EOL;
        });
    }
}
