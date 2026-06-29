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

        $userData = [
            'phone'        => $data['phone'] ?? null,
            'branch_id'    => $data['branch_id'] ?? null,
            'biometric_id' => $data['biometric_id'] ?? null,
        ];

        if (array_key_exists('account_name', $data)) {
            $userData['name'] = $data['account_name'];
        }

        if (array_key_exists('account_email', $data)) {
            $userData['email'] = $data['account_email'];
        }

        if (! empty($data['account_password'])) {
            $userData['password'] = $data['account_password'];
        }

        $user->update($userData);

        return $user;
    }

    protected function profileData(array $data): array
    {
        unset($data['account_name'], $data['account_email'], $data['account_password']);

        return $data;
    }

    protected function profileUserSelectData(string $roleTitle, string $profileRelation, $currentUserId = null): array
    {
        $userRecords = User::whereHas('roles', function ($query) use ($roleTitle) {
                $query->where('title', $roleTitle);
            })
            ->where(function ($query) use ($profileRelation, $currentUserId) {
                $query->whereDoesntHave($profileRelation);

                if ($currentUserId) {
                    $query->orWhere('id', $currentUserId);
                }
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'branch_id', 'biometric_id']);

        return [
            'users' => $userRecords
                ->pluck('name', 'id')
                ->prepend('Select User (Optional)', ''),
            'userDetails' => $userRecords->mapWithKeys(function ($user) {
                return [
                    $user->id => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'branch_id' => $user->branch_id,
                        'biometric_id' => $user->biometric_id,
                    ],
                ];
            }),
        ];
    }
}
