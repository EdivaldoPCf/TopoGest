<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recibo extends Model
{
    protected $fillable = [
        'contrato_id',
        'valor_total',
        'valor_recebido',
        'descricao',
        'hash',
        'pdf_path',
    ];

    public function contrato()
    {
        return $this->belongsTo(Contrato::class);
    }
}
