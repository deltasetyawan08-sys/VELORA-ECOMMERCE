<?php

namespace App\Enums;

enum ReservationStatus: string
{
    case Held = 'held';
    case Consumed = 'consumed';
    case Released = 'released';

    public function label(): string
    {
        return match ($this) {
            self::Held => 'Reservasi Aktif',
            self::Consumed => 'Stok Terpakai',
            self::Released => 'Stok Dikembalikan',
        };
    }
}
