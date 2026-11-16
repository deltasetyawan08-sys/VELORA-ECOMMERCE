<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Pembayaran',
            self::Paid => 'Dibayar',
            self::Processing => 'Diproses',
            self::Shipped => 'Dikirim',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PendingPayment => 'badge-warning',
            self::Paid => 'badge-info',
            self::Processing => 'badge-info',
            self::Shipped => 'badge-info',
            self::Completed => 'badge-success',
            self::Cancelled => 'badge-neutral',
        };
    }

    public function nextStatus(): ?self
    {
        return match ($this) {
            self::Paid => self::Processing,
            self::Processing => self::Shipped,
            self::Shipped => self::Completed,
            default => null,
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return $this->nextStatus() === $target;
    }
}
