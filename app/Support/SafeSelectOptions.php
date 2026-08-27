<?php

namespace App\Support;

use Illuminate\Support\Collection;

class SafeSelectOptions
{
    /**
     * Run a select-options query without letting a missing table/column 500 the admin page.
     *
     * @return array<int|string, mixed>
     */
    public static function get(callable $callback): array
    {
        try {
            $result = $callback();
            if ($result instanceof Collection) {
                return $result->all();
            }
            if (is_array($result)) {
                return $result;
            }
            if ($result instanceof \Illuminate\Contracts\Support\Arrayable) {
                return $result->toArray();
            }

            return [];
        } catch (\Throwable) {
            return [];
        }
    }
}
