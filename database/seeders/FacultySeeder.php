<?php
// database/seeders/FacultySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faculty;

class FacultySeeder extends Seeder
{
    public function run(): void
    {
        $faculties = [
            ['name' => 'Faculty of Engineering',             'code' => 'FE',  'dean_name' => 'Dr. Nguonphan Pheakdey'],
            ['name' => 'Faculty of Development Study',        'code' => 'FDS', 'dean_name' => 'Mr. Rath Sethik'],
            ['name' => 'Faculty of Science',                 'code' => 'FS',  'dean_name' => 'Asst. Prof. Meak Kamerane'],
            ['name' => 'Faculty of Social Science & Humanities', 'code' => 'FSH', 'dean_name' => 'Dr. Un Leang'],
            ['name' => 'Faculty of Education',               'code' => 'FED', 'dean_name' => 'Prof. Sok Soth'],
            ['name' => 'Institute of Foreign Languages',   'code' => 'IFL', 'dean_name' => 'Mr. Tith Mab'],
            ['name' => 'Institute for International Studies & Public Policy',   'code' => 'IISPP', 'dean_name' => 'Assoc. Prof. Dr. Neak Chandarith'],
        ];

        foreach ($faculties as $faculty) {
            Faculty::firstOrCreate(['code' => $faculty['code']], $faculty);
        }
    }
}