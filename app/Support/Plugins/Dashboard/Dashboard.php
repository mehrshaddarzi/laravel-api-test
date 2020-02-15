<?php

namespace Blegrator\Support\Plugins\Dashboard;

use Blegrator\Plugins\Plugin;
use Blegrator\Support\Sidebar\Item;

class Dashboard extends Plugin
{
    public function sidebar()
    {
        return Item::create(__('Dashboard'))
            ->route('dashboard')
            ->icon('fas fa-home')
            ->active("/");
    }
}
