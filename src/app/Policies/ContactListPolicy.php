<?php

namespace App\Policies;

use App\Models\ContactList;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ContactListPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ContactList $contactList): Response
    {
        return $contactList->isUserOwner($user)
                    ? Response::allow()
                    : Response::denyWithStatus(403, 'Non autorizzato.');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ContactList $contactList): Response
    {
         return $contactList->isUserOwner($user)
                    ? Response::allow()
                    : Response::denyWithStatus(403, 'Non autorizzato.');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ContactList $contactList): Response
    {
        return $contactList->isUserOwner($user)
                    ? Response::allow()
                    : Response::denyWithStatus(403, 'Non autorizzato.');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ContactList $contactList): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ContactList $contactList): bool
    {
        return false;
    }
}
