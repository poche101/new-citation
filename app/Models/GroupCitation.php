<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupCitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'kingschat',
        'unit',
        'designation',
        'title',
        'group_id',
        'period_from',
        'period_to',
        'description',
    ];

    public function group()
    {
        return $this->belongsTo(Group::class);
    }
}
