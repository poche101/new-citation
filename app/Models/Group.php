<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'kingschat',
        'unit',
        'designation',
        'group_name',
        'title',
        'period_from',
        'period_to',
        'description',
    ];
}
