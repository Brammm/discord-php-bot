<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use React\Http\Browser;

use function sprintf;
use function str_contains;
use function strtolower;

final class MessageCreated implements EventHandler
{
    private const string REACTION = '👋';

    public function __construct(
        private readonly Browser $browser,
        private readonly LoggerInterface $logger,
    ) {
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
                self::REACTION,
            ),
        )->then(function (ResponseInterface $response): void {
            $this->logger->debug('Reacted to message with ' . self::REACTION, [
                'status_code' => $response->getStatusCode(),
            ]);
        });
    }
}
