<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadHelper
{
    /**
     * Upload a file from base64 data URI or UploadedFile instance.
     *
     * @param  string|UploadedFile|null  $file
     * @param  string  $folder  Storage disk name (or path prefix when using public disk)
     * @return string Stored relative path / filename
     */
    public function uploadFile($file, $folder)
    {
        if (!$file) {
            return '';
        }

        $diskExists = array_key_exists($folder, config('filesystems.disks', []));

        // Multipart / Filament / API UploadedFile
        if ($file instanceof UploadedFile) {
            $extension = $file->getClientOriginalExtension() ?: 'bin';
            $fileName = Str::random(10) . '.' . $extension;

            if ($diskExists) {
                Storage::disk($folder)->putFileAs('', $file, $fileName);
                return $fileName;
            }

            $path = trim($folder, '/') . '/' . $fileName;
            Storage::disk('public')->putFileAs(trim($folder, '/'), $file, $fileName);
            return $path;
        }

        // Legacy base64 data URI support
        if (!is_string($file) || !str_contains($file, ';base64,')) {
            return '';
        }

        $file_64 = $file;
        $meta = substr($file_64, 0, strpos($file_64, ';'));
        $extension = explode('/', $meta)[1] ?? 'bin';
        $replace = substr($file_64, 0, strpos($file_64, ',') + 1);
        $fileType = str_replace(' ', '+', str_replace($replace, '', $file_64));
        $fileName = Str::random(10) . '.' . $extension;

        if ($diskExists) {
            Storage::disk($folder)->put($fileName, base64_decode($fileType));
            return $fileName;
        }

        $path = trim($folder, '/') . '/' . $fileName;
        Storage::disk('public')->put($path, base64_decode($fileType));
        return $path;
    }

    public function getFile($filename, $folder)
    {
        if ($filename == '') {
            return '';
        }

        return MediaUrlHelper::resolve(
            str_contains($filename, '/') ? $filename : trim($folder, '/') . '/' . $filename
        );
    }
}
