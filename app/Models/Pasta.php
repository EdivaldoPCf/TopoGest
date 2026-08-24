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

    // Relacionamento com contratos gerados para este imóvel
    public function contratos()
    {
        return $this->hasMany(\App\Models\Contrato::class);
    }

    // Clientes adicionais (caso haja ODS de múltiplos proprietários)
    public function clientesSecundarios()
    {
        return $this->belongsToMany(User::class, 'pasta_user', 'pasta_id', 'user_id');
    }

    public function scopeOwnedBy($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('cliente_id', $userId)
              ->orWhereHas('clientesSecundarios', function($subQ) use ($userId) {
                  $subQ->where('users.id', $userId);
              });
        });
    }

    public function isOwner($userId)
    {
        if ($this->cliente_id == $userId) {
            return true;
        }
        return $this->clientesSecundarios()->where('users.id', $userId)->exists();
    }
}