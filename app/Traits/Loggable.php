<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(fn($model) => $model->recordActivity('criacao'));
        static::updated(fn($model) => $model->recordActivity('edicao'));
        static::deleted(fn($model) => $model->recordActivity('exclusao'));
    }

    protected function recordActivity($acao)
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'acao' => $acao . '_' . strtolower(class_basename($this)),
            'detalhes' => json_encode([
                'id' => $this->id,
                'dados' => $this->getAttributes(),
                'ip' => request()->ip()
            ]),
            'ip_address' => request()->ip(),
        ]);
    }
}