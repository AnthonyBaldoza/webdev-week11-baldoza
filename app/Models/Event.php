<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Event extends Model
{
    protected $fillable = ['venue_id', 'title', 'starts_at', 'description', 'details'];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'details'   => 'array',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(Participant::class)
            ->withPivot('status')
            ->withTimestamps();
    }

    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    // Local scopes

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now());
    }

    public function scopeSoonest(Builder $query): Builder
    {
        return $query->orderBy('starts_at');
    }

    public function scopeAtVenue(Builder $query, int $venueId): Builder
    {
        return $query->where('venue_id', $venueId);
    }

    public function scopeWithDressCode(Builder $query, string $code): Builder
    {
        return $query->where('details->dress_code', $code);
    }
}