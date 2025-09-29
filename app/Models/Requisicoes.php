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
        'status',
        'nota_atendimento',
        'nota_aprovacao',
        'gestor_id',
        'aparelhos_id',
        'prioridade',
        'prazo_conclusao',
        'custo_estimado',
        'nota_cliente',
        'assinatura_gerente',
    ];

    protected $casts = [
        'prazo_conclusao' => 'datetime',
        'custo_estimado'  => 'decimal:2',
    ];
    


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Academia::class);
    }

    public function gestor()
{
    return $this->belongsTo(User::class, 'gestor_id');
}

public function aparelho(): BelongsTo
    {
        return $this->belongsTo(Aparelho::class);
    }

}
