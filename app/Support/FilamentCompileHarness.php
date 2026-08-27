<?php

namespace App\Support;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * Minimal Livewire host so Resource::form() / table() can be compiled
 * without booting a full Filament page (used by filament:smoke and tests).
 */
class FilamentCompileHarness extends Component implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    public function form(Form $form): Form
    {
        return $form;
    }

    public function table(Table $table): Table
    {
        return $table;
    }

    protected function getTableQuery(): Builder
    {
        return (new FilamentCompileDummyModel)->newQuery();
    }

    public function render()
    {
        return '<div></div>';
    }
}
