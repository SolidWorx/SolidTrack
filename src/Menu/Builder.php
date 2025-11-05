<?php

namespace App\Menu;

use Knp\Menu\MenuItem;
use SolidWorx\Platform\PlatformBundle\Attributes\Menu\MenuBuilder;
use SolidWorx\Platform\PlatformBundle\Menu\Options;

final class Builder
{
    #[MenuBuilder(name: 'sidebar')]
    public function sidebar(MenuItem $menu): void
    {
        $menu->addChild(
            'Dashboard',
            Options::create()
            ->route('dashboard')
            ->icon('home')
            ->build()
        );

        $menu->addChild(
            'Activities',
            Options::create()
            ->route('app_activity_index')
            ->icon('chart-infographic')
            ->build()
        );

        $menu->addChild(
            'Clients',
            Options::create()
            ->route('app_client_index')
            ->icon('users-group')
            ->build()
        );

        $menu->addChild(
            'Projects',
            Options::create()
            ->route('app_project_index')
            ->icon('mist')
            ->build()
        );
    }
}
