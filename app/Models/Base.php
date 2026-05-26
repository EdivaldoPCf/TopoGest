<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $fillable = ['nome', 'norte', 'este', 'norte_original', 'este_original', 'latitude', 'longitude', 'arquivo_zip'];

    protected $casts = [
        'norte' => 'double',
        'este' => 'double',
    ];
}