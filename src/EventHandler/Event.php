<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

enum Event: string
{
    case Ready         = 'READY';
    case MessageCreate = 'MESSAGE_CREATE';
}
