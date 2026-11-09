<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class DepartmentCitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'fullname',
        'unit',
        'designation',
        'kingschat',
        'phone',
        'department',
        'period',
        'citation',              // the personal citation
        'unit_citation',         // the unit / department citation
        'personal_report_summary',
        'personal_report_detail',
        'unit_report_summary',
        'unit_report_detail',
        'approved',
        'admin_comment',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    public function attachments(): MorphMany
    {
        return $this->morphMany(CitationAttachment::class, 'attachable');
    }
}
