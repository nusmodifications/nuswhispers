<?php

namespace NUSWhispers\Policies;

use NUSWhispers\Models\User;

class UserPolicy
{
    /**
     * Determine if the given user can manage users.
     *
     * @param \NUSWhispers\Models\User $user
     *
     * @return bool
     */
    public function manage(User $user): bool
    {
        return $user->role === 'Administrator';
    }

    /**
     * Determine if the given user can delete users.
     *
     * @param \NUSWhispers\Models\User $user
     * @param \NUSWhispers\Models\User $target
     *
     * @return bool
     */
    public function delete(User $user, User $target): bool
    {
        return $user->role === 'Administrator' &&
            $user->getKey() !== $target->getKey();
    }
}
