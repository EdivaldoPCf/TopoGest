<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Arquivo extends Model
{
    protected $fillable = ['nome', 'nome_original', 'path', 'caminho', 'tamanho', 'tipo', 'pasta_id', 'oculto'];

    protected $casts = [
        'detalhes' => 'array',
        'oculto' => 'boolean',
    ];

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }

    public function getArquivoNomeAttribute()
    {
        return $this->nome ?? $this->nome_original ?? basename($this->path ?? $this->caminho);
    }
}