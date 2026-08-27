<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

class FilamentUserLabel
{
    public static function from(?Model $record): string
    {
        if (! $record) {
            return '';
        }

        $name = trim(($record->first_name ?? '') . ' ' . ($record->last_name ?? ''));
        $email = (string) ($record->email ?? '');

        if ($name !== '' && $email !== '') {
            return $name . ' | ' . $email;
        }

        return $name !== '' ? $name : ($email !== '' ? $email : '#' . $record->getKey());
    }
}
