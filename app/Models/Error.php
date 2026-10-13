<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Error extends Model
{
    public function app(): BelongsTo
    {
        return $this->belongsTo(App::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(ErrorEvent::class);
    }
}
