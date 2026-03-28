<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'nom'            => 'Admin',
            'prenom'         => 'System',
            'email'          => 'admin@bank.ma',
            'password'       => Hash::make('password'),
            'date_naissance' => '1985-03-15',
            'role'           => 'admin',
        ]);

        User::create([
            'nom'            => 'Alami',
            'prenom'         => 'Youssef',
            'email'          => 'youssef@bank.ma',
            'password'       => Hash::make('password'),
            'date_naissance' => '1980-06-20',
            'role'           => 'customer',
        ]);

        User::create([
            'nom'            => 'Alami',
            'prenom'         => 'Amine',
            'email'          => 'amine@bank.ma',
            'password'       => Hash::make('password'),
            'date_naissance' => '2012-09-10',
            'role'           => 'customer',
        ]);

        User::create([
            'nom'            => 'Benali',
            'prenom'         => 'Sara',
            'email'          => 'sara@bank.ma',
            'password'       => Hash::make('password'),
            'date_naissance' => '1992-11-05',
            'role'           => 'customer',
        ]);

        User::create([
            'nom'            => 'Chraibi',
            'prenom'         => 'Omar',
            'email'          => 'omar@bank.ma',
            'password'       => Hash::make('password'),
            'date_naissance' => '1988-04-30',
            'role'           => 'customer',
        ]);
    }
}