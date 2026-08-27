<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

class StripUnknownModelColumns
{
    /** @var array<string, array<string, true>|null> */
    private static array $columnCache = [];

    public static function register(): void
    {
        Model::saving(function (Model $model) {
            self::strip($model);
        });

        // creating/updating fire AFTER saving and after model observers, so strip again
        // immediately before INSERT/UPDATE (wildcard listeners run last).
        Event::listen('eloquent.creating: *', function ($event, $payload) {
            $model = is_array($payload) ? ($payload[0] ?? null) : $payload;
            if ($model instanceof Model) {
                self::strip($model);
            }
        });
        Event::listen('eloquent.updating: *', function ($event, $payload) {
            $model = is_array($payload) ? ($payload[0] ?? null) : $payload;
            if ($model instanceof Model) {
                self::strip($model);
            }
        });
    }

    public static function strip(Model $model): void
    {
        try {
            $table = $model->getTable();
            $connection = $model->getConnectionName() ?: config('database.default');
            $cacheKey = $connection . '.' . $table;

            if (! array_key_exists($cacheKey, self::$columnCache)) {
                $schema = Schema::connection($connection);
                if (! $schema->hasTable($table)) {
                    self::$columnCache[$cacheKey] = null;
                } else {
                    self::$columnCache[$cacheKey] = array_flip($schema->getColumnListing($table));
                }
            }

            $columns = self::$columnCache[$cacheKey];
            if ($columns === null) {
                return;
            }

            foreach (array_keys($model->getAttributes()) as $key) {
                if (! isset($columns[$key])) {
                    $model->offsetUnset($key);
                }
            }
        } catch (\Throwable) {
            // Schema lookup can fail if the connection is down; do not block the save.
        }
    }

    public static function resetCache(): void
    {
        self::$columnCache = [];
    }
}
