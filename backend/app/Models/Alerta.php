<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $guarded = ['id'];

    public function motocicleta(): BelongsTo
    {
        return $this->belongsTo(Motocicleta::class);
    }
}
