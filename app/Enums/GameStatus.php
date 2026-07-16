<?php

namespace App\Enums;

enum GameStatus: string
{
    case Pending = 'pending';
    case Playing = 'playing';
    case Finished = 'finished';
}
