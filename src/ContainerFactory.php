<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use DI\Container;
use DI\ContainerBuilder;
use DiscordPhpBot\EventHandler\Heartbeat;
use DiscordPhpBot\EventHandler\Identify;
use DiscordPhpBot\EventHandler\MessageCreated;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\Browser;

use function DI\get;

final class ContainerFactory
{
    public static function build(): Container
    {
        $builder = new ContainerBuilder();
        $builder->useAttributes(true);
        $builder->useAutowiring(true);
        $builder->addDefinitions(self::definitions());

        return $builder->build();
    }

    /** @return array<string, callable|callable[]> */
    public static function definitions(): array
    {
        return [
            LoggerInterface::class => function () {
                $logger = new Logger('discord-bot');
                $logger->pushHandler(new StreamHandler('php://stdout'));
                
                return $logger;
            },
            LoopInterface::class => fn () => Loop::get(),
            Browser::class => fn (LoopInterface $loop) => (new Browser($loop))
                ->withBase('https://discord.com/api/')
                ->withHeader('Authorization', 'Bot ' . $_ENV['TOKEN']),
            'eventHandlers' => [
                get(Heartbeat::class),
                get(Identify::class),
                get(MessageCreated::class),
            ],
        ];
    }
}
