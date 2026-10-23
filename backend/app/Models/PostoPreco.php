<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostoPreco extends Model
{
    protected $table = 'posto_precos';

    protected $fillable = ['combustivel', 'preco_litro'];

    protected function casts(): array
    {
        return ['preco_litro' => 'decimal:2'];
    }
}
