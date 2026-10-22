<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Despesa extends Model
{
    protected $fillable = ['tipo_despesa', 'descricao', 'valor', 'litros', 'preco_litro', 'posto', 'data'];

    protected function casts(): array
    {
        return ['valor' => 'decimal:2', 'litros' => 'decimal:2', 'preco_litro' => 'decimal:2', 'data' => 'date:Y-m-d'];
    }

    public function motocicleta(): BelongsTo
    {
        return $this->belongsTo(Motocicleta::class);
    }
}
