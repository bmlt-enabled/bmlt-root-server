<?php
namespace App\Policies;

use App\Models\User;

trait DeniesDeactivatedUser
{
    public function before(User $user, $ability)
    {
        if ($user->isDeactivated()) {
            abort(403, 'User is deactivated.');
        }
    }
}
