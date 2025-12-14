<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Departments
        $departments = [
            ['name' => 'Cell Ministry', 'description' => 'Click to submit your citation'],
            ['name' => 'Zonal Operations', 'description' => 'Click to submit your citation'],
            ['name' => 'Church Admin / Pioneering & Visitation', 'description' => 'Click to submit your citation'],
            ['name' => 'Rhapsody of Realities', 'description' => 'Click to submit your citation'],
            ['name' => 'Healing School', 'description' => 'Click to submit your citation'],
            ['name' => 'Finance', 'description' => 'Click to submit your citation'],
            ['name' => 'TV Production', 'description' => 'Click to submit your citation'],
            ['name' => 'Ministry Material', 'description' => 'Click to submit your citation'],
            ['name' => 'Foundation School & First Timer Ministries', 'description' => 'Click to submit your citation'],
            ['name' => 'Love World Music Department', 'description' => 'Click to submit your citation'],
            ['name' => 'Global Mission / HR / Admin', 'description' => 'Click to submit your citation'],
            ['name' => 'Children & Women Ministries', 'description' => 'Click to submit your citation'],
            ['name' => 'LMMS, LXP, Ministry Programs, Bibles Partnership', 'description' => 'Click to submit your citation'],
            ['name' => 'LW USA, LTM / Radio Brands, Inner City Missions', 'description' => 'Click to submit your citation'],
            ['name' => 'Follow Up Department', 'description' => 'Click to submit your citation'],
            ['name' => 'Prayer', 'description' => 'Click to submit your citation'],
            ['name' => 'Evangelism', 'description' => 'Click to submit your citation'],
            ['name' => 'Sceptre', 'description' => 'Click to submit your citation'],
        ];

        // Groups
        $groups = [
            'Lekki', 'Victoria Island', 'Alasia', 'Ikoyi Group 1', 'Ikoyi Group 2', 'Ajiwe', 'Obalende', 'Mobil',
            'Chevron', 'Onishon', 'Ajah', 'Kajola', 'Lekki Phase 1', 'Epe', 'Lagos Island', 'Youth Group',
            'Owode Badore', 'Free Trade Zone', 'Eputu', 'Ogombo', 'Abijo', 'Tedo'
        ];

        return view('home', compact('departments', 'groups'));
    }
}
