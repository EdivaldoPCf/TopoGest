<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marco extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'credencial',
        'tipo',
        'numero',
        'imovel',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}