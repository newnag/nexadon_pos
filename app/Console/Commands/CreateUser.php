<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating a new user...');

        // 1. Get Name
        $name = $this->ask('Full Name');
        while (empty($name)) {
            $this->error('Name is required.');
            $name = $this->ask('Full Name');
        }

        // 2. Get Email
        $email = $this->ask('Email Address');
        $validator = Validator::make(['email' => $email], [
            'email' => ['required', 'email', 'unique:users,email'],
        ]);

        while ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }
            $email = $this->ask('Email Address');
            $validator = Validator::make(['email' => $email], [
                'email' => ['required', 'email', 'unique:users,email'],
            ]);
        }

        // 3. Get Password
        $password = $this->secret('Password');
        while (empty($password) || strlen($password) < 8) {
            $this->error('Password is required and must be at least 8 characters.');
            $password = $this->secret('Password');
        }
        
        $confirmPassword = $this->secret('Confirm Password');
        while ($password !== $confirmPassword) {
            $this->error('Passwords do not match.');
            $password = $this->secret('Password');
            $confirmPassword = $this->secret('Confirm Password');
        }

        // 4. Get Role
        $roles = Role::pluck('name')->toArray();
        if (empty($roles)) {
            $this->error('No roles found in the database. Please seed the database first.');
            return 1;
        }

        $roleName = $this->choice('Select Role', $roles);
        $role = Role::where('name', $roleName)->first();

        // Create User
        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role_id' => $role->id,
            ]);

            $this->info("User '{$user->name}' ({$user->email}) created successfully with role '{$role->name}'.");
            return 0;

        } catch (\Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
            return 1;
        }
    }
}
