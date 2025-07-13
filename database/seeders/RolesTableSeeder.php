<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
//use Illuminate\Support\Facades\Hash;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Admin', 'description' => 'Full access. Everything—create, edit, delete any record'],
            ['name' => 'Inspector', 'description' => 'Create/edit plantings and inspections, but not delete others'],
            ['name' => 'Verifier', 'description' => 'Limited access, Mark plantings as verified'],
            ['name' => 'Viewer', 'description' => 'View only'],
        ]);
    }
}
