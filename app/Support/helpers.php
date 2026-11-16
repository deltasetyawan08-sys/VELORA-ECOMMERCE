<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('rupiah')) {
    function rupiah(int|string|null $amount, bool $withPrefix = true): string
    {
        $value = number_format((int) $amount, 0, ',', '.');

        return $withPrefix ? 'Rp'.$value : $value;
    }
}

if (! function_exists('product_image_url')) {
    function product_image_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}

if (! function_exists('initials')) {
    function initials(string $name): string
    {
        $parts = preg_split('/\s+/', trim($name)) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode(''));
    }
}
