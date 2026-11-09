<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class GroupCitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'name', 'unit', 'designation', 'kingschat', 'phone',
        'group_name', 'period_from', 'period_to',
        'description',           // the personal citation
        'unit_citation',         // the unit / group citation
        'personal_report_summary',
        'personal_report_detail',
        'unit_report_summary',
        'unit_report_detail',
        'admin_comment', 'approved',
    ];

    // Cast period fields to Carbon
    protected $casts = [
        'period_from' => 'datetime',
        'period_to' => 'datetime',
    ];

    public function attachments(): MorphMany
    {
        return $this->morphMany(CitationAttachment::class, 'attachable');
    }
}
