<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'krema:create-superadmin 
                            {--name= : Name of the super admin}
                            {--email= : Email address}
                            {--password= : Password (min 8 characters)}
                            {--phone= : Phone number (optional)}';

    protected $description = 'Create a super admin user for the system';

    public function handle(): int
    {
        $this->info('Creating Super Admin User...');
        $this->newLine();

        // Get name
        $name = $this->option('name');
        if (!$name) {
            $name = $this->ask('Enter name', 'Super Admin');
        }

        // Get email
        $email = $this->option('email');
        if (!$email) {
            $email = $this->ask('Enter email address');
        }
        $email = strtolower(trim($email));

        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:users,email'
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('email')) {
                $error = $validator->errors()->first('email');
                if (str_contains($error, 'taken')) {
                    $this->warn('User with this email already exists.');
                    if ($this->confirm('Do you want to update this user to super admin?', false)) {
                        $user = User::where('email', $email)->first();
                        $user->update([
                            'is_superadmin' => true,
                            'is_active' => true,
                        ]);
                        $this->info("✓ User {$email} has been updated to super admin.");
                        return self::SUCCESS;
                    }
                    return self::FAILURE;
                }
            }
            $this->error('Validation failed: ' . $validator->errors()->first());
            return self::FAILURE;
        }

        // Get password
        $password = $this->option('password');
        if (!$password) {
            $password = $this->secret('Enter password (min 8 characters)');
            $confirmPassword = $this->secret('Confirm password');
            
            if ($password !== $confirmPassword) {
                $this->error('Passwords do not match!');
                return self::FAILURE;
            }
        }

        // Validate password
        if (strlen($password) < 8) {
            $this->error('Password must be at least 8 characters long!');
            return self::FAILURE;
        }

        // Get phone (optional)
        $phone = $this->option('phone');
        if (!$phone && $this->confirm('Do you want to add a phone number?', false)) {
            $phone = $this->ask('Enter phone number');
        }

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'phone' => $phone,
                'is_superadmin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            $this->newLine();
            $this->info('✓ Super Admin created successfully!');
            $this->newLine();
            $this->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->id],
                    ['Name', $user->name],
                    ['Email', $user->email],
                    ['Phone', $user->phone ?? 'N/A'],
                    ['Super Admin', $user->is_superadmin ? 'Yes' : 'No'],
                    ['Active', $user->is_active ? 'Yes' : 'No'],
                ]
            );
            $this->newLine();
            $this->warn('Please save these credentials securely!');
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to create super admin: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}

