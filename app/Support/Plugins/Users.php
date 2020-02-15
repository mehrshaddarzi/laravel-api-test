<?php

namespace Blegrator\Support\Plugins;

use Blegrator\Plugins\Plugin;
use Blegrator\Support\Sidebar\Item;

class Users extends Plugin
{
    public function sidebar()
    {
        return Item::create(__('Users'))
            ->route('users.index')
            ->icon('fas fa-users')
            ->active("users*")
            ->permissions('users.manage');
    }
}
