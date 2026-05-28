<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasta extends Model
{
    protected $fillable = [
        'nome', 
        'parent_id', 
        'tipo_servico', 
        'cliente_id', 
        'identificador_cliente', 
        'categoria_servico',
        'codigo_sigef',
        'oculto'
    ];

    protected $casts = [
        'oculto' => 'boolean',
    ];

    // Relacionamento para buscar as pastas que estão DENTRO desta
    public function subpastas()
    {
        return $this->hasMany(Pasta::class, 'parent_id');
    }

    // Relacionamento para buscar a pasta que é PAI desta (O que estava faltando!)
    public function parent()
    {
        return $this->belongsTo(Pasta::class, 'parent_id');
    }

    // Relacionamento com os arquivos
    public function arquivos()
    {
        return $this->hasMany(Arquivo::class);
    }

    // Relacionamento com as pendências
    public function pendencias()
    {
        return $this->hasMany(Pendencia::class);
    }

    // Relacionamento com o cliente
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }
}