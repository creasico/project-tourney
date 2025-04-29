<?php

declare(strict_types=1);

namespace App\View\Navigations;

use Filament\Pages\Page;
use Filament\Resources\Resource;

/**
 * @mixin \Filament\Resources\Resource
 */
trait NavigationItem
{
    public static function getModelLabel(): string
    {
        return is_subclass_of(self::class, Resource::class)
            ? trans((string) self::getTranslationKey()->append('.singular'))
            : '';
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

    public static function getNavigationLabel(): string
    {
        if (is_subclass_of(self::class, Resource::class)) {
            return self::getModelPluralLabel();
        }

        if (is_subclass_of(self::class, Page::class)) {
            return trans(
                (string) str(self::class)->classBasename()->slug()->append('.navigation_label')
            );
        }

        return '';
    }
}
