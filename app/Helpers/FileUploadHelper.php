<?php

namespace App\Helpers;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\File;

class FileUploadHelper
{
    public function uploadFile($file, $folder)
    {
        // upload file
        $fileName = "";
        if ($file) {
            $file_64 = $file; //your base64 encoded data
            $extension = explode('/', explode(':', substr($file_64, 0, strpos($file_64, ';')))[1])[1]; // .jpg .png .pdf
            $replace = substr($file_64, 0, strpos($file_64, ',')+1);
            // find substring fro replace here eg: data:image/png;base64,
            $fileType = str_replace($replace, '', $file_64);
            $fileType = str_replace(' ', '+', $fileType);
            $fileName = Str::random(10).'.'.$extension;
            Storage::disk($folder)->put($fileName, base64_decode($fileType));
        }

        return $fileName;
    }

    public function getFile($filename, $folder)
    {
        if ($filename == '') {
            return '';
        }
        return Storage::disk($folder)->url($filename);
    }
}
