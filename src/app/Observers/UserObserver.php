<?php

namespace App\Observers;

use App\Models\User\User;
use App\Services\User\UserService;

class UserObserver {
    /**
     * @var UserService
     */
    private $_userService;

    public function __construct(UserService $userService)
    {
        $this->_userService = $userService;
    }

    public function creating(User $user) : void
    {
    }
}
