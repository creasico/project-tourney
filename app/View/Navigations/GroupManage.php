<?php

namespace App\View\Navigations;

trait GroupManage
{
    use NavigationItem;

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.manage');
    }
}
