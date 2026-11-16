<?php

namespace App\Enums;

enum ContributionType: string
{
    case Photo = 'photo';
    case Video = 'video';
    case Voice = 'voice';
    case Wish = 'wish';
}
