<?php

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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

        $menu->addChild(
            'Tags',
            Options::create()
                ->route('app_tag_index')
                ->icon('tag')
                ->build()
        );
    }
}
