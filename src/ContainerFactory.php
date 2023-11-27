<?php

declare(strict_types=1);

namespace DiscordPhpBot;

use DI\Container;
use DI\ContainerBuilder;
use DiscordPhpBot\EventHandler\Heartbeat;
use DiscordPhpBot\EventHandler\Identify;
use DiscordPhpBot\EventHandler\MessageCreated;
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
        $builder->addDefinitions(self::definitions());
        
        return $builder->build();
    }

    public static function definitions(): array
    {
        return [
            LoopInterface::class => fn() => Loop::get(),
            Connection::class => fn(LoopInterface $loop) => new Connection($loop),
            Browser::class => fn (LoopInterface $loop) => (new Browser($loop))
                ->withBase('https://discord.com/api/')
                ->withHeader('Authorization', 'Bot ' . $_ENV['TOKEN']),
            Heartbeat::class => fn (LoopInterface $loop, Connection $connection) => new Heartbeat($loop, $connection),
            Identify::class => fn (Connection $connection) => new Identify($connection),
            MessageCreated::class => fn (Browser $browser) => new MessageCreated($browser),
            'eventHandlers' => [
                get(Heartbeat::class),
                get(Identify::class),
                get(MessageCreated::class),
            ],
        ];
    }
}
