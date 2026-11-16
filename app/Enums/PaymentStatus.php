<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Expired = 'expired';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Review = 'review';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Expired => 'Kedaluwarsa',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
            self::Review => 'Perlu Tinjauan',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Pending => 'Belum Dibayar',
            self::Paid => 'Lunas',
            self::Expired => 'Kedaluwarsa',
            self::Failed => 'Gagal',
            self::Cancelled => 'Dibatalkan',
            self::Review => 'Tinjauan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'badge-warning',
            self::Paid => 'badge-success',
            self::Expired => 'badge-neutral',
            self::Failed => 'badge-danger',
            self::Cancelled => 'badge-neutral',
            self::Review => 'badge-danger',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Paid, self::Expired, self::Failed, self::Cancelled], true);
    }

    public function isUnsuccessfulFinal(): bool
    {
        return in_array($this, [self::Expired, self::Failed, self::Cancelled], true);
    }
}
