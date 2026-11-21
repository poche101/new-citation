<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Anything a person submits: a personal annual report, a unit annual report,
 * a personal citation or a unit/department citation (see config/citation.php).
 */
class Report extends Model
{
    protected $fillable = [
        'type',
        'title', 'name', 'unit', 'designation', 'kingschat', 'phone',
        'entity_type', 'entity_name', 'entity_id',
        'period_from', 'period_to',
        'executive_summary', 'detailed_report', 'citation',
        'approved', 'admin_comment',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to'   => 'date',
        'approved'    => 'boolean',
    ];

    protected static function booted(): void
    {
        // Deleting a report also deletes its uploaded files (each attachment
        // removes its own file from the disk).
        static::deleting(function (self $report) {
            $report->attachments->each->delete();
        });
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(CitationAttachment::class, 'attachable');
    }

    /* ---------- Types ---------- */

    /** All four types: key => settings (from config/citation.php). */
    public static function types(): array
    {
        return config('citation.reports');
    }

    public static function typeKeys(): array
    {
        return array_keys(self::types());
    }

    /** Settings for one type, or null if the key is unknown. */
    public static function typeConfig(string $key): ?array
    {
        return self::types()[$key] ?? null;
    }

    /** The type key for a URL slug such as "personal-report". */
    public static function keyForSlug(string $slug): ?string
    {
        foreach (self::types() as $key => $config) {
            if ($config['slug'] === $slug) {
                return $key;
            }
        }

        return null;
    }

    public function config(): array
    {
        return self::typeConfig($this->type) ?? [
            'slug' => '', 'kind' => 'citation', 'scope' => 'personal',
            'label' => $this->type, 'short' => $this->type, 'description' => '', 'includes' => [],
        ];
    }

    public function label(): string
    {
        return $this->config()['label'];
    }

    public function isReport(): bool
    {
        return $this->config()['kind'] === 'report';
    }

    /* ---------- Display helpers ---------- */

    public function fullName(): string
    {
        return trim(($this->title ? $this->title . ' ' : '') . $this->name);
    }

    public function entityLabel(): string
    {
        return $this->entity_type === 'group' ? 'Group' : 'Department';
    }

    /** "1 Jan 2026 to 30 Nov 2026", or "Not given". */
    public function periodText(): string
    {
        if (! $this->period_from && ! $this->period_to) {
            return 'Not given';
        }

        $from = $this->period_from?->format('j M Y') ?? '?';
        $to   = $this->period_to?->format('j M Y') ?? '?';

        return "{$from} to {$to}";
    }

    /** The main text shown in lists: the citation, or (for a report) its uploaded file names. */
    public function preview(): ?string
    {
        if (! $this->isReport()) {
            return $this->citation;
        }

        // Reports submitted before the text boxes were removed still have a summary
        if (filled($this->executive_summary)) {
            return $this->executive_summary;
        }

        $names = $this->attachments->pluck('original_name')->implode(', ');

        return $names !== '' ? 'File: ' . $names : null;
    }

    /* ---------- Admin list filters ---------- */

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $type && in_array($type, self::typeKeys(), true)
            ? $query->where('type', $type)
            : $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);

        if ($term === '') {
            return $query;
        }

        $like = '%' . $term . '%';

        return $query->where(function (Builder $q) use ($like) {
            foreach (['name', 'unit', 'designation', 'kingschat', 'phone', 'entity_name'] as $column) {
                $q->orWhere($column, 'like', $like);
            }
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'approved' => $query->where('approved', true),
            'pending'  => $query->where('approved', false),
            default    => $query,
        };
    }

    /** Fields shown to admins as readable sections: [heading, text]. */
    public function sections(): array
    {
        if ($this->isReport()) {
            // Only older reports have typed text; new ones are an uploaded file
            return array_values(array_filter([
                filled($this->executive_summary) ? ['Executive summary', $this->executive_summary] : null,
                filled($this->detailed_report) ? ['Detailed report', $this->detailed_report] : null,
            ]));
        }

        return [['Citation', $this->citation]];
    }
}
