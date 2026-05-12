<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'status',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'owner_id' => 'integer',
            'status' => ProjectStatus::class,
            'due_date' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
