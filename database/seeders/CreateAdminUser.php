<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrNew(['email' => 'rstewart@capefearlandscaping.com']);
        $user->name = 'Robert Stewart';
        $user->password = Hash::make('db2003A!');
        $user->email_verified_at = now();
        $user->forceFill(['role' => 'admin']);
        $user->save();

        $this->command->info('Admin user ensured: rstewart@capefearlandscaping.com');
    }
}
