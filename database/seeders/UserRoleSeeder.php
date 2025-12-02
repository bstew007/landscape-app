<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserRoleSeeder extends Seeder
{
    /**
     * Seed test users with different roles.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_MANAGER,
            ],
            [
                'name' => 'Foreman User',
                'email' => 'foreman@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_FOREMAN,
            ],
            [
                'name' => 'Crew User',
                'email' => 'crew@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_CREW,
            ],
            [
                'name' => 'Office User',
                'email' => 'office@example.com',
                'password' => Hash::make('password'),
                'role' => User::ROLE_OFFICE,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        $this->command->info('Test users created with roles: admin, manager, foreman, crew, office');
        $this->command->info('All passwords are: password');
    }
}
