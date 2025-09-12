<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Requisicoes extends Model
{
    protected $fillable = [
        'user_id',
        'unidade_id',
        'foto',
        'relato',
        'emergencial',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }
}
