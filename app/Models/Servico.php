<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servico extends Model
{
    // Adicione 'nome_imovel' e os outros campos aqui
    protected $fillable = [
        'nome_imovel', 
        'pasta_id', 
        'status', 
        'cliente_id',
        'tipo_servico',
        'valor'
    ];

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }
}