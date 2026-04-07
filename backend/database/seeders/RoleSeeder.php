<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrateur de l’organisation',
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'developer'],
            [
                'name' => 'Developer',
                'description' => 'Développeur membre de l’équipe',
            ]
        );

        Role::updateOrCreate(
            ['slug' => 'client'],
            [
                'name' => 'Client',
                'description' => 'Client externe',
            ]
        );
    }
}