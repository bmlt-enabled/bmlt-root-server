<?php
namespace App\Policies;

use App\Models\User;

trait DeniesDisabledUser
{
    public function before(User $user, $ability)
    {
        if ($user->isDisabled()) {
            return false;
        }
    }
}
