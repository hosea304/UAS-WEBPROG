<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdministratorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminstrator = new User;
        $adminstrator->name = 'Andi';
        $adminstrator->email = 'hosea@student.umn.ac.id';
        $adminstrator->roles = 'admin';
        $adminstrator->password = bcrypt('hosea@student.umn.ac.id');
        $adminstrator->save();
        $this->command->info('Data Berhasil di Insert!');

    }
}
