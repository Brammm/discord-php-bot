<?php

declare(strict_types=1);

namespace DiscordPhpBot\EventHandler;

use JsonSerializable;

enum OpCode: int implements JsonSerializable
{
    case Dispatch            = 0;
    case Heartbeat           = 1;
    case Identify            = 2;
    case PresenceUpdate      = 3;
    case VoiceStateUpdate    = 4;
    case Resume              = 6;
    case Reconnect           = 7;
    case RequestGuildMembers = 8;
    case InvalidSession      = 9;
    case Hello               = 10;
    case HeartbeatAck        = 11;

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
