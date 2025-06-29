<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManifestCrew extends Model
{
    protected $fillable = [
        'manifest_id',
        'crew_id'
    ];

    // Relationships
    public function manifest(): BelongsTo
    {
        return $this->belongsTo(Manifest::class);
    }

    public function crew(): BelongsTo
    {
        return $this->belongsTo(Crew::class);
    }
}
