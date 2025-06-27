<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources'],
            ['name' => 'Finance and Administration'],
            ['name' => 'Marketing'],
            ['name' => 'Sales'],
            ['name' => 'Software Development'],
            ['name' => 'Infrastructure'],
            ['name' => 'Training'],
        ];

        DB::table('departments')->insert($departments);
    }
}
