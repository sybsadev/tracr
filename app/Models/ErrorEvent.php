<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorEvent extends Model
{
    public function error(): BelongsTo
    {
        return $this->belongsTo(Error::class);
    }
}
