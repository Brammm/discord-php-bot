<?php

namespace DiscordPhpBot\EventHandler;

enum Event: string
{
    case Ready = 'READY';
    case MessageCreate = 'MESSAGE_CREATE';
}
