<?php

namespace App\Enums;

enum EventCategory: string
{
    case Wedding = 'wedding';
    case Birthday = 'birthday';
    case Engagement = 'engagement';
    case Other = 'other';
}
