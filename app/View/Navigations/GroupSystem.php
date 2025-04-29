<?php

declare(strict_types=1);

namespace App\View\Navigations;

trait GroupSystem
{
    use NavigationItem;

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.system');
    }
}
