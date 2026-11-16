<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Closed = 'closed';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft (belum dibuka)',
            self::Active => 'Aktif (tamu dapat mengirim)',
            self::Closed => 'Selesai (kiriman ditutup)',
            self::Expired => 'Kadaluarsa',
        };
    }
}
