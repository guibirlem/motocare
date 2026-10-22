<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['km_limite' => 'integer', 'km_base' => 'integer', 'data_limite' => 'date:Y-m-d'];
    }

    public function motocicleta(): BelongsTo
    {
        return $this->belongsTo(Motocicleta::class);
    }
}
