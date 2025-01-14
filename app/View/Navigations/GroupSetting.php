<?php

namespace App\View\Navigations;

trait GroupSetting
{
    use NavigationItem;

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.settings');
    }
}
