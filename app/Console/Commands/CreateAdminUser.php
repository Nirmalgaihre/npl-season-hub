<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin';

    protected $description = 'Create the initial administrator from environment settings';

    public function handle(): int
    {
        $username = (string) config('admin.username');
        $email = (string) config('admin.email');
$name = (string) env('ADMIN_NAME', 'NPL Administrator');        $password = config('admin.password');

        $validator = Validator::make([
            'username' => $username,
            'email' => $email,
            'name' => $name,
            'password' => $password,
        ], [
            'username' => ['required', 'alpha_dash', 'min:3', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:12'],
        ]);

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $existing = User::query()
            ->where('username', $username)
            ->orWhere('email', $email)
            ->first();

        if ($existing) {
            $this->error(
                'An account with this username or email already exists. No changes were made.'
            );

            return self::FAILURE;
        }

        $user = new User();
        $user->name = $name;
        $user->username = $username;
        $user->email = $email;
        $user->password = Hash::make($password);
        $user->role = 'admin';
        $user->save();

        $this->info("Administrator '{$username}' created successfully.");

        return self::SUCCESS;
    }
}