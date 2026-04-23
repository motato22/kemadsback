<?php

namespace App\Policies\Advertising;

use App\Models\User;
use App\Models\Advertising\AdvTablet;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdvTabletPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_adv::tablet');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('view_adv::tablet');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_adv::tablet');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('update_adv::tablet');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('delete_adv::tablet');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_adv::tablet');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('force_delete_adv::tablet');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_adv::tablet');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('restore_adv::tablet');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_adv::tablet');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, AdvTablet $advTablet): bool
    {
        return $user->can('replicate_adv::tablet');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_adv::tablet');
    }
}
