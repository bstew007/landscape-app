<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Robert Stewart',
            'email' => 'rstewart@capefearlandscaping.com',
            'password' => Hash::make('db2003A!'),
            'email_verified_at' => now(),
            'role' => 'admin',
        ]);

        $this->command->info('Admin user created: rstewart@capefearlandscaping.com');
    }
}
