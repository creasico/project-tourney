<?php

namespace App\Filament\Pages;

use App\View\Navigations\GroupSystem;
use Filament\Forms\Components;
use Filament\Pages\Page;

class Settings extends Page
{
    use GroupSystem;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $view = 'filament.pages.settings';

    public function getFormSchema(): array
    {
        return [
            Components\Section::make(trans('continent.section.info_heading'))
                ->aside()
                ->schema([
                    Components\TextInput::make('name')
                        ->label(trans('continent.field.name')),
                ]),
        ];
    }
}
