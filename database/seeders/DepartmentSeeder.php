<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            'Cell Ministry',
            'Zonal Operations',
            'Church Admin / Pioneering & Visitation',
            'Rhapsody of Realities',
            'Healing School',
            'Finance',
            'TV Production',
            'Ministry Material',
            'Foundation School & First Timer Ministries',
            'Love World Music Department',
            'Global Mission / HR / Admin',
            'Children & Women Ministries',
            'LMMS, LXP, Ministry Programs, Bibles Partnership',
            'LW USA, LTM / Radio Brands, Inner City Missions',
            'Follow Up Department',
            'Prayer',
            'Evangelism',
            'Sceptre',
        ];

        $groups = [
            'Lekki', 'Victoria Island', 'Alasia', 'Ikoyi Group 1', 'Ikoyi Group 2', 'Ajiwe', 'Obalende', 'Mobil',
            'Chevron', 'Onishon', 'Ajah', 'Kajola', 'Lekki Phase 1', 'Epe', 'Lagos Island', 'Youth Group',
            'Owode Badore', 'Free Trade Zone', 'Eputu', 'Ogombo', 'Abijo', 'Tedo',
        ];

        foreach ($departments as $name) {
            Department::firstOrCreate(['name' => $name, 'type' => 'department']);
        }

        foreach ($groups as $name) {
            Department::firstOrCreate(['name' => $name, 'type' => 'group']);
        }
    }
}
