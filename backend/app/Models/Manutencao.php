<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Manutencao extends Model
{
    protected $table = 'manutencoes';

    protected $fillable = ['tipo_servico', 'descricao', 'valor', 'status', 'data', 'km_servico'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2', 'data' => 'date:Y-m-d', 'km_servico' => 'integer'];
    }

    public function motocicleta(): BelongsTo
    {
        return $this->belongsTo(Motocicleta::class);
    }
}
