<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;

class Notificacao extends Model
{
    use Loggable;

    protected $fillable = ['titulo', 'descricao', 'servico_id', 'user_id', 'lida'];
}