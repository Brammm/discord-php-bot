<?php

declare(strict_types=1);

use DiscordPhpBot\Bot;
use DiscordPhpBot\ContainerFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad();

$container = ContainerFactory::build();

$bot = $container->get(Bot::class);
$bot->run();
