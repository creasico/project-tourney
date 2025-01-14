<?php

namespace App\View\Navigations;

/**
 * @mixin \Filament\Resources\Resource
 */
trait NavigationItem
{
    public static function getModelLabel(): string
    {
        return trans((string) self::getTranslationKey()->append('.singular'));
    }

    public static function getModelPluralLabel(): string
    {
        return trans((string) self::getTranslationKey()->append('.plural'));
    }

    protected static function getTranslationKey()
    {
        return str(self::class)
            ->classBasename()
            ->beforeLast('Resource')
            ->slug();
    }
}
