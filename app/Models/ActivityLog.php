<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'acao', 'detalhes', 'ip_address'];

    protected $casts = [
        'detalhes' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getArquivoNomeAttribute()
    {
        return $this->detalhes['arquivo_nome'] ?? null;
    }
}