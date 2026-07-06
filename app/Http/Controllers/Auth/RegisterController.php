<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    use RegistersUsers;

    private const REGISTRATION_ROLES = [
        'Teacher',
        'Staff',
        'Student',
        'Parent',
    ];

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $branches = Branch::where('status', 'active')
            ->orderBy('name')
            ->pluck('name', 'id');

        $roles = Role::whereIn('title', self::REGISTRATION_ROLES)
            ->orderBy('title')
            ->pluck('title', 'id');

        return view('auth.register', compact('branches', 'roles'));
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone'     => ['required', 'string', 'max:20'],
            'branch_id' => ['required', 'exists:branches,id'],
            'role_id'   => [
                'required',
                Rule::exists('roles', 'id')->where(fn ($query) => $query
                    ->whereIn('title', self::REGISTRATION_ROLES)
                    ->whereNull('deleted_at')),
            ],
            'password'  => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $user = User::create([
            'name'      => $data['name'],
            'email'     => $data['email'],
            'phone'     => $data['phone'],
            'branch_id' => $data['branch_id'],
            'password'  => Hash::make($data['password']),
        ]);

        $user->roles()->sync([$data['role_id']]);

        return $user;
    }
}
