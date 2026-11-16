<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageService
{
    public function store(UploadedFile $file, string $directory): string
    {
        $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = $name !== '' ? $name : 'image';
        $filename = $name.'-'.Str::random(8).'.'.$file->getClientOriginalExtension();

        return $file->storeAs($directory, $filename, 'public');
    }

    public function delete(?string $path): void
    {
        if ($path && ! Str::startsWith($path, ['http://', 'https://'])) {
            Storage::disk('public')->delete($path);
        }
    }
}
