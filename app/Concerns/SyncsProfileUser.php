<?php

namespace App\Concerns;

use App\Models\Role;
use App\Models\User;

trait SyncsProfileUser
{
    /**
     * Return the selected user or create a login account for a new profile,
     * then keep the shared account details in sync.
     */
    protected function syncProfileUser(array $data, string $roleTitle): User
    {
        $user = ! empty($data['user_id'])
            ? User::findOrFail($data['user_id'])
            : User::create([
                'name'     => $data['account_name'],
                'email'    => $data['account_email'],
                'password' => $data['account_password'],
            ]);

        $roleId = Role::where('title', $roleTitle)->value('id');

        if ($roleId) {
            $user->roles()->syncWithoutDetaching([$roleId]);
        }

        $user->update([
            'phone'        => $data['phone'] ?? null,
            'branch_id'    => $data['branch_id'] ?? null,
            'biometric_id' => $data['biometric_id'] ?? null,
        ]);

        return $user;
    }

    protected function profileData(array $data): array
    {
        unset($data['account_name'], $data['account_email'], $data['account_password']);

        return $data;
    }
}
