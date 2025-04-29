<?php

declare(strict_types=1);

namespace App\View\Navigations;

trait GroupManage
{
    use NavigationItem;

    public static function getNavigationGroup(): ?string
    {
        return trans('navigation.manage');
    }
}
