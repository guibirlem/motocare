<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Posto extends Model
{
    protected $fillable = ['nome'];

    public function precos(): HasMany
    {
        return $this->hasMany(PostoPreco::class);
    }
}
