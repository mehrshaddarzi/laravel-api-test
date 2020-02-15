<?php

namespace Blegrator\Support\Plugins\Dashboard\Widgets;

use Blegrator\Plugins\Widget;
use Blegrator\User;

class UserActions extends Widget
{
    /**
     * UserActions constructor.
     */
    public function __construct()
    {
        $this->permissions(function (User $user) {
            return $user->hasRole('Customer');
        });
    }

    /**
     * {@inheritdoc}
     */
    public function render()
    {
        return view('plugins.dashboard.widgets.user-actions');
    }
}
