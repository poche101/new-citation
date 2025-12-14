<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'name',
        'unit',
        'designation',
        'kingschat',
        'phone',
        'group_name',
        'period_from',
        'period_to',
        'description',
    ];
}
