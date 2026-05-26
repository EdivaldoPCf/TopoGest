<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendencia extends Model
{
    protected $fillable = ['titulo', 'descricao', 'status', 'pasta_id'];

    public function pasta()
    {
        return $this->belongsTo(Pasta::class);
    }
}