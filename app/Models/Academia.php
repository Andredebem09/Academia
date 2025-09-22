<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Academia extends Model
{
    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'cep',
        'estado',
        'cidade',
        'bairro',
        'rua',
    ];

    public function users()
{
    return $this->belongsToMany(User::class, 'gestor_academia', 'academias_id', 'user_id');
}

}
