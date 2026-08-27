<?php

namespace App\Console\Commands;

use App\Support\FilamentCompileHarness;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\File;

class FilamentSmokeCommand extends Command
{
    protected $signature = 'filament:smoke {--path=app/Filament}';

    protected $description = 'Compile every Filament resource form and table and report TypeErrors / build failures';

    public function handle(): int
    {
        $base = base_path($this->option('path'));
        if (! is_dir($base)) {
            $this->error("Path not found: {$base}");

            return self::FAILURE;
        }

        $ok = 0;
        $warn = 0;
        $fail = 0;
        $livewire = new FilamentCompileHarness;

        foreach (File::allFiles($base) as $file) {
            $class = $this->classFromPath($file->getPathname());
            if (! $class || ! class_exists($class) || ! is_subclass_of($class, Resource::class)) {
                continue;
            }
            if ((new \ReflectionClass($class))->isAbstract()) {
                continue;
            }

            try {
                $model = $class::getModel();
                $class::form(Form::make($livewire)->model($model));
                $class::table(Table::make($livewire)->query($model::query()));
                $this->line("<info>OK</info>  {$class}");
                $ok++;
            } catch (QueryException $e) {
                $this->line('<comment>WARN</comment> '.$class.' — query during build: '.mb_substr($e->getMessage(), 0, 140));
                $warn++;
            } catch (\Throwable $e) {
                $this->line('<error>FAIL</error> '.$class.' — '.$e::class.': '.$e->getMessage());
                $fail++;
            }
        }

        $this->newLine();
        $this->info("OK={$ok}  WARN={$warn}  FAIL={$fail}");

        return $fail === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function classFromPath(string $path): ?string
    {
        $relative = str_replace(app_path().DIRECTORY_SEPARATOR, '', $path);
        $relative = str_replace(['/', '\\'], '\\', $relative);
        $relative = preg_replace('/\.php$/', '', $relative);

        return $relative ? 'App\\'.$relative : null;
    }
}
